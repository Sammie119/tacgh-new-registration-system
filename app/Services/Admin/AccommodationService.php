<?php

namespace App\Services\Admin;

use App\Helpers\Utils;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\EventVenue;
use App\Models\Admin\OnlinePayment;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Pipelines\Registration\RoomAllocationPipe;

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
        if (empty($data['accommodation'][1])) {
            return redirect(route('venues', absolute: false))->with('error', 'List of Resident is Empty!!!');
        }

        $results = 0;
        foreach ($data['accommodation'] as $value) {
            if (empty($value['id'])) {
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
                        'updated_by' => get_logged_in_user_id(),
                    ]);
            } else {
                $residence = Accommodation::find($value['id']);
                if (! $residence) {
                    continue;
                }

                $results = $residence->update([
                    'venue_id' => $data['accommodation_id'],
                    'name' => trim($value['name']),
                    'total_blocks' => trim($value['total_blocks']),
                    'gender' => trim($value['gender']),
                    'status' => trim($value['status']),
                    //                    'active_flag' => isset($value['active_flag']) ? 1 : 0,
                    'updated_by' => get_logged_in_user_id(),
                ]);
            }
        }

        if ($results) {
            return redirect(route('venues', absolute: false))->with('success', 'Residents Created Successfully!!!');
        }

        return redirect(route('venues', absolute: false))->with('error', 'Residents Creation Unsuccessful!!!');
    }

    public function accommodationUpdate(array $data)
    {
        $residence = Accommodation::find($data['id']);
        if (! $residence) {
            return back()->with('error', 'Residence was not found!!!');
        }

        $results = $residence->update([
            'name' => trim($data['name']),
            'total_blocks' => trim($data['total_blocks']),
            'gender' => trim($data['gender']),
            'status' => isset($data['status']) ? 'Active' : 'Blocked',
            'active_flag' => isset($data['active_flag']) ? 1 : 0,
            'updated_by' => get_logged_in_user_id(),
        ]);

        if ($results) {
            return back()->with('success', 'Residents Updated Successfully!!!');
        }

        return back()->with('error', 'Residents Update Unsuccessful!!!');
    }

    public static function accommodationDelete($id)
    {
        $record = Accommodation::find($id);
        if ($record) {
            $record->delete();

            return 1;
        }

        return 0;
    }

    public function blockStore(array $data)
    {
        if (empty($data['blocks'][1])) {
            return back()->with('error', 'List of Block is Empty!!!');
        }

        $results = 0;
        foreach ($data['blocks'] as $value) {
            if (empty($value['id'])) {
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
                        'updated_by' => get_logged_in_user_id(),
                    ]);
            } else {
                $block = AccommodationBlock::find($value['id']);
                if (! $block) {
                    continue;
                }

                $results = $block->update([
                    'residence_id' => $data['resident_id'],
                    'name' => trim($value['name']),
                    'total_rooms' => trim($value['total_rooms']),
                    'total_floors' => trim($value['total_floors']),
                    'gender' => trim($value['gender']),
                    'status' => trim($value['status']),
                    'active_flag' => 1,
                    'updated_by' => get_logged_in_user_id(),
                ]);
            }
        }

        //        Accommodation::find($data['resident_id'])->update(['total_rooms' => $total_rooms]);

        if ($results) {
            return back()->with('success', 'Blocks Created Successfully!!!');
        }

        return back()->with('error', 'Blocks Creation Unsuccessful!!!');
    }

    public function generateRoomsStore(array $data)
    {
        if (empty($data['rooms'][1])) {
            return back()->with('error', 'List of Room is Empty!!!');
        }

        $block = AccommodationBlock::find($data['block_id']);
        $residence = Accommodation::find($data['residence_id']);

        $total_rooms = $block->total_rooms;
        foreach ($data['rooms'] as $value) {
            for ($i = $value['room_no_from']; $i <= $value['room_no_to']; $i++) {
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
        abort_if(! $data['room'], 404, 'Room not found.');

        $data['venue_id'] = Accommodation::find($data['room']->residence_id)?->venue_id;
        $data['roommates'] = AssignedRoomEpisode::where(['room_id' => $data['room']->id, 'event_id' => get_logged_in_user_event_id()])->get();
        $data['participants'] = Registrant::where('event_id', get_logged_in_user_event_id())->get();

        return view('admin.accommodation.room.room', $data);
    }

    public function accommodationRoomUpdate(array $data)
    {
        $room = AccommodationRoom::find($data['id']);
        if (! $room) {
            return back()->with('error', 'Room was not found!!!');
        }

        $room->update([
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

    public function occupancyReport($venueId, $eventId)
    {
        $residences = Accommodation::where('venue_id', $venueId)->orderBy('name')->get(['id', 'name']);

        $blocks = AccommodationBlock::whereIn('residence_id', $residences->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name', 'residence_id']);

        $blockIds = $blocks->pluck('id');

        $capacityByBlock = AccommodationRoom::whereIn('block_id', $blockIds)
            ->selectRaw('block_id, SUM(total_occupants) as capacity')
            ->groupBy('block_id')
            ->get()
            ->pluck('capacity', 'block_id');

        $roomIdsByBlock = AccommodationRoom::whereIn('block_id', $blockIds)
            ->get(['id', 'block_id'])
            ->groupBy('block_id')
            ->map(fn ($rooms) => $rooms->pluck('id'));

        $occupiedByRoom = AssignedRoomEpisode::where('event_id', $eventId)
            ->where('active_flag', 1)
            ->whereIn('room_id', $roomIdsByBlock->flatten())
            ->selectRaw('room_id, COUNT(*) as occupied')
            ->groupBy('room_id')
            ->get()
            ->pluck('occupied', 'room_id');

        $data['residences'] = $residences->map(function ($residence) use ($blocks, $capacityByBlock, $roomIdsByBlock, $occupiedByRoom) {
            $residence->blocks = $blocks->where('residence_id', $residence->id)->map(function ($block) use ($capacityByBlock, $roomIdsByBlock, $occupiedByRoom) {
                $capacity = (int) ($capacityByBlock[$block->id] ?? 0);
                $occupied = ($roomIdsByBlock[$block->id] ?? collect())
                    ->sum(fn ($roomId) => $occupiedByRoom[$roomId] ?? 0);

                $block->capacity = $capacity;
                $block->occupied = $occupied;
                $block->vacant = max($capacity - $occupied, 0);

                return $block;
            });

            return $residence;
        });

        return view('admin.accommodation.occupancy_report', $data);
    }

    public function batchRoomAllocationIndex($eventId)
    {
        $data['batches'] = BatchLog::where('event_id', $eventId)
            ->orderByDesc('id')
            ->paginate(50)->withQueryString();

        $batchNos = $data['batches']->pluck('batch_no');

        // Batch-resolve every count needed for the table in a handful of
        // queries total, regardless of page size - no query per row.
        $stages = RegistrantStage::whereIn('batch_no', $batchNos)
            ->where('need_accommodation', 1)
            ->get(['id', 'batch_no']);

        $registrants = Registrant::whereIn('stage_id', $stages->pluck('id'))
            ->get(['stage_id', 'room_no', 'total_fee'])->keyBy('stage_id');

        $paidTotals = OnlinePayment::whereIn('reg_id', $stages->pluck('id'))
            ->selectRaw('reg_id, SUM(amount_paid) as total_paid')
            ->groupBy('reg_id')
            ->pluck('total_paid', 'reg_id');

        // Same rule as AssignRoomEpisodeService::addRoomMate(): full payment
        // needs no approval, a partial payment does (approved == 2) - a
        // registrant missing an OnlinePayment row entirely defaults to
        // not-approved (1), same as addRoomMate()'s `?? 1`.
        $approvedTotals = OnlinePayment::whereIn('reg_id', $stages->pluck('id'))
            ->selectRaw('reg_id, MAX(approved) as approved')
            ->groupBy('reg_id')
            ->pluck('approved', 'reg_id');

        $data['needing_accommodation_counts'] = $stages->countBy('batch_no');

        $data['eligible_counts'] = $stages->groupBy('batch_no')->map(
            fn ($group) => $group->filter(function ($stage) use ($registrants, $paidTotals, $approvedTotals) {
                $registrant = $registrants->get($stage->id);

                return $registrant && empty($registrant->room_no)
                    && Utils::isEligibleForRoomAllocation($registrant->total_fee, $paidTotals[$stage->id] ?? 0, $approvedTotals[$stage->id] ?? 1);
            })->count()
        );

        return view('admin.accommodation.batch_room_allocation', $data);
    }

    public function assignRoomsForBatch($batchNo, $eventId)
    {
        $batchLog = BatchLog::where(['batch_no' => $batchNo, 'event_id' => $eventId])->first();
        if (! $batchLog) {
            return back()->with('error', 'Batch was not found for this event!!!');
        }

        // Only which batch to process comes from the client - every
        // registrant's eligibility (paid, needs accommodation, no room
        // yet) is re-derived here rather than trusted from anywhere else.
        $stages = RegistrantStage::where('event_id', $eventId)
            ->where('batch_no', $batchNo)
            ->where('need_accommodation', 1)
            ->get();

        $registrants = Registrant::whereIn('stage_id', $stages->pluck('id'))->get()->keyBy('stage_id');

        $paidTotals = OnlinePayment::whereIn('reg_id', $stages->pluck('id'))
            ->selectRaw('reg_id, SUM(amount_paid) as total_paid')
            ->groupBy('reg_id')
            ->pluck('total_paid', 'reg_id');

        // Same rule as AssignRoomEpisodeService::addRoomMate() and the
        // eligibility count above: full payment needs no approval, a
        // partial payment does.
        $approvedTotals = OnlinePayment::whereIn('reg_id', $stages->pluck('id'))
            ->selectRaw('reg_id, MAX(approved) as approved')
            ->groupBy('reg_id')
            ->pluck('approved', 'reg_id');

        $assigned = 0;
        $skipped = 0;

        foreach ($stages as $stage) {
            $registrant = $registrants->get($stage->id);
            if (! $registrant || ! empty($registrant->room_no)) {
                continue;
            }

            if (! Utils::isEligibleForRoomAllocation($registrant->total_fee, $paidTotals[$stage->id] ?? 0, $approvedTotals[$stage->id] ?? 1)) {
                continue;
            }

            (new RoomAllocationPipe)->autoRoomAllocation([
                'registrant' => $stage,
                'confirmed_registrant' => $registrant,
            ]);

            $registrant->refresh()->room_no ? $assigned++ : $skipped++;
        }

        $message = ($assigned || $skipped)
            ? "Assigned rooms for {$assigned} registrant(s)."
                .($skipped ? " {$skipped} had no available room." : '')
            : 'No registrants in this batch are currently eligible for room assignment.';

        return back()->with('success', $message);
    }
}
