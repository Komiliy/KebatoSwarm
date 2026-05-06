<?php

declare(strict_types=1);

namespace Swarm\Services;

use Swarm\Helpers\Url;

/**
 * TemplateManager — Manages VoxelSite ZIP files and versioned templates.
 *
 * ZIPs are placed in template/voxelsite/ (any filename).
 * Processing a ZIP extracts it, reads the VERSION file from inside,
 * moves it to template/voxelsite/v{version}/, handles the centralized
 * image library, and optionally activates it.
 */
class TemplateManager
{
    private string $templateDir;
    private string $libraryDir;

    public function __construct()
    {
        $this->templateDir = SWARM_ROOT . '/template/voxelsite';
        $this->libraryDir  = SWARM_ROOT . '/library';
    }

    /**
     * List all ZIP files in the template directory.
     *
     * @return array<int, array{filename: string, path: string, size: int, modified: string}>
     */
    public function listZips(): array
    {
        $zips = [];

        if (!is_dir($this->templateDir)) {
            return $zips;
        }

        $files = scandir($this->templateDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $path = $this->templateDir . '/' . $file;
            if (!is_file($path) || !str_ends_with(strtolower($file), '.zip')) continue;

            $zips[] = [
                'filename' => $file,
                'path'     => $path,
                'size'     => filesize($path),
                'modified' => date('Y-m-d H:i:s', filemtime($path)),
            ];
        }

        // Sort by modified date descending (newest first)
        usort($zips, fn($a, $b) => strcmp($b['modified'], $a['modified']));

        return $zips;
    }

    /**
     * List all prepared (extracted) versions.
     *
     * @return array<int, array{version: string, directory: string, active: bool, size: int}>
     */
    public function listVersions(): array
    {
        $versions = [];
        $activeVersion = $this->getActiveVersion();

        $dirs = glob($this->templateDir . '/v*', GLOB_ONLYDIR);
        if (!$dirs) return $versions;

        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            $versionFile = $dir . '/VERSION';
            $version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : ltrim($dirName, 'v');

            $versions[] = [
                'version'   => $version,
                'directory' => $dirName,
                'active'    => ($dirName === $activeVersion),
                'size'      => $this->dirSize($dir),
                'path'      => $dir,
            ];
        }

        // Sort by version descending
        usort($versions, fn($a, $b) => version_compare($b['version'], $a['version']));

        return $versions;
    }

    /**
     * Get the currently active version directory name (e.g., "v1.8.0").
     */
    public function getActiveVersion(): ?string
    {
        $activeLink = $this->templateDir . '/active';

        if (is_link($activeLink)) {
            return readlink($activeLink) ?: null;
        }

        return null;
    }

    /**
     * Process a ZIP file: extract, detect version, prepare template.
     *
     * @return array{ok: bool, message: string, version?: string}
     */
    public function processZip(string $filename): array
    {
        $zipPath = $this->templateDir . '/' . $filename;

        if (!file_exists($zipPath)) {
            return ['ok' => false, 'message' => "ZIP file not found: {$filename}"];
        }

        if (!str_ends_with(strtolower($filename), '.zip')) {
            return ['ok' => false, 'message' => "Not a ZIP file: {$filename}"];
        }

        // Step 1: Extract to temp directory
        $zip = new \ZipArchive();
        $result = $zip->open($zipPath);
        if ($result !== true) {
            return ['ok' => false, 'message' => "Failed to open ZIP: error code {$result}"];
        }

        $tempDir = $this->templateDir . '/_extract_temp_' . uniqid();
        mkdir($tempDir, 0755, true);
        $zip->extractTo($tempDir);
        $zip->close();

        // Step 2: Find the VoxelSite root (may be in a subdirectory)
        $voxelRoot = $this->findVoxelSiteRoot($tempDir);
        if (!$voxelRoot) {
            $this->recursiveDelete($tempDir);
            return ['ok' => false, 'message' => "Extracted ZIP doesn't look like VoxelSite — no index.php found."];
        }

        // Step 3: Read VERSION file from inside the extracted files
        $versionFile = $voxelRoot . '/VERSION';
        if (!file_exists($versionFile)) {
            $this->recursiveDelete($tempDir);
            return ['ok' => false, 'message' => "No VERSION file found inside the ZIP. Cannot determine VoxelSite version."];
        }

        $version = trim(file_get_contents($versionFile));
        if (empty($version) || !preg_match('/^\d+\.\d+\.\d+/', $version)) {
            $this->recursiveDelete($tempDir);
            return ['ok' => false, 'message' => "Invalid VERSION file content: '{$version}'. Expected format like 1.8.0"];
        }

        $versionDir = 'v' . $version;
        $extractTo  = $this->templateDir . '/' . $versionDir;

        // Step 4: Check if this version already exists
        if (is_dir($extractTo) && file_exists($extractTo . '/index.php')) {
            $this->recursiveDelete($tempDir);
            return [
                'ok'      => false,
                'message' => "Version {$versionDir} is already prepared. Delete it first if you want to re-process.",
                'version' => $version,
            ];
        }

        // Step 5: Move to versioned directory
        if (is_dir($extractTo)) {
            $this->recursiveDelete($extractTo);
        }
        rename($voxelRoot, $extractTo);

        // Clean up temp dir
        if ($voxelRoot !== $tempDir && is_dir($tempDir)) {
            $this->recursiveDelete($tempDir);
        }

        // Step 6: Move assets/library/ to centralized library
        $instanceLibrary = $extractTo . '/assets/library';
        $libraryInfo = '';

        if (is_dir($instanceLibrary)) {
            if (!is_dir($this->libraryDir)) {
                mkdir($this->libraryDir, 0755, true);
            }

            $imageCount = 0;
            $skipped = 0;
            $this->copyLibraryImages($instanceLibrary, $this->libraryDir, '', $imageCount, $skipped);
            $this->recursiveDelete($instanceLibrary);
            $libraryInfo = " Copied {$imageCount} images to centralized library (skipped {$skipped} existing).";
        }

        // Step 7: Generate library.json
        $this->generateLibraryJson($extractTo);

        // Step 8: Remove any existing database (template must be clean)
        foreach (['storage/database.sqlite', '_data/database.sqlite'] as $dbPath) {
            $dbFile = $extractTo . '/' . $dbPath;
            if (file_exists($dbFile)) {
                unlink($dbFile);
            }
        }

        // Step 9: Set as active version
        $this->activateVersion($versionDir);

        return [
            'ok'      => true,
            'message' => "VoxelSite {$version} prepared and activated.{$libraryInfo}",
            'version' => $version,
        ];
    }

    /**
     * Activate a specific version for provisioning.
     *
     * @return array{ok: bool, message: string}
     */
    public function activateVersion(string $versionDir): array
    {
        // Allow with or without 'v' prefix
        if (!str_starts_with($versionDir, 'v')) {
            $versionDir = 'v' . $versionDir;
        }

        $versionPath = $this->templateDir . '/' . $versionDir;
        if (!is_dir($versionPath)) {
            return ['ok' => false, 'message' => "Version directory not found: {$versionDir}"];
        }

        $activeLink = $this->templateDir . '/active';
        if (is_link($activeLink) || file_exists($activeLink)) {
            unlink($activeLink);
        }

        symlink($versionDir, $activeLink);

        return ['ok' => true, 'message' => "Active version set to {$versionDir}. New instances will use this version."];
    }

    /**
     * Delete a ZIP file.
     *
     * @return array{ok: bool, message: string}
     */
    public function deleteZip(string $filename): array
    {
        $path = $this->templateDir . '/' . $filename;

        if (!file_exists($path)) {
            return ['ok' => false, 'message' => "File not found: {$filename}"];
        }

        if (!str_ends_with(strtolower($filename), '.zip')) {
            return ['ok' => false, 'message' => "Not a ZIP file: {$filename}"];
        }

        unlink($path);

        return ['ok' => true, 'message' => "Deleted {$filename}"];
    }

    /**
     * Delete a prepared version directory.
     *
     * @return array{ok: bool, message: string}
     */
    public function deleteVersion(string $versionDir): array
    {
        if (!str_starts_with($versionDir, 'v')) {
            $versionDir = 'v' . $versionDir;
        }

        $path = $this->templateDir . '/' . $versionDir;

        if (!is_dir($path)) {
            return ['ok' => false, 'message' => "Version not found: {$versionDir}"];
        }

        // Don't allow deleting the active version
        $activeVersion = $this->getActiveVersion();
        if ($versionDir === $activeVersion) {
            return ['ok' => false, 'message' => "Cannot delete the active version. Activate a different version first."];
        }

        $this->recursiveDelete($path);

        return ['ok' => true, 'message' => "Deleted version {$versionDir}"];
    }

    // ═══════════════════════════════════════
    // Private helper methods
    // ═══════════════════════════════════════

    /**
     * Find the VoxelSite root inside an extracted directory.
     * ZIPs often have a single subdirectory containing the actual files.
     */
    private function findVoxelSiteRoot(string $dir): ?string
    {
        // Check if index.php is directly in the dir
        if (file_exists($dir . '/index.php')) {
            return $dir;
        }

        // Check one level of subdirectories
        $contents = array_diff(scandir($dir), ['.', '..', '__MACOSX', '.DS_Store']);

        foreach ($contents as $item) {
            $subDir = $dir . '/' . $item;
            if (is_dir($subDir) && file_exists($subDir . '/index.php')) {
                return $subDir;
            }
        }

        return null;
    }

    /**
     * Generate library.json manifest.
     */
    private function generateLibraryJson(string $templatePath): void
    {
        $assetsDir = $templatePath . '/assets';
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }

        $baseUrl = Url::control('/library');

        $images = [];
        if (is_dir($this->libraryDir)) {
            $this->scanLibraryDir($this->libraryDir, $this->libraryDir, $images);
        }

        sort($images);

        $manifest = [
            'base_url' => $baseUrl,
            'version'  => date('c'),
            'count'    => count($images),
            'images'   => $images,
        ];

        file_put_contents(
            $assetsDir . '/library.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function scanLibraryDir(string $baseDir, string $currentDir, array &$images): void
    {
        $items = scandir($currentDir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.DS_Store') continue;
            $path = $currentDir . '/' . $item;
            if (is_dir($path)) {
                $this->scanLibraryDir($baseDir, $path, $images);
            } else {
                $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'avif'])) {
                    $images[] = ltrim(str_replace($baseDir, '', $path), '/');
                }
            }
        }
    }

    private function copyLibraryImages(string $source, string $dest, string $subDir, int &$count, int &$skipped): void
    {
        $currentSrc = $subDir ? $source . '/' . $subDir : $source;
        $currentDst = $subDir ? $dest . '/' . $subDir : $dest;

        $items = scandir($currentSrc);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.DS_Store') continue;
            $srcPath = $currentSrc . '/' . $item;
            $dstPath = $currentDst . '/' . $item;
            $relPath = $subDir ? $subDir . '/' . $item : $item;

            if (is_dir($srcPath)) {
                if (!is_dir($dstPath)) mkdir($dstPath, 0755, true);
                $this->copyLibraryImages($source, $dest, $relPath, $count, $skipped);
            } else {
                if (!file_exists($dstPath)) {
                    if (!is_dir(dirname($dstPath))) mkdir(dirname($dstPath), 0755, true);
                    copy($srcPath, $dstPath);
                    $count++;
                } else {
                    $skipped++;
                }
            }
        }
    }

    private function dirSize(string $dir): int
    {
        $size = 0;
        try {
            $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($items as $item) {
                if ($item->isFile()) $size += $item->getSize();
            }
        } catch (\Exception $e) {
            // Directory access issues — return 0
        }
        return $size;
    }

    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) && !is_link($path) ? $this->recursiveDelete($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Format bytes as human-readable.
     */
    public static function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
