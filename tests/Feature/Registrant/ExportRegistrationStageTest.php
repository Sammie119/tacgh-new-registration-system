<?php

namespace Tests\Feature\Registrant;

use App\Exports\RegistrationStageExport;
use App\Models\Admin\Dropdown;
use App\Models\Admin\Event;
use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriterType;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExportRegistrationStageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_downloaded_template_never_contains_real_registrant_data(): void
    {
        Excel::fake();

        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        // A real registrant with distinctive PII that must never appear
        // in the downloaded "template".
        RegistrantStage::create([
            'title' => 1, 'first_name' => 'ConfidentialFirstName', 'surname' => 'ConfidentialSurname',
            'gender' => 1, 'date_of_birth' => '1985-05-05', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233559998888', 'email' => 'real.person.pii@example.com',
            'address' => 'Real Home Address', 'position_held' => 1, 'profession' => 1,
            'residence_country_id' => 1, 'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Real Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => 'TOK1',
        ]);

        $response = $this->get(route('registrant_download'));

        $response->assertOk();

        Excel::assertDownloaded('registration_template.xlsx', function (RegistrationStageExport $export) {
            $row = $export->collection()->first();

            $this->assertSame('John', $row['first_name']);
            $this->assertSame('Doe', $row['surname']);
            $this->assertStringNotContainsString('Confidential', $row['first_name']);
            $this->assertStringNotContainsString('Confidential', $row['surname']);

            // Dropdown/country columns must be text labels (what the
            // import's LIKE-based lookup expects), not raw stored IDs.
            $this->assertFalse(is_numeric($row['gender']));
            $this->assertFalse(is_numeric($row['nationality']));

            return $export->collection()->count() === 1;
        });
    }

    public function test_headings_are_friendly_and_no_longer_include_the_dead_event_id_column(): void
    {
        $headings = (new RegistrationStageExport)->headings();

        $this->assertNotContains('event_id', $headings);
        $this->assertContains('Nationality', $headings);
        $this->assertContains('Residence Country', $headings);
        $this->assertNotContains('nationality_id', $headings);
        $this->assertNotContains('residence_country_id', $headings);
    }

    public function test_the_example_professions_value_is_a_real_dropdown_option(): void
    {
        // Regression: the example row used to say "Engineer", which
        // doesn't exist in the real Profession dropdown and silently
        // resolved to 0 on import with no error.
        $profession = Dropdown::create([
            'lookup_code_id' => 10, 'full_name' => 'Ascension Minister',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $row = (new RegistrationStageExport)->collection()->first();

        $this->assertSame($profession->full_name, $row['profession']);
    }

    public function test_dropdown_pickers_are_applied_to_the_expected_columns(): void
    {
        Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Mr.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Mrs.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        Dropdown::create(['lookup_code_id' => 2, 'full_name' => 'Male', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        Dropdown::create(['lookup_code_id' => 2, 'full_name' => 'Female', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);

        $bytes = Excel::raw(new RegistrationStageExport, ExcelWriterType::XLSX);
        $tmpPath = tempnam(sys_get_temp_dir(), 'export_test').'.xlsx';
        file_put_contents($tmpPath, $bytes);

        $sheet = IOFactory::load($tmpPath)->getActiveSheet();
        unlink($tmpPath);

        // Title column (A) - list validation containing the real options.
        $titleValidation = $sheet->getCell('A2')->getDataValidation();
        $this->assertSame(DataValidation::TYPE_LIST, $titleValidation->getType());
        $this->assertStringContainsString('Mr.', $titleValidation->getFormula1());
        $this->assertStringContainsString('Mrs.', $titleValidation->getFormula1());

        // Gender column (E).
        $genderValidation = $sheet->getCell('E2')->getDataValidation();
        $this->assertStringContainsString('Male', $genderValidation->getFormula1());
        $this->assertStringContainsString('Female', $genderValidation->getFormula1());

        // Need Accommodation column (M) - the two literal values the
        // import's boolean validation rule actually accepts.
        $accommodationValidation = $sheet->getCell('M2')->getDataValidation();
        $this->assertSame(DataValidation::TYPE_LIST, $accommodationValidation->getType());
        $this->assertStringContainsString('Yes', $accommodationValidation->getFormula1());
        $this->assertStringContainsString('No', $accommodationValidation->getFormula1());
    }
}
