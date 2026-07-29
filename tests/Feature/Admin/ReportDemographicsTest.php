<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Country;
use App\Models\Admin\Dropdown;
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

    public function test_demographics_report_resolves_nationality_and_residence_against_the_countries_table(): void
    {
        // nationality_id/residence_country_id reference the countries table,
        // not the generic dropdowns/lookups table used for gender - a stage
        // whose title/gender happens to collide with an unrelated dropdown
        // row must not leak that label into the nationality/residence columns.
        $user = $this->reportUser();
        $ghana = new Country;
        $ghana->name = 'Ghana';
        $ghana->code = 'GH';
        $ghana->save();
        $nigeria = new Country;
        $nigeria->name = 'Nigeria';
        $nigeria->code = 'NG';
        $nigeria->save();
        $this->createStage(1, ['nationality_id' => $ghana->id, 'residence_country_id' => $nigeria->id]);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Nationality', 'Ghana']);
        $response->assertSeeInOrder(['Country of Residence', 'Nigeria']);
    }

    public function test_demographics_report_shows_profession_and_position_held_breakdowns(): void
    {
        $user = $this->reportUser();
        $engineer = Dropdown::create(['lookup_code_id' => 10, 'full_name' => 'Engineer', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $pastor = Dropdown::create(['lookup_code_id' => 5, 'full_name' => 'Pastor', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $this->createStage(1, ['profession' => $engineer->id, 'position_held' => $pastor->id]);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Profession', 'Engineer']);
        $response->assertSeeInOrder(['Position Held', 'Pastor']);
    }

    public function test_demographics_report_separates_profession_from_registration_type(): void
    {
        // The batch-import path (RegistrationStageImport::getLookup()) does an
        // unscoped full_name LIKE match with no lookup_code_id filter, so real
        // `profession` data contains a mix of genuine Profession
        // (lookup_code_id=10) and Registration Type (lookup_code_id=8) rows.
        // The report must split them by their real category, not trust the
        // column.
        $user = $this->reportUser();
        $student = Dropdown::create(['lookup_code_id' => 10, 'full_name' => 'Student', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $regularStudent = Dropdown::create(['lookup_code_id' => 8, 'full_name' => 'Regular Student', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $this->createStage(1, ['profession' => $student->id]);
        $this->createStage(2, ['profession' => $regularStudent->id]);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Profession', 'Student']);
        $response->assertSeeInOrder(['Registration Type', 'Regular Student']);

        // "Regular Student" (Registration Type) must not appear in the
        // Profession table's row range, and vice versa.
        $data = app(\App\Services\Admin\ReportService::class)->demographics(1)->getData();
        $this->assertTrue($data['profession_counts']->has($student->id));
        $this->assertFalse($data['profession_counts']->has($regularStudent->id));
        $this->assertTrue($data['registration_type_counts']->has($regularStudent->id));
        $this->assertFalse($data['registration_type_counts']->has($student->id));
    }

    public function test_demographics_report_excludes_non_profession_categories_from_the_profession_breakdown(): void
    {
        // Historical batch-import corruption also left Accommodation Type
        // (lookup_code_id=9) and YesNo (lookup_code_id=1) rows sitting in
        // `profession`. Neither belongs under Profession or Registration
        // Type, so both must be excluded from both breakdowns entirely.
        $user = $this->reportUser();
        $student = Dropdown::create(['lookup_code_id' => 10, 'full_name' => 'Student', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $accommodationType = Dropdown::create(['lookup_code_id' => 9, 'full_name' => 'Student Ministers', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $yesNo = Dropdown::create(['lookup_code_id' => 1, 'full_name' => 'Yes', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $this->createStage(1, ['profession' => $student->id]);
        $this->createStage(2, ['profession' => $accommodationType->id]);
        $this->createStage(3, ['profession' => $yesNo->id]);

        $this->actingAs($user)->get(route('demographics_report'))->assertOk();

        $data = app(\App\Services\Admin\ReportService::class)->demographics(1)->getData();
        $this->assertTrue($data['profession_counts']->has($student->id));
        $this->assertFalse($data['profession_counts']->has($accommodationType->id));
        $this->assertFalse($data['profession_counts']->has($yesNo->id));
        $this->assertFalse($data['registration_type_counts']->has($accommodationType->id));
        $this->assertFalse($data['registration_type_counts']->has($yesNo->id));
    }

    public function test_demographics_report_shows_marital_status_breakdown(): void
    {
        $user = $this->reportUser();
        $married = Dropdown::create(['lookup_code_id' => 3, 'full_name' => 'Married', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $this->createStage(1, ['marital_status' => $married->id]);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Marital Status', 'Married']);
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
