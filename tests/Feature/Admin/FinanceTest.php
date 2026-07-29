<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Dropdown;
use App\Models\Admin\OnlinePayment;
use App\Models\FinancialEpisode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private function financeUser(): User
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    public function test_a_finance_user_can_approve_an_online_payment(): void
    {
        $user = $this->financeUser();

        $payment = OnlinePayment::create([
            'reg_id' => 1,
            'event_id' => 1,
            'payment_mode' => 'Paystack',
            'transaction_no' => 'TXN-1',
            'amount_to_pay' => 100,
            'amount_paid' => 100,
            'approved' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('financial_clearance'), [
            'payment_id' => $payment->id,
            'comment' => 'Verified against bank statement',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('online_payments', [
            'id' => $payment->id,
            'approved' => 2,
            'comment' => 'Verified against bank statement',
        ]);
    }

    public function test_a_finance_user_can_disapprove_an_online_payment(): void
    {
        $user = $this->financeUser();

        $payment = OnlinePayment::create([
            'reg_id' => 1,
            'event_id' => 1,
            'payment_mode' => 'Paystack',
            'transaction_no' => 'TXN-1',
            'amount_to_pay' => 100,
            'amount_paid' => 100,
            'approved' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('financial_clearance'), [
            'payment_id' => $payment->id,
            'approved' => 1,
            'comment' => 'Chargeback reported',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('online_payments', [
            'id' => $payment->id,
            'approved' => 1,
            'comment' => 'Chargeback reported',
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'finance',
            'description' => 'Payment disapproved',
        ]);
    }

    public function test_financial_clearance_rejects_an_invalid_approved_value(): void
    {
        // Anything outside {1, 2} must not be trusted verbatim - falls
        // back to the safe default (Approved) rather than storing garbage.
        $user = $this->financeUser();

        $payment = OnlinePayment::create([
            'reg_id' => 1, 'event_id' => 1, 'payment_mode' => 'Paystack',
            'transaction_no' => 'TXN-1', 'amount_to_pay' => 100, 'amount_paid' => 100, 'approved' => 0,
        ]);

        $this->actingAs($user)->post(route('financial_clearance'), [
            'payment_id' => $payment->id,
            'approved' => 999,
            'comment' => 'Tampered value',
        ]);

        $this->assertDatabaseHas('online_payments', [
            'id' => $payment->id,
            'approved' => 2,
        ]);
    }

    public function test_a_user_without_the_finance_role_cannot_approve_a_payment(): void
    {
        $user = User::factory()->create(['event_id' => 1]);

        $payment = OnlinePayment::create([
            'reg_id' => 1,
            'event_id' => 1,
            'amount_to_pay' => 100,
            'amount_paid' => 100,
            'approved' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('financial_clearance'), [
            'payment_id' => $payment->id,
            'comment' => 'Should not be allowed',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('online_payments', [
            'id' => $payment->id,
            'approved' => 0,
        ]);
    }

    public function test_a_finance_user_can_record_an_income_entry(): void
    {
        $user = $this->financeUser();
        $transactionType = Dropdown::create([
            'lookup_code_id' => 1,
            'full_name' => 'Registration Fee',
            'active_flag' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('financial_entry'), [
            'entry_type' => 'Income',
            'transaction_type' => $transactionType->id,
            'description' => 'Cash payment at registration desk',
            'transaction_date' => now()->toDateString(),
            'amount' => 250.50,
        ]);

        $response->assertRedirect(route('financial_entries'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('financial_episodes', [
            'event_id' => 1,
            'entry_type' => 'Income',
            'amount' => 250.50,
            'description' => 'Cash payment at registration desk',
        ]);
    }

    public function test_financial_entry_requires_a_positive_amount(): void
    {
        $user = $this->financeUser();
        $transactionType = Dropdown::create([
            'lookup_code_id' => 1,
            'full_name' => 'Registration Fee',
            'active_flag' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('financial_entry'), [
            'entry_type' => 'Income',
            'transaction_type' => $transactionType->id,
            'description' => 'Invalid entry',
            'transaction_date' => now()->toDateString(),
            'amount' => 0,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('financial_episodes', 0);
    }

    public function test_a_finance_user_can_update_an_existing_entry(): void
    {
        $user = $this->financeUser();
        $transactionType = Dropdown::create([
            'lookup_code_id' => 1,
            'full_name' => 'Miscellaneous',
            'active_flag' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $entry = FinancialEpisode::create([
            'transaction_id' => 1,
            'event_id' => 1,
            'entry_type' => 'Expense',
            'transaction_type' => $transactionType->id,
            'description' => 'Original description',
            'transaction_date' => now()->toDateString(),
            'amount' => 50,
            'active_flag' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('financial_entry'), [
            'id' => $entry->id,
            'entry_type' => 'Expense',
            'transaction_type' => $transactionType->id,
            'description' => 'Updated description',
            'transaction_date' => now()->toDateString(),
            'amount' => 75,
        ]);

        $response->assertRedirect(route('financial_entries'));

        $this->assertDatabaseHas('financial_episodes', [
            'id' => $entry->id,
            'description' => 'Updated description',
            'amount' => 75,
        ]);
    }
}
