<?php

namespace App\Services\Admin;

use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\OnlinePayment;
use App\Models\Registrant;

class AssignRoomEpisodeService
{
    public function addRoomMate(array $data)
    {
        $registrant = Registrant::where('registration_no', $data['registration_no'])->first();
        if (! $registrant) {
            return back()->with('error', "Registration No. $data[registration_no] was not found!!!");
        }

        $count = AssignedRoomEpisode::where(['event_id' => $data['event_id'], 'registrant_id' => $registrant->id, 'active_flag' => 1])->count();
        if (event_registrant_age($registrant->stage_id) < 6) {
            return back()->with('error', "Registration No. $data[registration_no] is less than 6 years old!!!.");
        }

        $financial_clarance = OnlinePayment::where(['reg_id' => $registrant->stage_id, 'event_id' => $data['event_id']])->first();
        $total_payment = OnlinePayment::where(['reg_id' => $registrant->stage_id, 'event_id' => $data['event_id']])->sum('amount_paid');

        if (($total_payment < $registrant->total_fee) && (($financial_clarance->approved ?? 1) == 1)) {
            return back()->with('error', "Registration No. $data[registration_no] has not completed payment yet!!! See Finance Committee.");
        }

        if ($count >= 1) {
            return back()->with('error', "Registration No. $data[registration_no] already assigned to another room!!!");
        }

        $room = AccommodationRoom::find($data['room_id']);
        if (! $room) {
            return back()->with('error', 'Room was not found!!!');
        }

        $total_assigns = AssignedRoomEpisode::where(['room_id' => $data['room_id'], 'event_id' => $data['event_id'], 'active_flag' => 1])->count();

        if ($total_assigns == $room->total_occupants) {
            return back()->with('error', 'Room '.get_room_number($data['room_id']).' is full!!!');
        }

        // updateOrCreate, not firstOrCreate: the "already assigned" guard
        // above only allows reaching here when no ACTIVE episode exists for
        // this registrant+event, so any existing row matching room_id must
        // be a stale inactive one - it needs to be reactivated (active_flag
        // and checkin_date refreshed), not left untouched.
        $assigned = AssignedRoomEpisode::updateOrCreate([
            'room_id' => $data['room_id'],
            'event_id' => $data['event_id'],
            'registrant_id' => $registrant->id,
        ], [
            'checkin_date' => now()->toDateString(),
            'active_flag' => 1,
            'created_by' => get_logged_in_user_id(),
            'updated_by' => get_logged_in_user_id(),
        ]);

        if ($assigned) {
            Registrant::where('registration_no', $data['registration_no'])->update(['room_no' => $data['room_id']]);
        }

        return back()->with('success', "Registration No. $data[registration_no] assigned successfully!!!");
    }

    public function transferRoomMate(array $data)
    {
        $room = AccommodationRoom::find($data['room_id']);
        if (! $room) {
            return back()->with('error', 'Room was not found!!!');
        }

        $total_assigns = AssignedRoomEpisode::where(['room_id' => $data['room_id'], 'event_id' => $data['event_id'], 'active_flag' => 1])->count();

        if ($total_assigns == $room->total_occupants) {
            return back()->with('error', 'Room '.get_room_number($data['room_id']).' is full!!!');
        }

        $registrant = Registrant::where('registration_no', $data['registration_no'])->first();
        if (! $registrant) {
            return back()->with('error', "Registration No. $data[registration_no] was not found!!!");
        }

        $assigned_to = AssignedRoomEpisode::where(['event_id' => $data['event_id'], 'registrant_id' => $registrant->id, 'active_flag' => 1])->first();

        if (! $assigned_to) {
            return back()->with('error', "Registration No. $data[registration_no] has not been assigned to room yet!!!");
        }

        $assigned = $assigned_to->update([
            'room_id' => $data['room_id'],
            'updated_by' => get_logged_in_user_id(),
        ]);

        if ($assigned) {
            Registrant::where('registration_no', $data['registration_no'])->update(['room_no' => $data['room_id']]);
        }

        return back()->with('success', "Registration No. $data[registration_no] has been transferred successfully!!!");
    }

    public static function destroy($id)
    {
        $record = AssignedRoomEpisode::find($id);
        if ($record) {
            $record->delete();

            return 1;
        }

        return 0;
    }
}
