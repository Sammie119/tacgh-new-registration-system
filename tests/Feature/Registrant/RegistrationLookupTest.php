<?php

namespace Tests\Feature\Registrant;

use App\Models\Admin\Event;
use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationLookupTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'name' => 'Test Conference',
            'description' => 'A test event',
            'code_prefix' => 'TC',
            'start_date' => '2025-08-10',
            'end_date' => '2025-08-12',
            'is_payment_required' => 'No',
            'status' => 'Completed',
            'active_flag' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ], $overrides));
    }

    private function createRegistrant(Event $event, array $overrides = []): RegistrantStage
    {
        return RegistrantStage::create(array_merge([
            'event_id' => $event->id,
            'title' => 1,
            'first_name' => 'Ama',
            'surname' => 'Mensah',
            'gender' => 1,
            'date_of_birth' => '1990-01-01',
            'marital_status' => 1,
            'nationality_id' => 1,
            'phone_number' => '+233541234567',
            'whatsapp_number' => '+233541234567',
            'email' => 'ama.mensah@example.com',
            'address' => 'N/A',
            'position_held' => 1,
            'profession' => 1,
            'residence_country_id' => 1,
            'languages_spoken' => 'English',
            'need_accommodation' => 1,
            'emergency_contacts_name' => 'Kofi Mensah',
            'emergency_contacts_phone_number' => '+233541234568',
            'disability' => 0,
            'special_needs' => 'None',
            'is_student' => 0,
            'token' => (string) random_int(100000, 999999),
        ], $overrides));
    }

    public function test_local_format_phone_finds_registrant_stored_in_international_format(): void
    {
        $this->createRegistrant($this->createEvent());

        $response = $this->postJson(route('registrant.lookup'), ['identifier' => '0541234567']);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.fields.first_name', 'Ama')
            ->assertJsonPath('0.fields.languages_spoken', 'English')
            ->assertJsonPath('0.label', 'A*a M****h · Test Conference 2025');
    }

    public function test_email_match_is_case_insensitive(): void
    {
        $this->createRegistrant($this->createEvent());

        $this->postJson(route('registrant.lookup'), ['identifier' => 'AMA.Mensah@Example.com'])
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_same_person_across_events_collapses_to_newest_record(): void
    {
        $this->createRegistrant($this->createEvent(), ['profession' => 1]);
        $this->createRegistrant($this->createEvent(['name' => 'Later Conference', 'start_date' => '2026-08-10']), ['profession' => 2]);
        $this->createRegistrant($this->createEvent(), ['first_name' => 'Kwame', 'gender' => 2]);

        $response = $this->postJson(route('registrant.lookup'), ['identifier' => '+233541234567']);

        $response->assertOk()->assertJsonCount(2);
        $ama = collect($response->json())->firstWhere('fields.first_name', 'Ama');
        $this->assertSame(2, (int) $ama['fields']['profession']);
        $this->assertStringContainsString('Later Conference 2026', $ama['label']);
    }

    public function test_response_never_contains_sensitive_fields(): void
    {
        $this->createRegistrant($this->createEvent());

        $body = $this->postJson(route('registrant.lookup'), ['identifier' => '0541234567'])->getContent();

        foreach (['date_of_birth', '1990-01-01', 'emergency_contacts', 'Kofi', 'marital_status', 'special_needs', 'token', '"id"', 'disability'] as $needle) {
            $this->assertStringNotContainsString($needle, $body);
        }
    }

    public function test_unknown_identifier_returns_empty_list(): void
    {
        $this->createRegistrant($this->createEvent());

        $this->postJson(route('registrant.lookup'), ['identifier' => '0200000000'])
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_lookup_is_rate_limited_after_five_tries(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('registrant.lookup'), ['identifier' => '0200000000'])->assertOk();
        }

        $this->postJson(route('registrant.lookup'), ['identifier' => '0200000000'])->assertStatus(429);
    }
}
