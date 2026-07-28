<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\Event;
use App\Models\Admin\EventVenue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccommodationOccupancyReportTest extends TestCase
{
    use RefreshDatabase;

    private function roomAllocatorUser(int $venueId): User
    {
        $role = Role::create(['name' => RolesEnum::ROOMALLOCATOR->value]);
        $event = Event::create([
            'name' => 'Occupancy Test Conference', 'description' => 'desc', 'code_prefix' => 'OTC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => $venueId, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);

        return $user;
    }

    public function test_occupancy_report_shows_capacity_and_occupied_counts_per_block(): void
    {
        $venue = EventVenue::create(['name' => 'Main Campus', 'region_id' => 1, 'location' => 'Accra', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $user = $this->roomAllocatorUser($venue->id);
        $event = Event::find($user->event_id);

        $residence = Accommodation::create(['name' => 'Hostel A', 'venue_id' => $venue->id, 'created_by' => 1, 'updated_by' => 1]);
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => $residence->id, 'total_rooms' => 2,
            'total_floors' => 1, 'gender' => 'M', 'created_by' => 1, 'updated_by' => 1,
        ]);
        $roomA = AccommodationRoom::create([
            'room_no' => 101, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
            'residence_id' => $residence->id, 'total_occupants' => 4, 'prefix' => 'R', 'suffix' => '',
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $roomB = AccommodationRoom::create([
            'room_no' => 102, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
            'residence_id' => $residence->id, 'total_occupants' => 4, 'prefix' => 'R', 'suffix' => '',
            'created_by' => 1, 'updated_by' => 1,
        ]);

        foreach ([$roomA, $roomA, $roomB] as $i => $room) {
            AssignedRoomEpisode::create([
                'room_id' => $room->id, 'event_id' => $event->id, 'registrant_id' => 900 + $i,
                'checkin_date' => now()->toDateString(), 'active_flag' => 1,
                'created_by' => 1, 'updated_by' => 1,
            ]);
        }

        $response = $this->actingAs($user)->get(route('occupancy_report'));

        $response->assertOk();
        $response->assertSee('Hostel A');
        $response->assertSee('Block 1');
        // Capacity: 4 + 4 = 8. Occupied: 3 episodes. Vacant: 5.
        $response->assertSeeInOrder(['Block 1', '8', '3', '5']);
    }

    public function test_occupancy_report_excludes_inactive_room_episodes(): void
    {
        $venue = EventVenue::create(['name' => 'Main Campus', 'region_id' => 1, 'location' => 'Accra', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $user = $this->roomAllocatorUser($venue->id);
        $event = Event::find($user->event_id);

        $residence = Accommodation::create(['name' => 'Hostel A', 'venue_id' => $venue->id, 'created_by' => 1, 'updated_by' => 1]);
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => $residence->id, 'total_rooms' => 1,
            'total_floors' => 1, 'gender' => 'M', 'created_by' => 1, 'updated_by' => 1,
        ]);
        $room = AccommodationRoom::create([
            'room_no' => 101, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
            'residence_id' => $residence->id, 'total_occupants' => 4, 'prefix' => 'R', 'suffix' => '',
            'created_by' => 1, 'updated_by' => 1,
        ]);

        // Inactive episode must not count toward occupancy.
        AssignedRoomEpisode::create([
            'room_id' => $room->id, 'event_id' => $event->id, 'registrant_id' => 900,
            'checkin_date' => now()->toDateString(), 'active_flag' => 0,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        // Soft-deleted episode must not count either.
        $deleted = AssignedRoomEpisode::create([
            'room_id' => $room->id, 'event_id' => $event->id, 'registrant_id' => 901,
            'checkin_date' => now()->toDateString(), 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $deleted->delete();

        $response = $this->actingAs($user)->get(route('occupancy_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Block 1', '4', '0', '4']);
    }

    public function test_occupancy_report_returns_404_when_event_has_no_venue(): void
    {
        $role = Role::create(['name' => RolesEnum::ROOMALLOCATOR->value]);
        $event = Event::create([
            'name' => 'No Venue Conference', 'description' => 'desc', 'code_prefix' => 'NVC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => null, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('occupancy_report'));

        $response->assertNotFound();
    }

    public function test_occupancy_report_does_not_run_a_query_per_block(): void
    {
        $venue = EventVenue::create(['name' => 'Main Campus', 'region_id' => 1, 'location' => 'Accra', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $user = $this->roomAllocatorUser($venue->id);
        $event = Event::find($user->event_id);

        $residence = Accommodation::create(['name' => 'Hostel A', 'venue_id' => $venue->id, 'created_by' => 1, 'updated_by' => 1]);

        foreach (range(1, 5) as $i) {
            $block = AccommodationBlock::create([
                'name' => "Block {$i}", 'residence_id' => $residence->id, 'total_rooms' => 1,
                'total_floors' => 1, 'gender' => 'M', 'created_by' => 1, 'updated_by' => 1,
            ]);
            AccommodationRoom::create([
                'room_no' => 100 + $i, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
                'residence_id' => $residence->id, 'total_occupants' => 4, 'prefix' => 'R', 'suffix' => '',
                'created_by' => 1, 'updated_by' => 1,
            ]);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($user)->get(route('occupancy_report'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $this->assertLessThan(15, $queryCount, "Expected a small, constant number of queries, got {$queryCount} for 5 blocks.");
    }
}
