<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AccommodationEpisode;
use App\Services\Admin\EventService;
use Illuminate\Http\Request;

class AccommodationEpisodeController extends Controller
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
        return $this->eventService->eventVenues();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AccommodationEpisode $accommodationEpisode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccommodationEpisode $accommodationEpisode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccommodationEpisode $accommodationEpisode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccommodationEpisode $accommodationEpisode)
    {
        //
    }
}
