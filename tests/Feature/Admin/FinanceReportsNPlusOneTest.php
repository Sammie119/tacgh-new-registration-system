<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Dropdown;
use App\Models\Admin\Event;
use App\Models\Admin\OnlinePayment;
use App\Models\FinancialEpisode;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceReportsNPlusOneTest extends TestCase
{
    use RefreshDatabase;

    private function financeUser(): User
    {
        Event::create([
            'id' => 1, 'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    private function createOnlinePayment(int $i, string $firstName): OnlinePayment
    {
        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => $firstName, 'surname' => 'Test', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => sprintf('+2335412%04d', $i), 'email' => "reportpayer{$i}@example.com",
            'address' => 'Address', 'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => 1, 'disability' => 0, 'token' => "RPTTOK{$i}",
        ]);

        Registrant::create(['registration_no' => "RPT-{$i}", 'stage_id' => $stage->id, 'event_id' => 1]);

        return OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'amount_to_pay' => 100,
            'amount_paid' => 100, 'approved' => 1, 'payment_status' => 1,
        ]);
    }

    public function test_financial_entries_page_does_not_run_a_query_per_row(): void
    {
        $user = $this->financeUser();
        $type = Dropdown::create(['lookup_code_id' => 23, 'full_name' => 'Registration Fee', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        foreach (range(1, 15) as $i) {
            FinancialEpisode::create([
                'transaction_id' => "TX{$i}", 'event_id' => 1, 'entry_type' => 'Income',
                'transaction_type' => $type->id, 'transaction_date' => now()->toDateString(),
                'amount' => 10 + $i, 'description' => "Entry {$i}", 'active_flag' => 1,
                'created_by' => 1, 'updated_by' => 1,
            ]);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($user)->get(route('financial_entries'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSee('Registration Fee');
        $this->assertLessThan(10, $queryCount, "Expected a small, constant number of queries, got {$queryCount} for 15 rows.");
    }

    public function test_financial_report_does_not_run_a_query_per_row(): void
    {
        $user = $this->financeUser();
        $type = Dropdown::create(['lookup_code_id' => 23, 'full_name' => 'Donations', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        foreach (range(1, 10) as $i) {
            $this->createOnlinePayment($i, "ReportPayer{$i}");

            FinancialEpisode::create([
                'transaction_id' => "TXI{$i}", 'event_id' => 1, 'entry_type' => 'Income',
                'transaction_type' => $type->id, 'transaction_date' => now()->toDateString(),
                'amount' => 10, 'description' => 'Income', 'active_flag' => 1,
                'created_by' => 1, 'updated_by' => 1,
            ]);

            FinancialEpisode::create([
                'transaction_id' => "TXE{$i}", 'event_id' => 1, 'entry_type' => 'Expense',
                'transaction_type' => $type->id, 'transaction_date' => now()->toDateString(),
                'amount' => 5, 'description' => 'Expense', 'active_flag' => 1,
                'created_by' => 1, 'updated_by' => 1,
            ]);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($user)->get(route('financial_report', ['report' => 'generate_report']));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSee('REPORTPAYER1');
        $response->assertSee('Donations');
        // 20 financial entries + 10 online payments would have cost 1+ query
        // per row under the old event_registrant_name()/get_dropdown_name()
        // pattern (30+ queries just for names), on top of the base queries.
        $this->assertLessThan(15, $queryCount, "Expected a small, constant number of queries, got {$queryCount}.");
    }

    public function test_print_financial_report_renders_registrant_and_transaction_names(): void
    {
        $user = $this->financeUser();
        $type = Dropdown::create(['lookup_code_id' => 23, 'full_name' => 'Merchandise', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $this->createOnlinePayment(1, 'PrintPayer');
        FinancialEpisode::create([
            'transaction_id' => 'TXP1', 'event_id' => 1, 'entry_type' => 'Income',
            'transaction_type' => $type->id, 'transaction_date' => now()->toDateString(),
            'amount' => 15, 'description' => 'Print income', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('print_financial_report', ['report' => 'generate_report']));

        $response->assertOk();
        $response->assertSee('PRINTPAYER');
        $response->assertSee('Merchandise');
    }
}
