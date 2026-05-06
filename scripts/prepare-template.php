<?php

declare(strict_types=1);

/**
 * Ricsian — Template Preparation Script
 *
 * Prepares a VoxelSite ZIP for Swarm deployment:
 *   1. Extracts the VoxelSite ZIP into a versioned directory under template/voxelsite/
 *   2. Copies assets/library/ images into the centralized library/ directory
 *   3. Removes assets/library/ from the extracted template
 *   4. Generates assets/library.json — a manifest pointing to centralized library URLs
 *   5. Creates/updates the "active" symlink to the prepared version
 *
 * Usage:
 *   php scripts/prepare-template.php /path/to/voxelsite-v1.8.0.zip
 *   php scripts/prepare-template.php --regenerate
 *   php scripts/prepare-template.php --list
 *   php scripts/prepare-template.php --activate v1.8.0
 *
 * The template/voxelsite/ directory structure:
 *   template/voxelsite/
 *   ├── voxelsite-v1.8.0.zip          ← Source ZIP(s), kept for reference
 *   ├── v1.8.0/                       ← Extracted + prepared version
 *   │   ├── index.php
 *   │   ├── _studio/
 *   │   ├── assets/
 *   │   │   └── library.json          ← Generated manifest
 *   │   └── ...
 *   └── active -> v1.8.0              ← Symlink to the active version
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Swarm\Helpers\Url;
use Swarm\Models\Setting;

// ── Paths ──
$templateDir = SWARM_ROOT . '/template/voxelsite';
$libraryDir  = SWARM_ROOT . '/library';

// ── CLI output helpers ──
function info(string $msg): void { echo "  \033[0;36m→\033[0m {$msg}\n"; }
function success(string $msg): void { echo "  \033[0;32m✓\033[0m {$msg}\n"; }
function warn(string $msg): void { echo "  \033[0;33m!\033[0m {$msg}\n"; }
function fail(string $msg): void { echo "  \033[0;31m✗\033[0m {$msg}\n"; exit(1); }

echo "\n\033[1mRicsian — Template Preparation\033[0m\n";
echo "────────────────────────────────────────\n\n";

// ── Parse arguments ──
$args = array_slice($argv, 1);

if (empty($args)) {
    echo "Usage:\n";
    echo "  php scripts/prepare-template.php /path/to/voxelsite-v*.zip   — Prepare a new version\n";
    echo "  php scripts/prepare-template.php --regenerate                 — Regenerate library.json for active version\n";
    echo "  php scripts/prepare-template.php --list                      — List available versions\n";
    echo "  php scripts/prepare-template.php --activate v1.8.0           — Switch active version\n";
    echo "\n";
    exit(0);
}

// ── --list: Show available versions ──
if ($args[0] === '--list') {
    $versions = glob($templateDir . '/v*', GLOB_ONLYDIR);
    if (empty($versions)) {
        warn("No prepared versions found.");
    } else {
        $activeLink = is_link($templateDir . '/active') ? readlink($templateDir . '/active') : null;
        foreach ($versions as $path) {
            $name = basename($path);
            $marker = ($name === $activeLink) ? ' \033[0;32m← active\033[0m' : '';
            $versionFile = $path . '/VERSION';
            $version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '?';
            info("{$name} (VoxelSite {$version}){$marker}");
        }
    }
    echo "\n";
    exit(0);
}

// ── --activate: Switch the active symlink ──
if ($args[0] === '--activate') {
    if (empty($args[1])) {
        fail("Usage: --activate <version>  (e.g. --activate v1.8.0)");
    }
    $version = $args[1];
    // Allow with or without 'v' prefix
    if (!str_starts_with($version, 'v')) {
        $version = 'v' . $version;
    }
    $versionPath = $templateDir . '/' . $version;
    if (!is_dir($versionPath)) {
        fail("Version directory not found: {$versionPath}");
    }
    $activeLink = $templateDir . '/active';
    if (is_link($activeLink) || file_exists($activeLink)) {
        unlink($activeLink);
    }
    symlink($version, $activeLink);
    success("Active version set to: {$version}");
    info("New instances will use this version.");
    echo "\n";
    exit(0);
}

// ── --regenerate: Regenerate library.json for the active version ──
if ($args[0] === '--regenerate') {
    $activePath = $templateDir . '/active';
    if (!is_link($activePath) || !is_dir($activePath)) {
        fail("No active version set. Run prepare-template.php with a ZIP first, or use --activate.");
    }
    info("Regenerating library.json for active version...");
    generateLibraryJson(realpath($activePath), $libraryDir);
    success("library.json regenerated.");
    echo "\n";
    exit(0);
}

// ── Main flow: Prepare a ZIP ──
$zipPath = $args[0];

if (!file_exists($zipPath)) {
    // Check if it's a filename in the template directory
    $localPath = $templateDir . '/' . $zipPath;
    if (file_exists($localPath)) {
        $zipPath = $localPath;
    } else {
        fail("ZIP file not found: {$zipPath}");
    }
}

if (!str_ends_with(strtolower($zipPath), '.zip')) {
    fail("Expected a .zip file, got: {$zipPath}");
}

// ── Step 1: Extract ZIP to temp directory ──
$zipFilename = basename($zipPath);
info("ZIP: {$zipPath}");
info("Extracting...");

$zip = new ZipArchive();
$result = $zip->open($zipPath);
if ($result !== true) {
    fail("Failed to open ZIP: error code {$result}");
}

// Extract to a temp dir first (ZIPs often have a subdirectory inside)
$tempDir = $templateDir . '/_extract_temp_' . uniqid();
mkdir($tempDir, 0755, true);
$zip->extractTo($tempDir);
$zip->close();

// Find the actual VoxelSite root (may be in a subdirectory)
$contents = array_diff(scandir($tempDir), ['.', '..', '__MACOSX', '.DS_Store']);
$voxelRoot = $tempDir;

if (count($contents) === 1) {
    $singleDir = $tempDir . '/' . reset($contents);
    if (is_dir($singleDir) && file_exists($singleDir . '/index.php')) {
        $voxelRoot = $singleDir;
    }
}

if (!file_exists($voxelRoot . '/index.php')) {
    recursiveDelete($tempDir);
    fail("Extracted ZIP doesn't look like VoxelSite — no index.php found.");
}

// ── Step 2: Detect version from VERSION file inside the extracted ZIP ──
$versionFile = $voxelRoot . '/VERSION';
if (file_exists($versionFile)) {
    $versionTag = trim(file_get_contents($versionFile));
    info("Detected version from VERSION file: {$versionTag}");
} else {
    // Fallback: try to detect from filename
    preg_match('/v?(\d+\.\d+\.\d+)/', $zipFilename, $matches);
    $versionTag = $matches[1] ?? null;
    if ($versionTag) {
        warn("No VERSION file found in ZIP. Detected version from filename: {$versionTag}");
    } else {
        recursiveDelete($tempDir);
        fail("Cannot detect version. No VERSION file inside the ZIP and filename '{$zipFilename}' doesn't contain a version number.");
    }
}

if (!preg_match('/^\d+\.\d+\.\d+/', $versionTag)) {
    recursiveDelete($tempDir);
    fail("Invalid version format: '{$versionTag}'. Expected format like 1.8.0");
}

$versionDir = "v{$versionTag}";
$extractTo  = $templateDir . '/' . $versionDir;

info("Version: {$versionDir}");
info("Target: {$extractTo}");

// ── Step 3: Check if already prepared ──
if (is_dir($extractTo) && file_exists($extractTo . '/index.php')) {
    warn("Version {$versionDir} already exists. Removing for fresh extraction...");
    recursiveDelete($extractTo);
}

// Move to the version directory
rename($voxelRoot, $extractTo);

// Clean up temp dir if it's different
if ($voxelRoot !== $tempDir && is_dir($tempDir)) {
    recursiveDelete($tempDir);
}

$actualVersion = $versionTag;
success("Extracted VoxelSite {$actualVersion} → {$versionDir}/");

// ── Step 4: Move assets/library/ to centralized library ──
$instanceLibrary = $extractTo . '/assets/library';

if (is_dir($instanceLibrary)) {
    if (!is_dir($libraryDir)) {
        mkdir($libraryDir, 0755, true);
    }

    $imageCount = 0;
    $skipped    = 0;
    copyLibraryImages($instanceLibrary, $libraryDir, '', $imageCount, $skipped);

    info("Copied {$imageCount} images to centralized library/ (skipped {$skipped} existing)");

    // Remove assets/library/ from the template
    recursiveDelete($instanceLibrary);
    success("Removed assets/library/ from template (saves ~" . formatSize(dirSize($libraryDir)) . " per instance)");
} else {
    warn("No assets/library/ directory found in this version — skipped library extraction.");
}

// ── Step 5: Generate library.json ──
generateLibraryJson($extractTo, $libraryDir);
success("Generated assets/library.json manifest");

// ── Step 6: Ensure no storage/database.sqlite (clean install) ──
$dbFile = $extractTo . '/storage/database.sqlite';
if (file_exists($dbFile)) {
    unlink($dbFile);
    info("Removed existing database.sqlite (template must be a fresh install)");
}
$dbFile2 = $extractTo . '/_data/database.sqlite';
if (file_exists($dbFile2)) {
    unlink($dbFile2);
    info("Removed existing _data/database.sqlite (template must be a fresh install)");
}

// ── Step 7: Set as active version ──
$activeLink = $templateDir . '/active';
if (is_link($activeLink) || file_exists($activeLink)) {
    unlink($activeLink);
}
symlink($versionDir, $activeLink);
success("Active version set to: {$versionDir}");

// ── Done ──
echo "\n\033[1m✓ Template prepared successfully.\033[0m\n";
echo "  New instances will use VoxelSite {$actualVersion}.\n";
echo "  Centralized library: " . (is_dir($libraryDir) ? countImages($libraryDir) . " images" : "empty") . "\n";
echo "\n";


// ═══════════════════════════════════════════
// Helper functions
// ═══════════════════════════════════════════

/**
 * Generate library.json manifest from the centralized library directory.
 */
function generateLibraryJson(string $templatePath, string $libraryDir): void
{
    $assetsDir = $templatePath . '/assets';
    if (!is_dir($assetsDir)) {
        mkdir($assetsDir, 0755, true);
    }

    $baseUrl = Url::control('/library');

    $images = [];
    if (is_dir($libraryDir)) {
        scanLibraryDir($libraryDir, $libraryDir, $images);
    }

    sort($images);

    $manifest = [
        'base_url' => $baseUrl,
        'version'  => date('c'),
        'count'    => count($images),
        'images'   => $images,
    ];

    $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($assetsDir . '/library.json', $json);
}

/**
 * Recursively scan the library directory and collect relative image paths.
 */
function scanLibraryDir(string $baseDir, string $currentDir, array &$images): void
{
    $items = scandir($currentDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.DS_Store') {
            continue;
        }

        $path = $currentDir . '/' . $item;
        if (is_dir($path)) {
            scanLibraryDir($baseDir, $path, $images);
        } else {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'avif'])) {
                $relativePath = ltrim(str_replace($baseDir, '', $path), '/');
                $images[] = $relativePath;
            }
        }
    }
}

/**
 * Copy library images, preserving subdirectory structure.
 * Skips files that already exist in the destination.
 */
function copyLibraryImages(string $source, string $dest, string $subDir, int &$count, int &$skipped): void
{
    $currentSrc = $subDir ? $source . '/' . $subDir : $source;
    $currentDst = $subDir ? $dest . '/' . $subDir : $dest;

    $items = scandir($currentSrc);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.DS_Store') {
            continue;
        }

        $srcPath = $currentSrc . '/' . $item;
        $dstPath = $currentDst . '/' . $item;
        $relPath = $subDir ? $subDir . '/' . $item : $item;

        if (is_dir($srcPath)) {
            if (!is_dir($dstPath)) {
                mkdir($dstPath, 0755, true);
            }
            copyLibraryImages($source, $dest, $relPath, $count, $skipped);
        } else {
            if (!file_exists($dstPath)) {
                if (!is_dir(dirname($dstPath))) {
                    mkdir(dirname($dstPath), 0755, true);
                }
                copy($srcPath, $dstPath);
                $count++;
            } else {
                $skipped++;
            }
        }
    }
}

/**
 * Count images in a directory recursively.
 */
function countImages(string $dir): int
{
    $count = 0;
    $items = [];
    scanLibraryDir($dir, $dir, $items);
    return count($items);
}

/**
 * Get directory size recursively.
 */
function dirSize(string $dir): int
{
    $size = 0;
    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($items as $item) {
        if ($item->isFile()) {
            $size += $item->getSize();
        }
    }
    return $size;
}

/**
 * Format bytes as human-readable.
 */
function formatSize(int $bytes): string
{
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

/**
 * Delete a directory recursively.
 */
function recursiveDelete(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        is_dir($path) ? recursiveDelete($path) : unlink($path);
    }
    rmdir($dir);
}
