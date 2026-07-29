<?php

namespace Tests\Feature\Admin;

use App\Imports\RegistrationStageImport;
use App\Models\Admin\Country;
use App\Models\Admin\Dropdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationStageImportTest extends TestCase
{
    use RefreshDatabase;

    private function baseRow(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Mr',
            'first_name' => 'Ama',
            'surname' => 'Mensah',
            'other_names' => null,
            'marital_status' => 'Single',
            'nationality' => 'Ghana',
            'whatsapp_number' => '+233541234567',
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'phone_number' => '+233541234567',
            'email' => 'ama@example.com',
            'address' => 'Address',
            'position_held' => 'Member',
            'profession' => 'Engineer',
            'residence_country' => 'Ghana',
            'languages_spoken' => 'English',
            'need_accommodation' => 1,
            'emergency_contacts_name' => 'Kofi',
            'emergency_contacts_relationship' => 'Sibling',
            'emergency_contacts_phone_number' => '+233541234568',
            'attendance_type' => 'In-Person',
            'disability' => 0,
            'special_needs' => 'None',
        ], $overrides);
    }

    public function test_lookup_values_containing_sql_syntax_do_not_break_the_query(): void
    {
        Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Mr.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        $import = new RegistrationStageImport(1, '20260101000000');

        $model = $import->model($this->baseRow([
            'title' => "Mr' OR '1'='1",
        ]));

        // No match for the injected value: falls back to 0 rather than throwing
        // a SQL error or matching every row via the injected condition.
        $this->assertSame(0, $model->title);
    }

    public function test_lookup_still_partially_matches_legitimate_values(): void
    {
        $dropdown = Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Mr.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        $import = new RegistrationStageImport(1, '20260101000000');

        $model = $import->model($this->baseRow(['title' => 'Mr']));

        $this->assertSame($dropdown->id, $model->title);
    }

    public function test_profession_lookup_does_not_match_a_same_named_row_from_a_different_category(): void
    {
        // Real bug: an imported "Student" profession cell used to match
        // "Regular Student" (Registration Type, lookup_code_id=8) instead of
        // "Student" (Profession, lookup_code_id=10), because getLookup() had
        // no lookup_code_id filter - whichever row happened to LIKE-match
        // first won, regardless of category.
        $registrationType = Dropdown::create(['lookup_code_id' => 8, 'full_name' => 'Regular Student', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $profession = Dropdown::create(['lookup_code_id' => 10, 'full_name' => 'Student', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        $import = new RegistrationStageImport(1, '20260101000000');

        $model = $import->model($this->baseRow(['profession' => 'Student']));

        $this->assertSame($profession->id, $model->profession);
        $this->assertNotSame($registrationType->id, $model->profession);
    }

    public function test_lookup_fields_are_each_scoped_to_their_own_category(): void
    {
        // One Dropdown row per field below, all sharing a name that could
        // plausibly collide across categories if unscoped.
        $title = Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Member', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $gender = Dropdown::create(['lookup_code_id' => 2, 'full_name' => 'Male', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $maritalStatus = Dropdown::create(['lookup_code_id' => 3, 'full_name' => 'Single', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $positionHeld = Dropdown::create(['lookup_code_id' => 5, 'full_name' => 'Elder', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        $import = new RegistrationStageImport(1, '20260101000000');

        $model = $import->model($this->baseRow([
            'title' => 'Member', 'gender' => 'Male', 'marital_status' => 'Single', 'position_held' => 'Elder',
        ]));

        $this->assertSame($title->id, $model->title);
        $this->assertSame($gender->id, $model->gender);
        $this->assertSame($maritalStatus->id, $model->marital_status);
        $this->assertSame($positionHeld->id, $model->position_held);
    }

    public function test_country_lookup_values_containing_sql_syntax_do_not_break_the_query(): void
    {
        $country = new Country;
        $country->name = 'Ghana';
        $country->code = 'GH';
        $country->save();

        $import = new RegistrationStageImport(1, '20260101000000');

        $model = $import->model($this->baseRow([
            'nationality' => "Ghana'; DROP TABLE countries; --",
        ]));

        $this->assertSame(0, $model->nationality_id);
    }
}
