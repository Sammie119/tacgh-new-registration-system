<?php

namespace Tests\Feature\Registrant;

use App\Models\Admin\Event;
use App\Models\Admin\OnlinePayment;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchPaymentViewTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(): Event
    {
        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    private function createStage(Event $event, string $token): RegistrantStage
    {
        return RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes',
            'token' => $token, 'batch_no' => 1,
        ]);
    }

    public function test_a_partially_paid_registrants_amount_field_is_not_readonly(): void
    {
        // Regression: the field locked as soon as ANY payment landed
        // (amount_paid > 0), making a partial payment look like the
        // registrant was fully settled when they still owed money.
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 100,
        ]);
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 100]);
        OnlinePayment::create(['reg_id' => $stage->id, 'event_id' => $event->id, 'amount_paid' => 30, 'amount_to_pay' => 100]);

        $response = $this->withSession(['registrant' => $batchLog])->get(route('registrant_page_batch'));

        $response->assertOk();
        $response->assertDontSee('readonly', false);
    }

    public function test_a_fully_paid_registrants_amount_field_is_readonly(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 100,
        ]);
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 100]);
        OnlinePayment::create(['reg_id' => $stage->id, 'event_id' => $event->id, 'amount_paid' => 100, 'amount_to_pay' => 100]);

        $response = $this->withSession(['registrant' => $batchLog])->get(route('registrant_page_batch'));

        $response->assertOk();
        $response->assertSee('readonly', false);
    }
}
