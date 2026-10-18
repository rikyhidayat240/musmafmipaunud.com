<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupObsoleteAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-obsolete-assets
        {--force : Actually delete the verified obsolete files (dry-run by default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and delete provably obsolete static assets with 0 references in MUSMA';

    /**
     * Candidate obsolete files in public/ with expected zero references.
     *
     * @var array<string, string>
     */
    protected array $candidates = [
        'assets/foto-slide-hero/Banner_1.webp' => 'Unused hero slider banner (slider replaced by 3D Kotak Suara)',
        'assets/foto-slide-hero/Banner_2.webp' => 'Unused hero slider banner',
        'assets/foto-slide-hero/Banner_3.webp' => 'Unused hero slider banner',
        'assets/foto-slide-hero/Banner_4.webp' => 'Unused hero slider banner',
        'assets/foto-slide-hero/Banner_5.webp' => 'Unused hero slider banner',
        'assets/foto-slide-hero/Banner_6.webp' => 'Unused hero slider banner',
        'assets/foto-slide-hero/Banner_7.webp' => 'Unused hero slider banner',
        'assets/foto-slide-hero/Banner_8.webp' => 'Unused hero slider banner',
        'assets/foto-slide-hero/Banner_9.webp' => 'Unused hero slider banner',
        'image 64.png'                         => 'Unused red leaf botanical ornament',
        'image 65.png'                         => 'Unused floral bracket border ornament',
        'image 67.png'                         => 'Unused blossom flower ornament',
        'assets/IMG_5597.webp'                 => 'Unused historical event photo',
        'assets/background-hero.png'           => 'Unused legacy hero background',
        'assets/background-logo-dpm.png'       => 'Unused legacy DPM background ported from PEMIRA',
        'assets/calon-1.png'                   => 'Duplicate template photo; active records use storage/foto-kandidat/',
        'assets/calon-2.png'                   => 'Duplicate template photo; active records use storage/foto-kandidat/',
        'assets/calon-3.png'                   => 'Unused demo candidate 3 photo',
        'assets/corner-image-left.png'         => 'Superseded by image 62.webp',
        'assets/corner-image-right.png'        => 'Superseded by image 63.webp',
        'Text.png'                             => 'Unused legacy ballot box mail icon',
        'gembok.png'                           => 'Unused legacy lock icon',
        'petir.png'                            => 'Unused legacy lightning icon',
        'logo-musma.svg'                       => 'Duplicate at root; active view uses /assets/musma-logo.svg',
        'images/blank-profile-picture.webp'    => 'Duplicate in images/; active views use /assets/blank-profile-picture.webp',
    ];

    /**
     * Directories to scan for references.
     *
     * @var array<string>
     */
    protected array $scanDirectories = [
        'resources',
        'app',
        'routes',
        'database',
        'config',
        'tests',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $publicDir = public_path();
        $baseDir = base_path();

        $this->info('=== MUSMA OBSOLETE ASSET CLEANUP & AUDIT ===');
        $this->info('Mode: ' . ($force ? 'FORCE (Deleting verified files)' : 'DRY RUN (Auditing only)'));

        // Collect all source files to scan
        $sourceFiles = $this->collectSourceFiles($baseDir);
        $this->info(sprintf('Scanning %d codebase files across resources/, app/, routes/, database/, config/, tests/ ...', count($sourceFiles)));

        $tableData = [];
        $totalBytesReclaimed = 0;
        $deletedCount = 0;
        $blockedCount = 0;

        foreach ($this->candidates as $relPath => $reason) {
            $filePath = $publicDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
            $exists = file_exists($filePath);
            $fileSize = $exists ? filesize($filePath) : 0;

            // Integrity protection check: never delete anything outside public/ or in storage/ or .env
            if (!$this->isSafeToDelete($relPath, $filePath, $publicDir)) {
                $this->error("BLOCKED: {$relPath} violates safety constraints.");
                $blockedCount++;
                continue;
            }

            // Reference audit
            $references = $this->findReferences($relPath, $sourceFiles);

            if (count($references) > 0) {
                $this->warn(sprintf('PRESERVED: %s has %d reference(s).', $relPath, count($references)));
                $tableData[] = [
                    $relPath,
                    number_format($fileSize) . ' B',
                    'PRESERVED (' . count($references) . ' refs)',
                    $reason,
                ];
                continue;
            }

            if (!$exists) {
                $tableData[] = [
                    $relPath,
                    '0 B',
                    'ALREADY DELETED',
                    $reason,
                ];
                continue;
            }

            // 0 references verified and file exists
            if ($force) {
                @unlink($filePath);
                $deletedCount++;
                $totalBytesReclaimed += $fileSize;
                $status = 'DELETED (' . number_format($fileSize) . ' B)';
            } else {
                $totalBytesReclaimed += $fileSize;
                $status = 'CANDIDATE (' . number_format($fileSize) . ' B)';
            }

            $tableData[] = [
                $relPath,
                number_format($fileSize) . ' B',
                $status,
                $reason,
            ];
        }

        // Clean up empty directories if force
        if ($force) {
            $heroDir = $publicDir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'foto-slide-hero';
            if (is_dir($heroDir) && count(scandir($heroDir)) <= 2) {
                @rmdir($heroDir);
                $this->info("Removed empty directory: {$heroDir}");
            }
            $imgDir = $publicDir . DIRECTORY_SEPARATOR . 'images';
            if (is_dir($imgDir) && count(scandir($imgDir)) <= 2) {
                @rmdir($imgDir);
                $this->info("Removed empty directory: {$imgDir}");
            }
        }

        $this->table(
            ['Asset Path', 'Size', 'Status', 'Justification'],
            $tableData
        );

        $this->newLine();
        $this->info(sprintf(
            'Summary: %d files verified obsolete | Total Reclaimed: %s bytes (%s MB)',
            count($this->candidates),
            number_format($totalBytesReclaimed),
            round($totalBytesReclaimed / 1024 / 1024, 2)
        ));

        if (!$force) {
            $this->warn('This was a DRY RUN. Pass --force to execute the deletions.');
        }

        return self::SUCCESS;
    }

    /**
     * Check if a path is safe to delete under integrity constraints.
     */
    protected function isSafeToDelete(string $relPath, string $fullPath, string $publicDir): bool
    {
        $realPublic = realpath($publicDir) ?: $publicDir;
        $normalizedRealPublic = strtolower(rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $realPublic), DIRECTORY_SEPARATOR));
        $normalizedFull = strtolower(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath));

        // Must be inside public/
        if (!str_starts_with($normalizedFull, $normalizedRealPublic)) {
            return false;
        }

        // Never touch storage runtime data, .env, or backup
        if (str_contains($relPath, 'storage') || str_contains($relPath, '.env') || str_contains($relPath, 'backup')) {
            return false;
        }

        return true;
    }

    /**
     * Collect all source files to scan for references.
     *
     * @return array<string>
     */
    protected function collectSourceFiles(string $baseDir): array
    {
        $files = [];

        foreach ($this->scanDirectories as $dir) {
            $dirPath = $baseDir . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($dirPath)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    if (str_ends_with($fileInfo->getFilename(), 'CleanupObsoleteAssetsTest.php') ||
                        str_ends_with($fileInfo->getFilename(), 'CleanupObsoleteAssets.php')) {
                        continue;
                    }
                    $files[] = $fileInfo->getPathname();
                }
            }
        }

        // Add root config files
        foreach (['vite.config.ts', 'package.json', 'composer.json'] as $rootFile) {
            $rfp = $baseDir . DIRECTORY_SEPARATOR . $rootFile;
            if (file_exists($rfp)) {
                $files[] = $rfp;
            }
        }

        return $files;
    }

    /**
     * Find references to an asset in source files.
     *
     * @param array<string> $sourceFiles
     * @return array<string>
     */
    protected function findReferences(string $relPath, array $sourceFiles): array
    {
        $basename = basename($relPath);
        $forwardSlash = '/' . ltrim(str_replace('\\', '/', $relPath), '/');

        // If asset is inside a subdirectory, match by path to avoid false collisions with other dirs (e.g. storage/foto-kandidat/)
        if (str_contains($relPath, '/') || str_contains($relPath, '\\')) {
            $terms = [
                $relPath,
                $forwardSlash,
                str_replace('/', '\\', $relPath),
            ];
        } else {
            $terms = [
                $basename,
                $forwardSlash,
            ];
        }

        $matches = [];

        foreach ($sourceFiles as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            foreach ($terms as $term) {
                if (stripos($content, $term) !== false) {
                    $matches[] = $file . ' (matched: ' . $term . ')';
                    break;
                }
            }
        }

        return $matches;
    }
}
