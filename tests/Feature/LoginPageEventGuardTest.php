<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageEventGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_when_no_event_is_active(): void
    {
        // No Event rows at all — used to crash with
        // "Call to a member function on null" via
        // Event::where('active_flag', 1)->first()->flyer_path.
        $response = $this->get('/login');

        $response->assertOk();
    }
}
