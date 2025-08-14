<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\EventFees;
use App\Services\Admin\EventFeesService;
use Illuminate\Http\Request;

class EventFeesController extends Controller
{
    private EventFeesService $eventFeesService;

    public function __construct(EventFeesService $eventFeesService)
    {
        $this->eventFeesService = $eventFeesService;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required',
            'fee_type' => 'required',
            'fees' => 'required',
            'fees.*' => 'required',
        ]);

        return $this->eventFeesService->store($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    static public function destroy($id)
    {
        return EventFeesService::destroy($id);
    }
}
