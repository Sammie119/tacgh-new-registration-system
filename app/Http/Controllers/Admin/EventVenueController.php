<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\EventVenue;
use App\Services\Admin\VenueService;
use Illuminate\Http\Request;

class EventVenueController extends Controller
{
    private VenueService $venueService;

    public function __construct(VenueService $venueService)
    {
        $this->venueService = $venueService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->venueService->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'region_id' => 'required',
        ]);

        return $this->venueService->store($request->all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventVenue $eventVenue)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'region_id' => 'required',
        ]);

        return $this->venueService->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    static public function destroy($id)
    {
        return VenueService::destroy($id);
    }
}
