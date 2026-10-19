<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PurgeLegacyAssetsCommandTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test dry-run execution of server:purge-legacy-assets.
     */
    public function test_purge_legacy_assets_dry_run_exits_successfully(): void
    {
        $exitCode = Artisan::call('server:purge-legacy-assets');
        $this->assertSame(0, $exitCode);
    }

    /**
     * Test force purge execution does not harm active assets.
     */
    public function test_purge_legacy_assets_force_preserves_active_assets(): void
    {
        $exitCode = Artisan::call('server:purge-legacy-assets', ['--force' => true]);
        $this->assertSame(0, $exitCode);

        $publicDir = public_path();
        $this->assertFileExists($publicDir . '/logo 2026.webp');
        $this->assertFileExists($publicDir . '/image 61.webp');
        $this->assertFileExists($publicDir . '/image 62.webp');
        $this->assertFileExists($publicDir . '/image 63.webp');
        $this->assertFileExists($publicDir . '/image 66.webp');
        $this->assertFileExists($publicDir . '/favicon.png');
    }
}
