<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\OnlinePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormViewServiceGuardTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function systemAdminUser(): User
    {
        $role = Role::create(['name' => RolesEnum::SYSTEMADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_user_roles_view_returns_404_for_a_nonexistent_user(): void
    {
        // 'user_roles' is only reachable by System Admin, matching the
        // role middleware on the real user_roles route.
        $admin = $this->systemAdminUser();

        // A different, nonexistent user id — used to crash via
        // $user->id / $user->getRoleNames() with no null check.
        $response = $this->actingAs($admin)->get('/execute_form/view/user_roles/999999');

        $response->assertNotFound();
    }

    public function test_blocks_setup_view_returns_404_for_a_nonexistent_accommodation(): void
    {
        $admin = $this->adminUser();

        // Used to crash via $resident->id / $resident->total_blocks in
        // setup_block.blade.php with no null check.
        $response = $this->actingAs($admin)->get('/execute_form/view/blocks_setup/999999');

        $response->assertNotFound();
    }

    public function test_financial_clearance_view_returns_404_for_a_nonexistent_payment(): void
    {
        $admin = $this->adminUser();

        // Used to crash via $data['payment']->reg_id immediately after the
        // unguarded OnlinePayment::find($id).
        $response = $this->actingAs($admin)->get('/execute_form/view/financial_clearance/999999');

        $response->assertNotFound();
    }

    public function test_financial_clearance_view_loads_for_a_real_payment(): void
    {
        $admin = $this->adminUser();
        $payment = OnlinePayment::create([
            'reg_id' => 1, 'event_id' => 1, 'amount_to_pay' => 100, 'amount_paid' => 100, 'approved' => 0,
        ]);

        $response = $this->actingAs($admin)->get("/execute_form/view/financial_clearance/{$payment->id}");

        $response->assertOk();
    }
}
