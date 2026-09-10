<?php

namespace Tests\Feature\Registrant;

use App\Models\Admin\Event;
use App\Services\Registrant\RegistrantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RegistrationEventGuardTest extends TestCase
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

    private function registrationPayload(int $eventId): array
    {
        return [
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'other_names' => null,
            'gender' => 1, 'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567',
            'email' => 'ama@example.com', 'address' => 'Address', 'position_held' => 1, 'profession' => 1,
            'residence_country_id' => 1, 'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'emergency_contacts_relationship' => 'Sibling',
            'emergency_contacts_phone_number' => '+233541234568', 'attendance_type' => 'In-Person',
            'event_id' => $eventId, 'disability' => 0, 'special_needs' => 'None', 'is_student' => 0,
        ];
    }

    public function test_individual_registration_fails_gracefully_when_event_is_soft_deleted(): void
    {
        Bus::fake();

        // registrantRegistration() no longer trusts a client-submitted
        // event_id - it resolves the active/not-completed event itself.
        // Soft-deleting the only event makes that lookup find zero
        // events, so the "can't determine which event" guard should
        // reject the registration before any row is inserted.
        $event = $this->createEvent();
        $eventId = $event->id;
        $event->delete();

        $response = (new RegistrantService)->registrantRegistration($this->registrationPayload($eventId));

        $this->assertTrue(session()->has('error'));
        $this->assertDatabaseCount('registrants_stage', 0);
    }

    public function test_batch_import_fails_gracefully_when_event_is_soft_deleted(): void
    {
        Bus::fake();

        $event = $this->createEvent();
        $eventId = $event->id;
        $event->delete();

        $csv = "title,first_name,surname,other_names,gender,date_of_birth,marital_status,nationality_id,phone_number,whatsapp_number,email,address,position_held,profession,residence_country_id,languages_spoken,need_accommodation,emergency_contacts_name,emergency_contacts_relationship,emergency_contacts_phone_number,attendance_type,disability,special_needs\n";
        $csv .= "Mr,John,Doe,,Male,1990-01-01,Single,Ghana,+233500000001,,john@example.com,Address 1,Member,Engineer,Ghana,English,1,Jane Doe,Sister,+233500000002,In-Person,0,None\n";

        $path = tempnam(sys_get_temp_dir(), 'batch').'.csv';
        file_put_contents($path, $csv);
        $file = new \Illuminate\Http\UploadedFile($path, 'batch.csv', 'text/csv', null, true);

        $request = new \Illuminate\Http\Request;
        $request->merge([
            'event_id' => $eventId, 'email' => 'coordinator@example.com',
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567',
        ]);
        $request->files->set('file', $file);

        $response = (new RegistrantService)->batchImportRegistration($request);

        $this->assertTrue(session()->has('error'));

        @unlink($path);
    }
}
