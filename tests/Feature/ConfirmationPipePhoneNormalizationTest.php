<?php

namespace Tests\Feature;

use App\Models\Admin\Event;
use App\Models\RegistrantStage;
use App\Pipelines\Registration\ConfirmationPipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmationPipePhoneNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_with_local_format_phone_numbers_normalizes_them(): void
    {
        $event = Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'No', 'token' => 'TOK1',
        ]);

        (new ConfirmationPipe)->handle([
            'id' => $stage->id,
            'date_of_birth' => '1990-01-01', 'gender' => 1, 'event_id' => $event->id,
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'other_names' => null,
            'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '0541234569', 'whatsapp_number' => '0541234570',
            'email' => 'ama@example.com', 'address' => 'Address', 'position_held' => 1, 'profession' => 1,
            'residence_country_id' => 1, 'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'emergency_contacts_relationship' => 'Sibling',
            'emergency_contacts_phone_number' => '0541234571', 'attendance_type' => 'In-Person',
            'disability' => 0, 'special_needs' => 'None',
        ], fn ($data) => $data);

        $this->assertDatabaseHas('registrants_stage', [
            'id' => $stage->id,
            'phone_number' => '+233541234569',
            'whatsapp_number' => '+233541234570',
            'emergency_contacts_phone_number' => '+233541234571',
        ]);
    }
}
