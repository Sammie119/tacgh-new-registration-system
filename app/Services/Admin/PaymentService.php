<?php

namespace App\Services\Admin;

use App\Models\Admin\OnlinePayment;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;

class PaymentService
{
    public function makePayment(array $data)
    {
        if (isset($data['batch'])) {
            $reg = BatchLog::find($data['batch_id']);
            $owed = $this->batchOutstandingBalance($data['batch_id']);
        } else {
            $reg = RegistrantStage::find($data['stage_id']);
            $owed = $this->registrantOutstandingBalance($data['stage_id']);
        }

        // The client-submitted amount is only ever used to let someone pay
        // LESS than the full balance (a supported partial/installment
        // payment) - never more than what's actually owed, computed here
        // from the DB rather than trusted from the request.
        $requested = (float) ($data['total_fee'] ?? 0);
        $amount = max(0, min($requested, $owed));

        return [
            'email' => $reg['email'],
            'amount' => $amount * 100,
            'metadata' => [
                'name' => event_registrant_name($reg['id']),
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
            // The per-registrant breakdown is computed here from the
            // REAL confirmed Paystack amount, not trusted from the
            // client-submitted session('batch_payment') data - otherwise a
            // coordinator could pay a token amount while claiming an
            // arbitrary, much larger amount was paid for each registrant,
            // fabricating "fully paid" status and triggering room
            // allocation for money that was never actually received.
            $remaining = $paymentDetails['amount'] / 100;

            foreach ($data['batch'] as $registrantData) {
                if ($remaining <= 0) {
                    break;
                }

                $confirmed_registrant = Registrant::where('stage_id', $registrantData['id'])->first();
                if (! $confirmed_registrant) {
                    continue;
                }

                $owed = $this->registrantOutstandingBalance($registrantData['id']);
                $allocated = min($owed, $remaining);
                if ($allocated <= 0) {
                    continue;
                }

                OnlinePayment::create([
                    'reg_id' => $registrantData['id'],
                    'payment_mode' => $paymentDetails['channel'],
                    'transaction_no' => $paymentDetails['id'],
                    'amount_to_pay' => $confirmed_registrant->total_fee,
                    'amount_paid' => $allocated,
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
}
