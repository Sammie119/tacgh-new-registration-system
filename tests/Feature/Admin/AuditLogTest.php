<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\OnlinePayment;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

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

    private function finance(): User
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_creating_a_user_writes_an_activity_log_entry(): void
    {
        $admin = $this->systemAdmin();

        $this->actingAs($admin)->post(route('register'), [
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'user-management',
            'description' => 'User created',
            'causer_id' => $admin->id,
        ]);
    }

    public function test_updating_a_user_does_not_log_the_plaintext_password(): void
    {
        $admin = $this->systemAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)->put(route('register'), [
            'id' => $target->id,
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ]);

        $activity = Activity::where('log_name', 'user-management')->where('description', 'User updated')->first();

        $this->assertNotNull($activity);
        $this->assertTrue($activity->properties['password_changed']);
        $this->assertStringNotContainsString('brand-new-secret', $activity->properties->toJson());
    }

    public function test_deleting_a_role_writes_an_activity_log_entry(): void
    {
        $developer = $this->systemDeveloper();
        $role = Role::create(['name' => 'Doomed Role']);

        $this->actingAs($developer)->get("/execute_form/delete/role/{$role->id}");

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'role-management',
            'description' => 'Role deleted: Doomed Role',
        ]);
    }

    public function test_assigning_permissions_to_a_role_writes_an_activity_log_entry(): void
    {
        $developer = $this->systemDeveloper();
        $role = Role::create(['name' => 'Editor']);
        $permission = Permission::create(['name' => 'edit-things']);

        $this->actingAs($developer)->put(route('assign_permissions'), [
            'id' => $role->id,
            'permissions' => [$permission->name],
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'role-management',
            'description' => 'Permissions assigned to role: Editor',
        ]);
    }

    public function test_financial_clearance_writes_an_activity_log_entry(): void
    {
        $finance = $this->finance();
        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Addr',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => 1, 'disability' => 0, 'token' => 'TOK1',
        ]);
        $payment = OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'payment_mode' => 'Cash',
            'amount_to_pay' => 100, 'amount_paid' => 100, 'approved' => 1,
        ]);

        $this->actingAs($finance)->post(route('financial_clearance'), [
            'payment_id' => $payment->id,
            'comment' => 'Confirmed with bank statement',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'finance',
            'description' => 'Payment cleared',
        ]);
    }

    public function test_audit_log_page_lists_recent_activity_and_can_be_searched(): void
    {
        $developer = $this->systemDeveloper();
        activity('user-management')->causedBy($developer)->log('User created');
        activity('finance')->causedBy($developer)->log('Payment cleared');

        $response = $this->actingAs($developer)->get(route('audit_log', ['search' => 'Payment']));

        $response->assertOk();
        $response->assertSee('Payment cleared');
        $response->assertDontSee('User created');
    }

    public function test_audit_log_page_is_not_accessible_to_finance_role(): void
    {
        $finance = $this->finance();

        $response = $this->actingAs($finance)->get(route('audit_log'));

        $response->assertForbidden();
    }
}
