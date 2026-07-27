<?php

namespace Tests\Feature;

use App\Jobs\WhatsappNotificationJob;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
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

    public function test_auto_room_allocation_assigns_a_room_and_sets_checkin_date(): void
    {
        Bus::fake();

        $venue = EventVenue::create([
            'name' => 'Main Campus', 'region_id' => 1, 'location' => 'Address',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => $venue->id, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Regular Room',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        // RoomAllocationPipe unconditionally looks up a Dropdown row whose
        // full_name exactly matches the EventFees description (a separate,
        // already-tracked fragile-join issue) — satisfy it here so this test
        // exercises the checkin_date fix rather than that unrelated bug.
        Dropdown::create([
            'lookup_code_id' => 1, 'full_name' => 'Regular Room', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        // vw_registration inner-joins event_fees on both accommodation_type and
        // registration_type, so a registrant needs a valid row for each to
        // appear in the view at all (see the migration for this gap).
        $registrationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 100, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $residence = Accommodation::create([
            'name' => 'Hostel A', 'venue_id' => $venue->id, 'gender' => 'M',
            'status' => 'Active', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => $residence->id, 'total_rooms' => 10,
            'total_floors' => 1, 'gender' => 'M', 'status' => 'Active', 'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = AccommodationRoom::create([
            'room_no' => 101, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
            'residence_id' => $residence->id, 'total_occupants' => 2, 'prefix' => 'R', 'suffix' => '',
            'gender' => 'M', 'type' => 'Regular', 'assign' => 1, 'created_by' => 1, 'updated_by' => 1,
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
        $registrant = Registrant::create([
            'registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id,
            'accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee->id,
            'total_fee' => 0,
        ]);

        $result = (new RoomAllocationPipe)->autoRoomAllocation([
            'registrant' => $stage->toArray(),
            'confirmed_registrant' => $registrant,
        ]);

        $this->assertTrue($result);
        $this->assertDatabaseHas('assigned_room_episodes', [
            'room_id' => $room->id,
            'event_id' => $event->id,
            'registrant_id' => $stage->id,
        ]);

        $episode = \App\Models\Admin\AssignedRoomEpisode::first();
        $this->assertNotNull($episode->checkin_date);

        $this->assertSame($room->id, $registrant->fresh()->room_no);
        Bus::assertDispatched(WhatsappNotificationJob::class);
    }
}
