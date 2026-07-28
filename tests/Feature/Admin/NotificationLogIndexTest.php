<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationLogIndexTest extends TestCase
{
    use RefreshDatabase;

    private function reportUser(): User
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    private function createLog(int $i, array $overrides = []): NotificationLog
    {
        return NotificationLog::create(array_merge([
            'channel' => 'sms',
            'recipient' => sprintf('+2335412%04d', $i),
            'message' => "Message {$i}",
            'success' => true,
            'response' => '{"code":"2000"}',
            'event_id' => 1,
        ], $overrides));
    }

    public function test_notification_log_page_does_not_run_a_query_per_row(): void
    {
        $user = $this->reportUser();

        foreach (range(1, 15) as $i) {
            $this->createLog($i);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($user)->get(route('notification_log'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $this->assertLessThan(15, $queryCount, "Expected a small, constant number of queries, got {$queryCount} for 15 rows.");
    }

    public function test_notification_log_page_paginates_results(): void
    {
        $user = $this->reportUser();

        foreach (range(1, 55) as $i) {
            $this->createLog($i, ['recipient' => sprintf('+23354120%02d-END', $i)]);
        }

        $firstPage = $this->actingAs($user)->get(route('notification_log'));
        $firstPage->assertOk();
        $firstPage->assertSee('+2335412055-END');
        $firstPage->assertDontSee('+2335412001-END');

        $secondPage = $this->actingAs($user)->get(route('notification_log', ['page' => 2]));
        $secondPage->assertOk();
        $secondPage->assertSee('+2335412001-END');
        $secondPage->assertDontSee('+2335412055-END');
    }

    public function test_notification_log_page_can_be_filtered_by_channel(): void
    {
        $user = $this->reportUser();
        $this->createLog(1, ['channel' => 'sms', 'recipient' => '+233500000001']);
        $this->createLog(2, ['channel' => 'whatsapp', 'recipient' => '+233500000002']);

        $response = $this->actingAs($user)->get(route('notification_log', ['channel' => 'whatsapp']));

        $response->assertOk();
        $response->assertSee('+233500000002');
        $response->assertDontSee('+233500000001');
    }

    public function test_notification_log_page_can_be_filtered_by_status(): void
    {
        $user = $this->reportUser();
        $this->createLog(1, ['success' => true, 'recipient' => '+233500000001']);
        $this->createLog(2, ['success' => false, 'recipient' => '+233500000002']);

        $response = $this->actingAs($user)->get(route('notification_log', ['status' => 'failed']));

        $response->assertOk();
        $response->assertSee('+233500000002');
        $response->assertDontSee('+233500000001');
    }

    public function test_notification_log_page_excludes_other_events(): void
    {
        $user = $this->reportUser();
        $this->createLog(1, ['event_id' => 1, 'recipient' => '+233500000001']);
        $this->createLog(2, ['event_id' => 999, 'recipient' => '+233500000002']);

        $response = $this->actingAs($user)->get(route('notification_log'));

        $response->assertOk();
        $response->assertSee('+233500000001');
        $response->assertDontSee('+233500000002');
    }
}
