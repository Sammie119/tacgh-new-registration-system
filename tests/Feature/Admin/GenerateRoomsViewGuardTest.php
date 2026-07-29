<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GenerateRoomsViewGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads_when_the_blocks_residence_no_longer_exists(): void
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        // residence_id points at an Accommodation that doesn't exist —
        // used to crash via Accommodation::find($block->residence_id)->status.
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => 999999, 'total_rooms' => 10,
            'total_floors' => 1, 'gender' => 'M', 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->get("/execute_form/view/generate_rooms/{$block->id}");

        $response->assertOk();
    }

    public function test_returns_404_when_the_block_itself_does_not_exist(): void
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        // No AccommodationBlock with this id at all — used to crash via
        // $block->status/$block->residence_id with no guard on $block itself.
        $response = $this->actingAs($user)->get('/execute_form/view/generate_rooms/999999');

        $response->assertNotFound();
    }

    public function test_occupant_count_is_scoped_to_the_logged_in_users_active_event(): void
    {
        // Regression: the occupant count used to be hardcoded to event_id=1
        // ("get_total_room_occupants($value->id, 1); //Change 5 to event_id..")
        // instead of the logged-in user's active event, so a room correctly
        // full for event 1 looked full for every other event too.
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create(['event_id' => 2]);
        $user->assignRole($role);

        $block = AccommodationBlock::create([
            'name' => 'Block A', 'residence_id' => 999999, 'total_rooms' => 1,
            'total_floors' => 1, 'gender' => 'M', 'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = AccommodationRoom::create([
            'room_no' => 103, 'floor_no' => 1, 'floor_name' => 'Ground',
            'block_id' => $block->id, 'residence_id' => 999999, 'total_occupants' => 5,
            'assign' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        // 5 occupants under event_id=1 - must not leak into event_id=2's count.
        for ($i = 1; $i <= 5; $i++) {
            AssignedRoomEpisode::create([
                'room_id' => $room->id, 'event_id' => 1, 'registrant_id' => $i,
                'checkin_date' => now()->toDateString(), 'created_by' => 1, 'updated_by' => 1,
            ]);
        }

        $response = $this->actingAs($user)->get("/execute_form/view/generate_rooms/{$block->id}");

        $response->assertOk();
        $response->assertSee('(0 of 5)');
        $response->assertDontSee('(5 of 5)');
    }
}
