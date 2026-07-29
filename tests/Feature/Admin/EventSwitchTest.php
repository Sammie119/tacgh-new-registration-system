<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventSwitchTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(string $name = 'New Conference'): Event
    {
        return Event::create([
            'name' => $name, 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 0,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    public function test_switching_active_event_updates_the_logged_in_users_event_id(): void
    {
        $oldEvent = $this->createEvent('Old Conference');
        $newEvent = $this->createEvent('New Conference');
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create(['event_id' => $oldEvent->id]);
        $user->assignRole($role);

        $response = $this->actingAs($user)->post(route('event.switch'), ['event_id' => $newEvent->id]);

        $response->assertRedirect();
        $this->assertSame($newEvent->id, $user->fresh()->event_id);
    }

    public function test_switching_to_a_nonexistent_event_fails_validation_and_does_not_change_event_id(): void
    {
        $oldEvent = $this->createEvent('Old Conference');
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create(['event_id' => $oldEvent->id]);
        $user->assignRole($role);

        $response = $this->actingAs($user)->post(route('event.switch'), ['event_id' => 999999]);

        $response->assertSessionHasErrors('event_id');
        $this->assertSame($oldEvent->id, $user->fresh()->event_id);
    }

    public function test_a_user_without_the_required_role_cannot_switch_the_active_event(): void
    {
        $oldEvent = $this->createEvent('Old Conference');
        $newEvent = $this->createEvent('New Conference');
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create(['event_id' => $oldEvent->id]);
        $user->assignRole($role);

        $response = $this->actingAs($user)->post(route('event.switch'), ['event_id' => $newEvent->id]);

        $response->assertForbidden();
        $this->assertSame($oldEvent->id, $user->fresh()->event_id);
    }
}
