<?php

namespace Tests\Feature\Registrant;

use App\Models\Admin\Event;
use App\Models\BatchLog;
use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveFromBatchTest extends TestCase
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

    private function createBatchLog(Event $event, int $batchNo): BatchLog
    {
        return BatchLog::create([
            'batch_no' => $batchNo, 'event_id' => $event->id, 'email' => "batch{$batchNo}@example.com",
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567',
            'confirmed' => 'No', 'token' => "BTOK{$batchNo}", 'total_registration_fees' => 0,
        ]);
    }

    private function createStage(Event $event, int $batchNo): RegistrantStage
    {
        return RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes',
            'token' => 'TOK'.$batchNo, 'batch_no' => $batchNo,
        ]);
    }

    public function test_an_unauthenticated_visitor_cannot_remove_a_registrant(): void
    {
        $event = $this->createEvent();
        $stage = $this->createStage($event, 20260101000000);

        $response = $this->get(route('remove_registrant_from_batch', $stage->id));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($stage);
    }

    public function test_an_individual_registrant_session_cannot_remove_a_batch_registrant(): void
    {
        $event = $this->createEvent();
        $stage = $this->createStage($event, 20260101000000);
        $individual = $this->createStage($event, 0);

        session(['registrant' => $individual]);

        $response = $this->get(route('remove_registrant_from_batch', $stage->id));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($stage);
    }

    public function test_a_batch_coordinator_cannot_remove_a_registrant_from_a_different_batch(): void
    {
        $event = $this->createEvent();
        $ownBatch = $this->createBatchLog($event, 20260101000001);
        $otherBatchStage = $this->createStage($event, 20260101000002);

        session(['registrant' => $ownBatch]);

        $response = $this->get(route('remove_registrant_from_batch', $otherBatchStage->id));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($otherBatchStage);
    }

    public function test_a_batch_coordinator_can_remove_a_registrant_from_their_own_batch(): void
    {
        $event = $this->createEvent();
        $batchNo = 20260101000003;
        $batchLog = $this->createBatchLog($event, $batchNo);
        $stage = $this->createStage($event, $batchNo);

        session(['registrant' => $batchLog]);

        $response = $this->get(route('remove_registrant_from_batch', $stage->id));

        $response->assertOk();
        $this->assertSoftDeleted('registrants_stage', ['id' => $stage->id]);
    }
}
