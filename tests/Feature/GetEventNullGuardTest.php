<?php

namespace Tests\Feature;

use App\Enums\RolesEnum;
use App\Models\Admin\Event;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GetEventNullGuardTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(): Event
    {
        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    public function test_individual_summary_page_loads_when_the_registrants_event_no_longer_exists(): void
    {
        $event = $this->createEvent();
        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => 'TOK1',
        ]);
        $event->delete();

        $response = $this->withSession(['registrant' => $stage])->get(route('registrant_page'));

        $response->assertOk();
    }

    public function test_profile_page_loads_when_the_users_event_no_longer_exists(): void
    {
        $event = $this->createEvent();
        $role = Role::create(['name' => RolesEnum::SYSTEMADMIN->value]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);
        $event->delete();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
    }
}
