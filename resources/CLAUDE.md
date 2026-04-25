# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A contribution environment for the **Aropixel open source bundles** — a CMS admin suite for Symfony. The goal is to develop and test changes to these bundles:

- **AdminBundle** (`admin-bundle/`) — Core bundle: admin UI, user management, CRUD generation. BlogBundle, PageBundle, and MenuBundle are dependencies of AdminBundle.
- **BlogBundle** (`blog-bundle/`) — Blog/news content management
- **PageBundle** (`page-bundle/`) — Page/subpage management
- **MenuBundle** (`menu-bundle/`) — Navigation menu management

The `application/` directory is a **Symfony sandbox** used exclusively to test bundle modifications. It is not a deliverable — its sole purpose is to provide a working Symfony app where all four bundles are installed and exercisable.

The sandbox uses Docker-first infrastructure via the JoliCode Docker Starter (v4) with **Castor** as the task runner.

## Commands

All commands use **Castor** (PHP CLI task runner). The infrastructure runs inside Docker containers.

### Development Lifecycle

```bash
castor start          # Build images, install deps, start services, migrate DB
castor up             # Start containers (after initial setup)
castor about          # Show available URLs (app at admin-bundle.local)
```

### Quality Assurance

```bash
castor qa:all             # Run all QA checks
castor qa:cs              # Fix coding standards (PHP-CS-Fixer)
castor qa:cs --dry-run    # Check only, don't fix
castor qa:phpstan         # Static analysis at level 8
castor qa:twig-cs         # Twig template linting
```

### Tests

```bash
# Run all tests
castor builder bin/phpunit

# Run a single test file
castor builder bin/phpunit tests/Controller/YourTest.php

# Run by filter
castor builder bin/phpunit --filter TestMethodName
```

PHPUnit config: `application/phpunit.dist.xml`. Tests live in `application/tests/`.

### Symfony Console

```bash
castor builder bin/console [command]
```

## Architecture

### Monorepo Bundle Structure

Each bundle is a standalone Composer package published as an open source library. The `application/composer.json` requires them via local path repositories so that changes to bundle code are immediately reflected in the sandbox without reinstalling.

The dependency graph between bundles: **AdminBundle ← BlogBundle, PageBundle, MenuBundle** (the three sub-bundles depend on AdminBundle, not the other way around).

### Bundle Internal Layout

Each bundle follows standard Symfony bundle structure under `src/`:
- `Entity/` — Doctrine entities
- `Repository/` — Data repositories
- `Form/` — Symfony form types
- `Resources/config/` — Routes and service definitions

### Application Integration

- `application/config/bundles.php` — Registers all four Aropixel bundles
- `application/config/services.yaml` — DI container configuration
- `application/config/routes.yaml` — Application routes (imports bundle routes)
- `application/config/packages/` — Package-specific configs

### Internationalisation

All four bundles support multilingual content — they can be switched to multilingue mode. This is a first-class feature of the suite, not an afterthought: entities, routes, and templates are designed to accommodate i18n from the ground up.

### Key Technology Choices

| Concern | Solution |
|---|---|
| ORM | Doctrine 3.6 |
| Database | PostgreSQL 16 |
| Frontend | Stimulus + Turbo, AssetMapper/ImportMap |
| Image processing | LiipImagineBundle |
| File storage | FlysystemBundle |
| Doctrine extensions | StofDoctrineExtensionsBundle |

### QA Tooling

QA tools are installed in isolation under `tools/` (each with its own `composer.json`):
- `tools/php-cs-fixer/` — PHP-CS-Fixer (rules in `.php-cs-fixer.php`, targets PHP 8.3+, Symfony preset, risky rules enabled)
- `tools/phpstan/` — PHPStan (config in `phpstan.neon`, level 8)
- `tools/twig-cs-fixer/` — Twig linting

### CI/CD

GitHub Actions (`.github/workflows/ci.yml`) runs the full QA suite and tests on PHP 8.3, 8.4, and 8.5 in a matrix build.
