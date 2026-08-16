<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic smoke test.
     *
     * The root route is gated by the installer middleware, which redirects to
     * the installer (302) whenever storage/installed is missing. We force the
     * "not installed" state so the test is deterministic in every environment.
     *
     * @return void
     */
    public function testUninstalledAppRedirectsToInstaller()
    {
        $installed = storage_path('installed');
        $backup = $installed.'.bak';

        if (file_exists($installed)) {
            rename($installed, $backup);
        }

        try {
            $response = $this->get('/');

            $response->assertRedirect();
        } finally {
            if (file_exists($backup)) {
                rename($backup, $installed);
            }
        }
    }
}
