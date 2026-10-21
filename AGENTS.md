# AGENTS.md

## Scope

These instructions apply to the entire repository. Follow the user's task first, then preserve the contracts documented here. Keep changes small, reviewable, and compatible with the current no-framework architecture.

## Project Overview

Digit is a Persian RTL full-stack e-commerce portfolio application. Static HTML/CSS/JavaScript provides the storefront; public PHP scripts provide a JSON API; a server-rendered PHP admin panel manages catalog data, orders, and contact messages; MySQL is the system of record.

There is no Composer, npm, framework, router, template engine, or build step. PHP files use `declare(strict_types=1)`. The internal PHP namespace is `Nova` and the browser API is `window.NovaStore`; retain these compatibility identifiers unless a task explicitly includes a coordinated migration.

## Repository Map

- Root `*.html`: public storefront pages.
- `assets/css/`: shared storefront and page styles.
- `assets/js/catalog.js`: API client, product normalization, cart state, and shared rendering helpers.
- `assets/js/main.js`: shared header/footer and home-page interactions.
- `assets/js/pages.js`: shop, product, cart, checkout, and contact controllers.
- `assets/images/products/uploads/`: runtime admin uploads; only `.gitkeep` and the protective `.htaccess` belong in Git.
- `api/`: thin public endpoint scripts. Keep validation and business rules out of endpoint files.
- `admin/`: authenticated server-rendered panel and its assets.
- `backend/bootstrap.php`: `Nova` autoloader, environment loading, and API error handling.
- `backend/src/Http/`: request parsing and response envelopes.
- `backend/src/Service/`: validation, authentication, and upload rules.
- `backend/src/Repository/`: prepared SQL, transactions, and persistence mapping.
- `backend/src/Security/`: admin sessions and CSRF.
- `database/schema.sql`: complete schema for new installs.
- `database/seed.sql`: repeatable initial catalog data.
- `database/migrations/`: ordered, manually tracked upgrades for older databases.
- `.env.example`: placeholder-only configuration reference.
- `deploy/` and `dist/`: ignored local artifacts; never treat them as source.

## Coding Conventions

- Preserve the PHP 8.1+, vanilla JavaScript, and plain CSS stack unless explicitly asked to change it.
- Use strict PHP types, typed signatures, `final` classes where the existing design does, and the existing `Nova` path convention.
- Keep endpoints thin: parse through `Request`, place business rules in services, and database work in repositories.
- Use PDO prepared statements for all values. Never concatenate untrusted input into SQL. Keep allowlisted SQL fragments explicit.
- Return API data through `ApiResponse`; preserve the `success/data/meta` and `success/error` envelopes and localized public messages.
- Keep user-facing storefront/admin text in Persian and layouts RTL. Write repository documentation and code-level explanations in English unless the surrounding user-facing context requires Persian.
- Escape server-rendered output with `admin_e()` and browser-generated markup with `NovaStore.escapeHtml()`.
- Keep asset and page URLs relative so local and subdirectory deployments remain possible.
- Do not introduce a package manager or generated build output for work that fits the existing stack.
- The MIT License covers only first-party material the copyright holder may license. Do not assume third-party images, fonts, trademarks, or product content are MIT-licensed.

## Compatibility Rules

- Preserve product/category query parameters, API endpoint paths, response shapes, and HTTP status behavior.
- Preserve the `nova_demo_cart_v2` local-storage key and order-attempt session-storage keys unless migration behavior is provided.
- Product prices and availability must remain server-authoritative during checkout.
- Order submission must remain transactional and idempotent; do not remove row locking or the unique idempotency key.
- Admin product deactivation is soft state (`is_active`), not deletion.
- Preserve both WebP and AVIF product paths and the upload-directory protections.
- Any schema change must update the current fresh-install schema and add an ordered migration for existing databases.

## Security Requirements

- Never commit `.env` files, credentials, setup tokens, API keys, private keys, production exports, logs, backups, or deployment archives.
- `APP_KEY` and `ADMIN_SETUP_TOKEN` must be independent random values of at least 32 characters. Clear the setup token after initial production setup.
- Keep secrets in `backend/.env`, a file selected by `NOVA_ENV_FILE`, or the private production directory. Do not expose `backend/` from the public document root in production.
- Retain same-origin enforcement for public POST endpoints and CSRF validation for every state-changing admin action.
- Retain secure session settings, timeout/regeneration behavior, login throttling, password hashing, and generic production errors.
- Validate uploads by upload status, size, detected MIME, decoded image type, dimensions, destination path, and randomized filename. Never trust an extension or client-provided MIME type.
- Do not log secrets or return exception details to clients.

## Database Changes

- `database/schema.sql` must describe the latest complete schema for a fresh installation.
- Add upgrades to `database/migrations/` using the next numeric prefix and a descriptive suffix.
- Migrations are manually tracked; make them safe and document whether they can be rerun. Never assume a migration ledger exists.
- Do not edit previously deployed migrations merely to represent a new change; add a migration instead.
- Keep InnoDB, `utf8mb4`, foreign keys, indexes, and UTC-oriented timestamps consistent with the existing schema.
- Update `database/seed.sql` only when a task explicitly requires initial catalog changes. Preserve its repeatable upsert behavior.

## Testing and Quality Gates

There is no automated test suite or CI workflow. Match verification to the change and report exactly what was run.

At minimum for PHP changes, run syntax checks when PHP is available:

```powershell
Get-ChildItem admin,api,backend -Recurse -Filter *.php |
  ForEach-Object { php -l $_.FullName }
```

For database changes, test a fresh schema import and the upgrade path on disposable databases. For frontend/API changes, run `php -S 127.0.0.1:8000` and manually exercise affected pages and endpoints. Check responsive layout, browser console errors, validation failures, authentication/CSRF behavior, and failure states—not only the happy path.

Never claim tests, browser checks, production verification, or migration execution that did not occur.

## Deployment Contract

Production uses a public/private split:

- Public document root: root HTML pages, `assets/`, `api/`, and `admin/`.
- Private sibling `nova-private/`: the **contents** of `backend/`, including `bootstrap.php`, `src/`, and `.env`.
- Never deploy `.git/`, `database/`, `.env.example`, `deploy/`, `dist/`, `assets/images/reference.png`, or repository documentation into the public document root.
- Set `PUBLIC_ROOT` to the absolute public document root so admin uploads reach the expected directory.
- Remove deployment archives after extraction and ensure runtime uploads are writable but cannot execute scripts.

The API/admin bootstraps support `backend/bootstrap.php` only as a local-development fallback. Do not weaken the preferred private production layout.

## Agent Workflow

1. Read this file, `README.md`, and the relevant implementation before planning a change.
2. Inspect `git status` and preserve unrelated user changes; never reset, stage, commit, push, deploy, or rewrite history without explicit authorization.
3. Search for all consumers before changing an endpoint, schema field, storage key, CSS contract, or shared helper.
4. Implement the smallest coherent change within the existing layer boundaries.
5. Update documentation, `.env.example`, schema, seed, or migrations only when the implementation contract changes.
6. Run available checks and state any unavailable tooling or untested behavior.
7. Review the final diff for secrets, generated files, accidental data changes, and public/private deployment mistakes.

When requirements are ambiguous, preserve current behavior and ask before making a destructive, security-sensitive, or compatibility-breaking choice.
