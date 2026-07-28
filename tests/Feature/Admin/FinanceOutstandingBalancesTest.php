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

class FinanceOutstandingBalancesTest extends TestCase
{
    use RefreshDatabase;

    private function financeUser(): User
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    private function createDebtor(int $i, string $firstName, float $toPay = 200, float $paid = 100, int $titleId = 1): RegistrantStage
    {
        $stage = RegistrantStage::create([
            'title' => $titleId, 'first_name' => $firstName, 'surname' => 'Test', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => sprintf('+2335412%04d', $i), 'email' => "debtor{$i}@example.com",
            'address' => 'Address', 'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => 1, 'disability' => 0, 'token' => "DEBTTOK{$i}",
        ]);

        Registrant::create(['registration_no' => "DEBT-{$i}", 'stage_id' => $stage->id, 'event_id' => 1]);

        OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'payment_mode' => 'Paystack',
            'transaction_no' => "TXN-{$i}", 'amount_to_pay' => $toPay, 'amount_paid' => $paid,
            'approved' => 0, 'payment_status' => 1,
        ]);

        return $stage;
    }

    public function test_outstanding_balances_page_does_not_run_a_query_per_row(): void
    {
        $user = $this->financeUser();
        $title = Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Mr.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        foreach (range(1, 15) as $i) {
            $this->createDebtor($i, "Debtor{$i}", 200, 100, $title->id);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($user)->get(route('outstanding_balances'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSee('DEBTOR1');
        $response->assertSee('DEBT-1');

        $this->assertLessThan(20, $queryCount, "Expected a small, constant number of queries, got {$queryCount} for 15 rows.");
    }

    public function test_outstanding_balances_page_paginates_results(): void
    {
        $user = $this->financeUser();

        foreach (range(1, 55) as $i) {
            $this->createDebtor($i, "Debtor-{$i}-END", 200, 100);
        }

        $firstPage = $this->actingAs($user)->get(route('outstanding_balances'));
        $firstPage->assertOk();
        $firstPage->assertSee('DEBTOR-55-END');
        $firstPage->assertDontSee('DEBTOR-1-END');

        $secondPage = $this->actingAs($user)->get(route('outstanding_balances', ['page' => 2]));
        $secondPage->assertOk();
        $secondPage->assertSee('DEBTOR-1-END');
        $secondPage->assertDontSee('DEBTOR-55-END');
    }

    public function test_outstanding_balances_page_can_be_searched_by_name_or_registration_number(): void
    {
        $user = $this->financeUser();
        $this->createDebtor(1, 'Kwame', 200, 100);
        $this->createDebtor(2, 'Abena', 200, 100);

        $byName = $this->actingAs($user)->get(route('outstanding_balances', ['search' => 'Kwame']));
        $byName->assertOk();
        $byName->assertSee('KWAME');
        $byName->assertDontSee('ABENA');

        $byRegNo = $this->actingAs($user)->get(route('outstanding_balances', ['search' => 'DEBT-2']));
        $byRegNo->assertOk();
        $byRegNo->assertSee('ABENA');
        $byRegNo->assertDontSee('KWAME');
    }

    public function test_fully_paid_registrants_are_excluded(): void
    {
        $user = $this->financeUser();
        $this->createDebtor(1, 'FullyPaid', 200, 200);

        $response = $this->actingAs($user)->get(route('outstanding_balances'));

        $response->assertOk();
        $response->assertDontSee('FULLYPAID');
    }

    public function test_balance_reflects_the_sum_across_multiple_payment_rows(): void
    {
        $user = $this->financeUser();
        $stage = $this->createDebtor(1, 'MultiRow', 100, 100);

        // A second payment row for the same registrant pushes the total
        // owed above what's been paid; the balance must reflect the sum,
        // not just the first row (which alone looks fully paid).
        OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'amount_to_pay' => 100,
            'amount_paid' => 0, 'approved' => 0, 'payment_status' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('outstanding_balances'));

        $response->assertOk();
        $response->assertSee('MULTIROW');
        $response->assertSee('100.00');
    }
}
