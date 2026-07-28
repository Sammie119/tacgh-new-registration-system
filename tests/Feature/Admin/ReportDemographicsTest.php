<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportDemographicsTest extends TestCase
{
    use RefreshDatabase;

    private function reportUser(): User
    {
        $role = Role::create(['name' => RolesEnum::SUPERADMIN->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        return $user;
    }

    private function createStage(int $i, array $overrides = []): RegistrantStage
    {
        return RegistrantStage::create(array_merge([
            'title' => 1, 'first_name' => "Reg{$i}", 'surname' => 'Test', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => sprintf('+2335412%04d', $i), 'email' => "demo{$i}@example.com",
            'address' => 'Address', 'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => 1, 'disability' => 0, 'confirmed' => 'Yes',
            'token' => "DEMOTOK{$i}",
        ], $overrides));
    }

    public function test_demographics_report_shows_correct_totals_and_age_brackets(): void
    {
        $user = $this->reportUser();

        $this->createStage(1, ['gender' => 3, 'date_of_birth' => now()->subYears(10)->toDateString(), 'confirmed' => 'Yes']);
        $this->createStage(2, ['gender' => 3, 'date_of_birth' => now()->subYears(25)->toDateString(), 'confirmed' => 'Yes']);
        $this->createStage(3, ['gender' => 1, 'date_of_birth' => now()->subYears(60)->toDateString(), 'confirmed' => 'No']);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Total Registrants', '3']);
        $response->assertSeeInOrder(['Confirmed', '2']);
        $response->assertSeeInOrder(['Pending', '1']);
        // Age brackets from the 3 registrants above: one under 18, one 18-30, one 51+.
        $response->assertSeeInOrder(['Under 18', '18-30', '31-50', '51+', 'Unknown']);
        $response->assertSeeInOrder(['1,', '1,', '0,', '1,', '0,']);
    }

    public function test_demographics_report_excludes_other_events_registrants(): void
    {
        $user = $this->reportUser();
        $this->createStage(1, ['event_id' => 1]);
        $this->createStage(2, ['event_id' => 999]);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Total Registrants', '1']);
    }

    public function test_demographics_report_handles_an_empty_date_of_birth_without_error(): void
    {
        // date_of_birth is NOT NULL at the DB level, so an empty string
        // (not null) is the realistic "missing" case to guard against.
        $user = $this->reportUser();
        $this->createStage(1, ['date_of_birth' => '']);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Unknown', '1']);
    }

    public function test_demographics_report_does_not_run_a_query_per_row(): void
    {
        $user = $this->reportUser();

        foreach (range(1, 15) as $i) {
            $this->createStage($i);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($user)->get(route('demographics_report'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $this->assertLessThan(10, $queryCount, "Expected a small, constant number of queries, got {$queryCount} for 15 rows.");
    }
}
