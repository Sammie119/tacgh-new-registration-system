<?php

namespace Tests\Feature\Registrant;

use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function createRegistrantStage(array $overrides = []): RegistrantStage
    {
        return RegistrantStage::create(array_merge([
            'title' => 1,
            'first_name' => 'Ama',
            'surname' => 'Mensah',
            'gender' => 1,
            'date_of_birth' => '1990-01-01',
            'marital_status' => 1,
            'nationality_id' => 1,
            'phone_number' => '+233541234567',
            'email' => 'ama.mensah@example.com',
            'address' => '123 Test Street',
            'position_held' => 1,
            'profession' => 1,
            'residence_country_id' => 1,
            'languages_spoken' => 'English',
            'need_accommodation' => 1,
            'emergency_contacts_name' => 'Kofi Mensah',
            'attendance_type' => 'In-Person',
            'event_id' => 1,
            'disability' => 0,
            'token' => 'ABC123',
        ], $overrides));
    }

    public function test_registrant_can_login_with_a_valid_token_and_matching_email(): void
    {
        $this->createRegistrantStage([
            'token' => 'ABC123',
            'email' => 'ama.mensah@example.com',
        ]);

        $response = $this->post(route('registrant_login'), [
            'email' => 'ama.mensah@example.com',
            'password' => 'ABC123',
        ]);

        $response->assertRedirect(route('registrant_page'));
        $this->assertNotNull(session('registrant'));
    }

    public function test_registrant_can_login_with_a_valid_token_and_matching_phone_number(): void
    {
        $this->createRegistrantStage([
            'token' => 'ABC123',
            'phone_number' => '+233541234567',
        ]);

        $response = $this->post(route('registrant_login'), [
            'email' => '+233541234567',
            'password' => 'ABC123',
        ]);

        $response->assertRedirect(route('registrant_page'));
    }

    public function test_login_fails_with_a_valid_token_but_mismatched_identity(): void
    {
        $this->createRegistrantStage([
            'token' => 'ABC123',
            'email' => 'ama.mensah@example.com',
        ]);

        $response = $this->post(route('registrant_login'), [
            'email' => 'someone.else@example.com',
            'password' => 'ABC123',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(session('registrant'));
    }

    public function test_login_fails_with_an_unknown_token(): void
    {
        $response = $this->post(route('registrant_login'), [
            'email' => 'ama.mensah@example.com',
            'password' => 'NOTREAL',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(session('registrant'));
    }

    public function test_login_attempts_are_rate_limited_after_five_tries(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('registrant_login'), [
                'email' => 'nobody@example.com',
                'password' => 'GUESS'.$i,
            ]);
            $response->assertStatus(302);
        }

        $response = $this->post(route('registrant_login'), [
            'email' => 'nobody@example.com',
            'password' => 'GUESS5',
        ]);

        $response->assertStatus(429);
    }
}
