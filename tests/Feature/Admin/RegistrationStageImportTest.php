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
            'nationality_id' => 'Ghana',
            'whatsapp_number' => '+233541234567',
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'phone_number' => '+233541234567',
            'email' => 'ama@example.com',
            'address' => 'Address',
            'position_held' => 'Member',
            'profession' => 'Engineer',
            'residence_country_id' => 'Ghana',
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

    public function test_country_lookup_values_containing_sql_syntax_do_not_break_the_query(): void
    {
        $country = new Country;
        $country->name = 'Ghana';
        $country->code = 'GH';
        $country->save();

        $import = new RegistrationStageImport(1, '20260101000000');

        $model = $import->model($this->baseRow([
            'nationality_id' => "Ghana'; DROP TABLE countries; --",
        ]));

        $this->assertSame(0, $model->nationality_id);
    }
}
