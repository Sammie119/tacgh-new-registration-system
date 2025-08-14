<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AccommodationRoom;
use App\Services\Admin\AccommodationService;
use Illuminate\Http\Request;

class AccommodationRoomController extends Controller
{
    private AccommodationService $accommodationService;

    public function __construct(AccommodationService $accommodationService)
    {
        $this->accommodationService = $accommodationService;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'block_id' => 'required',
            'total_rooms' => 'required',
            'residence_id' => 'required',
            'rooms' => 'required',
        ]);

       return $this->accommodationService->generateRoomsStore($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->accommodationService->accommodationRoomShow($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request['id'] = $id;
        return $this->accommodationService->accommodationRoomUpdate($request->all());
    }
}
