<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Tests\TestCase;

class NotifyComponentXssTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // $errors is normally bound by ShareErrorsFromSession middleware
        // during a real HTTP request; bind it manually since these tests
        // render the component directly.
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);
    }

    public function test_flash_messages_cannot_break_out_of_the_javascript_string_context(): void
    {
        $payload = '</script><script>alert(document.cookie)</script>';

        session()->flash('success', $payload);
        session()->flash('error', $payload);
        session()->flash('warning', $payload);
        session()->flash('info', $payload);
        session()->flash('warning_sweet', $payload);
        session()->flash('success_sweet', $payload);
        session()->flash('info_sweet', $payload);
        session()->flash('error_sweet', $payload);

        $html = view('components.notify')->render();

        // The raw payload must never appear unescaped in the output — if it
        // did, the </script> would close the surrounding tag and the
        // injected <script> would execute.
        $this->assertStringNotContainsString('</script><script>alert(document.cookie)</script>', $html);
        $this->assertStringNotContainsString('<script>alert(document.cookie)</script>', $html);

        // The safely-encoded payload (produced the same way Js::from()
        // encodes it) must be present instead — derived programmatically
        // rather than hand-typed, since it contains unicode escapes that
        // are easy to transcribe incorrectly.
        $this->assertStringContainsString((string) Js::from($payload), $html);
    }

    public function test_notify_component_renders_without_error_when_no_flash_messages_are_present(): void
    {
        $html = view('components.notify')->render();

        $this->assertIsString($html);
    }
}
