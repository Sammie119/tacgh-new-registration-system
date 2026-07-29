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
use Illuminate\Support\Facades\DB;

class RoomAllocationPipe
{
    use SMSNotify;

    public function autoRoomAllocation(array $data)
    {
        $registrant = $data['registrant'];
        $gender = ($registrant['gender'] == 3) ? 'M' : 'F';

        if (event_registrant_age($registrant['id']) < 6) {
            return false;
        }

        $event = Event::select('id', 'venue_id')->where('id', $registrant['event_id'])->first();
        if (! $event) {
            return false;
        }
        $event = $event->toArray();

        // Get Accommodation type of Room
        $accommodation_type = $data['confirmed_registrant']->accommodation_type;
        $acc_type = EventFees::find($accommodation_type)?->description ?? '';
        $subString = 'Regular';

        $residences = Accommodation::where('venue_id', '=', $event['venue_id'])
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                    ->orWhere('gender', 'A');
            })
            ->where('active_flag', 1)
            ->orderBy('id', 'asc')
            ->pluck('id')->toArray();

        $blocks = AccommodationBlock::whereIn('residence_id', $residences)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                    ->orWhere('gender', 'A');
            })
            ->where('status', 'Active')
            ->pluck('id')->toArray();

        // Get all unfull rooms based on gender and residence
        $unfull = AccommodationRoom::where(function ($query) use ($gender) {
            $query->where('gender', "$gender")
                ->orWhere('gender', 'A');
        })
            ->whereRaw('total_occupants > (SELECT count(id) FROM assigned_room_episodes WHERE room_id = accommodation_rooms.id AND event_id = ? AND active_flag = 1 AND deleted_at IS NULL)', [$event['id']])
            ->whereIn('residence_id', $residences)
            ->whereIn('block_id', $blocks)
            ->where('assign', 1);

        if (str_contains($acc_type, $subString)) {
            $unfull = $unfull->where('type', 'Regular');
        } else {
            $special_acc = Dropdown::where('full_name', $acc_type)->first()?->id ?? 0;
            $unfull = $unfull->where('type', 'Special')->where('special_acc', $special_acc);
        }

        $unfull = $unfull->orderBy('id', 'ASC')
            ->get();

        // Checks if the return value (unfull rooms) is not empty otherwise execute
        if (count($unfull) != 0) {

            foreach ($unfull as $candidate) {
                // Re-check capacity and assign inside a locked transaction so
                // two registrants confirmed at nearly the same time can't
                // both pass the capacity check for the same room before
                // either write lands (the $unfull query above already
                // filtered to rooms with space, but that snapshot can be
                // stale by the time we get here).
                $outcome = DB::transaction(function () use ($candidate, $event, $data, $registrant) {
                    $room = AccommodationRoom::where('id', $candidate->id)->lockForUpdate()->first();
                    if (! $room) {
                        return 'unavailable';
                    }

                    if (get_total_room_occupants($room->id, $event['id']) >= $room->total_occupants) {
                        return 'unavailable';
                    }

                    if ($data['confirmed_registrant']->room_no == $room->id) {
                        return 'already_assigned';
                    }

                    $reg_confirm = $data['confirmed_registrant']->update(['room_no' => $room->id]);
                    if (! $reg_confirm) {
                        return 'unavailable';
                    }

                    // updateOrCreate, not firstOrCreate: the capacity check
                    // above only counts active episodes, so an existing row
                    // matching this room+event+registrant here must be a
                    // stale inactive one and needs reactivating rather than
                    // being left untouched.
                    AssignedRoomEpisode::updateOrCreate([
                        'room_id' => $room->id,
                        'event_id' => $event['id'],
                        'registrant_id' => $registrant['id'],
                    ], [
                        'checkin_date' => now()->toDateString(),
                        'active_flag' => 1,
                        'created_by' => $registrant['id'],
                        'updated_by' => $registrant['id'],
                    ]);

                    return 'assigned';
                });

                if ($outcome === 'already_assigned') {
                    break;
                }

                if ($outcome === 'assigned') {
                    $reg_name = event_registrant_name($registrant['id']);
                    $roomName = get_room_number($candidate->id);
                    $msg = "$reg_name , you have been assigned to room $roomName";

                    WhatsappNotificationJob::dispatch($registrant['whatsapp_number'], $msg, $registrant['id']);

                    if ($registrant['residence_country_id'] == 64) {
                        SmsNotificationJob::dispatch($registrant['phone_number'], $msg, $registrant['id']);
                    }

                    break;
                }
            }
        }

        return true;
    }
}
