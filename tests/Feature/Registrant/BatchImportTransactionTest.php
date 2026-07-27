<?php

namespace Tests\Feature\Registrant;

use App\Models\Admin\Event;
use App\Models\BatchLog;
use App\Models\RegistrantStage;
use App\Services\Registrant\RegistrantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BatchImportTransactionTest extends TestCase
{
    use RefreshDatabase;

    private array $headers = [
        'title', 'first_name', 'surname', 'other_names', 'gender', 'date_of_birth',
        'marital_status', 'nationality_id', 'phone_number', 'whatsapp_number', 'email', 'address',
        'position_held', 'profession', 'residence_country_id', 'languages_spoken', 'need_accommodation',
        'emergency_contacts_name', 'emergency_contacts_relationship', 'emergency_contacts_phone_number',
        'attendance_type', 'disability', 'special_needs',
    ];

    private function createEvent(): Event
    {
        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    /**
     * Builds a real .xlsx file with every cell explicitly typed as a string,
     * so values like "+233500000001" survive intact (unlike CSV, where
     * PhpSpreadsheet's auto-detection strips the leading "+" as if it were
     * a numeric sign — see the reported finding on this).
     */
    private function buildSpreadsheet(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($this->headers as $col => $name) {
            $sheet->setCellValueExplicit([$col + 1, 1], $name, DataType::TYPE_STRING);
        }

        foreach ($rows as $rowIndex => $row) {
            foreach (array_values($row) as $col => $value) {
                $sheet->setCellValueExplicit([$col + 1, $rowIndex + 2], (string) $value, DataType::TYPE_STRING);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'batch').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    private function buildRequest(Event $event, array $rows): Request
    {
        $path = $this->buildSpreadsheet($rows);
        $file = new UploadedFile($path, 'batch.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $request = new Request;
        $request->merge([
            'event_id' => $event->id, 'email' => 'coordinator@example.com',
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567',
        ]);
        $request->files->set('file', $file);

        return $request;
    }

    private function validRow(string $suffix = '1'): array
    {
        return [
            'title' => 'Mr', 'first_name' => 'John'.$suffix, 'surname' => 'Doe'.$suffix, 'other_names' => '',
            'gender' => 'Male', 'date_of_birth' => '1990-01-01', 'marital_status' => 'Single',
            'nationality_id' => 'Ghana', 'phone_number' => '+23350000000'.$suffix, 'whatsapp_number' => '',
            'email' => 'john'.$suffix.'@example.com', 'address' => 'Address '.$suffix,
            'position_held' => 'Member', 'profession' => 'Engineer', 'residence_country_id' => 'Ghana',
            'languages_spoken' => 'English', 'need_accommodation' => '1',
            'emergency_contacts_name' => 'Jane Doe', 'emergency_contacts_relationship' => 'Sister',
            'emergency_contacts_phone_number' => '+23350000001'.$suffix, 'attendance_type' => 'In-Person',
            'disability' => '0', 'special_needs' => 'None',
        ];
    }

    public function test_a_bad_row_rolls_back_the_whole_batch_instead_of_leaving_orphaned_registrants(): void
    {
        Bus::fake();

        $event = $this->createEvent();

        $validRow = $this->validRow('1');
        $invalidRow = $this->validRow('2');
        $invalidRow['surname'] = ''; // missing required field

        $request = $this->buildRequest($event, [$validRow, $invalidRow]);

        $response = (new RegistrantService)->batchImportRegistration($request);

        $this->assertTrue(session()->has('error'));

        // Row 1 (valid on its own) must NOT have been committed either —
        // the whole batch rolls back together.
        $this->assertDatabaseCount('registrants_stage', 0);
        $this->assertDatabaseCount('batch_logs', 0);
        $this->assertSame(0, RegistrantStage::count());
        $this->assertSame(0, BatchLog::count());
    }

    public function test_a_fully_valid_batch_still_imports_successfully(): void
    {
        Bus::fake();

        $event = $this->createEvent();

        $request = $this->buildRequest($event, [$this->validRow('1'), $this->validRow('2')]);

        $response = (new RegistrantService)->batchImportRegistration($request);

        $this->assertTrue(session()->has('success'));
        $this->assertDatabaseCount('registrants_stage', 2);
        $this->assertDatabaseCount('batch_logs', 1);
        $this->assertDatabaseHas('registrants_stage', ['phone_number' => '+233500000001']);
    }
}
