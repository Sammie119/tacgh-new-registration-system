<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function demographics()
    {
        return $this->reportService->demographics(get_logged_in_user_event_id());
    }

    public function tokens(Request $request)
    {
        $activeTab = 'individual';
        if ($request->filled('batch_search') || $request->filled('batch_page')) {
            $activeTab = 'batch';
        } elseif ($request->filled('member_search') || $request->filled('member_page')) {
            $activeTab = 'member';
        }

        return $this->reportService->tokens(
            get_logged_in_user_event_id(),
            $request->input('individual_search'),
            $request->input('batch_search'),
            $request->input('member_search'),
        )->with('active_tab', $activeTab);
    }
}
