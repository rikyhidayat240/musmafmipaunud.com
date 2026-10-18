<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeStaticImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-static-images
        {--force : Apply the image conversions and overwrites (dry-run by default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resize and convert oversized active static images in public/ to WebP';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $publicDir = public_path();

        $targets = [
            [
                'src' => 'logo 2026.png',
                'webp' => 'logo 2026.webp',
                'max_dim' => 600,
                'quality' => 85,
            ],
            [
                'src' => 'image 63.png',
                'webp' => 'image 63.webp',
                'max_dim' => 1000,
                'quality' => 85,
            ],
            [
                'src' => 'image 62.png',
                'webp' => 'image 62.webp',
                'max_dim' => 1000,
                'quality' => 85,
            ],
            [
                'src' => 'image 66.png',
                'webp' => 'image 66.webp',
                'max_dim' => 600,
                'quality' => 85,
            ],
            [
                'src' => 'image 61.png',
                'webp' => 'image 61.webp',
                'max_dim' => 600,
                'quality' => 85,
            ],
        ];

        $this->info('=== STATIC IMAGE OPTIMIZATION (MUSMA) ===');
        $this->info('Mode: ' . ($force ? 'FORCE (Writing changes)' : 'DRY RUN (Simulating)'));

        // Check if Python with Pillow is available
        $pythonScript = base_path('../../optimize_musma_images.py');
        $pythonReal = realpath($pythonScript);
        $usedMethod = 'GD';

        if ($force) {
            // First attempt python script if exists
            if ($pythonReal && file_exists($pythonReal)) {
                $pyOutput = @shell_exec('python "' . $pythonReal . '" 2>&1');
                if ($pyOutput && str_contains($pyOutput, 'SUMMARY_TABLE:')) {
                    $this->info("Python Pillow optimization output:\n" . $pyOutput);
                    $usedMethod = 'Python PIL';
                }
            }

            // Verify if WebP files were generated. If not, use GD fallback directly in PHP
            $missingWebp = false;
            foreach ($targets as $t) {
                if (!file_exists($publicDir . DIRECTORY_SEPARATOR . $t['webp'])) {
                    $missingWebp = true;
                    break;
                }
            }

            if ($missingWebp) {
                $this->warn('WebP files not yet found, executing native PHP GD optimization...');
                $usedMethod = 'PHP GD';
                foreach ($targets as $t) {
                    $srcFile = $publicDir . DIRECTORY_SEPARATOR . $t['src'];
                    $webpFile = $publicDir . DIRECTORY_SEPARATOR . $t['webp'];
                    if (!file_exists($srcFile)) {
                        $this->error("Source file not found: {$srcFile}");
                        continue;
                    }

                    if (!function_exists('imagecreatefrompng')) {
                        $this->error('PHP GD extension is not installed.');
                        return self::FAILURE;
                    }

                    $img = @imagecreatefrompng($srcFile);
                    if (!$img) {
                        $this->error("Failed to load PNG: {$srcFile}");
                        continue;
                    }

                    $origW = imagesx($img);
                    $origH = imagesy($img);
                    $maxDim = $t['max_dim'];
                    $newW = $origW;
                    $newH = $origH;

                    if ($maxDim && max($origW, $origH) > $maxDim) {
                        if ($origW >= $origH) {
                            $newW = $maxDim;
                            $newH = (int) round($origH * ($maxDim / $origW));
                        } else {
                            $newH = $maxDim;
                            $newW = (int) round($origW * ($maxDim / $origH));
                        }
                    }

                    $resized = imagecreatetruecolor($newW, $newH);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                    imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);

                    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                    // Save WebP
                    if (function_exists('imagewebp')) {
                        imagewebp($resized, $webpFile, $t['quality']);
                    }

                    // Save optimized PNG fallback
                    imagepng($resized, $srcFile, 9);

                    imagedestroy($img);
                    imagedestroy($resized);
                }
            }
        }

        // Print inspection table
        $this->newLine();
        $this->info("Image Measurement Report (Engine: {$usedMethod}):");
        $tableData = [];
        $totalOrig = 0;
        $totalWebp = 0;

        foreach ($targets as $t) {
            $srcFile = $publicDir . DIRECTORY_SEPARATOR . $t['src'];
            $webpFile = $publicDir . DIRECTORY_SEPARATOR . $t['webp'];

            $origBytes = file_exists($srcFile) ? filesize($srcFile) : 0;
            $webpBytes = file_exists($webpFile) ? filesize($webpFile) : 0;
            $totalOrig += $origBytes;
            $totalWebp += $webpBytes;

            $pct = $origBytes > 0 && $webpBytes > 0 ? round((1 - ($webpBytes / $origBytes)) * 100, 1) . '%' : 'N/A';

            $tableData[] = [
                $t['src'],
                number_format($origBytes) . ' B (' . round($origBytes / 1024, 1) . ' KB)',
                $t['webp'],
                $webpBytes > 0 ? number_format($webpBytes) . ' B (' . round($webpBytes / 1024, 1) . ' KB)' : 'Not generated',
                $pct,
            ];
        }

        $totalSavedPct = $totalOrig > 0 && $totalWebp > 0 ? round((1 - ($totalWebp / $totalOrig)) * 100, 1) . '%' : 'N/A';

        $this->table(
            ['Source File', 'Original Size', 'WebP File', 'WebP Size', 'Savings'],
            $tableData
        );

        $this->info(sprintf(
            'Total Baseline: %s bytes | Total WebP: %s bytes | Total Saved: %s bytes (%s)',
            number_format($totalOrig),
            number_format($totalWebp),
            number_format($totalOrig - $totalWebp),
            $totalSavedPct
        ));

        return self::SUCCESS;
    }
}
