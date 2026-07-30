<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Event;
use App\Models\Admin\EventVenue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventDetailsViewTest extends TestCase
{
    use RefreshDatabase;

    private function systemAdminUser(): User
    {
        $role = Role::create(['name' => RolesEnum::SYSTEMADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_the_modal_shows_the_events_details(): void
    {
        $admin = $this->systemAdminUser();
        $venue = EventVenue::create([
            'name' => 'Main Campus', 'region_id' => 1, 'location' => 'Address',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $event = Event::create([
            'name' => 'APOSA CAMPMEETING 2026', 'description' => 'Annual meeting', 'code_prefix' => 'AP26',
            'start_date' => '2026-08-01', 'end_date' => '2026-08-10',
            'is_payment_required' => 'Yes', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => $venue->id, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($admin)->get("/execute_form/view/event_details/{$event->id}");

        $response->assertOk();
        $response->assertSee('APOSA CAMPMEETING 2026');
        $response->assertSee('AP26');
        $response->assertSee('Main Campus');
        $response->assertSee('In-Progress');
    }

    public function test_returns_404_for_a_nonexistent_event(): void
    {
        $admin = $this->systemAdminUser();

        $response = $this->actingAs($admin)->get('/execute_form/view/event_details/999999');

        $response->assertNotFound();
    }

    public function test_a_role_outside_the_allowed_group_cannot_view_event_details(): void
    {
        $user = User::factory()->create();
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'Pending', 'active_flag' => 1,
            'venue_id' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->get("/execute_form/view/event_details/{$event->id}");

        $response->assertForbidden();
    }

    public function test_loads_gracefully_when_the_venue_no_longer_exists(): void
    {
        $admin = $this->systemAdminUser();
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'Pending', 'active_flag' => 1,
            'venue_id' => 999999, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($admin)->get("/execute_form/view/event_details/{$event->id}");

        $response->assertOk();
        $response->assertSee('N/A');
    }
}
