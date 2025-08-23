<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Event;
use App\Services\Admin\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->eventService->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'description' =>'required',
            'code_prefix' =>'required|max:5|min:3',
            'start_date' =>'required',
            'end_date' =>'required',
            'venue_id' =>'required|exists:event_venues,id',
            'file' =>'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:1048',
        ]);

        return $this->eventService->store($request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name'=>'required',
            'description' =>'required',
            'code_prefix' =>'required|max:5|min:3',
            'start_date' =>'required',
            'end_date' =>'required',
            'venue_id' =>'required|exists:event_venues,id',
            'file' =>'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:1048',
        ]);

        return $this->eventService->update($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    static public function destroy($id)
    {
        return EventService::destroy($id);
    }
}
