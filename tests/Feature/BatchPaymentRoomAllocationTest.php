<?php

namespace Tests\Feature;

use App\Models\Admin\Event;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Services\Registrant\RegistrantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchPaymentRoomAllocationTest extends TestCase
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

    private function createStage(Event $event, string $token, int $batchNo): RegistrantStage
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

    public function test_batch_payment_skips_an_unconfirmed_registrant_instead_of_crashing(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 20260101000000, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);

        // registrant_id 999999 matches no RegistrantStage/Registrant at all —
        // used to crash inside RoomAllocationPipe with no guard beforehand.
        $response = (new RegistrantService)->batchPayment([
            'batch_id' => $batchLog->id,
            'total_amount_paid' => 0,
            'total_fee_to_pay' => 0,
            'reg' => [
                ['registrant_id' => 999999],
            ],
        ]);

        $this->assertTrue(session()->has('success'));
        $this->assertDatabaseHas('batch_logs', [
            'id' => $batchLog->id,
            'confirmed' => 'Yes',
        ]);
    }

    public function test_batch_payment_does_not_confirm_or_allocate_when_a_real_fee_is_owed_but_client_claims_zero(): void
    {
        // Regression: total_fee_to_pay and total_amount_paid are both
        // client-submitted (hidden/editable) fields. A coordinator used to
        // be able to submit both as 0 regardless of what was actually
        // owed, which set BatchLog.total_registration_fees to the
        // fabricated 0 and then used that same fabricated value to decide
        // the batch was fully paid - triggering free room allocation for
        // every registrant with no payment ever happening.
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $stage = $this->createStage($event, 'TOK1', 1);
        $registrant = Registrant::create([
            'registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 380,
        ]);

        $response = (new RegistrantService)->batchPayment([
            'batch_id' => $batchLog->id,
            'total_amount_paid' => 0,
            'total_fee_to_pay' => 0, // fabricated - the registrant really owes 380
            'reg' => [
                ['registrant_id' => $stage->id, 'amount_paid' => 0],
            ],
        ]);

        $this->assertTrue(session()->has('error'));
        $this->assertDatabaseHas('batch_logs', [
            'id' => $batchLog->id,
            'confirmed' => 'No',
            'total_registration_fees' => 380,
        ]);
        $this->assertNull($registrant->fresh()->room_no);
    }

    public function test_batch_payment_computes_the_real_total_even_when_an_owing_registrant_is_omitted_from_the_request(): void
    {
        // Regression: the real-total computation must come from every
        // registrant actually in the batch (batch_no), not just whichever
        // ones the client includes in reg[] - omitting an owing registrant
        // from the submitted list must not make the batch look fully paid.
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $freeStage = $this->createStage($event, 'TOK1', 1);
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $freeStage->id, 'event_id' => $event->id, 'total_fee' => 0]);
        $owingStage = $this->createStage($event, 'TOK2', 1);
        $owingRegistrant = Registrant::create(['registration_no' => 'REG-2', 'stage_id' => $owingStage->id, 'event_id' => $event->id, 'total_fee' => 380]);

        // Only the free registrant is submitted - the owing one is left out.
        $response = (new RegistrantService)->batchPayment([
            'batch_id' => $batchLog->id,
            'total_amount_paid' => 0,
            'total_fee_to_pay' => 0,
            'reg' => [
                ['registrant_id' => $freeStage->id, 'amount_paid' => 0],
            ],
        ]);

        $this->assertTrue(session()->has('error'));
        $this->assertDatabaseHas('batch_logs', [
            'id' => $batchLog->id,
            'confirmed' => 'No',
            'total_registration_fees' => 380,
        ]);
        $this->assertNull($owingRegistrant->fresh()->room_no);
    }

    public function test_batch_payment_still_confirms_and_allocates_a_genuinely_free_batch(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $stage = $this->createStage($event, 'TOK1', 1);
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 0]);

        $response = (new RegistrantService)->batchPayment([
            'batch_id' => $batchLog->id,
            'total_amount_paid' => 0,
            'total_fee_to_pay' => 0,
            'reg' => [
                ['registrant_id' => $stage->id, 'amount_paid' => 0],
            ],
        ]);

        $this->assertTrue(session()->has('success'));
        $this->assertDatabaseHas('batch_logs', [
            'id' => $batchLog->id,
            'confirmed' => 'Yes',
            'total_registration_fees' => 0,
        ]);
    }
}
