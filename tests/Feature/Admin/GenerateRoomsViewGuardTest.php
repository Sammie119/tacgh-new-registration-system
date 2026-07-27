<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\AccommodationBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GenerateRoomsViewGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads_when_the_blocks_residence_no_longer_exists(): void
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create();
        $user->assignRole($role);

        // residence_id points at an Accommodation that doesn't exist —
        // used to crash via Accommodation::find($block->residence_id)->status.
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => 999999, 'total_rooms' => 10,
            'total_floors' => 1, 'gender' => 'M', 'created_by' => 1, 'updated_by' => 1,
        ]);

        $response = $this->actingAs($user)->get("/execute_form/view/generate_rooms/{$block->id}");

        $response->assertOk();
    }
}
