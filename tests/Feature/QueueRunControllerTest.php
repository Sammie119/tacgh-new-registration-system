<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueRunControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_404s_with_the_wrong_secret(): void
    {
        config(['cron.secret' => 'the-real-secret']);

        $response = $this->get('/tasks/run-queue/wrong-guess');

        $response->assertNotFound();
    }

    public function test_it_404s_when_no_secret_is_configured(): void
    {
        config(['cron.secret' => null]);

        // No configured secret must reject every guess, not just wrong ones.
        $response = $this->get('/tasks/run-queue/anything');

        $response->assertNotFound();
    }

    public function test_it_runs_with_the_correct_secret(): void
    {
        config(['cron.secret' => 'the-real-secret']);

        $response = $this->get('/tasks/run-queue/the-real-secret');

        $response->assertOk();
        $response->assertSee('OK');
    }

    public function test_overlapping_hits_are_skipped_instead_of_stacking(): void
    {
        config(['cron.secret' => 'the-real-secret']);

        \Illuminate\Support\Facades\Cache::lock('queue-run-lock', 60)->get(function () {
            $response = $this->get('/tasks/run-queue/the-real-secret');

            $response->assertOk();
            $response->assertSee('SKIPPED');
        });
    }
}
