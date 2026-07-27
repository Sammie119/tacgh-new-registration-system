<?php

namespace Tests\Feature\Registrant;

use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdorAuthorizationTest extends TestCase
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

    private function createFees(Event $event): array
    {
        $accommodation = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Standard',
            'fee_amount' => 0, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registration = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 0, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        return [$accommodation, $registration];
    }

    private function createStage(Event $event, string $token, int $batchNo = 0): RegistrantStage
    {
        return RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes',
            'token' => $token, 'batch_no' => $batchNo,
        ]);
    }

    private function confirmPayload(RegistrantStage $stage, EventFees $accommodation, EventFees $registration): array
    {
        return [
            'id' => $stage->id,
            'title' => $stage->title, 'first_name' => $stage->first_name, 'surname' => $stage->surname,
            'other_names' => '', 'gender' => $stage->gender, 'date_of_birth' => $stage->date_of_birth,
            'marital_status' => $stage->marital_status, 'nationality_id' => $stage->nationality_id,
            'phone_number' => $stage->phone_number, 'whatsapp_number' => $stage->whatsapp_number,
            'email' => $stage->email, 'address' => $stage->address,
            'position_held' => $stage->position_held, 'profession' => $stage->profession,
            'residence_country_id' => $stage->residence_country_id, 'languages_spoken' => $stage->languages_spoken,
            'need_accommodation' => 1, 'emergency_contacts_name' => $stage->emergency_contacts_name,
            'emergency_contacts_relationship' => 'Sibling', 'emergency_contacts_phone_number' => '+233541234568',
            'attendance_type' => 'In-Person', 'event_id' => $stage->event_id, 'disability' => 0,
            'special_needs' => 'None', 'accommodation_fee' => $accommodation->id,
            'registration_fee' => $registration->id, 'amount_to_pay' => 0,
        ];
    }

    // --- individualRegistrationConfirm ---

    public function test_individual_confirm_denied_without_a_session(): void
    {
        $event = $this->createEvent();
        [$acc, $reg] = $this->createFees($event);
        $stage = $this->createStage($event, 'TOK1');

        $response = $this->post(route('registrant.confirm'), $this->confirmPayload($stage, $acc, $reg));

        $response->assertForbidden();
    }

    public function test_individual_confirm_denied_for_someone_elses_id(): void
    {
        $event = $this->createEvent();
        [$acc, $reg] = $this->createFees($event);
        $stage = $this->createStage($event, 'TOK1');
        $otherStage = $this->createStage($event, 'TOK2');

        session(['registrant' => $otherStage]);

        $response = $this->post(route('registrant.confirm'), $this->confirmPayload($stage, $acc, $reg));

        $response->assertForbidden();
    }

    public function test_individual_confirm_allowed_for_own_id(): void
    {
        $event = $this->createEvent();
        [$acc, $reg] = $this->createFees($event);
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id]);

        session(['registrant' => $stage]);

        $response = $this->post(route('registrant.confirm'), $this->confirmPayload($stage, $acc, $reg));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    // --- individualRegistrationUpdate ---

    public function test_individual_update_denied_for_someone_elses_reg_id(): void
    {
        $event = $this->createEvent();
        [$acc, $reg] = $this->createFees($event);
        $stage = $this->createStage($event, 'TOK1');
        $otherStage = $this->createStage($event, 'TOK2');

        session(['registrant' => $otherStage]);

        $response = $this->post(route('registrant.update'), [
            'reg_id' => $stage->id,
            'accommodation_fee' => $acc->id,
            'registration_fee' => $reg->id,
        ]);

        $response->assertForbidden();
    }

    public function test_individual_update_allowed_for_own_reg_id(): void
    {
        $event = $this->createEvent();
        [$acc, $reg] = $this->createFees($event);
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id]);

        session(['registrant' => $stage]);

        $response = $this->post(route('registrant.update'), [
            'reg_id' => $stage->id,
            'accommodation_fee' => $acc->id,
            'registration_fee' => $reg->id,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    // --- batchRegistrationConfirm (GET) ---

    public function test_batch_confirm_view_denied_for_a_different_batch(): void
    {
        $event = $this->createEvent();
        $ownBatch = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $otherBatchStage = $this->createStage($event, 'TOK1', 2);

        session(['registrant' => $ownBatch]);

        $response = $this->get('/registrant/batch/confirmation/'.$otherBatchStage->id);

        $response->assertForbidden();
    }

    public function test_batch_confirm_view_allowed_for_own_batch(): void
    {
        $event = $this->createEvent();
        [$acc, $reg] = $this->createFees($event);
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $stage = $this->createStage($event, 'TOK1', 1);

        session(['registrant' => $batchLog]);

        $response = $this->get('/registrant/batch/confirmation/'.$stage->id);

        $response->assertOk();
    }

    // --- batchRegistrationConfirmation (POST) ---

    public function test_batch_confirmation_denied_for_a_different_batch(): void
    {
        $event = $this->createEvent();
        [$acc, $reg] = $this->createFees($event);
        $ownBatch = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $otherBatchStage = $this->createStage($event, 'TOK1', 2);

        session(['registrant' => $ownBatch]);

        $response = $this->post(route('batch.confirm'), $this->confirmPayload($otherBatchStage, $acc, $reg));

        $response->assertForbidden();
    }

    public function test_batch_confirmation_allowed_for_own_batch(): void
    {
        $event = $this->createEvent();
        [$acc, $reg] = $this->createFees($event);
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $stage = $this->createStage($event, 'TOK1', 1);

        session(['registrant' => $batchLog]);

        $response = $this->post(route('batch.confirm'), $this->confirmPayload($stage, $acc, $reg));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    // --- batchPayment ---

    public function test_batch_payment_denied_when_batch_id_does_not_match_session(): void
    {
        $event = $this->createEvent();
        $ownBatch = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $otherBatch = BatchLog::create([
            'batch_no' => 2, 'event_id' => $event->id, 'email' => 'batch2@example.com',
            'confirmed' => 'No', 'token' => 'BTOK2', 'total_registration_fees' => 0,
        ]);
        $stage = $this->createStage($event, 'TOK1', 1);
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id]);

        session(['registrant' => $ownBatch]);

        $response = $this->post(route('batch_payment'), [
            'batch_id' => $otherBatch->id,
            'total_amount_paid' => 0,
            'total_fee_to_pay' => 0,
            'reg' => [['registrant_id' => $stage->id, 'amount_paid' => 0]],
        ]);

        $response->assertForbidden();
    }

    public function test_batch_payment_denied_when_a_registrant_belongs_to_a_different_batch(): void
    {
        $event = $this->createEvent();
        $ownBatch = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $otherBatchStage = $this->createStage($event, 'TOK1', 2);
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $otherBatchStage->id, 'event_id' => $event->id]);

        session(['registrant' => $ownBatch]);

        $response = $this->post(route('batch_payment'), [
            'batch_id' => $ownBatch->id,
            'total_amount_paid' => 0,
            'total_fee_to_pay' => 0,
            'reg' => [['registrant_id' => $otherBatchStage->id, 'amount_paid' => 0]],
        ]);

        $response->assertForbidden();
    }

    public function test_batch_payment_allowed_for_own_batch(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $stage = $this->createStage($event, 'TOK1', 1);
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id]);

        session(['registrant' => $batchLog]);

        $response = $this->post(route('batch_payment'), [
            'batch_id' => $batchLog->id,
            'total_amount_paid' => 0,
            'total_fee_to_pay' => 0,
            'reg' => [['registrant_id' => $stage->id, 'amount_paid' => 0]],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }
}
