<?php

namespace App\Services\Admin;

use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\EventVenue;
use App\Models\Registrant;

class AccommodationService
{
    public function accommodations($id)
    {
        $data['accommodations'] = Accommodation::where('venue_id', $id)->orderByDesc('id')->get();
        $data['venue'] = EventVenue::find($id);
        return view('admin.accommodation.resident.index', $data);
    }

    public function accommodationStore(array $data)
    {
        if(empty($data['accommodation'][1])){
            return redirect(route('venues', absolute: false))->with('error', 'List of Resident is Empty!!!');
        }

        $results = 0;
        foreach ($data['accommodation'] as $value) {
//            dd(empty($value['id']));
            if(empty($value['id'])){
                $results = Accommodation::updateOrCreate([
                        'venue_id' => $data['accommodation_id'],
                        'name' => trim($value['name']),
                        'total_blocks' => trim($value['total_blocks']),
                        'gender' => trim($value['gender']),
                    ],
                    [
                        'status' => trim($value['status']),
//                        'active_flag' => isset($value['active_flag']) ? 1 : 0,
                        'created_by' => get_logged_in_user_id(),
                        'updated_by' =>  get_logged_in_user_id(),
                    ]);
            } else {
//                dd(Accommodation::find($value['id']));
                $results = Accommodation::find($value['id'])->update([
                    'venue_id' => $data['accommodation_id'],
                    'name' => trim($value['name']),
                    'total_blocks' => trim($value['total_blocks']),
                    'gender' => trim($value['gender']),
                    'status' => trim($value['status']),
//                    'active_flag' => isset($value['active_flag']) ? 1 : 0,
                    'updated_by' =>  get_logged_in_user_id(),
                ]);
            }
        }

        if($results){
            return redirect(route('venues', absolute: false))->with('success', 'Residents Created Successfully!!!');
        }

        return redirect(route('venues', absolute: false))->with('error', 'Residents Creation Unsuccessful!!!');
    }

    public function accommodationUpdate(array $data)
    {
        $results = Accommodation::find($data['id'])->update([
            'name' => trim($data['name']),
            'total_blocks' => trim($data['total_blocks']),
            'gender' => trim($data['gender']),
            'status' => isset($data['status']) ? "Active" : "Blocked",
            'active_flag' => isset($data['active_flag']) ? 1 : 0,
            'updated_by' =>  get_logged_in_user_id(),
        ]);

        if($results){
            return back()->with('success', 'Residents Updated Successfully!!!');
        }

        return back()->with('error', 'Residents Update Unsuccessful!!!');
    }

    static public function accommodationDelete($id)
    {
        $record = Accommodation::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }

    public function blockStore(array $data)
    {
        if(empty($data['blocks'][1])){
            return back()->with('error', 'List of Block is Empty!!!');
        }

        $results = 0;
        foreach ($data['blocks'] as $value) {
            if(empty($value['id'])){
                $results = AccommodationBlock::updateOrCreate([
                    'residence_id' => $data['resident_id'],
                    'name' => trim($value['name']),
                ],
                [
                    'total_rooms' => $value['total_rooms'],
                    'total_floors' => trim($value['total_floors']),
                    'gender' => trim($value['gender']),
                    'status' => trim($value['status']),
                    'active_flag' => 1,
                    'created_by' => get_logged_in_user_id(),
                    'updated_by' =>  get_logged_in_user_id(),
                ]);
            } else {
                $results = AccommodationBlock::find($value['id'])->update([
                    'residence_id' => $data['resident_id'],
                    'name' => trim($value['name']),
                    'total_rooms' => trim($value['total_rooms']),
                    'total_floors' => trim($value['total_floors']),
                    'gender' => trim($value['gender']),
                    'status' => trim($value['status']),
                    'active_flag' => 1,
                    'updated_by' =>  get_logged_in_user_id(),
                ]);
            }
        }

//        Accommodation::find($data['resident_id'])->update(['total_rooms' => $total_rooms]);

        if($results){
            return back()->with('success', 'Blocks Created Successfully!!!');
        }

        return back()->with('error', 'Blocks Creation Unsuccessful!!!');
    }

    public function generateRoomsStore(array $data)
    {
        if(empty($data['rooms'][1])){
            return back()->with('error', 'List of Room is Empty!!!');
        }

        $block = AccommodationBlock::find($data['block_id']);
        $residence = Accommodation::find($data['residence_id']);

        $total_rooms = $block->total_rooms;
        foreach ($data['rooms'] as $value) {
            for ($i = $value['room_no_from']; $i <= $value['room_no_to']; $i++){
                AccommodationRoom::create([
                    'room_no' => $i,
                    'floor_no' => $value['floor_no'],
                    'block_id' => $data['block_id'],
                    'residence_id' => $data['residence_id'],
                    'total_occupants' => $value['beds_per_room'],
                    'floor_name' => 'NULL',
                    'prefix' => $value['prefix'],
                    'suffix' => $value['suffix'],
                    'assign' => 1,
                    'gender' => $block->gender,
                    'created_by' => get_logged_in_user_id(),
                    'updated_by' => get_logged_in_user_id(),
                ]);

                $total_rooms++;
            }
        }

        $block->update(['total_rooms' => $total_rooms]);
        $residence->update(['total_rooms' => ($residence->total_rooms + $total_rooms)]);

        return back()->with('success', 'Blocks Created Successfully!!!');
    }

    public function accommodationRoomShow($id)
    {
        $data['room'] = AccommodationRoom::find($id);
        $data['venue_id'] = Accommodation::find($data['room']->residence_id)->venue_id;
        $data['roommates'] = AssignedRoomEpisode::where(['room_id' => $data['room']->id, 'event_id' => get_logged_in_user_event_id()])->get();
        $data['participants'] = Registrant::where('event_id', get_logged_in_user_event_id())->get();
        return view('admin.accommodation.room.room', $data);
    }

    public function accommodationRoomUpdate(array $data)
    {
//        dd($data);
        AccommodationRoom::find($data['id'])->update([
            'name' => $data['name'],
            'prefix' => $data['prefix'],
            'suffix' => $data['suffix'],
            'gender' => $data['gender'],
            'assign' => $data['assign'],
            'type' => $data['type'],
            'total_occupants' => $data['total_occupants'],
            'special_acc' => $data['special_acc'] ?? 0,
            'updated_by' => get_logged_in_user_id(),
        ]);

        return back()->with('success', 'Room Updated Successfully!!!');
    }

    public function allocateRoomsSingle($id)
    {
        $data['accommodations'] = Accommodation::where('venue_id', $id)->orderByDesc('id')->get();
        $data['venue'] = EventVenue::find($id);
        return view('admin.accommodation.allocation_room', $data);
    }

}
