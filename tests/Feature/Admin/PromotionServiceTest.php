<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Event;
use App\Models\Admin\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(RolesEnum $roleEnum): User
    {
        $role = Role::create(['name' => $roleEnum->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createEvent(): Event
    {
        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    public function test_a_low_privilege_role_cannot_view_the_promotions_form(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $event = $this->createEvent();

        $response = $this->actingAs($lowPrivilegeUser)->get("/execute_form/view/promotions/{$event->id}");

        $response->assertForbidden();
    }

    public function test_an_admin_can_view_the_promotions_form(): void
    {
        $admin = $this->userWithRole(RolesEnum::SUPERADMIN);
        $event = $this->createEvent();
        Promotion::create([
            'event_id' => $event->id, 'name' => 'Early Bird', 'applies_to' => 'both',
            'discount_percentage' => 10, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(),
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($admin)->get("/execute_form/view/promotions/{$event->id}");

        $response->assertOk();
        $response->assertSee('Early Bird');
    }

    public function test_a_low_privilege_role_cannot_create_a_promotion(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $event = $this->createEvent();

        $response = $this->actingAs($lowPrivilegeUser)->post('/admin/promotions', [
            'event_id' => $event->id,
            'promotions' => [
                ['name' => 'Early Bird', 'applies_to' => 'both', 'discount_percentage' => 10, 'starts_at' => now()->toDateTimeString(), 'ends_at' => now()->addDay()->toDateTimeString()],
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_an_admin_can_create_a_promotion(): void
    {
        $admin = $this->userWithRole(RolesEnum::SUPERADMIN);
        $event = $this->createEvent();

        $response = $this->actingAs($admin)->post('/admin/promotions', [
            'event_id' => $event->id,
            'promotions' => [
                ['name' => 'Early Bird', 'applies_to' => 'accommodation', 'discount_percentage' => 15, 'starts_at' => now()->toDateTimeString(), 'ends_at' => now()->addDay()->toDateTimeString(), 'active_flag' => 1],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('promotions', ['event_id' => $event->id, 'name' => 'Early Bird', 'discount_percentage' => 15]);
    }

    public function test_an_admin_can_update_an_existing_promotion(): void
    {
        $admin = $this->userWithRole(RolesEnum::SUPERADMIN);
        $event = $this->createEvent();
        $promotion = Promotion::create([
            'event_id' => $event->id, 'name' => 'Early Bird', 'applies_to' => 'both',
            'discount_percentage' => 10, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(),
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($admin)->post('/admin/promotions', [
            'event_id' => $event->id,
            'promotions' => [
                ['id' => $promotion->id, 'name' => 'Early Bird', 'applies_to' => 'both', 'discount_percentage' => 25, 'starts_at' => $promotion->starts_at, 'ends_at' => $promotion->ends_at, 'active_flag' => 1],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('promotions', ['id' => $promotion->id, 'discount_percentage' => 25]);
    }

    public function test_creating_an_overlapping_promotion_for_the_same_event_is_rejected(): void
    {
        $admin = $this->userWithRole(RolesEnum::SUPERADMIN);
        $event = $this->createEvent();
        Promotion::create([
            'event_id' => $event->id, 'name' => 'Early Bird', 'applies_to' => 'both',
            'discount_percentage' => 10, 'starts_at' => now(), 'ends_at' => now()->addDays(5),
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($admin)->post('/admin/promotions', [
            'event_id' => $event->id,
            'promotions' => [
                ['name' => 'Overlapping', 'applies_to' => 'both', 'discount_percentage' => 20, 'starts_at' => now()->addDays(2)->toDateTimeString(), 'ends_at' => now()->addDays(8)->toDateTimeString(), 'active_flag' => 1],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('promotions', ['name' => 'Overlapping']);
    }

    public function test_a_non_overlapping_promotion_for_the_same_event_is_accepted(): void
    {
        $admin = $this->userWithRole(RolesEnum::SUPERADMIN);
        $event = $this->createEvent();
        Promotion::create([
            'event_id' => $event->id, 'name' => 'Early Bird', 'applies_to' => 'both',
            'discount_percentage' => 10, 'starts_at' => now(), 'ends_at' => now()->addDays(5),
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($admin)->post('/admin/promotions', [
            'event_id' => $event->id,
            'promotions' => [
                ['name' => 'Later Promo', 'applies_to' => 'both', 'discount_percentage' => 20, 'starts_at' => now()->addDays(10)->toDateTimeString(), 'ends_at' => now()->addDays(15)->toDateTimeString(), 'active_flag' => 1],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('promotions', ['name' => 'Later Promo']);
    }

    public function test_a_low_privilege_role_cannot_delete_a_promotion(): void
    {
        $lowPrivilegeUser = $this->userWithRole(RolesEnum::ROOMALLOCATOR);
        $event = $this->createEvent();
        $promotion = Promotion::create([
            'event_id' => $event->id, 'name' => 'Early Bird', 'applies_to' => 'both',
            'discount_percentage' => 10, 'starts_at' => now(), 'ends_at' => now()->addDay(),
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($lowPrivilegeUser)->get("/execute_form/delete/promotions/{$promotion->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('promotions', ['id' => $promotion->id]);
    }

    public function test_an_admin_can_delete_a_promotion(): void
    {
        $admin = $this->userWithRole(RolesEnum::SUPERADMIN);
        $event = $this->createEvent();
        $promotion = Promotion::create([
            'event_id' => $event->id, 'name' => 'Early Bird', 'applies_to' => 'both',
            'discount_percentage' => 10, 'starts_at' => now(), 'ends_at' => now()->addDay(),
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($admin)->get("/execute_form/delete/promotions/{$promotion->id}");

        $response->assertOk();
        $this->assertSoftDeleted('promotions', ['id' => $promotion->id]);
    }
}
