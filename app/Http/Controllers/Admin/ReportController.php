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
        return $this->reportService->tokens(
            get_logged_in_user_event_id(),
            $request->input('individual_search'),
            $request->input('batch_search'),
            $request->input('member_search'),
        );
    }
}
