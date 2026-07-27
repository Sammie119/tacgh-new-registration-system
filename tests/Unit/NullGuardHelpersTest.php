<?php

namespace Tests\Unit;

use App\Enums\RolesEnum;
use App\Helpers\Utils;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NullGuardHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_registration_fee_returns_zero_for_a_nonexistent_id_instead_of_crashing(): void
    {
        $this->assertSame(0, Utils::eventRegistrationFee(999999));
    }

    public function test_get_assigned_role_to_permission_returns_false_when_the_role_does_not_exist(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        // No Role row created for FINANCE at all.
        $this->assertFalse(get_assigned_role_to_permission(RolesEnum::FINANCE));
    }

    public function test_get_assigned_role_to_permission_returns_false_when_user_has_role_but_no_permission_assigned(): void
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create();
        $user->assignRole($role);
        Auth::login($user);

        // Role exists and user has it, but no AssignPermissionToRole row exists.
        $this->assertFalse(get_assigned_role_to_permission(RolesEnum::FINANCE));
    }
}
