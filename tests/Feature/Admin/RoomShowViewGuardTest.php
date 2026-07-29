<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoomShowViewGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads_when_a_roommates_registrant_no_longer_exists(): void
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);
        $residence = Accommodation::create(['name' => 'Hostel A', 'venue_id' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => $residence->id, 'total_rooms' => 10,
            'total_floors' => 1, 'gender' => 'M', 'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = AccommodationRoom::create([
            'room_no' => 101, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
            'residence_id' => $residence->id, 'total_occupants' => 2, 'prefix' => 'R', 'suffix' => '',
            'created_by' => 1, 'updated_by' => 1,
        ]);

        // registrant_id doesn't match any real Registrant row — used to
        // crash via $registrant->registration_no (etc.) with no null check.
        AssignedRoomEpisode::create([
            'room_id' => $room->id, 'event_id' => $event->id, 'registrant_id' => 999999,
            'checkin_date' => now()->toDateString(), 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('room', $room->id));

        $response->assertOk();
    }

    public function test_gender_dropdown_reflects_the_rooms_actual_gender(): void
    {
        // Regression: the Gender <select> used to check $room->assign (the
        // Active/Blocked 0/1 flag) instead of $room->gender, so it always
        // defaulted to "Male" regardless of the room's real gender.
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);
        $residence = Accommodation::create(['name' => 'Hostel A', 'venue_id' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => $residence->id, 'total_rooms' => 10,
            'total_floors' => 1, 'gender' => 'F', 'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = AccommodationRoom::create([
            'room_no' => 101, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
            'residence_id' => $residence->id, 'total_occupants' => 2, 'prefix' => 'R', 'suffix' => '',
            'gender' => 'F', 'assign' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('room', $room->id));

        $response->assertOk();
        $response->assertSee('selected  value="F"', false);
        $response->assertDontSee('selected  value="M"', false);
    }
}
