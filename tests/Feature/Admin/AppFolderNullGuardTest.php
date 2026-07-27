<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Event;
use App\Models\Admin\EventVenue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppFolderNullGuardTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function systemAdmin(): User
    {
        $role = Role::create(['name' => RolesEnum::SYSTEMADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function systemDeveloper(): User
    {
        $role = Role::create(['name' => RolesEnum::SYSTEMDEVELOPER->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_dashboard_loads_when_the_users_event_no_longer_exists(): void
    {
        $user = $this->superAdmin();
        $user->update(['event_id' => 999999]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_updating_a_nonexistent_permission_fails_gracefully(): void
    {
        $user = $this->systemDeveloper();

        $response = $this->actingAs($user)->put(route('permission'), [
            'id' => 999999, 'name' => 'Test',
        ]);

        $response->assertRedirect(route('permissions'));
        $response->assertSessionHas('error');
    }

    public function test_updating_a_nonexistent_role_fails_gracefully(): void
    {
        $user = $this->systemDeveloper();

        $response = $this->actingAs($user)->put(route('role'), [
            'id' => 999999, 'name' => 'Test',
        ]);

        $response->assertRedirect(route('roles'));
        $response->assertSessionHas('error');
    }

    public function test_assigning_permissions_to_a_nonexistent_role_fails_gracefully(): void
    {
        $user = $this->systemDeveloper();
        $permission = Permission::create(['name' => 'Write']);

        $response = $this->actingAs($user)->put(route('assign_permissions'), [
            'id' => 999999, 'permissions' => [$permission->name],
        ]);

        $response->assertRedirect(route('roles'));
        $response->assertSessionHas('error');
    }

    public function test_updating_a_nonexistent_event_fails_gracefully(): void
    {
        $user = $this->systemAdmin();
        $venue = EventVenue::create([
            'name' => 'Main Campus', 'region_id' => 1, 'location' => 'Address',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->put(route('event'), [
            'id' => 999999, 'name' => 'Test', 'description' => 'desc', 'code_prefix' => 'TST',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'venue_id' => $venue->id, 'status' => 'Pending',
        ]);

        $response->assertRedirect(route('events'));
        $response->assertSessionHas('error');
    }

    public function test_updating_a_nonexistent_user_fails_gracefully(): void
    {
        $user = $this->systemAdmin();

        $response = $this->actingAs($user)->put(route('register'), [
            'id' => 999999, 'name' => 'Test', 'email' => 'nonexistent-user-test@example.com',
        ]);

        $response->assertRedirect(route('users'));
        $response->assertSessionHas('error');
    }

    public function test_assigning_roles_to_a_nonexistent_user_fails_gracefully(): void
    {
        $admin = $this->systemAdmin();
        $role = Role::create(['name' => 'Some Role']);
        $permission = Permission::create(['name' => 'Write']);

        $response = $this->actingAs($admin)->post(route('user_roles'), [
            'id' => 999999, 'roles' => [$role->name], 'permissions' => [$permission->name],
        ]);

        $response->assertRedirect(route('users'));
        $response->assertSessionHas('error');
    }

    public function test_assigning_roles_skips_a_role_when_no_matching_permission_was_submitted(): void
    {
        $admin = $this->systemAdmin();
        $target = User::factory()->create();
        $realRole = Role::create(['name' => 'Real Role']);
        $extraRole = Role::create(['name' => 'Extra Role']);
        $realPermission = Permission::create(['name' => 'Write']);

        // Two roles submitted but only one permission — the second role has
        // no corresponding permissions[$key] entry, which used to crash via
        // ->first()->id with no null check when the array index didn't exist.
        $response = $this->actingAs($admin)->post(route('user_roles'), [
            'id' => $target->id,
            'roles' => [$realRole->name, $extraRole->name],
            'permissions' => [$realPermission->name],
        ]);

        $response->assertRedirect(route('users'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('assign_permission_to_roles', [
            'user_id' => $target->id,
            'role_id' => $realRole->id,
            'permission_id' => $realPermission->id,
        ]);
        $this->assertDatabaseCount('assign_permission_to_roles', 1);
    }

    public function test_allocate_rooms_single_returns_404_when_the_users_event_no_longer_exists(): void
    {
        $role = Role::create(['name' => RolesEnum::ROOMALLOCATOR->value]);
        $user = User::factory()->create(['event_id' => 999999]);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('allocate_room'));

        $response->assertNotFound();
    }

    public function test_events_index_loads_when_a_venue_no_longer_exists(): void
    {
        $user = $this->systemAdmin();
        Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => 999999, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('events'));

        $response->assertOk();
    }
}
