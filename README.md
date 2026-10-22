# Digit

Digit is a full-stack, Persian right-to-left e-commerce portfolio project built to demonstrate practical web design and development skills to prospective clients and employers. It combines a polished storefront, a JSON API, persistent catalog and order data, customer-contact workflows, and a protected administration panel without a framework or build pipeline.

**Live application:** [https://digit-store.ir/](https://digit-store.ir/)

The deployed application is operational as a portfolio system. It is not presented as an active retail business.

## Project Scope

Digit implements product discovery, a browser-based cart, server-validated order intake, contact and newsletter submissions, and administrative catalog/order management. Payment processing, customer accounts, shipping and tax calculation, fulfillment, email delivery, and automatic inventory deduction are outside the current scope.

## Features

### Storefront

- Responsive Persian RTL interface for desktop, tablet, and mobile
- Home-page sections, product collections, promotional content, hero controls, and countdown UI
- API-backed catalog, category filtering, search, sorting, and product details
- Product specifications, related products, and stock-state handling
- Browser-side cart stored in `localStorage`, with quantity limits and removal
- Checkout that persists orders in MySQL
- Idempotent order submission to prevent duplicates after retries
- Contact and newsletter forms persisted in MySQL
- Local WebP and AVIF assets with no external runtime asset dependency

### Administration

- One-time first-administrator setup protected by a setup token
- Session-based login with password hashing and failed-login throttling
- Dashboard counts and recent-order overview
- Product creation and editing, specifications, image uploads, and soft activation/deactivation
- Category creation and editing
- Order search, filtering, details, status updates, and status history
- Contact-message search, filtering, details, and read/unread state

## Technology Stack

| Layer | Technology |
| --- | --- |
| Storefront | Semantic HTML5, CSS3, vanilla JavaScript, Persian RTL layout |
| API and admin | PHP 8.1+ with strict types and no framework |
| Data access | PDO with prepared statements |
| Database | MySQL or MariaDB using InnoDB and `utf8mb4` |
| Authentication | PHP sessions and `password_hash()` / `password_verify()` |
| Assets | Local CSS, JavaScript, Vazirmatn WOFF2, WebP, and AVIF |
| Tooling | No Composer, npm, transpiler, or build step |

The legacy internal identifiers `Nova` (PHP namespace) and `window.NovaStore` (browser API) are retained for compatibility; the product name is Digit.

## Architecture

The application uses a small layered architecture:

1. Static HTML pages load shared layout and page behavior from `assets/js/`.
2. Browser code calls endpoint scripts in `api/` and expects a consistent JSON envelope.
3. Endpoint scripts bootstrap the private runtime, validate HTTP requests, and delegate to services and repositories.
4. Services implement validation and security-sensitive business rules.
5. Repositories use PDO prepared statements and transactions to access MySQL.
6. The `admin/` application renders server-side PHP pages and uses the same private layers.

```text
Digit/
├── admin/                  Server-rendered administration panel
│   └── assets/             Admin-only CSS and JavaScript
├── api/                    Public JSON endpoint scripts
├── assets/
│   ├── css/                Storefront styles
│   ├── fonts/              Local Vazirmatn font
│   ├── images/             Promotional and product images
│   └── js/                 API client, shared UI, and page controllers
├── backend/
│   ├── bootstrap.php       Autoloader, environment loading, error handling
│   └── src/
│       ├── Config/         Environment reader
│       ├── Database/       PDO connection factory
│       ├── Exception/      API and admin validation exceptions
│       ├── Http/           Request parsing and JSON responses
│       ├── Repository/     Database queries and transactions
│       ├── Security/       Admin sessions and CSRF tokens
│       └── Service/        Validation, authentication, and uploads
├── database/
│   ├── schema.sql          Complete schema for a fresh installation
│   ├── seed.sql            Repeatable initial catalog data
│   └── migrations/         Manual upgrades for older databases
├── *.html                  Public storefront pages
├── .env.example            Environment variable template
└── AGENTS.md               Guidance for maintainers and coding agents
```

## Requirements

- PHP 8.1 or newer
- MySQL 8+ or a compatible MariaDB release
- PHP extensions: `pdo_mysql`, `mbstring`, and `fileinfo`
- A PHP build capable of inspecting WebP and AVIF images if admin uploads are used
- A modern browser

## Local Setup

1. Create a database with `utf8mb4` support:

   ```sql
   CREATE DATABASE digit_store
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;
   ```

2. Import the current schema, then the initial product catalog:

   ```bash
   mysql -u root -p digit_store < database/schema.sql
   mysql -u root -p digit_store < database/seed.sql
   ```

3. Create the local private environment file:

   ```bash
   cp .env.example backend/.env
   ```

   PowerShell equivalent:

   ```powershell
   Copy-Item .env.example backend/.env
   ```

4. Edit `backend/.env`. Set the database credentials and generate independent random values of at least 32 characters for `APP_KEY` and `ADMIN_SETUP_TOKEN`. `PUBLIC_ROOT` may be empty locally because the application can resolve the repository root from `backend/`.

5. Start PHP's development server from the repository root:

   ```bash
   php -S 127.0.0.1:8000
   ```

6. Open [http://127.0.0.1:8000/](http://127.0.0.1:8000/). To create the first administrator, visit [http://127.0.0.1:8000/admin/setup.php](http://127.0.0.1:8000/admin/setup.php) and use the configured setup token.

The application may alternatively load an environment file from the absolute path in `NOVA_ENV_FILE`. Without that process variable, it looks for `backend/.env`.

## Environment Configuration

| Variable | Required | Purpose |
| --- | --- | --- |
| `APP_KEY` | Yes | HMAC key for privacy-preserving client and login identifiers; minimum 32 characters |
| `ADMIN_SETUP_TOKEN` | For initial setup | Authorizes the first administrator; minimum 32 characters |
| `DB_HOST` | Yes | MySQL host |
| `DB_PORT` | No | MySQL port; defaults to `3306` |
| `DB_NAME` | Yes | Database name; letters, digits, and underscores only |
| `DB_USER` | Yes | Database user |
| `DB_PASS` | Yes | Database password |
| `DB_CHARSET` | No | `utf8mb4` by default; `utf8` is also accepted |
| `PUBLIC_ROOT` | Production/uploads | Absolute public document root used for product image uploads |
| `NOVA_ENV_FILE` | No | Process variable pointing to an alternate environment file |

`APP_ENV` and `APP_DEBUG` are present in the template for deployment convention, but the current runtime does not branch on either value. Never commit a populated `.env` file.

## Database Lifecycle

`database/schema.sql` is the source of truth for a fresh installation and already contains the current schema. `database/seed.sql` supplies the project's initial categories, products, specifications, prices, and image paths through repeatable upserts.

Files in `database/migrations/` are for databases created before later features were added. They are manual, ordered upgrades and there is no migration ledger. Apply only migrations that have not already been applied:

- `002_admin_panel.sql` adds admin support, product activation, and order status history.
- `003_contact_newsletter.sql` adds contact messages and newsletter subscribers.

Do not run `002_admin_panel.sql` against the current fresh schema because its `ALTER TABLE` would attempt to add an existing column.

## HTTP API

Successful and failed responses use these envelopes:

```json
{"success":true,"data":{},"meta":{}}
```

```json
{"success":false,"error":{"code":"error_code","message":"Localized message"}}
```

| Method | Endpoint | Behavior |
| --- | --- | --- |
| `GET` | `/api/categories.php` | All categories with active-product counts |
| `GET` | `/api/products.php` | Paginated active products |
| `GET` | `/api/product.php?id=1` | One active product with specifications |
| `POST` | `/api/orders.php` | Validate and persist an order |
| `POST` | `/api/contact.php` | Validate and persist a contact message |
| `POST` | `/api/newsletter.php` | Create or reactivate a subscription |

`GET /api/products.php` accepts `q`, `category`, `sort`, `page`, and `limit`. Valid sort values are `default`, `price-asc`, `price-desc`, `name`, and `newest`; `limit` is restricted to 1–100.

POST endpoints require `Content-Type: application/json` and reject cross-site browser requests. Order creation additionally requires an `Idempotency-Key` header containing a valid UUID. The server reloads product prices and availability from the database inside a transaction; client totals are never trusted.

The browser API base defaults to the relative `api` directory. A different deployment path can set `window.NOVA_CONFIG = { apiBase: '/path/to/api' }` before loading `assets/js/catalog.js`.

## Administration

The panel is available at `/admin/`. On an empty `admins` table, `/admin/setup.php` accepts `ADMIN_SETUP_TOKEN` and creates exactly one initial administrator under a database lock. After the first administrator exists, the setup route redirects to login.

For production, clear `ADMIN_SETUP_TOKEN` after initial setup. Admin product uploads accept WebP and AVIF variants up to 4 MiB each; a new product requires both formats. Uploaded files are stored under `assets/images/products/uploads/` with random filenames.

## Security Model

The implementation includes:

- PDO prepared statements and strict server-side validation
- Transactional orders, row locking, server-authoritative prices, and idempotency keys
- Same-origin checks for public POST endpoints
- Contact and newsletter rate limits using HMAC-derived client identifiers
- Password hashing with PHP's current default algorithm and transparent rehashing
- Login throttling after five failed attempts within 15 minutes
- Strict, HTTP-only, same-site admin cookies; secure cookies under HTTPS
- A 30-minute idle timeout and periodic session ID regeneration
- CSRF tokens on all state-changing admin forms
- Output escaping and an admin Content Security Policy
- MIME, format, dimensions, size, and path validation for uploads
- Generic production error responses with details sent to server logs

These controls do not replace infrastructure protections such as HTTPS enforcement, least-privilege database access, backups, monitoring, and operating-system patching.

## Production Deployment

The repository supports a split public/private cPanel-style deployment:

1. Place only the root HTML files plus `assets/`, `api/`, and `admin/` in the public document root (for example, `public_html`).
2. Place the **contents** of `backend/` in a private sibling directory named `nova-private`, so `nova-private/bootstrap.php` exists outside the document root.
3. Create `nova-private/.env` with production credentials and set `PUBLIC_ROOT` to the absolute public document root.
4. Import `database/schema.sql`, followed by `database/seed.sql` when the initial catalog is required. Do not publish `database/`, `.git/`, `.env`, deployment archives, or `assets/images/reference.png`.
5. Ensure the web server can write to `assets/images/products/uploads/` if admin uploads are enabled.
6. Create the first administrator, clear `ADMIN_SETUP_TOKEN`, remove deployment archives, and test the storefront, API, and admin login.

The public bootstraps first look for `nova-private/bootstrap.php` beside the document root and retain `backend/bootstrap.php` as a local-development fallback.

## Quality and Test Status

This repository currently has **no automated test suite, CI workflow, or dependency-based linter**.

Before submitting PHP changes, run syntax checks when PHP is installed:

```powershell
Get-ChildItem admin,api,backend -Recurse -Filter *.php |
  ForEach-Object { php -l $_.FullName }
```

Then run the application locally and manually verify:

- home, shop, search, category filtering, sorting, and product details;
- cart persistence, quantity changes, and unavailable-product behavior;
- one order submission and safe retry behavior;
- contact and newsletter submissions;
- admin setup/login, CSRF-protected writes, product/category changes, uploads, order status, and messages;
- responsive layouts and absence of browser console errors.

## Repository Safety

The repository ignores populated environment files, runtime uploads, deployment bundles, logs, editor files, and local database exports. `.env.example` contains placeholders only and is intentionally tracked. Before publishing, inspect both the working tree and reachable history; ignored files can still contain production credentials on a developer machine.

## License

First-party source code and original documentation in this repository are available under the [MIT License](LICENSE).

The bundled Vazirmatn font is distributed independently under the [SIL Open Font License, Version 1.1](assets/fonts/OFL.txt) and is not covered by the repository's MIT License.

The MIT License applies only to material that the copyright holder is authorized to license. It does not automatically relicense third-party images, fonts, trademarks, product names, or other externally sourced content; those materials remain subject to their respective owners' terms.
