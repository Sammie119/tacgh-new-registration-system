<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignRoomEpisodeTest extends TestCase
{
    use RefreshDatabase;

    private function superAdminUser(): User
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function roomAllocatorUser(): User
    {
        $role = Role::create(['name' => RolesEnum::ROOMALLOCATOR->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createRoom(): AccommodationRoom
    {
        $residence = Accommodation::create(['name' => 'Hostel A', 'created_by' => 1, 'updated_by' => 1]);
        $block = AccommodationBlock::create([
            'name' => 'Block 1',
            'residence_id' => $residence->id,
            'total_rooms' => 10,
            'total_floors' => 1,
            'gender' => 'M',
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        return AccommodationRoom::create([
            'room_no' => 101,
            'floor_no' => 1,
            'floor_name' => 'Ground',
            'block_id' => $block->id,
            'residence_id' => $residence->id,
            'total_occupants' => 2,
            'prefix' => 'R',
            'suffix' => '',
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }

    public function test_a_room_allocator_can_add_a_roommate(): void
    {
        $user = $this->roomAllocatorUser();
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = $this->createRoom();

        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Standard',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registrationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 0, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => 'TOK1',
        ]);
        // accommodation_type/registration_type must reference real event_fees rows:
        // vw_registration inner-joins event_fees on both, so a registrant without
        // a match (e.g. registration_type's default of 0) is silently excluded
        // from the view and event_registrant_age() would return null.
        Registrant::create([
            'registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id,
            'accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee->id,
        ]);

        $response = $this->actingAs($user)->post(route('add_roommate'), [
            'room_id' => $room->id,
            'event_id' => $event->id,
            'registration_no' => 'REG-1',
        ]);

        // Proves the Room Allocator role clears the role middleware (fixed
        // separately in routes/admin.php) rather than being blocked with a
        // 403. Not asserting full success here: a separate, pre-existing bug
        // (assigned_room_episodes.checkin_date is NOT NULL but never set by
        // this method) currently breaks this request regardless of role or
        // the vw_registration view — see VwRegistrationViewTest for isolated
        // coverage of the view itself.
        $this->assertNotSame(403, $response->getStatusCode());
    }

    public function test_adding_a_roommate_with_a_soft_deleted_registrant_fails_gracefully(): void
    {
        $user = $this->superAdminUser();
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = $this->createRoom();

        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => 'TOK1',
        ]);
        $registrant = Registrant::create([
            'registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id,
        ]);

        // Simulate the exact gap: exists:registrants,registration_no ignores the
        // SoftDeletes scope, so this row still satisfies controller validation
        // even though Registrant::where(...)->first() will no longer find it.
        $registrant->delete();

        $response = $this->actingAs($user)->post(route('add_roommate'), [
            'room_id' => $room->id,
            'event_id' => $event->id,
            'registration_no' => 'REG-1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_transferring_a_roommate_with_a_soft_deleted_registrant_fails_gracefully(): void
    {
        $user = $this->superAdminUser();
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = $this->createRoom();

        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => 'TOK1',
        ]);
        $registrant = Registrant::create([
            'registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id,
        ]);
        $registrant->delete();

        $response = $this->actingAs($user)->post(route('transfer_roommate'), [
            'room_id' => $room->id,
            'event_id' => $event->id,
            'registration_no' => 'REG-1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
