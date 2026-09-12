<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Dropdown;
use App\Models\Admin\OnlinePayment;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinancePaymentsIndexTest extends TestCase
{
    use RefreshDatabase;

    private function financeUser(): User
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    private function createPayment(int $i, string $firstName, int $titleId = 1): OnlinePayment
    {
        $stage = RegistrantStage::create([
            'title' => $titleId, 'first_name' => $firstName, 'surname' => 'Test', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => sprintf('+2335412%04d', $i), 'email' => "payer{$i}@example.com",
            'address' => 'Address', 'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => 1, 'disability' => 0, 'token' => "PAYTOK{$i}",
        ]);

        Registrant::create([
            'registration_no' => "PAY-{$i}",
            'stage_id' => $stage->id,
            'event_id' => 1,
        ]);

        return OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'payment_mode' => 'Paystack',
            'transaction_no' => "TXN-{$i}", 'amount_to_pay' => 100, 'amount_paid' => 100,
            'approved' => 0, 'payment_status' => 1,
        ]);
    }

    public function test_payments_page_does_not_run_a_query_per_row(): void
    {
        $user = $this->financeUser();
        $title = Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Mr.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        foreach (range(1, 15) as $i) {
            $this->createPayment($i, "Payer{$i}", $title->id);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($user)->get(route('payments'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSee('PAYER1');
        $response->assertSee('PAY-1');

        // Without batching, 15 rows would have cost 3+ queries each (45+).
        $this->assertLessThan(20, $queryCount, "Expected a small, constant number of queries, got {$queryCount} for 15 rows.");
    }

    public function test_payments_page_paginates_results(): void
    {
        $user = $this->financeUser();

        foreach (range(1, 55) as $i) {
            $this->createPayment($i, "Payer-{$i}-END");
        }

        $firstPage = $this->actingAs($user)->get(route('payments'));
        $firstPage->assertOk();
        $firstPage->assertSee('PAYER-55-END');
        $firstPage->assertDontSee('PAYER-1-END');

        $secondPage = $this->actingAs($user)->get(route('payments', ['page' => 2]));
        $secondPage->assertOk();
        $secondPage->assertSee('PAYER-1-END');
        $secondPage->assertDontSee('PAYER-55-END');
    }

    public function test_payments_page_can_be_searched_by_name_or_registration_number(): void
    {
        $user = $this->financeUser();
        $this->createPayment(1, 'Kwame');
        $this->createPayment(2, 'Abena');

        $byName = $this->actingAs($user)->get(route('payments', ['search' => 'Kwame']));
        $byName->assertOk();
        $byName->assertSee('KWAME');
        $byName->assertDontSee('ABENA');

        $byRegNo = $this->actingAs($user)->get(route('payments', ['search' => 'PAY-2']));
        $byRegNo->assertOk();
        $byRegNo->assertSee('ABENA');
        $byRegNo->assertDontSee('KWAME');
    }

    public function test_payments_with_no_amount_due_are_excluded(): void
    {
        $user = $this->financeUser();
        $payment = $this->createPayment(1, 'ZeroFee');
        $payment->update(['amount_to_pay' => 0]);

        $response = $this->actingAs($user)->get(route('payments'));

        $response->assertOk();
        $response->assertDontSee('ZEROFEE');
    }

    public function test_amount_paid_total_reflects_all_payments_for_a_registrant(): void
    {
        $user = $this->financeUser();
        $payment = $this->createPayment(1, 'PartialPayer');
        $payment->update(['amount_to_pay' => 200, 'amount_paid' => 100]);

        // A second payment record for the same registrant should be
        // included in the running total used to enable the Approve button.
        OnlinePayment::create([
            'reg_id' => $payment->reg_id, 'event_id' => 1, 'amount_to_pay' => 200,
            'amount_paid' => 100, 'approved' => 0, 'payment_status' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('payments'));

        $response->assertOk();
        $response->assertSee('PARTIALPAYER');
    }

    public function test_a_registrant_with_multiple_payments_appears_once_with_the_summed_amount(): void
    {
        // Regression: a registrant paying in installments used to appear
        // once per OnlinePayment row (same name repeated), each row only
        // showing its own individual amount rather than the running total.
        $user = $this->financeUser();
        $payment = $this->createPayment(1, 'Installments');
        $payment->update(['amount_to_pay' => 300, 'amount_paid' => 100]);
        OnlinePayment::create([
            'reg_id' => $payment->reg_id, 'event_id' => 1, 'amount_to_pay' => 300,
            'amount_paid' => 150, 'approved' => 0, 'payment_status' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('payments'));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertSame(1, substr_count($content, 'INSTALLMENTS'), 'Expected the registrant to appear exactly once.');
        // Summed: 100 + 150 = 250.00, not either individual row's amount.
        $response->assertSee('250.00');
        $response->assertDontSee('100.00');
        $response->assertDontSee('150.00');
    }

    public function test_the_approved_select_shows_disapproved_by_default(): void
    {
        // The select only appears for a partial payment - a full payment
        // shows the "Fully Paid" badge instead (see the next test file).
        $user = $this->financeUser();
        $payment = $this->createPayment(1, 'NotYetReviewed');
        $payment->update(['amount_paid' => 50]);

        $response = $this->actingAs($user)->get(route('payments'));

        $response->assertOk();
        $response->assertSeeInOrder(['<option value="1"  selected >Disapproved</option>', '<option value="2" >Approved</option>'], false);
    }

    public function test_the_approved_select_shows_approved_when_cleared(): void
    {
        $user = $this->financeUser();
        $payment = $this->createPayment(1, 'Cleared');
        $payment->update(['amount_paid' => 50, 'approved' => 2]);

        $response = $this->actingAs($user)->get(route('payments'));

        $response->assertOk();
        $response->assertSeeInOrder(['<option value="1" >Disapproved</option>', '<option value="2"  selected >Approved</option>'], false);
    }

    public function test_a_fully_paid_registrant_shows_a_fully_paid_badge_instead_of_the_approval_select(): void
    {
        // A full payment needs no approval decision at all - showing an
        // actionable Approved/Disapproved control would be misleading.
        $user = $this->financeUser();
        $this->createPayment(1, 'PaidInFull');

        $response = $this->actingAs($user)->get(route('payments'));

        $response->assertOk();
        $response->assertSee('Fully Paid');
        // "Disapproved" also appears in a static HTML comment elsewhere on
        // the page, unrelated to any row - check for the actual <option>
        // markup the select would render instead.
        $this->assertStringNotContainsString('<option value="1"', $response->getContent());
    }
}
