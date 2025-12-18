<?php

namespace App\Services\Admin;

use App\Models\Admin\OnlinePayment;
use App\Models\FinancialEpisode;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    public function index($id)
    {
        $data['finances'] = OnlinePayment::where('event_id', $id)->orderByDesc('id')->get();
        return view('admin.finance.index', $data);
    }

    public function financialClearance(array $data)
    {
        $payment = OnlinePayment::find($data['payment_id']);
        OnlinePayment::where([
            'reg_id' => $payment->reg_id,
            'event_id' => $payment->event_id,
        ])->update([
            'approved' => 2,
        ]);

        return back()->with('success', 'Financial Clearance Successful!');
    }

    public function financialEntryIndex()
    {
        $data['finances'] = FinancialEpisode::where('event_id', get_logged_in_user_event_id())->orderByDesc('transaction_date')->get();
        return view('admin.finance.financial_entries', $data);
    }

    public function financialEntry(array $data)
    {
        $results = FinancialEpisode::firstOrCreate([
                'transaction_id' => date("YmdHis"),
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
                'updated_by' =>  get_logged_in_user_id(),
            ]);


        if($results){
            return redirect(route('financial_entries', absolute: false))->with('success', 'Financial Entry Created Successfully!!!');
        }

        return redirect(route('financial_entries', absolute: false))->with('error', 'Financial Entry Creation Unsuccessful!!!');
    }

    public function financialEntryUpdate(array $data)
    {
        $results = FinancialEpisode::find($data['id'])->update([
                'entry_type' => trim($data['entry_type']),
                'transaction_type' => $data['transaction_type'],
                'transaction_date' => $data['transaction_date'],
                'amount' => $data['amount'],
                'description' => trim($data['description']),
                'updated_by' =>  get_logged_in_user_id(),
            ]);

        if($results){
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

    static function financialEntryDelete($id)
    {
        $record = FinancialEpisode::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getFinancialPrintData(array $data): array
    {
        if (!empty($data['report'])) {
            $data['online_payments'] = OnlinePayment::where('event_id', get_logged_in_user_event_id())->orderByDesc('date_paid')->get();
            $data['finance_income'] = FinancialEpisode::where(['event_id' => get_logged_in_user_event_id(), 'entry_type' => 'Income'])->orderByDesc('transaction_date')->get();
            $data['finance_expense'] = FinancialEpisode::where(['event_id' => get_logged_in_user_event_id(), 'entry_type' => 'Expense'])->orderByDesc('transaction_date')->get();
            $data['finance_income_group'] = FinancialEpisode::selectRaw("transaction_type, SUM(amount) AS amount")->where(['event_id' => get_logged_in_user_event_id(), 'entry_type' => 'Income'])->groupBy('transaction_type')->orderByDesc('transaction_date')->get();
            $data['finance_expense_group'] = FinancialEpisode::selectRaw("transaction_type, SUM(amount) AS amount")->where(['event_id' => get_logged_in_user_event_id(), 'entry_type' => 'Expense'])->groupBy('transaction_type')->orderByDesc('transaction_date')->get();
            $data['header'] = "Financial Report for " . get_event(get_logged_in_user_event_id())->name;
            $data['event_id'] = get_logged_in_user_event_id();
        } else {
            $data['report'] = [];
        }
        return $data;
    }

    public function onlinePaymentCorrectionStore(array $data)
    {
        $confirmed_registrant = DB::table('vw_registration')->where(['registration_no' => $data['registration_no'], 'event_id' => get_logged_in_user_event_id()])->first();

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
            'batch_no' => empty($data['batch_no']) ? 0 : $data['batch_no'],
            'event_total_fee' => $confirmed_registrant->total_fee,
            'payment_token' => $data['transaction_no'],
            'payment_status' => 1,
            'event_id' => get_logged_in_user_event_id(),
        ]);


        if($results){
            return redirect(route('payments', absolute: false))->with('success', 'Online Payment Entry Created Successfully!!!');
        }

        return redirect(route('payments', absolute: false))->with('error', 'Online Payment Entry Creation Unsuccessful!!!');
    }
}
