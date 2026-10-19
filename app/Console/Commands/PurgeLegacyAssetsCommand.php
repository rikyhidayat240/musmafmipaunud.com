<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PurgeLegacyAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:purge-legacy-assets
        {--force : Actually delete verified obsolete legacy files from server disk (dry-run by default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge obsolete legacy files (>300 KB) flagged on Hostinger server disk';

    /**
     * Obsolete legacy files on disk that should be completely removed from public/.
     *
     * @var array<string, string>
     */
    protected array $legacyFiles = [
        'image 67.png'                         => 'Legacy uncompressed blossom ornament (2.21 MB)',
        'image 65.png'                         => 'Legacy uncompressed bracket ornament (903 KB)',
        'assets/background-hero.png'           => 'Legacy uncompressed background hero (856 KB)',
        'assets/IMG_5597.webp'                 => 'Legacy unused photo (649 KB)',
        'assets/background-logo-dpm.png'       => 'Legacy uncompressed logo background (549 KB)',
        'image 62.png'                         => 'Legacy uncompressed botanical ornament (477 KB, superseded by image 62.webp)',
        'image 64.png'                         => 'Legacy uncompressed leaf ornament (463 KB)',
        'image 61.png'                         => 'Legacy uncompressed botanical ornament (357 KB, superseded by image 61.webp)',
        'image 63.png'                         => 'Legacy uncompressed botanical ornament (319 KB, superseded by image 63.webp)',
        'image 66.png'                         => 'Legacy uncompressed botanical ornament (superseded by image 66.webp)',
        'assets/foto-slide-hero/Banner_1.webp' => 'Legacy unused hero banner (1.2 MB)',
        'assets/foto-slide-hero/Banner_2.webp' => 'Legacy unused hero banner (1.1 MB)',
        'assets/foto-slide-hero/Banner_3.webp' => 'Legacy unused hero banner (1.2 MB)',
        'assets/foto-slide-hero/Banner_4.webp' => 'Legacy unused hero banner (1.2 MB)',
        'assets/foto-slide-hero/Banner_5.webp' => 'Legacy unused hero banner (1.2 MB)',
        'assets/foto-slide-hero/Banner_6.webp' => 'Legacy unused hero banner (1.2 MB)',
        'assets/foto-slide-hero/Banner_7.webp' => 'Legacy unused hero banner (1.2 MB)',
        'assets/foto-slide-hero/Banner_8.webp' => 'Legacy unused hero banner (1.2 MB)',
        'assets/foto-slide-hero/Banner_9.webp' => 'Legacy unused hero banner (1.2 MB)',
        'assets/calon-1.png'                   => 'Legacy uncompressed template photo (superseded by storage/foto-kandidat)',
        'assets/calon-2.png'                   => 'Legacy uncompressed template photo (superseded by storage/foto-kandidat)',
        'assets/calon-3.png'                   => 'Legacy uncompressed template photo',
        'assets/corner-image-left.png'         => 'Superseded by image 62.webp',
        'assets/corner-image-right.png'        => 'Superseded by image 63.webp',
        'Text.png'                             => 'Legacy unused icon',
        'gembok.png'                           => 'Legacy unused icon',
        'petir.png'                            => 'Legacy unused icon',
        'logo-musma.svg'                       => 'Duplicate root svg (active: /assets/musma-logo.svg)',
        'images/blank-profile-picture.webp'    => 'Duplicate in images/ (active: /assets/blank-profile-picture.webp)',
    ];

    /**
     * Active protected assets that must NEVER be deleted.
     *
     * @var array<string>
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
        'favicon.png',
        'favicon-32x32.png',
        'favicon.ico',
        'favicon.svg',
        'apple-touch-icon.png',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $publicDir = public_path();

        $this->info('====================================================');
        $this->info('  MUSMA FMIPA — HOSTINGER SERVER ASSET PURGE');
        $this->info('====================================================');
        $this->info('Mode: ' . ($force ? 'FORCE PURGE (Unlinking legacy files)' : 'DRY RUN (Previewing files on disk)'));
        $this->line('');

        $totalBytes = 0;
        $foundCount = 0;
        $purgedCount = 0;

        $rows = [];

        foreach ($this->legacyFiles as $relPath => $desc) {
            $fullPath = $publicDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);

            // Safety guard: Must reside within public_path() and never in storage/ or .env
            $realPublic = realpath($publicDir) ?: $publicDir;
            if (str_contains($relPath, '..') || str_starts_with($relPath, 'storage') || str_starts_with($relPath, '.env')) {
                $this->error("BLOCKED: {$relPath} violates safety policy.");
                continue;
            }

            if (file_exists($fullPath)) {
                $foundCount++;
                $size = filesize($fullPath);
                $totalBytes += $size;

                if ($force) {
                    if (@unlink($fullPath)) {
                        $purgedCount++;
                        $rows[] = [$relPath, $this->formatBytes($size), 'PURGED', $desc];
                    } else {
                        $rows[] = [$relPath, $this->formatBytes($size), 'FAILED TO DELETE', $desc];
                    }
                } else {
                    $rows[] = [$relPath, $this->formatBytes($size), 'FOUND (Dry Run)', $desc];
                }
            } else {
                $rows[] = [$relPath, '-', 'ALREADY GONE', $desc];
            }
        }

        $this->table(['File Path (public/)', 'Size', 'Status', 'Description'], $rows);

        // Remove empty legacy directories if force
        if ($force) {
            $heroDir = $publicDir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'foto-slide-hero';
            if (is_dir($heroDir) && count(scandir($heroDir) ?: []) <= 2) {
                @rmdir($heroDir);
                $this->info("Removed empty directory: {$heroDir}");
            }
        }

        $this->line('');
        $this->info(sprintf(
            'Summary: %d legacy files detected on disk (%s). Purged: %d.',
            $foundCount,
            $this->formatBytes($totalBytes),
            $purgedCount
        ));

        // Integrity check: verify protected assets exist
        $missingProtected = 0;
        foreach ($this->protectedAssets as $protected) {
            $protPath = $publicDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $protected);
            if (!file_exists($protPath)) {
                $this->warn("WARNING: Protected asset missing: {$protected}");
                $missingProtected++;
            }
        }

        if ($missingProtected === 0) {
            $this->info('Integrity Check: All active assets are verified safe and present.');
        }

        if (!$force && $foundCount > 0) {
            $this->comment('To permanently purge these files, run:');
            $this->line('  php artisan server:purge-legacy-assets --force');
        }

        return 0;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
