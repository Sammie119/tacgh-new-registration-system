<?php

namespace App\Services\Admin;

use App\Models\Admin\OnlinePayment;
use App\Models\FinancialEpisode;

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
        $data['finances'] = FinancialEpisode::where('event_id', Auth()->user()->event_id)->orderByDesc('transaction_date')->get();
        return view('admin.finance.financial_entries', $data);
    }

    public function financialEntry(array $data)
    {
        $results = FinancialEpisode::firstOrCreate([
                'transaction_id' => date("YmdHis"),
                'event_id' => Auth()->user()->event_id,
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

    static function financialEntryDelete($id)
    {
        $record = FinancialEpisode::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }
}
