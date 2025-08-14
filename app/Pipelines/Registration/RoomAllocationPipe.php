<?php

namespace App\Pipelines\Registration;

use App\Http\Traits\SMSNotify;
use App\Jobs\SmsNotificationJob;
use App\Jobs\WhatsappNotificationJob;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\Dropdown;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\Registrant;

class RoomAllocationPipe
{
    use SMSNotify;

    public function autoRoomAllocation(array $data)
    {
        $registrant = $data['registrant'];
        $gender = ($registrant['gender'] == 3) ? 'M' : 'F';

        $event = Event::select('id', 'venue_id')->where('id', $registrant['event_id'])->first()->toArray();

        // Get Accommodation type of Room
        $accommodation_type = $data['confirmed_registrant']->accommodation_type;
        $acc_type = EventFees::find($accommodation_type)->description;
        $special_acc = Dropdown::where('full_name', $acc_type)->first()->id;
        $subString = "Regular";

        $residences = Accommodation::where('venue_id','=', $event['venue_id'])
            ->where(function ($query) use ($gender) {
                $query->where('gender',$gender)
                    ->orWhere('gender','A');
            })
            ->where('active_flag', 1)
            ->orderBy('id','asc')
            ->pluck('id')->toArray();

        $blocks = AccommodationBlock::whereIn('residence_id', $residences)
            ->where(function ($query) use ($gender) {
                $query->where('gender',$gender)
                    ->orWhere('gender','A');
            })
            ->where('status', 'Active')
            ->pluck('id')->toArray();

        // Get all unfull rooms based on gender and residence
        $unfull = AccommodationRoom::where(function ($query) use ($gender) {
            $query->where('gender',"$gender")
                ->orWhere('gender','A');
        })
            ->whereRaw("total_occupants > (SELECT count(id) FROM  assigned_room_episodes WHERE room_id = accommodation_rooms.id AND event_id =".$event['id']." AND deleted_at IS NULL)")
            ->whereIn('residence_id',$residences)
            ->whereIn('block_id',$blocks)
            ->where('assign', 1);

        if(strpos($acc_type, $subString))
            $unfull = $unfull->where('type','Regular');
        else
            $unfull = $unfull->where('type', 'Special')->where('special_acc',$special_acc);

        $unfull = $unfull->orderBy('id','ASC')
            ->get();

        // Checks if the return value (unfull rooms) is not empty otherwise execute
        if (sizeof($unfull) != 0) {

            // $applicant->room_id = $unfull->first();
            for ($i=0; $i < sizeof($unfull); $i++) {
                if (get_total_room_occupants ($unfull[$i]->id, $event['id']) < $unfull[$i]->total_occupants) {

                    if ($data['confirmed_registrant']->room_no == $unfull[$i]->id) {
                        break;
                    }

                    // Give room number to Registrant
                    $reg_confirm = Registrant::find($data['confirmed_registrant']->id)->update(['room_no' => $unfull[$i]->id]);

                    if ($reg_confirm) {
                        AssignedRoomEpisode::firstOrCreate([
                            'room_id' => $unfull[$i]->id,
                            'event_id' => $event['id'],
                            'registrant_id' => $registrant['id'],
                        ],[
                            'active_flag' => 1,
                            'created_by' => $registrant['id'],
                            'updated_by' => $registrant['id'],
                        ]);

                        $reg_name = event_registrant_name($registrant['id']);
                        $roomName = get_room_number($unfull[$i]->id);
                        $msg = "$reg_name , you have been assigned to room $roomName";

                        WhatsappNotificationJob::dispatch($registrant->whatsapp_number, $msg);

                        if($registrant->residence_country_id == 64)
                            SmsNotificationJob::dispatch($registrant->phone_number, $msg);
                        //                $this->sendSms($results->phone_number, $msg);

                        //            $this->sendWhatsApp($results->whatsapp_number, $msg);

                        break;
                    }
                }
            }
        }

//        dd($residences, $blocks, get_total_room_occupants(1, $event['venue_id']), $unfull);
        return true;
    }
}
