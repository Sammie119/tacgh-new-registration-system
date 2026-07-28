<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\Dropdown;
use App\Models\Admin\Event;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrantIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_registrants_page_does_not_run_a_query_per_row(): void
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $event = Event::create([
            'name' => 'Test Conference',
            'description' => 'A test event',
            'code_prefix' => 'TC',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'is_payment_required' => 'No',
            'status' => 'In-Progress',
            'active_flag' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);

        $titleLookup = Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Mr.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $genderLookup = Dropdown::create(['lookup_code_id' => 2, 'full_name' => 'Male', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

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
        $room = AccommodationRoom::create([
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

        foreach (range(1, 15) as $i) {
            $stage = RegistrantStage::create([
                'title' => $titleLookup->id,
                'first_name' => "Registrant{$i}",
                'surname' => 'Test',
                'gender' => $genderLookup->id,
                'date_of_birth' => '1990-01-01',
                'marital_status' => 1,
                'nationality_id' => 1,
                'phone_number' => "+23354123{$i}00",
                'email' => "registrant{$i}@example.com",
                'address' => 'Address',
                'position_held' => 1,
                'profession' => 1,
                'residence_country_id' => 1,
                'languages_spoken' => 'English',
                'need_accommodation' => 1,
                'emergency_contacts_name' => 'Contact',
                'attendance_type' => 'In-Person',
                'event_id' => $event->id,
                'disability' => 0,
                'confirmed' => 'Yes',
                'token' => "TOK{$i}",
            ]);

            Registrant::create([
                'registration_no' => "REG-{$i}",
                'stage_id' => $stage->id,
                'event_id' => $event->id,
                'room_no' => $room->id,
                'check_in_by' => $user->id,
            ]);
        }

        DB::enableQueryLog();

        $response = $this->actingAs($user)->get(route('all_registrant'));

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSee('REGISTRANT1');
        $response->assertSee('REG-1');

        // Without batching, 15 rows would have cost 8+ queries each (120+).
        // A handful of fixed, batched queries regardless of row count proves the N+1 is gone.
        $this->assertLessThan(20, $queryCount, "Expected a small, constant number of queries, got {$queryCount} for 15 rows.");
    }

    private function createConfirmedRegistrant(Event $event, int $i, string $firstName): RegistrantStage
    {
        $stage = RegistrantStage::create([
            'title' => 1,
            'first_name' => $firstName,
            'surname' => 'Test',
            'gender' => 1,
            'date_of_birth' => '1990-01-01',
            'marital_status' => 1,
            'nationality_id' => 1,
            'phone_number' => sprintf('+2335412%04d', $i),
            'email' => "registrant{$i}@example.com",
            'address' => 'Address',
            'position_held' => 1,
            'profession' => 1,
            'residence_country_id' => 1,
            'languages_spoken' => 'English',
            'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person',
            'event_id' => $event->id,
            'disability' => 0,
            'confirmed' => 'Yes',
            'token' => "TOK{$i}",
        ]);

        Registrant::create([
            'registration_no' => "REG-{$i}",
            'stage_id' => $stage->id,
            'event_id' => $event->id,
        ]);

        return $stage;
    }

    public function test_all_registrants_page_paginates_results(): void
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'A test event', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);

        foreach (range(1, 55) as $i) {
            $this->createConfirmedRegistrant($event, $i, "Registrant-{$i}-END");
        }

        // Results are ordered by id desc, so page 1 holds the highest ids (51-55, oldest last)
        // and page 2 holds the lowest ids (1-5). The "-END" suffix avoids false substring
        // matches (e.g. "Registrant-1" would otherwise match "Registrant-10").
        $firstPage = $this->actingAs($user)->get(route('all_registrant'));
        $firstPage->assertOk();
        $firstPage->assertSee('REGISTRANT-55-END');
        $firstPage->assertDontSee('REGISTRANT-1-END');

        $secondPage = $this->actingAs($user)->get(route('all_registrant', ['page' => 2]));
        $secondPage->assertOk();
        $secondPage->assertSee('REGISTRANT-1-END');
        $secondPage->assertDontSee('REGISTRANT-55-END');
    }

    public function test_all_registrants_page_can_be_searched(): void
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'A test event', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);

        $this->createConfirmedRegistrant($event, 1, 'Kwame');
        $this->createConfirmedRegistrant($event, 2, 'Abena');

        $response = $this->actingAs($user)->get(route('all_registrant', ['search' => 'Kwame']));

        $response->assertOk();
        $response->assertSee('KWAME');
        $response->assertDontSee('ABENA');
    }
}
