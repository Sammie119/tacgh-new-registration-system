<?php

namespace Tests\Feature\Admin;

use App\Models\Admin\Event;
use App\Models\Admin\OnlinePayment;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Services\Admin\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
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

    public function test_make_payment_clamps_a_requested_amount_above_the_real_outstanding_balance(): void
    {
        // Regression: total_fee came straight from the request with no
        // upper bound - a client could request any amount regardless of
        // what was actually owed.
        $event = $this->createEvent();
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 100]);

        $result = (new PaymentService)->makePayment(['stage_id' => $stage->id, 'total_fee' => 999999]);

        // 100 (owed) * 100 (Paystack expects amount in pesewas/kobo).
        $this->assertSame(10000.0, $result['amount']);
    }

    public function test_make_payment_still_allows_a_partial_payment_below_the_balance(): void
    {
        $event = $this->createEvent();
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 100]);

        $result = (new PaymentService)->makePayment(['stage_id' => $stage->id, 'total_fee' => 30]);

        $this->assertSame(3000.0, $result['amount']);
    }

    public function test_make_payment_accounts_for_amounts_already_paid(): void
    {
        $event = $this->createEvent();
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 100]);
        OnlinePayment::create(['reg_id' => $stage->id, 'event_id' => $event->id, 'amount_paid' => 60, 'amount_to_pay' => 100]);

        $result = (new PaymentService)->makePayment(['stage_id' => $stage->id, 'total_fee' => 999999]);

        $this->assertSame(4000.0, $result['amount']);
    }

    public function test_make_payment_clamps_a_batch_request_to_the_sum_of_real_outstanding_balances(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK1', 'total_registration_fees' => 0,
        ]);
        $stage1 = $this->createStage($event, 'TOK1', 1);
        $stage2 = $this->createStage($event, 'TOK2', 1);
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage1->id, 'event_id' => $event->id, 'total_fee' => 100]);
        Registrant::create(['registration_no' => 'REG-2', 'stage_id' => $stage2->id, 'event_id' => $event->id, 'total_fee' => 150]);

        $result = (new PaymentService)->makePayment(['batch' => 'batch', 'batch_id' => $batchLog->id, 'total_fee' => 999999]);

        // (100 + 150) * 100.
        $this->assertSame(25000.0, $result['amount']);
    }

    public function test_payment_receipt_allocates_the_real_confirmed_amount_not_the_fabricated_session_breakdown(): void
    {
        // The core exploit this closes: a batch coordinator could pay a
        // token amount via Paystack while the session('batch_payment')
        // data claimed a much larger amount was paid per registrant,
        // fabricating "fully paid" status without the money ever arriving.
        // amount_paid recorded per registrant must come from the real
        // Paystack-confirmed total, not from that session data.
        $event = $this->createEvent();
        $stage1 = $this->createStage($event, 'TOK1', 1);
        $stage2 = $this->createStage($event, 'TOK2', 1);
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage1->id, 'event_id' => $event->id, 'total_fee' => 100]);
        Registrant::create(['registration_no' => 'REG-2', 'stage_id' => $stage2->id, 'event_id' => $event->id, 'total_fee' => 150]);

        $paymentDetails = [
            'channel' => 'card', 'id' => 'PSK-REAL-1', 'amount' => 100 * 100, // only GHS 100 actually paid
            'transaction_date' => now()->toDateString(), 'paid_at' => now()->toDateString(),
        ];
        $response = ['message' => 'Approved', 'status' => 1];

        (new PaymentService)->paymentReceipt([
            'batch' => RegistrantStage::where('batch_no', 1)->get(),
        ], $paymentDetails, $response);

        // The total actually recorded must equal what Paystack really
        // confirmed (100), never the 250 combined face value of both
        // registrants' fees that a fabricated session breakdown could claim.
        $this->assertSame(100.0, (float) OnlinePayment::sum('amount_paid'));
        $this->assertLessThanOrEqual(100, OnlinePayment::where('reg_id', $stage1->id)->sum('amount_paid'));
        $this->assertLessThanOrEqual(150, OnlinePayment::where('reg_id', $stage2->id)->sum('amount_paid'));
    }

    public function test_payment_receipt_never_records_more_than_what_was_actually_confirmed(): void
    {
        $event = $this->createEvent();
        $stage1 = $this->createStage($event, 'TOK1', 1);
        $stage2 = $this->createStage($event, 'TOK2', 1);
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage1->id, 'event_id' => $event->id, 'total_fee' => 100]);
        Registrant::create(['registration_no' => 'REG-2', 'stage_id' => $stage2->id, 'event_id' => $event->id, 'total_fee' => 150]);

        $paymentDetails = [
            'channel' => 'card', 'id' => 'PSK-REAL-2', 'amount' => 5 * 100, // only GHS 5 actually paid
            'transaction_date' => now()->toDateString(), 'paid_at' => now()->toDateString(),
        ];
        $response = ['message' => 'Approved', 'status' => 1];

        (new PaymentService)->paymentReceipt([
            'batch' => RegistrantStage::where('batch_no', 1)->get(),
        ], $paymentDetails, $response);

        $this->assertSame(5.0, (float) OnlinePayment::sum('amount_paid'));
    }
}
