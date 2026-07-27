<?php

namespace Tests\Feature\Registrant;

use App\Models\Admin\Event;
use App\Services\Registrant\RegistrantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CsvPhoneNumberImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_plain_csv_upload_preserves_the_leading_plus_on_phone_numbers(): void
    {
        Bus::fake();

        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $headers = 'title,first_name,surname,other_names,gender,date_of_birth,marital_status,nationality_id,phone_number,whatsapp_number,email,address,position_held,profession,residence_country_id,languages_spoken,need_accommodation,emergency_contacts_name,emergency_contacts_relationship,emergency_contacts_phone_number,attendance_type,disability,special_needs';
        // A plain, unquoted CSV — no cell-type information at all, exactly
        // what PhpSpreadsheet's numeric auto-detection used to mangle.
        $row = 'Mr,John,Doe,,Male,1990-01-01,Single,Ghana,+233500000001,+233500000005,john@example.com,Address 1,Member,Engineer,Ghana,English,1,Jane Doe,Sister,+233500000002,In-Person,0,None';
        $csv = $headers."\n".$row."\n";

        $path = tempnam(sys_get_temp_dir(), 'batch').'.csv';
        file_put_contents($path, $csv);
        $file = new UploadedFile($path, 'batch.csv', 'text/csv', null, true);

        $request = new Request;
        $request->merge([
            'event_id' => $event->id, 'email' => 'coordinator@example.com',
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567',
        ]);
        $request->files->set('file', $file);

        $response = (new RegistrantService)->batchImportRegistration($request);

        $this->assertTrue(session()->has('success'));
        $this->assertDatabaseHas('registrants_stage', [
            'phone_number' => '+233500000001',
            'whatsapp_number' => '+233500000005',
            'emergency_contacts_phone_number' => '+233500000002',
        ]);

        @unlink($path);
    }
}
