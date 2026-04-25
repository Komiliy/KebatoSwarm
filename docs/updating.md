# Updating Ricsian

Ricsian updates ship as a full replacement. No in-app update mechanism — you update via FTP, SSH, or Git pull.

## Via Git (recommended)

```bash
cd /path/to/Ricsian
git pull origin main
php scripts/migrate.php
```

## Via ZIP Download

1. Download the new release from [GitHub](https://github.com/NowSquare/Ricsian/releases)
2. Extract over your existing installation
3. Run migrations:

```bash
php scripts/migrate.php
```

## What Survives an Update

| Preserved | Replaced |
|-----------|----------|
| `.env` | `src/` (application code) |
| `storage/swarm.db` (database) | `views/` (templates) |
| `storage/instances/` (provisioned sites) | `build/` (compiled assets) |
| `storage/logs/` | `migrations/` |
| `library/` (centralized images) | `scripts/` |
| `template/voxelsite/` (your prepared template) | `vendor/` (if using ZIP method) |

## Migration Safety

`php scripts/migrate.php` tracks which migrations have run in a `_migrations` table. It is idempotent — safe to run multiple times. It will only execute new migrations.

## Updating VoxelSite (the template)

Updating Ricsian does **not** update the VoxelSite template or existing instances. To update the template for future instances:

```bash
php scripts/prepare-template.php /path/to/new-voxelsite.zip
```

Existing instances update independently via VoxelSite's own update system.
