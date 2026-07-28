<?php

namespace Tests\Feature\Registrant;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_submissions_are_rate_limited_after_ten_tries(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('registrant.store'), []);
            $response->assertStatus(302);
        }

        $response = $this->post(route('registrant.store'), []);

        $response->assertStatus(429);
    }

    public function test_batch_uploads_are_rate_limited_after_five_tries(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('registrant.batch'), []);
            $response->assertStatus(302);
        }

        $response = $this->post(route('registrant.batch'), []);

        $response->assertStatus(429);
    }
}
