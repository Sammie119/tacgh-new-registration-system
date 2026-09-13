<?php

namespace App\Services\Admin;

use App\Models\Admin\OnlinePayment;
use App\Models\Admin\Promotion;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use Illuminate\Support\Str;

class PaymentService
{
    public function makePayment(array $data)
    {
        if (isset($data['batch'])) {
            $reg = BatchLog::find($data['batch_id']);
            $owed = $this->batchOutstandingBalance($data['batch_id']);
            $mata_name = "Batch Payment";
        } else {
            $reg = RegistrantStage::find($data['stage_id']);
            $owed = $this->registrantOutstandingBalance($data['stage_id']);
            $mata_name = event_registrant_name($reg['id']);
        }

        // The client-submitted amount is only ever used to let someone pay
        // LESS than the full balance (a supported partial/installment
        // payment) - never more than what's actually owed, computed here
        // from the DB rather than trusted from the request.
        $requested = (float) ($data['total_fee'] ?? 0);
        $amount = max(0, min($requested, $owed));
        $year = date('y');

        return [
            'email' => $reg['email'],
            'amount' => ceil($amount * 100),
            'reference' => 'APOSA-'.$year.'-'.Str::random(16),
//            'subaccount' => config('services.paystack.subaccount_code'),
            'metadata' => [
                'name' => $mata_name,
                'phone' => $reg['phone_number'],
            ],
            'callback_url' => isset($data['batch']) ? route('registrant_page_batch') : route('registrant_page'),
        ];
    }

    private function registrantOutstandingBalance($stageId): float
    {
        $registrant = Registrant::where('stage_id', $stageId)->first();
        if (! $registrant) {
            return 0;
        }

        // A promotion that discounted this registrant's fee may have ended
        // since the snapshot was taken - revert to the original price if
        // they still haven't paid in full before computing what's owed.
        Promotion::revertExpiredDiscountIfUnpaid($registrant);
        $registrant->refresh();

        $paid = OnlinePayment::where('reg_id', $stageId)->sum('amount_paid');

        return max(0, (float) $registrant->total_fee - $paid);
    }

    /**
     * The real amount still owed across every registrant actually in this
     * batch (via batch_no) - the only trustworthy source for that figure,
     * since anything the client submits about a batch's total (a hidden
     * form field, or which registrants are even included in the request)
     * can be tampered or incomplete.
     */
    public function batchOutstandingBalance($batchLogId): float
    {
        $batchLog = BatchLog::find($batchLogId);
        if (! $batchLog) {
            return 0;
        }

        return RegistrantStage::where('batch_no', $batchLog->batch_no)
            ->pluck('id')
            ->sum(fn ($stageId) => $this->registrantOutstandingBalance($stageId));
    }

    public function paymentReceipt(array $data, $paymentDetails, $response)
    {
        if (isset($data['batch'])) {
            $remaining = $paymentDetails['amount'] / 100;

            // Pass 1: honor the coordinator's claimed per-registrant split
            // for this transaction (session('batch_payment')['reg'] - how
            // they typed the amount into each row before submitting), each
            // capped at what that registrant genuinely still owes AND at
            // what's left of the real confirmed budget. This is what makes
            // separate transactions - paying for one registrant at a time,
            // the common real usage - land on the right person, while a
            // claim that tries to exceed what was actually paid can still
            // never draw down more than the real confirmed total.
            $batchByStageId = collect($data['batch'])->keyBy(fn ($r) => (int) $r['id']);
            $claims = collect(session('batch_payment')['reg'] ?? []);

            foreach ($claims as $claim) {
                if ($remaining <= 0) {
                    break;
                }

                $registrantData = $batchByStageId->get((int) ($claim['registrant_id'] ?? 0));
                if (! $registrantData) {
                    continue;
                }

                $confirmed_registrant = Registrant::where('stage_id', $registrantData['id'])->first();
                if (! $confirmed_registrant) {
                    continue;
                }

                $allocated = min(
                    $this->registrantOutstandingBalance($registrantData['id']),
                    $remaining,
                    max(0, (float) ($claim['amount_paid'] ?? 0))
                );
                if ($allocated <= 0) {
                    continue;
                }

                $this->recordBatchPayment($registrantData, $confirmed_registrant, $paymentDetails, $response, $allocated);
                $remaining -= $allocated;
            }

            // Pass 2: anything genuinely confirmed but still unattributed
            // (claims under-total the real payment, omit a registrant
            // entirely, or there's no claim data at all) is recorded via a
            // waterfall across the rest of the batch, so confirmed money
            // is never silently dropped or left unrecorded.
            foreach ($data['batch'] as $registrantData) {
                if ($remaining <= 0) {
                    break;
                }

                $confirmed_registrant = Registrant::where('stage_id', $registrantData['id'])->first();
                if (! $confirmed_registrant) {
                    continue;
                }

                $allocated = min($this->registrantOutstandingBalance($registrantData['id']), $remaining);
                if ($allocated <= 0) {
                    continue;
                }

                $this->recordBatchPayment($registrantData, $confirmed_registrant, $paymentDetails, $response, $allocated);
                $remaining -= $allocated;
            }
        } else {
            OnlinePayment::create([
                'reg_id' => $data['registrant']['id'],
                'payment_mode' => $paymentDetails['channel'],
                'transaction_no' => $paymentDetails['id'],
                'amount_to_pay' => $data['confirmed_registrant']->total_fee,
                'amount_paid' => $paymentDetails['amount'] / 100,
                'date_paid' => date('Y-m-d', strtotime($paymentDetails['transaction_date'])),
                'comment' => $response['message'],
                'approved' => 1,
                'approved_at' => date('Y-m-d', strtotime($paymentDetails['paid_at'])),
                'batch_no' => $data['registrant']['batch_no'],
                'event_total_fee' => $data['confirmed_registrant']->total_fee,
                'payment_token' => $paymentDetails['id'],
                'payment_status' => $response['status'],
                'event_id' => $data['registrant']['event_id'],
            ]);
        }
    }

    private function recordBatchPayment($registrantData, Registrant $confirmed_registrant, $paymentDetails, $response, float $amount): void
    {
        OnlinePayment::create([
            'reg_id' => $registrantData['id'],
            'payment_mode' => $paymentDetails['channel'],
            'transaction_no' => $paymentDetails['id'],
            'amount_to_pay' => $confirmed_registrant->total_fee,
            'amount_paid' => $amount,
            'date_paid' => date('Y-m-d', strtotime($paymentDetails['transaction_date'])),
            'comment' => $response['message'],
            'approved' => 1,
            'approved_at' => date('Y-m-d', strtotime($paymentDetails['paid_at'])),
            'batch_no' => $registrantData['batch_no'],
            'event_total_fee' => $confirmed_registrant->total_fee,
            'payment_token' => $paymentDetails['id'],
            'payment_status' => $response['status'],
            'event_id' => $registrantData['event_id'],
        ]);
    }
}
