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
        $count = AssignedRoomEpisode::where(['event_id' => $data['event_id'], 'registrant_id' => $registrant->id])->count();

        $financial_clarance = OnlinePayment::where(['reg_id' => $registrant->stage_id, 'event_id' => $data['event_id']])->first();
        $total_payment = OnlinePayment::where(['reg_id' => $registrant->stage_id, 'event_id' => $data['event_id']])->sum('amount_paid');

        if(($total_payment < $registrant->total_fee) && ($financial_clarance->approved == 1)){
            return back()->with('error', "Registration No. $data[registration_no] has not completed payment yet!!! See Finance Committee.");
        }

        if($count >= 1){
            return back()->with('error', "Registration No. $data[registration_no] already assigned to another room!!!");
        }

        $total_assigns = AssignedRoomEpisode::where(['room_id' => $data['room_id'], 'event_id' => $data['event_id']])->count();
        $total_occupants = AccommodationRoom::find($data['room_id'])->total_occupants;

        if($total_assigns == $total_occupants){
            return back()->with('error', "Room ".get_room_number($data['room_id'])." is full!!!");
        }

        AssignedRoomEpisode::firstOrCreate([
            'room_id' => $data['room_id'],
            'event_id' => $data['event_id'],
            'registrant_id' => $registrant->id,
        ],[
            'active_flag' => 1,
            'created_by' => get_logged_in_user_id(),
            'updated_by' => get_logged_in_user_id(),
        ]);

        return back()->with('success', "Registration No. $data[registration_no] assigned successfully!!!");
    }

    public function transferRoomMate(array $data)
    {
        $total_assigns = AssignedRoomEpisode::where(['room_id' => $data['room_id'], 'event_id' => $data['event_id']])->count();
        $total_occupants = AccommodationRoom::find($data['room_id'])->total_occupants;

        if($total_assigns == $total_occupants){
            return back()->with('error', "Room ".get_room_number($data['room_id'])." is full!!!");
        }

        $registrant = Registrant::where('registration_no', $data['registration_no'])->first();
        $assigned_to = AssignedRoomEpisode::where(['event_id' => $data['event_id'], 'registrant_id' => $registrant->id])->first();

        if(!$assigned_to){
            return back()->with('error', "Registration No. $data[registration_no] has not been assigned to room yet!!!");
        }

        AssignedRoomEpisode::find($assigned_to->id)->update([
            'room_id' => $data['room_id'],
            'updated_by' => get_logged_in_user_id(),
        ]);

        return back()->with('success', "Registration No. $data[registration_no] has been transferred successfully!!!");
    }

    static public function destroy($id)
    {
        $record = AssignedRoomEpisode::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }
}
