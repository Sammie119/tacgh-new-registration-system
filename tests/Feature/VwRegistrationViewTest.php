<?php

namespace Tests\Feature;

use App\Enums\RolesEnum;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VwRegistrationViewTest extends TestCase
{
    use RefreshDatabase;

    private function createConfirmedRegistrant(Event $event, array $stageOverrides = [], array $registrantOverrides = []): Registrant
    {
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Standard',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registrationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 100, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $stage = RegistrantStage::create(array_merge([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => now()->subYears(30)->toDateString(), 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => 'TOK1',
        ], $stageOverrides));

        return Registrant::create(array_merge([
            'registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id,
            'accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee->id,
            'total_fee' => 150,
        ], $registrantOverrides));
    }

    private function createEvent(): Event
    {
        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    public function test_event_registrant_age_reads_from_the_view_instead_of_crashing(): void
    {
        $event = $this->createEvent();
        $registrant = $this->createConfirmedRegistrant($event, [
            'date_of_birth' => now()->subYears(30)->toDateString(),
        ]);

        $age = event_registrant_age($registrant->stage_id);

        $this->assertIsInt($age);
        $this->assertEqualsWithDelta(30, $age, 1);
    }

    public function test_event_registrant_age_reflects_a_child_under_six(): void
    {
        $event = $this->createEvent();
        $registrant = $this->createConfirmedRegistrant($event, [
            'date_of_birth' => now()->subYears(4)->toDateString(),
        ]);

        $age = event_registrant_age($registrant->stage_id);

        $this->assertLessThan(6, $age);
    }

    public function test_finance_user_can_record_an_online_payment_correction_via_the_view(): void
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create();
        $user->assignRole($role);
        $event = $this->createEvent();
        $user->update(['event_id' => $event->id]);
        $this->createConfirmedRegistrant($event, [], ['registration_no' => 'REG-42']);

        $response = $this->actingAs($user)->post(route('store_online_payment_correction'), [
            'registration_no' => 'REG-42',
            'payment_mode' => 'Cash',
            'transaction_no' => 'TXN-1',
            'amount_paid' => 150,
            'date_paid' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('payments'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('online_payments', [
            'transaction_no' => 'TXN-1',
            'amount_paid' => 150,
            'event_id' => $event->id,
        ]);
    }
}
