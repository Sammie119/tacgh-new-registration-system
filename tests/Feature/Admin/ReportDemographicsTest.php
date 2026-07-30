<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Country;
use App\Models\Admin\Dropdown;
use App\Models\Admin\EventFees;
use App\Models\Registrant;
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

    public function test_demographics_report_only_counts_genuine_profession_rows(): void
    {
        // The batch-import path (RegistrationStageImport::getLookup()) does an
        // unscoped full_name LIKE match with no lookup_code_id filter, so real
        // `profession` data also contains stray rows from unrelated dropdown
        // categories (e.g. lookup_code_id=8 "Registration Type", 9
        // "Accomodation Type", 1 "YesNo"). None of those belong under
        // Profession, so the report must filter by real category rather than
        // trust the column.
        $user = $this->reportUser();
        $student = Dropdown::create(['lookup_code_id' => 10, 'full_name' => 'Student', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $regularStudent = Dropdown::create(['lookup_code_id' => 8, 'full_name' => 'Regular Student', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $accommodationType = Dropdown::create(['lookup_code_id' => 9, 'full_name' => 'Student Ministers', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $yesNo = Dropdown::create(['lookup_code_id' => 1, 'full_name' => 'Yes', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $this->createStage(1, ['profession' => $student->id]);
        $this->createStage(2, ['profession' => $regularStudent->id]);
        $this->createStage(3, ['profession' => $accommodationType->id]);
        $this->createStage(4, ['profession' => $yesNo->id]);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Profession', 'Student']);

        $data = app(\App\Services\Admin\ReportService::class)->demographics(1)->getData();
        $this->assertTrue($data['profession_counts']->has($student->id));
        $this->assertFalse($data['profession_counts']->has($regularStudent->id));
        $this->assertFalse($data['profession_counts']->has($accommodationType->id));
        $this->assertFalse($data['profession_counts']->has($yesNo->id));
    }

    public function test_demographics_report_shows_registration_fee_type_breakdown(): void
    {
        // Registration Fee Type comes from Registrant.registration_type ->
        // EventFees (fee_type='registration_fee'), not from the
        // profession/dropdowns table at all.
        $user = $this->reportUser();
        $regularStudentFee = EventFees::create([
            'event_id' => 1, 'fee_type' => 'registration_fee', 'description' => 'Regular Student',
            'fee_amount' => 380, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $regularWorkerFee = EventFees::create([
            'event_id' => 1, 'fee_type' => 'registration_fee', 'description' => 'Regular Worker',
            'fee_amount' => 450, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $stage1 = $this->createStage(1);
        $stage2 = $this->createStage(2);
        Registrant::create([
            'registration_no' => 'REG1', 'stage_id' => $stage1->id, 'event_id' => 1,
            'registration_type' => $regularStudentFee->id, 'registration_fee' => 380, 'total_fee' => 380,
        ]);
        Registrant::create([
            'registration_no' => 'REG2', 'stage_id' => $stage2->id, 'event_id' => 1,
            'registration_type' => $regularWorkerFee->id, 'registration_fee' => 450, 'total_fee' => 450,
        ]);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Registration Fee Type', 'Regular Student', 'Regular Worker']);

        $data = app(\App\Services\Admin\ReportService::class)->demographics(1)->getData();
        $this->assertSame(1, $data['registration_fee_type_counts']->get($regularStudentFee->id));
        $this->assertSame(1, $data['registration_fee_type_counts']->get($regularWorkerFee->id));
    }

    public function test_demographics_report_shows_accommodation_type_breakdown(): void
    {
        // Accommodation Type comes from Registrant.accommodation_type ->
        // EventFees (fee_type='accommodation') - a genuinely different data
        // path from Registration Fee Type above, even though both are
        // EventFees rows.
        $user = $this->reportUser();
        $acWithRoom = EventFees::create([
            'event_id' => 1, 'fee_type' => 'accommodation', 'description' => '2 in a room with AC',
            'fee_amount' => 1000, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $noAcRoom = EventFees::create([
            'event_id' => 1, 'fee_type' => 'accommodation', 'description' => '1 in a room without AC',
            'fee_amount' => 2000, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $stage1 = $this->createStage(1);
        $stage2 = $this->createStage(2);
        Registrant::create([
            'registration_no' => 'REG1', 'stage_id' => $stage1->id, 'event_id' => 1,
            'accommodation_type' => $acWithRoom->id, 'accommodation_fee' => 1000, 'total_fee' => 1000,
        ]);
        Registrant::create([
            'registration_no' => 'REG2', 'stage_id' => $stage2->id, 'event_id' => 1,
            'accommodation_type' => $noAcRoom->id, 'accommodation_fee' => 2000, 'total_fee' => 2000,
        ]);

        $response = $this->actingAs($user)->get(route('demographics_report'));

        $response->assertOk();
        $response->assertSeeInOrder(['Accommodation Type', '2 in a room with AC', '1 in a room without AC']);

        $data = app(\App\Services\Admin\ReportService::class)->demographics(1)->getData();
        $this->assertSame(1, $data['accommodation_type_counts']->get($acWithRoom->id));
        $this->assertSame(1, $data['accommodation_type_counts']->get($noAcRoom->id));
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
