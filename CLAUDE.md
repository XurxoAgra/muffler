# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

Symfony 7.4 JSON API on PHP 8.4 (containers) / 8.2+ (composer floor), Doctrine ORM 3, PostgreSQL, JWT auth via LexikJWT + refresh tokens, OpenAPI docs via NelmioApiDoc. No frontend: no `package.json`, no templates, no assets.

## Commands

**Nothing runs on the host.** Every command executes inside the `muffler-api-webserver` container. Always use the Makefile target rather than calling `php`, `composer`, or `docker` directly — the compose targets need `--env-file ./docker/develop/.env`, which is easy to forget.

| Task | Command |
|---|---|
| Start containers + install deps | `make init` |
| Start / stop / teardown | `make start` / `make stop` / `make down` |
| Composer install | `make install` |
| Shell in container | `make bash` |
| Create dev+test DBs | `make create-db` |
| Run migrations (dev+test) | `make migrate` |
| Generate migration from entity diff | `make migration/diff` |
| Fix code style | `make style/fix` |
| Check style without writing | `make style/code-style` |
| Static analysis (phpstan level 5) | `make style/static-analysis` |
| Both of the above | `make style/all` |
| Clear cache (dev+test) | `make clear` |

Run `make style/all` before committing — no CI or git hook enforces it.

Only application console command: `php bin/console app:import-vehicle-catalog` (defaults to `/migrations/import/make_models_data.json`).

**Stale Makefile targets — do not use, they reference tooling this project does not have:** `test/all`, `test/unit`, `test/integration`, `test/functional`, `rbac*`, `rebuild-db` (depends on `rbac`), `queue/*`, `consume/ticketing`, `init/websocket`, `uuid`, `clear/pool/mail`.

## Architecture

DDD + hexagonal, four bounded contexts (`Auth`, `Vehicle`, `Maintenance`, `Shared`). The full ruleset lives in `.claude/rules/architecture.md` and is loaded automatically when working under `src/` — follow it; it is authoritative over general PHP habits.

Two things that autowiring will not do for you:

- **New port (interface)?** Add an explicit `Port: '@Adapter'` line under `services:` in `config/services.yaml`. `src/**/Domain/` and `src/**/Http/Request/*Request.php` are excluded from the autowire resource, and Symfony does not resolve interfaces on its own.
- **New entity?** `auto_mapping: false` in `config/packages/doctrine.yaml`. Write a Doctrine **XML** mapping file (never `#[ORM\...]` attributes) and, if it lands outside the four existing mapping dirs, add a `mappings:` entry too.

## Environment setup

A fresh clone does not boot. Two required files are gitignored and must be recreated — see the `/bootstrap` skill for the full sequence:

- `docker/develop/.env` — copy from `.env.example`; sets `CONTAINER_SUFFIX`, `USER_ID`/`GROUP_ID`, ports, `POSTGRES_*`.
- `.env.local` — must define `DATABASE_URL` (absent from `.env`) plus `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY`, `JWT_PASSPHRASE`.
- JWT keypair: `bin/console lexik:jwt:generate-keypair` (no make target).
- `docker compose` reads `~/.ssh/id_rsa` as a secret; the entrypoint also generates an OAuth keypair into `var/oauth/`.

`.env` is listed in `.gitignore` but was committed before that rule, so it stays tracked and its edits always show as modifications. This is expected.

## Code style

- php-cs-fixer with the `@Symfony` ruleset (`.php-cs-fixer.dist.php`), phpstan level 5 on `src` (`phpstan.neon.dist`).
- `declare(strict_types=1)` in every file; classes are `final readonly` by default with plain `private` promoted constructor properties.
- 4-space indent, LF (`.editorconfig`).
- **English everywhere** — identifiers, comments, commit messages, and user-facing strings. Some existing strings are Spanish; treat them as legacy and write new ones in English.

## Tests

There is no test suite yet — PHPUnit is not installed and there is no `tests/` directory (though `composer.json` already maps `App\Tests\` to `tests/`). PHPUnit is planned. Do not scaffold a test framework or write test files unless asked.

## Git

Branch off `develop` as `feature/<name>`, open a PR into `develop`. `main` is the release branch.

## Known stubs

- `App\Maintenance\Application\Port\OcrServiceInterface` is bound to `NullOcrService` — invoice OCR is not implemented.
- `/api/auth/login` and `/api/auth/refresh` point at `UnreachableController` on purpose; the security firewall intercepts before routing.
