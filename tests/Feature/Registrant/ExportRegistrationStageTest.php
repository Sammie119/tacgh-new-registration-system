<?php

namespace Tests\Feature\Registrant;

use App\Exports\RegistrationStageExport;
use App\Models\Admin\Event;
use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
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
            $this->assertSame('john.doe@example.com', $row['email']);
            $this->assertStringNotContainsString('Confidential', $row['first_name']);
            $this->assertStringNotContainsString('Confidential', $row['surname']);
            $this->assertStringNotContainsString('real.person.pii', $row['email']);

            // Dropdown/country columns must be text labels (what the
            // import's LIKE-based lookup expects), not raw stored IDs.
            $this->assertFalse(is_numeric($row['gender']));
            $this->assertFalse(is_numeric($row['nationality_id']));

            return $export->collection()->count() === 1;
        });
    }
}
