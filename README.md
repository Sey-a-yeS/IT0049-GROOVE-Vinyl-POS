# GROOVE — Vinyl & Music Store POS

GROOVE is a standalone CodeIgniter 4 point-of-sale and store-management application built for the IT0049 Midterm Project. It combines authenticated staff access, catalog and customer CRUD, prepared image uploads, atomic inventory deduction, and an auditable sales ledger in one original retro-modern interface.

This repository is independent from the earlier `pos-system` and `tasks-for-today` projects.

## Features

- Secure staff login, session regeneration, logout, CSRF protection, and login throttling
- Dashboard with real product, customer, staff, sale, low-stock, and recent-sale data
- Product, customer, and staff create/read/update/archive workflows
- Genuine JPEG/PNG product and avatar uploads, cropped to 800 × 800 with safe random names
- Record Sale workflow with live preview, server-authoritative pricing, row locking, rollback, and one-time form tokens
- Joined Sales History that keeps archived relationships readable
- Searchable, paginated management tables
- Responsive layouts at desktop, tablet, and 390 px mobile widths
- Visible focus states, semantic landmarks, labelled fields, error associations, and reduced-motion support

## Technology stack

- PHP 8.2 or newer (developed and tested on PHP 8.5.10)
- CodeIgniter 4.7
- MySQL 8 or compatible modern MySQL with InnoDB
- Composer 2
- PHP extensions: `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `mysqli`, and `openssl`
- PHPUnit 10 for automated verification

The Composer constraint is PHP `^8.2`; production hosting does not need PHP 8.5 specifically.

## Screenshots

The completed local interface includes the login screen, dashboard, product catalog, customer and staff directories, Record Sale workflow, and Sales History. Repository screenshots are intentionally pending until the final hosted URL and production data are available; do not treat a localhost capture as proof of hosting.

## Local installation

1. Install dependencies.

   ```bash
   composer install
   ```

2. Copy the development environment template.

   ```bash
   cp .env.example .env
   ```

3. Create dedicated databases. The test database must be disposable.

   ```sql
   CREATE DATABASE groove_pos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE DATABASE groove_pos_test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. Set the local database username/password in `.env`. Never commit `.env`.

5. Create the required schema and load the fictional starter catalog.

   ```bash
   php spark migrate
   php spark db:seed GrooveSeeder
   ```

6. Securely initialize one existing staff password. The command targets exactly one active username, hashes the password, never prints it, and has no web route.

   ```bash
   printf 'Enter a new staff password: '
   IFS= read -r -s GROOVE_STAFF_PASSWORD
   printf '\n'
   export GROOVE_STAFF_PASSWORD
   php spark groove:staff-password grooveadmin
   unset GROOVE_STAFF_PASSWORD
   ```

   Seeded usernames are `grooveadmin`, `vinylclerk`, `stockroom`, and `weekendstaff`. Seeder-created passwords are deliberately unknown random values.

7. Start the local server and open the displayed address.

   ```bash
   php spark serve --host 127.0.0.1 --port 8082
   ```

## Main routes

| Area | Route | Purpose |
|---|---|---|
| Login | `/login` | Staff authentication |
| Dashboard | `/dashboard` | Live operational overview |
| Products | `/products` | Catalog, stock, pricing, images |
| Customers | `/customers` | Customer records |
| Staff | `/staff` | Staff accounts and avatars |
| Record Sale | `/sales/new` | Point-of-sale workflow |
| Sales History | `/sales/history` | Joined transaction ledger |

All management GET and POST routes pass through the authentication filter. Logout and every mutating form use POST with CSRF protection.

## Data model and lifecycle

Migrations create the official `products`, `customers`, `users`, and `sales` tables. The sales table has indexed foreign keys to all required parent records. `customer_id` is nullable for walk-ins.

Products, customers, and staff use an `is_archived` extension instead of destructive deletion. Archived records disappear from new operational selections while historical sales remain readable.

## Image uploads

Product images and staff avatars accept genuine JPG/JPEG or PNG content up to 2 MB. The application decodes and re-encodes each image to 800 × 800, stores a cryptographically random filename, and saves only that filename in MySQL.

Runtime files live in:

- `public/uploads/products`
- `public/uploads/avatars`

These folders are Git-ignored except for their placeholders. The target host must make them writable, persistent across releases, protected from script execution, and included in backups.

## Sale integrity

The browser preview is advisory. `SaleService` always retrieves the current database price and authenticated staff identity. In one MySQL transaction it validates active relationships, locks the selected product row with `SELECT ... FOR UPDATE`, checks stock, performs a conditional decrement, calculates money in integer cents, inserts the sale, and commits. Any failure rolls back inventory and the ledger together.

The HTTP workflow adds a single-use sale token and disables repeat clicks. Refreshing the success/history page cannot submit another sale.

## Testing

Configure `database.tests.*` in the private `.env`, then run:

```bash
vendor/bin/phpunit --testdox --no-coverage
```

The suite covers schema and foreign keys, seed integrity, password hashes, authentication and protected routes, archived-login denial, HTTP CRUD, validation failures, genuine PNG/JPEG preparation, unsupported-image rejection, exact sale totals, rollback, duplicate-token rejection, and a real two-process inventory race.

Do not point PHPUnit at a database containing meaningful data; the database test trait refreshes its configured test schema.

## Production deployment

Use [.env.production.example](.env.production.example) as a checklist, never as a credentials file. Full provider-neutral instructions are in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

Minimum production requirements:

- PHP 8.2+ with the listed extensions
- MySQL/InnoDB and permission to run migrations
- The web document root set to the repository's `public/` directory
- HTTPS with `app.forceGlobalSecureRequests = true` and `cookie.secure = true`
- A private production `.env`, `CI_ENVIRONMENT = production`, and debug output disabled
- Writable `writable/`, `public/uploads/products`, and `public/uploads/avatars`
- Persistent upload storage and backups of both MySQL and uploaded images
- Apache rewrite support or an equivalent Nginx front-controller rule

Recommended release commands:

```bash
composer install --no-dev --optimize-autoloader
php spark migrate --all
php spark cache:clear
```

Seed only a fresh intended database. Then initialize a named active account with the secure procedure above.

## GitHub readiness

The repository includes application source, migrations, seeders, services, views, local assets, tests, safe environment templates, and deployment documentation. `.gitignore` excludes the real `.env`, `vendor/`, sessions, logs, caches, runtime uploads, and test artifacts.

No remote, force-push, publication, hosting purchase, or deployment is performed automatically.

## Troubleshooting

- **Database connection refused:** verify MySQL is running and the `.env` host, port, database, username, and password are correct.
- **Login always fails after seeding:** securely run `php spark groove:staff-password <username>` with the temporary `GROOVE_STAFF_PASSWORD` environment variable.
- **Image preparation fails:** enable GD and Fileinfo; verify the two public upload directories are writable and persistent.
- **Image rejected as not uploaded:** compare PHP's `upload_max_filesize` and `post_max_size` with the application 2 MB limit.
- **CSRF failure after a form sat open:** reload the form for a fresh token.
- **Assets or routes 404:** set the document root to `public/`, confirm the production `app.baseURL`, and enable URL rewriting.
- **Port 8082 is busy locally:** choose another port and update the private `.env` base URL.

## Project status

Part 2 visual refinement, security auditing, regression tests, and provider-neutral deployment preparation are complete locally. A real hosted deployment remains pending until a hosting provider, domain, production database, and credentials are supplied and verified.
