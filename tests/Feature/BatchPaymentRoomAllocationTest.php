<?php

namespace Tests\Feature;

use App\Models\Admin\Event;
use App\Models\BatchLog;
use App\Services\Registrant\RegistrantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchPaymentRoomAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_payment_skips_an_unconfirmed_registrant_instead_of_crashing(): void
    {
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
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
}
