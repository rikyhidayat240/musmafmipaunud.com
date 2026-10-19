<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CleanupObsoleteAssetsTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Provably obsolete files that should be deleted.
     */
    protected array $expectedDeleted = [
        'assets/foto-slide-hero/Banner_1.webp',
        'assets/foto-slide-hero/Banner_2.webp',
        'assets/foto-slide-hero/Banner_3.webp',
        'assets/foto-slide-hero/Banner_4.webp',
        'assets/foto-slide-hero/Banner_5.webp',
        'assets/foto-slide-hero/Banner_6.webp',
        'assets/foto-slide-hero/Banner_7.webp',
        'assets/foto-slide-hero/Banner_8.webp',
        'assets/foto-slide-hero/Banner_9.webp',
        'image 61.png',
        'image 62.png',
        'image 63.png',
        'image 64.png',
        'image 65.png',
        'image 66.png',
        'image 67.png',
        'assets/IMG_5597.webp',
        'assets/background-hero.png',
        'assets/background-logo-dpm.png',
        'assets/calon-1.png',
        'assets/calon-2.png',
        'assets/calon-3.png',
        'assets/corner-image-left.png',
        'assets/corner-image-right.png',
        'Text.png',
        'gembok.png',
        'petir.png',
        'logo-musma.svg',
        'images/blank-profile-picture.webp',
    ];

    /**
     * Active assets that MUST NEVER be deleted.
     */
    protected array $protectedAssets = [
        'logo 2026.webp',
        'image 61.webp',
        'image 62.webp',
        'image 63.webp',
        'image 66.webp',
        'assets/musma-logo.svg',
        'assets/blank-photo-icon.jpg',
        'assets/blank-profile-picture.webp',
        'favicon.svg',
        'favicon.ico',
        'favicon.png',
        'favicon-32x32.png',
        'apple-touch-icon.png',
    ];

    /**
     * Test dry run does not delete any files.
     */
    public function test_cleanup_obsolete_assets_dry_run_exits_cleanly(): void
    {
        $exitCode = Artisan::call('app:cleanup-obsolete-assets');
        $this->assertSame(0, $exitCode);
    }

    /**
     * Test force cleanup deletes all obsolete files, cleans empty dirs, and preserves active assets.
     */
    public function test_cleanup_obsolete_assets_force_deletes_files_and_preserves_active(): void
    {
        $publicDir = public_path();

        // Run force cleanup
        $exitCode = Artisan::call('app:cleanup-obsolete-assets', ['--force' => true]);
        $this->assertSame(0, $exitCode);

        // Verify every candidate file is now deleted
        foreach ($this->expectedDeleted as $relPath) {
            $fullPath = $publicDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
            $this->assertFileDoesNotExist(
                $fullPath,
                "Obsolete asset {$relPath} must not exist after cleanup."
            );
        }

        // Verify empty directories are cleaned up
        $heroDir = $publicDir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'foto-slide-hero';
        $this->assertDirectoryDoesNotExist($heroDir, "Empty directory {$heroDir} should be removed.");

        // Verify all protected active assets remain intact
        foreach ($this->protectedAssets as $relPath) {
            $fullPath = $publicDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
            $this->assertFileExists(
                $fullPath,
                "Active asset {$relPath} must NOT be deleted."
            );
        }
    }

    /**
     * Exhaustive reference-scanning test across all Vue, Blade, CSS, and PHP files.
     * Verifies that every static asset referenced in code resolves to an existing file on disk
     * with exact case sensitivity.
     */
    public function test_zero_broken_asset_references_across_entire_musma_codebase(): void
    {
        $publicDir = public_path();
        $baseDir = base_path();

        $scanExtensions = ['vue', 'blade.php', 'css', 'ts', 'js', 'php'];
        $scanDirs = ['resources', 'app', 'routes', 'database'];

        $brokenReferences = [];
        $scannedReferencesCount = 0;

        foreach ($scanDirs as $dir) {
            $dirPath = $baseDir . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($dirPath)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }

                $filePath = $fileInfo->getPathname();
                $filename = $fileInfo->getFilename();

                // Check extension
                $matchedExt = false;
                foreach ($scanExtensions as $ext) {
                    if (str_ends_with($filename, '.' . $ext) || ($ext === 'blade.php' && str_ends_with($filename, '.blade.php'))) {
                        $matchedExt = true;
                        break;
                    }
                }

                if (!$matchedExt || str_ends_with($filename, 'CleanupObsoleteAssetsTest.php')) {
                    continue;
                }

                $content = file_get_contents($filePath);
                if ($content === false) {
                    continue;
                }

                // Match static asset references strictly within quotes or CSS url()
                $patterns = [
                    '/["\'](\/[a-zA-Z0-9_\-\s%.\/]+\.(?:png|webp|svg|jpg|jpeg|ico|gif|woff2?|ttf))["\']/i',
                    '/url\(["\']?(\/[a-zA-Z0-9_\-\s%.\/]+\.(?:png|webp|svg|jpg|jpeg|ico|gif|woff2?|ttf))["\']?\)/i',
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        foreach ($matches[1] as $refPath) {
                            $scannedReferencesCount++;
                            $cleanRef = urldecode(trim($refPath));

                            // Disregard Vite build outputs if checking static source assets, or verify them
                            if (str_starts_with($cleanRef, '/build/')) {
                                continue;
                            }

                            $diskPath = $publicDir . str_replace('/', DIRECTORY_SEPARATOR, $cleanRef);

                            if (!file_exists($diskPath)) {
                                $brokenReferences[] = [
                                    'source_file' => str_replace($baseDir . DIRECTORY_SEPARATOR, '', $filePath),
                                    'referenced' => $cleanRef,
                                    'reason' => 'File not found on disk at ' . $diskPath,
                                ];
                            } else {
                                // Check exact case sensitivity on disk
                                $parentDir = dirname($diskPath);
                                $expectedBasename = basename($diskPath);
                                $actualFiles = scandir($parentDir) ?: [];

                                if (!in_array($expectedBasename, $actualFiles, true)) {
                                    $brokenReferences[] = [
                                        'source_file' => str_replace($baseDir . DIRECTORY_SEPARATOR, '', $filePath),
                                        'referenced' => $cleanRef,
                                        'reason' => 'Case mismatch on disk (expected exact: ' . $expectedBasename . ')',
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->assertGreaterThan(0, $scannedReferencesCount, 'Must scan and find active static asset references.');
        $this->assertEmpty(
            $brokenReferences,
            'Zero broken asset references allowed. Found broken: ' . json_encode($brokenReferences, JSON_PRETTY_PRINT)
        );
    }
}
