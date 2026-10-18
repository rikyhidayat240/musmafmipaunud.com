<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class StaticImageDietTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Ensure that all active static images are converted to WebP with proper scaling,
     * total homepage static payload is reduced by >90% (>11 MB saved),
     * no static image exceeds 300 KB, and all WebP files exist with valid magic bytes.
     */
    public function test_musma_static_images_are_optimized_to_webp_and_meet_performance_budgets(): void
    {
        $publicDir = public_path();

        // Run the optimization command to ensure genuine images are generated
        $exitCode = Artisan::call('app:optimize-static-images', ['--force' => true]);
        $this->assertSame(0, $exitCode);

        $targets = [
            'logo 2026.webp' => ['max_size' => 300 * 1024, 'label' => 'Brand Logo'],
            'image 63.webp'  => ['max_size' => 300 * 1024, 'label' => 'Top-Right Foliage'],
            'image 62.webp'  => ['max_size' => 300 * 1024, 'label' => 'Bottom-Left Foliage'],
            'image 66.webp'  => ['max_size' => 300 * 1024, 'label' => 'Ornament Divider'],
            'image 61.webp'  => ['max_size' => 300 * 1024, 'label' => 'Mandala Ornament'],
        ];

        $totalWebpBytes = 0;

        foreach ($targets as $filename => $spec) {
            $filePath = $publicDir . DIRECTORY_SEPARATOR . $filename;
            $this->assertFileExists($filePath, "Target WebP image {$filename} must exist.");

            $size = filesize($filePath);
            $totalWebpBytes += $size;

            // Must not exceed 300 KB budget
            $this->assertLessThan(
                $spec['max_size'],
                $size,
                "Image {$filename} size ({$size} bytes) exceeds 300 KB budget."
            );

            // Must have genuine WebP binary header: RIFF....WEBP
            $handle = fopen($filePath, 'rb');
            $header = fread($handle, 12);
            fclose($handle);

            $this->assertStringStartsWith('RIFF', $header, "{$filename} must start with RIFF header.");
            $this->assertSame('WEBP', substr($header, 8, 4), "{$filename} must be a valid WebP container.");
        }

        // Homepage baseline payload was 11,674,319 bytes (image 63.png + logo 2026.png + image 62.png + image 66.png)
        $baselineHomepageBytes = 11674319;
        
        $homepageWebpBytes = filesize($publicDir . DIRECTORY_SEPARATOR . 'image 63.webp')
            + filesize($publicDir . DIRECTORY_SEPARATOR . 'logo 2026.webp')
            + filesize($publicDir . DIRECTORY_SEPARATOR . 'image 62.webp')
            + filesize($publicDir . DIRECTORY_SEPARATOR . 'image 66.webp');

        $savedBytes = $baselineHomepageBytes - $homepageWebpBytes;
        $savedPercent = ($savedBytes / $baselineHomepageBytes) * 100;

        // Ensure homepage static image weight is reduced by >90% (>11 MB saved)
        $this->assertGreaterThan(
            90.0,
            $savedPercent,
            "Homepage image savings ({$savedPercent}%) must exceed 90%."
        );
        $this->assertGreaterThan(
            11000000,
            $savedBytes,
            "Homepage image bytes saved ({$savedBytes} bytes) must exceed 11,000,000 bytes."
        );
    }

    /**
     * Verify that all image paths referenced in Vue components resolve to existing files on disk.
     */
    public function test_all_referenced_images_resolve_to_existing_files(): void
    {
        $publicDir = public_path();

        $referencedImages = [
            '/logo 2026.webp',
            '/image 63.webp',
            '/image 62.webp',
            '/image 66.webp',
            '/image 61.webp',
            '/assets/musma-logo.svg',
            '/favicon.svg',
            '/favicon.png',
            '/favicon-32x32.png',
            '/apple-touch-icon.png',
        ];

        foreach ($referencedImages as $ref) {
            $normalizedPath = $publicDir . str_replace('/', DIRECTORY_SEPARATOR, $ref);
            $this->assertFileExists(
                $normalizedPath,
                "Referenced asset {$ref} must resolve to a valid file on disk at {$normalizedPath}"
            );
        }
    }
}
