<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\OnlinePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExecuteFormCreateEditViewAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(RolesEnum $roleEnum): User
    {
        $role = Role::create(['name' => $roleEnum->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_a_low_privilege_role_cannot_open_the_create_role_form(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);

        $response = $this->actingAs($lowPrivilegeUser)->get('/execute_form/create/role');

        $response->assertForbidden();
    }

    public function test_system_developer_can_open_the_create_role_form(): void
    {
        $developer = $this->userWithRole(RolesEnum::SYSTEMDEVELOPER);

        $response = $this->actingAs($developer)->get('/execute_form/create/role');

        $response->assertOk();
    }

    public function test_a_low_privilege_role_cannot_open_the_edit_user_form(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $target = User::factory()->create();

        $response = $this->actingAs($lowPrivilegeUser)->get("/execute_form/edit/user/{$target->id}");

        $response->assertForbidden();
    }

    public function test_system_admin_can_open_the_edit_user_form(): void
    {
        $admin = $this->userWithRole(RolesEnum::SYSTEMADMIN);
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->get("/execute_form/edit/user/{$target->id}");

        $response->assertOk();
    }

    public function test_a_role_outside_finance_cannot_view_the_financial_clearance_form(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $payment = OnlinePayment::create([
            'reg_id' => 1, 'event_id' => 1, 'amount_to_pay' => 100, 'amount_paid' => 100, 'approved' => 0,
        ]);

        $response = $this->actingAs($lowPrivilegeUser)->get("/execute_form/view/financial_clearance/{$payment->id}");

        $response->assertForbidden();
    }

    public function test_finance_role_can_view_the_financial_clearance_form(): void
    {
        $finance = $this->userWithRole(RolesEnum::FINANCE);
        $payment = OnlinePayment::create([
            'reg_id' => 1, 'event_id' => 1, 'amount_to_pay' => 100, 'amount_paid' => 100, 'approved' => 0,
        ]);

        $response = $this->actingAs($finance)->get("/execute_form/view/financial_clearance/{$payment->id}");

        $response->assertOk();
    }

    public function test_an_unmapped_create_type_still_falls_through_to_no_form_selected(): void
    {
        $user = $this->userWithRole(RolesEnum::ROOMALLOCATOR);

        $response = $this->actingAs($user)->get('/execute_form/create/not_a_real_type');

        $response->assertOk();
        $response->assertSee('No Form Selected');
    }
}
