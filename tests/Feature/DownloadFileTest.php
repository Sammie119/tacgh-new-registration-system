<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_requesting_a_nonexistent_download_id_shows_an_error_instead_of_crashing(): void
    {
        $response = $this->get(route('registrant.download.file', ['file_path' => 999999]));

        $response->assertRedirect(route('registrant_page'));
        $response->assertSessionHas('error', 'File Not Found!!!');
    }
}
