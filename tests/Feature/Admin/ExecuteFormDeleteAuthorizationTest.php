<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExecuteFormDeleteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(RolesEnum $roleEnum): User
    {
        $role = Role::create(['name' => $roleEnum->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_a_low_privilege_role_cannot_delete_a_user_via_execute_form(): void
    {
        Role::create(['name' => RolesEnum::SYSTEMADMIN->value]);
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $target = User::factory()->create();

        $response = $this->actingAs($lowPrivilegeUser)->get("/execute_form/delete/user/{$target->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_a_low_privilege_role_cannot_delete_a_role_via_execute_form(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $role = Role::create(['name' => 'Target Role']);

        $response = $this->actingAs($lowPrivilegeUser)->get("/execute_form/delete/role/{$role->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_a_low_privilege_role_cannot_delete_a_permission_via_execute_form(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $permission = Permission::create(['name' => 'Target Permission']);

        $response = $this->actingAs($lowPrivilegeUser)->get("/execute_form/delete/permission/{$permission->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('permissions', ['id' => $permission->id]);
    }

    public function test_a_low_privilege_role_cannot_delete_a_financial_entry_via_execute_form(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $entry = \App\Models\FinancialEpisode::create([
            'transaction_id' => 'TX1', 'event_id' => 1, 'entry_type' => 'Income',
            'transaction_type' => 'Registration', 'transaction_date' => now()->toDateString(),
            'amount' => 100, 'description' => 'Test', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($lowPrivilegeUser)->get("/execute_form/delete/financial_entry/{$entry->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('financial_episodes', ['id' => $entry->id]);
    }

    public function test_the_correct_role_can_still_delete_a_user_via_execute_form(): void
    {
        $admin = $this->userWithRole(RolesEnum::SYSTEMADMIN);
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->get("/execute_form/delete/user/{$target->id}");

        $response->assertOk();
        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    public function test_an_unmapped_type_still_falls_through_to_no_form_selected(): void
    {
        $user = $this->userWithRole(RolesEnum::ROOMALLOCATOR);

        $response = $this->actingAs($user)->get('/execute_form/delete/not_a_real_type/1');

        $response->assertOk();
        $response->assertSee('No Form Selected');
    }
}
