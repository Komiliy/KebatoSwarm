# Contributing to Ricsian

Thank you for considering contributing to Ricsian. Every hosting environment is different — your testing, bug reports, and adapter contributions make Ricsian better for everyone.

If you are testing Ricsian on a real environment, read [docs/testing-feedback.md](docs/testing-feedback.md) first. It explains the early-access model, the logging strategy, and what to include when reporting failures.

## How to Contribute

### Reporting Bugs

1. **Search existing issues** first — your problem may already be reported
2. **Open a new issue** with:
   - Your hosting environment (OS, control panel, PHP version)
   - Steps to reproduce the issue
   - Expected vs actual behavior
   - Relevant log entries from `storage/logs/`
3. **Label it** appropriately: `bug`, `adapter:cpanel`, `adapter:plesk`, etc.

### Writing an Adapter

The most impactful contribution you can make. See [docs/adapters/writing-an-adapter.md](docs/adapters/writing-an-adapter.md) for the full guide.

Quick overview:
1. Create `src/Adapters/YourPanelAdapter.php` implementing the `ControlPanelAdapter` interface
2. Register it in `AdapterFactory.php`
3. Add config fields to the deployment view
4. Write documentation in `docs/adapters/yourpanel.md`
5. Submit a PR

### Submitting Pull Requests

1. Fork the repository
2. Create a feature branch: `git checkout -b adapter/directadmin`
3. Make your changes
4. Test on your hosting environment
5. Submit a PR with:
   - Description of what the change does
   - Hosting environment tested on
   - Screenshots if relevant (especially for UI changes)

### Code Style

- **PHP 8.2+** features are welcome (match expressions, named arguments, readonly properties, enums)
- No frameworks — keep it plain PHP
- Follow the existing code patterns in `src/`
- Use `declare(strict_types=1)` in all PHP files
- PSR-4 autoloading under the `Swarm` namespace

### What We Will Not Accept

- Framework dependencies (Laravel, Symfony framework, etc.)
- npm runtime dependencies on the server (Vite/Tailwind are dev-only)
- External service dependencies beyond control panel APIs
- Billing or payment integration (out of scope for the open-source project)
- Changes that break the "upload, configure, run" deployment model

## Development Setup

```bash
git clone https://github.com/NowSquare/Ricsian.git
cd Ricsian

# Dependencies are already included — no composer install needed
php scripts/migrate.php
php scripts/install.php
```

**If modifying CSS or JavaScript** (contributing to the UI):

```bash
npm install          # Only needed for UI development
npm run dev          # Vite watch mode for Tailwind + Alpine.js
```

> Note: `npm install` and `npm run dev` are **not required** for running Ricsian. The `build/` directory already contains pre-compiled CSS and JS. Only use this if you're modifying the UI.

Use [Laravel Herd](https://herd.laravel.com/) or [Laravel Valet](https://laravel.com/docs/valet) for macOS local development with the `local` adapter. Both can serve plain PHP projects; Laravel is not required.

## Questions?

Open an [issue](https://github.com/NowSquare/Ricsian/issues) for questions or issues.
