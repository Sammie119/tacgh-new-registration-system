<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Accommodation;
use App\Models\Admin\Event;
use App\Models\Admin\EventVenue;
use App\Services\Admin\AccommodationService;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    private AccommodationService $accommodationService;

    public function __construct(AccommodationService $accommodationService)
    {
        $this->accommodationService = $accommodationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        return $this->accommodationService->accommodations($id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        dd($request->all());
        $request->validate([
            'accommodation_id' => 'required',
            'accommodation' => 'required',
        ]);

        return $this->accommodationService->accommodationStore($request->all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'total_blocks' => 'required',
            'gender' => 'required',
        ]);

        return $this->accommodationService->accommodationUpdate($request->all());
    }

    public function allocateRoomsSingle()
    {
        $venue = Event::find(Auth()->user()->event_id)->venue_id;
        return $this->accommodationService->allocateRoomsSingle($venue);
    }

    /**
     * Remove the specified resource from storage.
     */
    static public function destroy($id)
    {
        return AccommodationService::accommodationDelete($id);
    }
}
