<?php

namespace Tests\Feature\Registrant;

use App\Jobs\SmsNotificationJob;
use App\Jobs\WhatsappNotificationJob;
use App\Models\Admin\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        $event = $overrides['event'] ?? $this->createEvent();

        return array_merge([
            'title' => 1,
            'first_name' => 'Ama',
            'surname' => 'Mensah',
            'other_names' => null,
            'gender' => 1,
            'date_of_birth' => '1990-01-01',
            'marital_status' => 1,
            'nationality_id' => 1,
            'phone_number' => '+233541234567',
            'whatsapp_number' => '+233541234567',
            'email' => 'ama.mensah@example.com',
            'address' => '123 Test Street',
            'position_held' => 1,
            'profession' => 1,
            'residence_country_id' => 1,
            'languages_spoken' => 'English',
            'need_accommodation' => 1,
            'emergency_contacts_name' => 'Kofi Mensah',
            'emergency_contacts_relationship' => 'Sibling',
            'emergency_contacts_phone_number' => '+233541234568',
            'attendance_type' => 'In-Person',
            'event_id' => $event->id,
            'disability' => 0,
            'special_needs' => 'None',
        ], $overrides);
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'name' => 'Test Conference',
            'description' => 'A test event',
            'code_prefix' => 'TC',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'is_payment_required' => 'No',
            'status' => 'In-Progress',
            'active_flag' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ], $overrides));
    }

    public function test_registrant_can_register_for_an_event_that_does_not_require_payment(): void
    {
        Bus::fake();

        $event = $this->createEvent(['is_payment_required' => 'No']);

        $response = $this->post(route('registrant.store'), $this->validPayload(['event' => $event]));

        $response->assertRedirect(route('registrant_login'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('registrants_stage', [
            'email' => 'ama.mensah@example.com',
            'phone_number' => '+233541234567',
            'event_id' => $event->id,
        ]);

        Bus::assertDispatched(WhatsappNotificationJob::class);
    }

    public function test_registrant_from_ghana_also_gets_an_sms_notification(): void
    {
        Bus::fake();

        $event = $this->createEvent();

        $this->post(route('registrant.store'), $this->validPayload([
            'event' => $event,
            'residence_country_id' => 64,
        ]));

        Bus::assertDispatched(SmsNotificationJob::class);
    }

    public function test_registrant_from_outside_ghana_does_not_get_an_sms_notification(): void
    {
        Bus::fake();

        $event = $this->createEvent();

        $this->post(route('registrant.store'), $this->validPayload([
            'event' => $event,
            'residence_country_id' => 1,
        ]));

        Bus::assertNotDispatched(SmsNotificationJob::class);
    }

    public function test_registration_requires_a_valid_event_id(): void
    {
        Bus::fake();

        $response = $this->post(route('registrant.store'), $this->validPayload([
            'event' => $this->createEvent(),
            'event_id' => 999999,
        ]));

        $response->assertSessionHasErrors('event_id');
        $this->assertDatabaseCount('registrants_stage', 0);
    }

    public function test_registration_requires_a_valid_phone_number_format(): void
    {
        Bus::fake();

        $response = $this->post(route('registrant.store'), $this->validPayload([
            'event' => $this->createEvent(),
            'phone_number' => '0541234567',
        ]));

        $response->assertSessionHasErrors('phone_number');
        $this->assertDatabaseCount('registrants_stage', 0);
    }

    public function test_registering_twice_with_the_same_identity_updates_the_existing_stage_record_instead_of_duplicating(): void
    {
        Bus::fake();

        $event = $this->createEvent();
        $payload = $this->validPayload(['event' => $event]);

        $this->post(route('registrant.store'), $payload);
        $this->post(route('registrant.store'), array_merge($payload, ['address' => 'A new address']));

        $this->assertDatabaseCount('registrants_stage', 1);
        $this->assertDatabaseHas('registrants_stage', ['address' => 'A new address']);
    }
}
