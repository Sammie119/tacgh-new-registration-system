<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Event;
use App\Models\Admin\EventVenue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Hits every page linked from the admin sidebar (resources/views/layouts/sidebar.blade.php)
 * as an authenticated admin holding every role those pages require, one page per test method,
 * asserting each loads (200) instead of crashing.
 */
class AdminMenuSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $venue = EventVenue::create([
            'name' => 'Main Campus', 'region_id' => 1, 'location' => 'Accra',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $event = Event::create([
            'name' => 'Smoke Test Conference', 'description' => 'desc', 'code_prefix' => 'STC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => $venue->id, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->admin = User::factory()->create(['event_id' => $event->id]);

        foreach ([
            RolesEnum::SUPERADMIN, RolesEnum::SYSTEMADMIN, RolesEnum::SYSTEMDEVELOPER,
            RolesEnum::FINANCE, RolesEnum::ROOMALLOCATOR,
        ] as $roleEnum) {
            $this->admin->assignRole(Role::create(['name' => $roleEnum->value]));
        }
    }

    private function assertPageLoads(string $routeName, array $params = []): void
    {
        $response = $this->actingAs($this->admin)->get(route($routeName, $params));

        $response->assertOk();
    }

    public function test_dashboard(): void
    {
        $this->assertPageLoads('dashboard');
    }

    public function test_all_registrants(): void
    {
        $this->assertPageLoads('all_registrant');
    }

    public function test_room_allocation(): void
    {
        $this->assertPageLoads('allocate_room');
    }

    public function test_finance_online_payment(): void
    {
        $this->assertPageLoads('payments');
    }

    public function test_finance_financial_entries(): void
    {
        $this->assertPageLoads('financial_entries');
    }

    public function test_finance_financial_report(): void
    {
        $this->assertPageLoads('financial_report');
    }

    public function test_forms(): void
    {
        $this->assertPageLoads('forms');
    }

    public function test_system_admin_user_management(): void
    {
        $this->assertPageLoads('users');
    }

    public function test_system_admin_venue_setup(): void
    {
        $this->assertPageLoads('venues');
    }

    public function test_system_admin_events(): void
    {
        $this->assertPageLoads('events');
    }

    public function test_system_admin_lookups(): void
    {
        $this->assertPageLoads('categories');
    }

    public function test_system_admin_downloads(): void
    {
        $this->assertPageLoads('downloads');
    }

    public function test_system_admin_roles(): void
    {
        $this->assertPageLoads('roles');
    }

    public function test_system_admin_permissions(): void
    {
        $this->assertPageLoads('permissions');
    }

    public function test_system_admin_audit_log(): void
    {
        $this->assertPageLoads('audit_log');
    }
}
