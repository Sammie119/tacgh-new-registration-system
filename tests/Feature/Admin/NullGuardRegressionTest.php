<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Dropdown;
use App\Models\User;
use App\Pipelines\Registration\ConfirmationPipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NullGuardRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_updating_a_nonexistent_financial_entry_fails_gracefully(): void
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create();
        $user->assignRole($role);
        $transactionType = Dropdown::create([
            'lookup_code_id' => 1, 'full_name' => 'Registration Fee', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->put(route('financial_entry'), [
            'id' => 999999,
            'entry_type' => 'Income',
            'transaction_type' => $transactionType->id,
            'description' => 'Test',
            'transaction_date' => now()->toDateString(),
            'amount' => 10,
        ]);

        $response->assertRedirect(route('financial_entries'));
        $response->assertSessionHas('error');
    }

    public function test_updating_a_nonexistent_venue_fails_gracefully(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->put(route('venue'), [
            'id' => 999999,
            'name' => 'Test Venue',
            'region_id' => 1,
            'location' => 'Address',
        ]);

        $response->assertRedirect(route('venues'));
        $response->assertSessionHas('error');
    }

    public function test_confirmation_pipe_aborts_with_404_for_a_nonexistent_registrant(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        (new ConfirmationPipe)->handle(['id' => 999999], fn ($data) => $data);
    }
}
