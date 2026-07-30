<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\EventFees;
use App\Models\Admin\OnlinePayment;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrantDetailsViewTest extends TestCase
{
    use RefreshDatabase;

    private function roomAllocatorUser(): User
    {
        $role = Role::create(['name' => RolesEnum::ROOMALLOCATOR->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    private function createStage(array $overrides = []): RegistrantStage
    {
        return RegistrantStage::create(array_merge([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567', 'email' => 'ama@example.com',
            'address' => 'Address', 'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => 1, 'disability' => 0, 'confirmed' => 'Yes',
            'token' => 'TOK1',
        ], $overrides));
    }

    public function test_the_modal_shows_the_registrants_profile_and_fee_details(): void
    {
        $user = $this->roomAllocatorUser();
        $stage = $this->createStage();
        $accommodationFee = EventFees::create([
            'event_id' => 1, 'fee_type' => 'accommodation', 'description' => 'Standard Room',
            'fee_amount' => 200, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registrationFee = EventFees::create([
            'event_id' => 1, 'fee_type' => 'registration_fee', 'description' => 'Regular Worker',
            'fee_amount' => 450, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        Registrant::create([
            'registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => 1,
            'accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee->id,
            'total_fee' => 650,
        ]);
        OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'payment_mode' => 'Paystack',
            'transaction_no' => 'TXN-1', 'amount_to_pay' => 650, 'amount_paid' => 300, 'approved' => 2,
        ]);

        $response = $this->actingAs($user)->get("/execute_form/view/registrant_details/{$stage->id}");

        $response->assertOk();
        $response->assertSee('AMA');
        $response->assertSee('MENSAH');
        $response->assertSee('REG-1');
        $response->assertSee('Standard Room');
        $response->assertSee('Regular Worker');
        $response->assertSeeInOrder(['300.00', 'Paystack']);
    }

    public function test_returns_404_for_a_nonexistent_registrant(): void
    {
        $user = $this->roomAllocatorUser();

        $response = $this->actingAs($user)->get('/execute_form/view/registrant_details/999999');

        $response->assertNotFound();
    }

    public function test_a_role_outside_the_allowed_group_cannot_view_registrant_details(): void
    {
        $user = User::factory()->create(['event_id' => 1]);
        $stage = $this->createStage();

        $response = $this->actingAs($user)->get("/execute_form/view/registrant_details/{$stage->id}");

        $response->assertForbidden();
    }
}
