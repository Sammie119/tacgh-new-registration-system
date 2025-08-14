<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\FinanceService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    private FinanceService $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function index()
    {
        return $this->financeService->index(Auth()->user()->event_id);
    }

    public function financialClearance(Request $request)
    {
        return $this->financeService->financialClearance($request->all());
    }

    public function financialEntryIndex()
    {
        return $this->financeService->financialEntryIndex();
    }

    public function financialEntry(Request $request)
    {
        $request->validate([
            'entry_type' => 'required|in:Income,Expense',
            'transaction_type' => 'required|exists:lookups,id',
            'description' => 'required|max:255|string',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
        ]);

        return $this->financeService->financialEntry($request->all());
    }

    public function financialEntryUpdate(Request $request)
    {
        $request->validate([
            'entry_type' => 'required|in:Income,Expense',
            'transaction_type' => 'required|exists:lookups,id',
            'description' => 'required|max:255|string',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
        ]);

        return $this->financeService->financialEntryUpdate($request->all());
    }

    static public function financialEntryDelete($id)
    {
        return FinanceService::financialEntryDelete($id);
    }
}
