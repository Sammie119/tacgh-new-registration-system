<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AccommodationBlock;
use App\Services\Admin\AccommodationService;
use Illuminate\Http\Request;

class AccommodationBlockController extends Controller
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
            'resident_id' => 'required',
            'blocks' => 'required',
        ]);

        return $this->accommodationService->blockStore($request->all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccommodationBlock $accommodationBlock)
    {
        //
    }
}
