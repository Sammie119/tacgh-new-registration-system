<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\OnlinePayment;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function financeUser(): User
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    private function createStage(): RegistrantStage
    {
        return RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => 1, 'disability' => 0, 'token' => 'TOK1',
        ]);
    }

    public function test_the_modal_lists_every_payment_for_the_registrant(): void
    {
        $user = $this->financeUser();
        $stage = $this->createStage();
        OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'payment_mode' => 'Paystack',
            'transaction_no' => 'TXN-1', 'amount_to_pay' => 200, 'amount_paid' => 100, 'approved' => 1,
        ]);
        OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'payment_mode' => 'Cash',
            'transaction_no' => 'TXN-2', 'amount_to_pay' => 200, 'amount_paid' => 100, 'approved' => 2,
        ]);

        $response = $this->actingAs($user)->get("/execute_form/view/payment_history/{$stage->id}");

        $response->assertOk();
        $response->assertSeeInOrder(['100.00', 'Paystack', 'TXN-1']);
        $response->assertSeeInOrder(['100.00', 'Cash', 'TXN-2']);
        $response->assertSee('Approved');
        $response->assertSee('Disapproved');
    }

    public function test_a_role_outside_finance_cannot_view_payment_history(): void
    {
        $user = User::factory()->create(['event_id' => 1]);
        $stage = $this->createStage();

        $response = $this->actingAs($user)->get("/execute_form/view/payment_history/{$stage->id}");

        $response->assertForbidden();
    }
}
