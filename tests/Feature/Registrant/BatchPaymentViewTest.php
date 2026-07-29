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

    public function test_a_partially_paid_registrant_still_shows_edit_and_delete_buttons(): void
    {
        // Regression: Edit/Delete used to hide as soon as ANY payment
        // landed (amount_paid <= 0 check), not once fully paid.
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
        $response->assertSee('title="Edit"', false);
        $response->assertSee('title="Delete"', false);
    }

    public function test_a_fully_paid_registrant_hides_edit_and_delete_buttons(): void
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
        $response->assertDontSee('title="Edit"', false);
        $response->assertDontSee('title="Delete"', false);
    }

    public function test_the_amount_paid_column_shows_the_real_amount_paid_so_far(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 100,
        ]);
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 100]);
        OnlinePayment::create(['reg_id' => $stage->id, 'event_id' => $event->id, 'amount_paid' => 42.50, 'amount_to_pay' => 100]);

        $response = $this->withSession(['registrant' => $batchLog])->get(route('registrant_page_batch'));

        $response->assertOk();
        $response->assertSeeInOrder(['Amount Paid', '42.50']);
    }

    public function test_the_pay_button_still_shows_when_an_earlier_registrant_is_unpaid_even_if_the_last_one_is_fully_paid(): void
    {
        // Regression: the summary row used to check whichever $amount_paid
        // value was left over from the LAST iteration of the registrant
        // loop, not the batch's real aggregate status - so if the last
        // registrant happened to be fully paid, the Pay button vanished
        // even though earlier registrants still owed money.
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 200,
        ]);
        $unpaidStage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $unpaidStage->id, 'event_id' => $event->id, 'total_fee' => 100]);

        $paidStage = $this->createStage($event, 'TOK2');
        Registrant::create(['registration_no' => 'REG-2', 'stage_id' => $paidStage->id, 'event_id' => $event->id, 'total_fee' => 100]);
        OnlinePayment::create(['reg_id' => $paidStage->id, 'event_id' => $event->id, 'amount_paid' => 100, 'amount_to_pay' => 100]);

        $response = $this->withSession(['registrant' => $batchLog])->get(route('registrant_page_batch'));

        $response->assertOk();
        $response->assertSee('id="batchPaymentSubmitBtn"', false);
    }

    public function test_the_amount_input_defaults_to_the_remaining_balance_not_the_amount_already_paid(): void
    {
        // The "Amount Paid" column already shows what's been paid so far -
        // the editable input should default to what's still OWED, so a
        // coordinator can just accept the pre-filled value to pay the rest.
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
        $response->assertSee('value="70.00"', false);
        $response->assertDontSee('value="30.00"', false);
    }

    public function test_the_amount_input_value_never_contains_a_thousands_separator(): void
    {
        // Regression: number_format() defaults to a "," thousands
        // separator (e.g. "1,099.50"), and <input type="number"> silently
        // rejects any value containing a comma - the field just renders
        // blank in the browser for any four-figure remaining balance,
        // even though the raw HTML value= attribute looks fine.
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 1100,
        ]);
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 1100]);
        OnlinePayment::create(['reg_id' => $stage->id, 'event_id' => $event->id, 'amount_paid' => 0.50, 'amount_to_pay' => 1100]);

        $response = $this->withSession(['registrant' => $batchLog])->get(route('registrant_page_batch'));

        $response->assertOk();
        $response->assertSee('value="1099.50"', false);
        $response->assertDontSee('value="1,099.50"', false);
    }

    public function test_the_total_input_value_never_contains_a_thousands_separator(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 1100,
        ]);
        $stage = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage->id, 'event_id' => $event->id, 'total_fee' => 1100]);

        $response = $this->withSession(['registrant' => $batchLog])->get(route('registrant_page_batch'));

        $response->assertOk();
        $response->assertSee('value="1100.00"', false);
        $response->assertDontSee('value="1,100.00"', false);
    }

    public function test_the_batch_table_does_not_show_an_attendance_type_column(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 100,
        ]);
        $this->createStage($event, 'TOK1');

        $response = $this->withSession(['registrant' => $batchLog])->get(route('registrant_page_batch'));

        $response->assertOk();
        $response->assertDontSee('attendance_type');
    }

    public function test_the_total_input_defaults_to_the_remaining_batch_balance(): void
    {
        $event = $this->createEvent();
        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 200,
        ]);
        $stage1 = $this->createStage($event, 'TOK1');
        Registrant::create(['registration_no' => 'REG-1', 'stage_id' => $stage1->id, 'event_id' => $event->id, 'total_fee' => 100]);
        OnlinePayment::create(['reg_id' => $stage1->id, 'event_id' => $event->id, 'amount_paid' => 30, 'amount_to_pay' => 100]);

        $stage2 = $this->createStage($event, 'TOK2');
        Registrant::create(['registration_no' => 'REG-2', 'stage_id' => $stage2->id, 'event_id' => $event->id, 'total_fee' => 100]);
        // stage2 has paid nothing yet.

        $response = $this->withSession(['registrant' => $batchLog])->get(route('registrant_page_batch'));

        $response->assertOk();
        // Owed: (100 - 30) + (100 - 0) = 170.00, not the 30.00 paid so far.
        $response->assertSee('id="total"', false);
        $response->assertSee('value="170.00"', false);
    }

    public function test_the_pay_button_is_hidden_once_the_whole_batch_is_fully_paid(): void
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
        $response->assertDontSee('id="batchPaymentSubmitBtn"', false);
    }
}
