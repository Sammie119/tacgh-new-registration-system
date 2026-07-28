<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\BatchLog;
use App\Models\RegistrantStage;
use App\Models\User;
use App\Services\Admin\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportTokensTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(RolesEnum $roleEnum): User
    {
        $role = Role::create(['name' => $roleEnum->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    private function createStage(int $i, array $overrides = []): RegistrantStage
    {
        return RegistrantStage::create(array_merge([
            'title' => 1, 'first_name' => "Reg{$i}", 'surname' => 'Test', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => sprintf('+2335412%04d', $i), 'email' => "token{$i}@example.com",
            'address' => 'Address', 'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => 1, 'disability' => 0, 'confirmed' => 'Yes',
            'batch_no' => 0, 'token' => "TOKEN{$i}",
        ], $overrides));
    }

    private function createBatchLog(int $i, array $overrides = []): BatchLog
    {
        return BatchLog::create(array_merge([
            'batch_no' => 20260101000000 + $i,
            'event_id' => 1,
            'email' => "coordinator{$i}@example.com",
            'phone_number' => sprintf('+2335413%04d', $i),
            'whatsapp_number' => sprintf('+2335413%04d', $i),
            'token' => "BATCHTOKEN{$i}",
        ], $overrides));
    }

    public function test_individual_tab_shows_individual_tokens(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $this->createStage(1, ['first_name' => 'Kwame']);

        $response = $this->actingAs($user)->get(route('tokens_report'));

        $response->assertOk();
        $response->assertSee('KWAME');
        $response->assertSee('TOKEN1');
    }

    public function test_batch_coordinator_tab_shows_token_and_member_count(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $batchNo = 20260101000099;
        $this->createBatchLog(1, ['batch_no' => $batchNo]);
        foreach (range(1, 3) as $i) {
            $this->createStage(100 + $i, ['batch_no' => $batchNo]);
        }

        $response = $this->actingAs($user)->get(route('tokens_report'));

        $response->assertOk();
        $response->assertSee('BATCHTOKEN1');
        $response->assertSeeInOrder([(string) $batchNo, 'BATCHTOKEN1', '3']);
    }

    public function test_batch_member_tab_shows_member_tokens(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $this->createStage(1, ['first_name' => 'Abena', 'batch_no' => 20260101000001]);

        $response = $this->actingAs($user)->get(route('tokens_report'));

        $response->assertOk();
        $response->assertSee('ABENA');
        $response->assertSee('TOKEN1');
    }

    public function test_individual_and_batch_member_queries_are_mutually_exclusive(): void
    {
        $individual = $this->createStage(1, ['batch_no' => 0]);
        $member = $this->createStage(2, ['batch_no' => 20260101000002]);

        $data = app(ReportService::class)->tokens(1)->getData();

        $individualIds = $data['individuals']->pluck('id');
        $memberIds = $data['members']->pluck('id');

        $this->assertTrue($individualIds->contains($individual->id));
        $this->assertFalse($individualIds->contains($member->id));
        $this->assertTrue($memberIds->contains($member->id));
        $this->assertFalse($memberIds->contains($individual->id));
    }

    public function test_tokens_report_excludes_other_events(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $this->createStage(1, ['event_id' => 1, 'first_name' => 'InEvent']);
        $this->createStage(2, ['event_id' => 999, 'first_name' => 'OutOfEvent']);
        $this->createBatchLog(1, ['event_id' => 1]);
        $this->createBatchLog(2, ['event_id' => 999]);

        $data = app(ReportService::class)->tokens(1)->getData();

        $this->assertTrue($data['individuals']->pluck('first_name')->contains('InEvent'));
        $this->assertFalse($data['individuals']->pluck('first_name')->contains('OutOfEvent'));
        $this->assertCount(1, $data['batches']);
    }

    public function test_default_page_load_opens_on_the_individual_tab(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);

        $response = $this->actingAs($user)->get(route('tokens_report'));

        $response->assertOk();
        $response->assertSee('tab-pane fade show active" id="individual-tokens"', false);
        $response->assertDontSee('tab-pane fade show active" id="batch-tokens"', false);
        $response->assertDontSee('tab-pane fade show active" id="member-tokens"', false);
    }

    public function test_searching_the_batch_tab_reopens_on_the_batch_tab(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $this->createBatchLog(1, ['email' => 'coordinator-lookup@example.com']);

        $response = $this->actingAs($user)->get(route('tokens_report', ['batch_search' => 'coordinator-lookup']));

        $response->assertOk();
        $response->assertSee('coordinator-lookup@example.com'); // sanity: result actually present
        $response->assertSee('tab-pane fade show active" id="batch-tokens"', false);
        $response->assertDontSee('tab-pane fade show active" id="individual-tokens"', false);
        $response->assertDontSee('tab-pane fade show active" id="member-tokens"', false);
    }

    public function test_searching_the_member_tab_reopens_on_the_member_tab(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $this->createStage(1, ['first_name' => 'MemberLookup', 'batch_no' => 20260101000003]);

        $response = $this->actingAs($user)->get(route('tokens_report', ['member_search' => 'MemberLookup']));

        $response->assertOk();
        $response->assertSee('MEMBERLOOKUP');
        $response->assertSee('tab-pane fade show active" id="member-tokens"', false);
        $response->assertDontSee('tab-pane fade show active" id="individual-tokens"', false);
        $response->assertDontSee('tab-pane fade show active" id="batch-tokens"', false);
    }

    public function test_paginating_the_batch_tab_reopens_on_the_batch_tab(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);

        $response = $this->actingAs($user)->get(route('tokens_report', ['batch_page' => 1]));

        $response->assertOk();
        $response->assertSee('tab-pane fade show active" id="batch-tokens"', false);
    }

    public function test_individual_tab_can_be_searched_by_token_value(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $this->createStage(1, ['first_name' => 'Kwame', 'token' => 'ABC123']);
        $this->createStage(2, ['first_name' => 'Abena', 'token' => 'XYZ789']);

        $response = $this->actingAs($user)->get(route('tokens_report', ['individual_search' => 'ABC123']));

        $response->assertOk();
        $response->assertSee('KWAME');
        $response->assertDontSee('ABENA');
    }

    public function test_individual_tab_can_be_searched_by_name_phone_or_email(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $this->createStage(1, ['first_name' => 'Kwame']);
        $this->createStage(2, ['first_name' => 'Abena']);

        $response = $this->actingAs($user)->get(route('tokens_report', ['individual_search' => 'Kwame']));

        $response->assertOk();
        $response->assertSee('KWAME');
        $response->assertDontSee('ABENA');
    }

    public function test_individual_tab_paginates_and_page_params_do_not_collide(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        foreach (range(1, 51) as $i) {
            $this->createStage($i, ['first_name' => "Reg-{$i}-END"]);
        }
        $this->createBatchLog(1);

        $firstPage = $this->actingAs($user)->get(route('tokens_report'));
        $firstPage->assertOk();
        $firstPage->assertSee('REG-51-END');
        $firstPage->assertDontSee('REG-1-END');

        $secondPage = $this->actingAs($user)->get(route('tokens_report', ['individual_page' => 2, 'batch_page' => 1]));
        $secondPage->assertOk();
        $secondPage->assertSee('REG-1-END');
    }

    public function test_tokens_report_does_not_run_a_query_per_row(): void
    {
        $user = $this->userWithRole(RolesEnum::SUPERADMIN);
        $batchNo = 20260101000050;
        $this->createBatchLog(1, ['batch_no' => $batchNo]);
        foreach (range(1, 5) as $i) {
            $this->createStage($i, ['batch_no' => 0]);
        }
        foreach (range(6, 10) as $i) {
            $this->createStage($i, ['batch_no' => $batchNo]);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($user)->get(route('tokens_report'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $this->assertLessThan(15, $queryCount, "Expected a small, constant number of queries, got {$queryCount} for 10 rows.");
    }

    public function test_tokens_report_denies_finance_role(): void
    {
        $user = $this->userWithRole(RolesEnum::FINANCE);

        $response = $this->actingAs($user)->get(route('tokens_report'));

        $response->assertForbidden();
    }

    public function test_tokens_report_denies_room_allocator_role(): void
    {
        $user = $this->userWithRole(RolesEnum::ROOMALLOCATOR);

        $response = $this->actingAs($user)->get(route('tokens_report'));

        $response->assertForbidden();
    }
}
