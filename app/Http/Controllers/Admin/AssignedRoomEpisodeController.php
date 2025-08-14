<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AssignedRoomEpisode;
use App\Services\Admin\AssignRoomEpisodeService;
use Illuminate\Http\Request;

class AssignedRoomEpisodeController extends Controller
{
    private AssignRoomEpisodeService $assignRoomEpisodeService;

    public function __construct(AssignRoomEpisodeService $assignRoomEpisodeService)
    {
        $this->assignRoomEpisodeService = $assignRoomEpisodeService;
    }

    public function addRoomMate(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:accommodation_rooms,id',
            'event_id' => 'required|exists:events,id',
            'registration_no' => 'required|exists:registrants,registration_no',
        ]);

        return $this->assignRoomEpisodeService->addRoomMate($request->all());
    }

    public function transferRoomMate(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:accommodation_rooms,id',
            'event_id' => 'required|exists:events,id',
            'registration_no' => 'required|exists:registrants,registration_no',
        ]);

        return $this->assignRoomEpisodeService->transferRoomMate($request->all());
    }

    static public function destroy($id)
    {
        return AssignRoomEpisodeService::destroy($id);
    }
}
