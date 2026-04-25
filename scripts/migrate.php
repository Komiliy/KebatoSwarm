#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Ricsian — Migration Runner
 *
 * Runs all pending SQL migrations from the migrations/ directory.
 * Tracks executed migrations in a _migrations table. Idempotent.
 *
 * Usage: php scripts/migrate.php
 */

// Bootstrap (defines SWARM_ROOT, SWARM_STORAGE, etc.)
require_once __DIR__ . '/../src/bootstrap.php';

echo "Ricsian — Migration Runner\n";
echo str_repeat('─', 40) . "\n\n";

$migrationsDir = SWARM_ROOT . '/migrations';

if (!is_dir($migrationsDir)) {
    echo "❌ Migrations directory not found: {$migrationsDir}\n";
    exit(1);
}

try {
    $ran = \Swarm\Database::migrate($migrationsDir);

    if (empty($ran)) {
        echo "✓ Database is up to date. No pending migrations.\n";
    } else {
        foreach ($ran as $file) {
            echo "  ✓ {$file}\n";
        }
        echo "\n✓ Ran " . count($ran) . " migration(s).\n";
    }
} catch (\Throwable $e) {
    echo "❌ Migration failed: {$e->getMessage()}\n";
    exit(1);
}

echo "\nDone.\n";
