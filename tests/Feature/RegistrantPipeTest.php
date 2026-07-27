<?php

namespace Tests\Feature;

use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Pipelines\Registration\RegistrantPipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrantPipeTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(string $prefix = 'TC'): Event
    {
        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => $prefix,
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    private function createFees(Event $event): array
    {
        $accommodation = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Standard',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registration = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 100, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        return [$accommodation, $registration];
    }

    private function createStage(Event $event, string $token): RegistrantStage
    {
        return RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => $token,
        ]);
    }

    private function runPipe(RegistrantStage $stage, Event $event, EventFees $accommodation, EventFees $registration): array
    {
        return (new RegistrantPipe)->handle([
            'id' => $stage->id,
            'event_id' => $event->id,
            'accommodation_fee' => $accommodation->id,
            'registration_fee' => $registration->id,
            'amount_to_pay' => 150,
        ], fn ($data) => $data);
    }

    public function test_first_registrant_of_an_event_gets_number_one(): void
    {
        $event = $this->createEvent('TC');
        [$accommodation, $registration] = $this->createFees($event);
        $stage = $this->createStage($event, 'TOK1');

        $result = $this->runPipe($stage, $event, $accommodation, $registration);

        $this->assertSame('TC-'.date('y').'-0001', $result['registration_no']);
    }

    public function test_registration_numbers_are_sequential_within_an_event(): void
    {
        $event = $this->createEvent('TC');
        [$accommodation, $registration] = $this->createFees($event);

        $first = $this->runPipe($this->createStage($event, 'TOK1'), $event, $accommodation, $registration);
        $second = $this->runPipe($this->createStage($event, 'TOK2'), $event, $accommodation, $registration);
        $third = $this->runPipe($this->createStage($event, 'TOK3'), $event, $accommodation, $registration);

        $this->assertSame('TC-'.date('y').'-0001', $first['registration_no']);
        $this->assertSame('TC-'.date('y').'-0002', $second['registration_no']);
        $this->assertSame('TC-'.date('y').'-0003', $third['registration_no']);
    }

    public function test_numbering_is_independent_per_event(): void
    {
        $eventA = $this->createEvent('AAA');
        [$accA, $regA] = $this->createFees($eventA);
        $eventB = $this->createEvent('BBB');
        [$accB, $regB] = $this->createFees($eventB);

        $this->runPipe($this->createStage($eventA, 'TOK1'), $eventA, $accA, $regA);
        $resultA2 = $this->runPipe($this->createStage($eventA, 'TOK2'), $eventA, $accA, $regA);
        $resultB1 = $this->runPipe($this->createStage($eventB, 'TOK3'), $eventB, $accB, $regB);

        $this->assertSame('AAA-'.date('y').'-0002', $resultA2['registration_no']);
        // Event B's first registrant starts back at 0001, unaffected by event A's count.
        $this->assertSame('BBB-'.date('y').'-0001', $resultB1['registration_no']);
    }

    public function test_reconfirming_the_same_registrant_keeps_the_same_registration_number(): void
    {
        $event = $this->createEvent('TC');
        [$accommodation, $registration] = $this->createFees($event);
        $stage = $this->createStage($event, 'TOK1');

        // A second registrant confirms in between, so a naive recompute
        // (e.g. re-counting) would bump stage's number if it weren't stable.
        $this->runPipe($stage, $event, $accommodation, $registration);
        $this->runPipe($this->createStage($event, 'TOK2'), $event, $accommodation, $registration);
        $reconfirmed = $this->runPipe($stage, $event, $accommodation, $registration);

        $this->assertSame('TC-'.date('y').'-0001', $reconfirmed['registration_no']);
        $this->assertDatabaseCount('registrants', 2);
    }
}
