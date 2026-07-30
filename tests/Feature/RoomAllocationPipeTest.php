<?php

namespace Tests\Feature;

use App\Jobs\WhatsappNotificationJob;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\Dropdown;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\Admin\EventVenue;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Pipelines\Registration\RoomAllocationPipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RoomAllocationPipeTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(): Event
    {
        $venue = EventVenue::create([
            'name' => 'Main Campus', 'region_id' => 1, 'location' => 'Address',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => $venue->id, 'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    private function createRoom(Event $event, array $overrides = []): AccommodationRoom
    {
        $residence = Accommodation::create([
            'name' => 'Hostel A', 'venue_id' => $event->venue_id, 'gender' => 'M',
            'status' => 'Active', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => $residence->id, 'total_rooms' => 10,
            'total_floors' => 1, 'gender' => 'M', 'status' => 'Active', 'created_by' => 1, 'updated_by' => 1,
        ]);

        return AccommodationRoom::create(array_merge([
            'room_no' => 101, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
            'residence_id' => $residence->id, 'total_occupants' => 2, 'prefix' => 'R', 'suffix' => '',
            'gender' => 'M', 'type' => 'Regular', 'assign' => 1, 'created_by' => 1, 'updated_by' => 1,
        ], $overrides));
    }

    private function createRegistrant(Event $event, EventFees $accommodationFee): Registrant
    {
        $registrationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 100, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Kofi', 'surname' => 'Mensah', 'gender' => 3,
            'date_of_birth' => now()->subYears(30)->toDateString(), 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567', 'email' => 'kofi@example.com',
            'address' => 'Address', 'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => $event->id, 'disability' => 0,
            'confirmed' => 'Yes', 'token' => 'TOK1',
        ]);

        return Registrant::create([
            'registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id,
            'accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee->id,
            'total_fee' => 0,
        ]);
    }

    public function test_auto_room_allocation_assigns_a_room_and_sets_checkin_date(): void
    {
        Bus::fake();

        $event = $this->createEvent();
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Regular Room',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = $this->createRoom($event, ['type' => 'Regular']);
        $registrant = $this->createRegistrant($event, $accommodationFee);

        $result = (new RoomAllocationPipe)->autoRoomAllocation([
            'registrant' => $registrant->stage->toArray(),
            'confirmed_registrant' => $registrant,
        ]);

        $this->assertTrue($result);
        $this->assertDatabaseHas('assigned_room_episodes', [
            'room_id' => $room->id,
            'event_id' => $event->id,
            'registrant_id' => $registrant->stage_id,
        ]);

        $episode = AssignedRoomEpisode::first();
        $this->assertNotNull($episode->checkin_date);

        $this->assertSame($room->id, $registrant->fresh()->room_no);
        Bus::assertDispatched(WhatsappNotificationJob::class);
    }

    public function test_accommodation_description_with_no_matching_dropdown_does_not_crash(): void
    {
        Bus::fake();

        $event = $this->createEvent();
        // "Deluxe Suite" has no matching Dropdown row - this used to crash
        // with "Attempt to read property id on null".
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Deluxe Suite',
            'fee_amount' => 80, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $this->createRoom($event, ['type' => 'Special', 'special_acc' => 99]);
        $registrant = $this->createRegistrant($event, $accommodationFee);

        $result = (new RoomAllocationPipe)->autoRoomAllocation([
            'registrant' => $registrant->stage->toArray(),
            'confirmed_registrant' => $registrant,
        ]);

        // No crash. No room assigned: with no resolvable Dropdown match it
        // falls back to Regular, and the only room here is Special.
        $this->assertTrue($result);
        $this->assertDatabaseCount('assigned_room_episodes', 0);
        $this->assertNull($registrant->fresh()->room_no);
    }

    public function test_a_plain_accommodation_description_with_no_special_rooms_configured_still_matches_regular(): void
    {
        // Regression: a real event whose accommodation option is plainly
        // named (e.g. "Accommodation", "2 in a room with AC" - no "Regular"
        // substring) and which has zero Special-type rooms configured must
        // still be routed to Regular rooms, not silently fail every
        // allocation by being routed into an empty Special-room pool. This
        // also covers the case where a Dropdown row happens to share the
        // exact same name (e.g. a leftover from a deprecated category) but
        // no matching Special room actually exists for it.
        Bus::fake();

        $event = $this->createEvent();
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Accommodation',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        Dropdown::create([
            'lookup_code_id' => 9, 'full_name' => 'Accommodation', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = $this->createRoom($event, ['type' => 'Regular']);
        $registrant = $this->createRegistrant($event, $accommodationFee);

        $result = (new RoomAllocationPipe)->autoRoomAllocation([
            'registrant' => $registrant->stage->toArray(),
            'confirmed_registrant' => $registrant,
        ]);

        $this->assertTrue($result);
        $this->assertSame($room->id, $registrant->fresh()->room_no);
    }

    public function test_special_accommodation_with_a_matching_dropdown_is_allocated_correctly(): void
    {
        Bus::fake();

        $event = $this->createEvent();
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'VIP Suite',
            'fee_amount' => 200, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $dropdown = Dropdown::create([
            'lookup_code_id' => 1, 'full_name' => 'VIP Suite', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = $this->createRoom($event, ['type' => 'Special', 'special_acc' => $dropdown->id]);
        $registrant = $this->createRegistrant($event, $accommodationFee);

        $result = (new RoomAllocationPipe)->autoRoomAllocation([
            'registrant' => $registrant->stage->toArray(),
            'confirmed_registrant' => $registrant,
        ]);

        $this->assertTrue($result);
        $this->assertDatabaseHas('assigned_room_episodes', [
            'room_id' => $room->id,
            'event_id' => $event->id,
        ]);
        $this->assertSame($room->id, $registrant->fresh()->room_no);
    }

    public function test_a_second_registrant_is_not_double_booked_into_an_already_full_room(): void
    {
        // Regression: the capacity check is now re-verified inside a locked
        // transaction at assignment time, not just trusted from the initial
        // query snapshot. This proves the re-check actually rejects a room
        // that became full since that snapshot was taken (called
        // sequentially here since PHPUnit can't simulate true concurrency,
        // but it exercises the same "recount under lock, then decide" path).
        Bus::fake();

        $event = $this->createEvent();
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Regular Room',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = $this->createRoom($event, ['type' => 'Regular', 'total_occupants' => 1]);
        $first = $this->createRegistrant($event, $accommodationFee);

        $result1 = (new RoomAllocationPipe)->autoRoomAllocation([
            'registrant' => $first->stage->toArray(),
            'confirmed_registrant' => $first,
        ]);
        $this->assertTrue($result1);
        $this->assertSame($room->id, $first->fresh()->room_no);

        // Second registrant: same event, same room the only candidate, but
        // it's now full - must not be double-booked into it.
        $registrationFee2 = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 100, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $stage2 = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Kwame', 'surname' => 'Boateng', 'gender' => 3,
            'date_of_birth' => now()->subYears(30)->toDateString(), 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234599', 'whatsapp_number' => '+233541234599', 'email' => 'kwame@example.com',
            'address' => 'Address', 'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => $event->id, 'disability' => 0,
            'confirmed' => 'Yes', 'token' => 'TOK2',
        ]);
        $second = Registrant::create([
            'registration_no' => 'REG-2', 'stage_id' => $stage2->id, 'event_id' => $event->id,
            'accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee2->id,
            'total_fee' => 0,
        ]);

        $result2 = (new RoomAllocationPipe)->autoRoomAllocation([
            'registrant' => $second->stage->toArray(),
            'confirmed_registrant' => $second,
        ]);

        $this->assertTrue($result2);
        $this->assertNull($second->fresh()->room_no);
        $this->assertDatabaseCount('assigned_room_episodes', 1);
    }

    public function test_returns_false_gracefully_when_the_event_no_longer_exists(): void
    {
        Bus::fake();

        $event = $this->createEvent();
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Regular Room',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registrant = $this->createRegistrant($event, $accommodationFee);
        $registrantData = $registrant->stage->toArray();
        $registrantData['event_id'] = 999999;

        $result = (new RoomAllocationPipe)->autoRoomAllocation([
            'registrant' => $registrantData,
            'confirmed_registrant' => $registrant,
        ]);

        $this->assertFalse($result);
        $this->assertDatabaseCount('assigned_room_episodes', 0);
    }
}
