<?php

namespace App\Services\Admin;

use App\Models\Admin\Dropdown;
use App\Models\Admin\OnlinePayment;
use App\Models\FinancialEpisode;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    public function index($id, ?string $search = null)
    {
        $query = OnlinePayment::with('registrant')
            ->where('event_id', $id)
            ->where('amount_to_pay', '>', 0)
            ->orderByDesc('id');

        if (! empty($search)) {
            $matchingStageIds = RegistrantStage::where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('other_names', 'like', "%{$search}%");
            })->pluck('id');

            $query->where(function ($q) use ($search, $matchingStageIds) {
                $q->whereIn('reg_id', $matchingStageIds)
                    ->orWhereHas('registrant', fn ($rq) => $rq->where('registration_no', 'like', "%{$search}%"));
            });
        }

        $data['finances'] = $query->paginate(50)->withQueryString();
        $data['search'] = $search;

        $regIds = $data['finances']->pluck('reg_id')->filter()->unique();

        $stages = RegistrantStage::whereIn('id', $regIds)->get(['id', 'title', 'first_name', 'other_names', 'surname']);
        $titleIds = $stages->pluck('title')->filter()->unique();
        $dropdownNames = Dropdown::whereIn('id', $titleIds)->pluck('full_name', 'id');

        $data['registrant_names'] = $stages->mapWithKeys(function ($stage) use ($dropdownNames) {
            $name = trim(($dropdownNames[$stage->title] ?? '').' '.$stage->first_name.' '.$stage->other_names.' '.$stage->surname);

            return [$stage->id => strtoupper($name)];
        });

        $data['amount_paid_totals'] = OnlinePayment::where('event_id', $id)
            ->whereIn('reg_id', $regIds)
            ->selectRaw('reg_id, SUM(amount_paid) as total_paid')
            ->groupBy('reg_id')
            ->get()
            ->pluck('total_paid', 'reg_id');

        return view('admin.finance.index', $data);
    }

    public function outstandingBalances($id, ?string $search = null)
    {
        // Real GROUP BY + HAVING here (valid everywhere, unlike HAVING on a
        // non-aggregate query, which sqlite rejects when Eloquent wraps a
        // paginated query in a count(*) subquery).
        $balanceTotals = OnlinePayment::where('event_id', $id)
            ->selectRaw('reg_id, SUM(amount_to_pay) as total_to_pay, SUM(amount_paid) as total_paid')
            ->groupBy('reg_id')
            ->havingRaw('SUM(amount_to_pay) > SUM(amount_paid)')
            ->get()
            ->keyBy('reg_id');

        $query = RegistrantStage::whereIn('id', $balanceTotals->keys())->orderByDesc('id');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('other_names', 'like', "%{$search}%")
                    ->orWhereHas('stage', fn ($rq) => $rq->where('registration_no', 'like', "%{$search}%"));
            });
        }

        $data['balances'] = $query->paginate(50)->withQueryString();
        $data['balance_totals'] = $balanceTotals;
        $data['search'] = $search;

        $titleIds = $data['balances']->pluck('title')->filter()->unique();
        $data['dropdown_names'] = Dropdown::whereIn('id', $titleIds)->pluck('full_name', 'id');

        $stageIds = $data['balances']->pluck('id');
        $data['registration_numbers'] = Registrant::whereIn('stage_id', $stageIds)->pluck('registration_no', 'stage_id');

        return view('admin.finance.outstanding_balances', $data);
    }

    public function financialClearance(array $data)
    {
        $payment = OnlinePayment::find($data['payment_id']);
        OnlinePayment::where([
            'reg_id' => $payment->reg_id,
            'event_id' => $payment->event_id,
        ])->update([
            'approved' => 2,
            'comment' => $data['comment'],
        ]);

        activity('finance')
            ->causedBy(auth()->user())
            ->performedOn($payment)
            ->withProperties(['reg_id' => $payment->reg_id, 'event_id' => $payment->event_id, 'comment' => $data['comment']])
            ->log('Payment cleared');

        return back()->with('success', 'Financial Clearance Successful!');
    }

    public function financialEntryIndex()
    {
        $data['finances'] = FinancialEpisode::where('event_id', get_logged_in_user_event_id())->orderByDesc('transaction_date')->get();

        $transactionTypeIds = $data['finances']->pluck('transaction_type')->filter()->unique();
        $data['dropdown_names'] = Dropdown::whereIn('id', $transactionTypeIds)->pluck('full_name', 'id');

        return view('admin.finance.financial_entries', $data);
    }

    public function financialEntry(array $data)
    {
        $results = FinancialEpisode::firstOrCreate([
            'transaction_id' => date('YmdHis'),
            'event_id' => get_logged_in_user_event_id(),
            'entry_type' => trim($data['entry_type']),
            'transaction_type' => $data['transaction_type'],
            'transaction_date' => $data['transaction_date'],
            'amount' => $data['amount'],
        ],
            [
                'description' => trim($data['description']),
                'active_flag' => isset($data['active_flag']) ? 1 : 0,
                'created_by' => get_logged_in_user_id(),
                'updated_by' => get_logged_in_user_id(),
            ]);

        if ($results) {
            activity('finance')
                ->causedBy(auth()->user())
                ->performedOn($results)
                ->withProperties(['entry_type' => $results->entry_type, 'transaction_type' => $results->transaction_type, 'amount' => $results->amount])
                ->log('Financial entry created');

            return redirect(route('financial_entries', absolute: false))->with('success', 'Financial Entry Created Successfully!!!');
        }

        return redirect(route('financial_entries', absolute: false))->with('error', 'Financial Entry Creation Unsuccessful!!!');
    }

    public function financialEntryUpdate(array $data)
    {
        $entry = FinancialEpisode::find($data['id']);
        if (! $entry) {
            return redirect(route('financial_entries', absolute: false))->with('error', 'Financial Entry not found!!!');
        }

        $before = $entry->only(['entry_type', 'transaction_type', 'amount', 'description']);

        $results = $entry->update([
            'entry_type' => trim($data['entry_type']),
            'transaction_type' => $data['transaction_type'],
            'transaction_date' => $data['transaction_date'],
            'amount' => $data['amount'],
            'description' => trim($data['description']),
            'updated_by' => get_logged_in_user_id(),
        ]);

        if ($results) {
            activity('finance')
                ->causedBy(auth()->user())
                ->performedOn($entry)
                ->withProperties(['before' => $before, 'after' => $entry->only(['entry_type', 'transaction_type', 'amount', 'description'])])
                ->log('Financial entry updated');

            return redirect(route('financial_entries', absolute: false))->with('success', 'Financial Entry Updated Successfully!!!');
        }

        return redirect(route('financial_entries', absolute: false))->with('error', 'Financial Entry Update Unsuccessful!!!');
    }

    public function financialReport(array $data)
    {
        $data = $this->getFinancialPrintData($data);

        return view('admin.finance.financial_report', $data);
    }

    public function printFinancialReport(array $data)
    {
        $data = $this->getFinancialPrintData($data);

        return view('admin.finance.print_financial_report', $data);
    }

    public static function financialEntryDelete($id)
    {
        $record = FinancialEpisode::find($id);
        if ($record) {
            activity('finance')
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['entry_type' => $record->entry_type, 'transaction_type' => $record->transaction_type, 'amount' => $record->amount])
                ->log('Financial entry deleted');

            $record->delete();

            return 1;
        }

        return 0;
    }

    protected function getFinancialPrintData(array $data): array
    {
        if (! empty($data['report'])) {
            $eventId = get_logged_in_user_event_id();
            $event = get_event($eventId);
            abort_if(! $event, 404, 'Event not found.');

            $data['online_payments'] = OnlinePayment::where('event_id', $eventId)->orderByDesc('date_paid')->get();
            $data['finance_income'] = FinancialEpisode::where(['event_id' => $eventId, 'entry_type' => 'Income'])->orderByDesc('transaction_date')->get();
            $data['finance_expense'] = FinancialEpisode::where(['event_id' => $eventId, 'entry_type' => 'Expense'])->orderByDesc('transaction_date')->get();
            $data['finance_income_group'] = FinancialEpisode::selectRaw('transaction_type, SUM(amount) AS amount')->where(['event_id' => $eventId, 'entry_type' => 'Income'])->groupBy('transaction_type')->orderByDesc('transaction_date')->get();
            $data['finance_expense_group'] = FinancialEpisode::selectRaw('transaction_type, SUM(amount) AS amount')->where(['event_id' => $eventId, 'entry_type' => 'Expense'])->groupBy('transaction_type')->orderByDesc('transaction_date')->get();
            $data['header'] = 'Financial Report for '.$event->name;
            $data['event_id'] = $eventId;

            $transactionTypeIds = collect([$data['finance_income'], $data['finance_expense'], $data['finance_income_group'], $data['finance_expense_group']])
                ->flatMap(fn ($collection) => $collection->pluck('transaction_type'))
                ->filter()
                ->unique();
            $data['dropdown_names'] = Dropdown::whereIn('id', $transactionTypeIds)->pluck('full_name', 'id');

            $regIds = $data['online_payments']->pluck('reg_id')->filter()->unique();
            $stages = RegistrantStage::whereIn('id', $regIds)->get(['id', 'title', 'first_name', 'other_names', 'surname']);
            $stageTitleIds = $stages->pluck('title')->filter()->unique();
            $stageTitleNames = Dropdown::whereIn('id', $stageTitleIds)->pluck('full_name', 'id');

            $data['registrant_names'] = $stages->mapWithKeys(function ($stage) use ($stageTitleNames) {
                $name = trim(($stageTitleNames[$stage->title] ?? '').' '.$stage->first_name.' '.$stage->other_names.' '.$stage->surname);

                return [$stage->id => strtoupper($name)];
            });
        } else {
            $data['report'] = [];
        }

        return $data;
    }

    public function onlinePaymentCorrectionStore(array $data)
    {
        $confirmed_registrant = DB::table('vw_registration')->where(['registration_no' => $data['registration_no'], 'event_id' => get_logged_in_user_event_id()])->first();
        if (! $confirmed_registrant) {
            return redirect(route('payments', absolute: false))->with('error', 'Registration No. was not found!!!');
        }

        $batch_no = RegistrantStage::find($confirmed_registrant->stage_id)?->batch_no;

        $results = OnlinePayment::create([
            'reg_id' => $confirmed_registrant->stage_id,
            'payment_mode' => $data['payment_mode'],
            'transaction_no' => $data['transaction_no'],
            'amount_to_pay' => $confirmed_registrant->total_fee,
            'amount_paid' => $data['amount_paid'],
            'date_paid' => $data['date_paid'],
            'comment' => 'Verification successful',
            'approved' => 1,
            'approved_at' => $data['date_paid'],
            'batch_no' => $batch_no,
            'event_total_fee' => $confirmed_registrant->total_fee,
            'payment_token' => $data['transaction_no'],
            'payment_status' => 1,
            'event_id' => get_logged_in_user_event_id(),
        ]);

        if ($results) {
            activity('finance')
                ->causedBy(auth()->user())
                ->performedOn($results)
                ->withProperties([
                    'registration_no' => $data['registration_no'],
                    'amount_paid' => $data['amount_paid'],
                    'payment_mode' => $data['payment_mode'],
                    'transaction_no' => $data['transaction_no'],
                ])
                ->log('Online payment correction recorded');

            return redirect(route('payments', absolute: false))->with('success', 'Online Payment Entry Created Successfully!!!');
        }

        return redirect(route('payments', absolute: false))->with('error', 'Online Payment Entry Creation Unsuccessful!!!');
    }

    public function checkPaymentConfirmation($reference)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.paystack.co/transaction/verify/'.$reference['reference'],
            'headers' => [
                'Authorization' => 'Bearer '.config('services.paystack.secret_key'),
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($this->client->request('GET')->getBody(), true);

        //        if($response['status'] && $response['data']['status'] === 'success'){
        //            return $response['data'];
        //        }
        //        else {
        //            return "Payment Failed";
        //        }

        //        return json_decode($response->getBody(), true);
        //        $curl = curl_init();
        //
        //        curl_setopt_array($curl, array(
        //            CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$reference['reference'],
        //            CURLOPT_RETURNTRANSFER => true,
        //            CURLOPT_ENCODING => "",
        //            CURLOPT_MAXREDIRS => 10,
        //            CURLOPT_TIMEOUT => 30,
        //            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //            CURLOPT_CUSTOMREQUEST => "GET",
        //            CURLOPT_HTTPHEADER => array(
        //                "Authorization" => "Bearer ".config("services.paystack.secret_key"),
        //                "Cache-Control: no-cache",
        //            ),
        //        ));
        //
        //        $response = curl_exec($curl);
        //        $err = curl_error($curl);
        //
        //        curl_close($curl);
        //
        //        if ($err) {
        //            echo "cURL Error #:" . $err;
        //        } else {
        //            return $response;
        //        }
    }
}
