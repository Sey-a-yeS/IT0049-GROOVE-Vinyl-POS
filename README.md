# GROOVE Vinyl and Music Store POS

GROOVE is a standalone CodeIgniter 4 point-of-sale management system created for the IT0049 Midterm Project. It provides authenticated staff access to product, customer, and staff management, image uploads, atomic sale recording, inventory control, and joined sales history.

This repository is independent from the earlier `pos-system` and `tasks-for-today` assessment projects.

## Technology stack

- PHP 8.2 or newer
- CodeIgniter 4.7
- MySQL 8 or newer with InnoDB
- Composer 2
- GD PHP extension for prepared product images and avatars
- PHPUnit 10 for automated verification

## System requirements

Confirm PHP, Composer, MySQL, and the required PHP extensions are available:

```bash
php -v
composer --version
mysql --version
php -m | grep -E 'curl|fileinfo|gd|intl|mbstring|mysqli|openssl'
```

## Installation

1. Install PHP dependencies.

   ```bash
   composer install
   ```

2. Copy the safe environment template.

   ```bash
   cp .env.example .env
   ```

3. Create a dedicated MySQL database. Use a database account with access only to the databases it needs.

   ```sql
   CREATE DATABASE groove_pos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. Update the database username and password in `.env`. Never commit `.env`.

5. Create the four required tables, indexes, constraints, and foreign keys.

   ```bash
   php spark migrate
   ```

6. Load the fictional product, customer, and staff records.

   ```bash
   php spark db:seed GrooveSeeder
   ```

7. Set a local password for an existing seeded account. The password is read from a one-command environment variable and is not stored in the repository or exposed by a web route.

   ```bash
   GROOVE_DEMO_PASSWORD='choose-a-strong-local-password' php spark groove:staff-password grooveadmin
   ```

   The seeded usernames are `grooveadmin`, `vinylclerk`, `stockroom`, and `weekendstaff`. Their initial hashes represent unknown random values, so run the command for any account that needs a known local password.

8. Start the development server.

   ```bash
   php spark serve --host 127.0.0.1 --port 8082
   ```

9. Open `http://localhost:8082/` and sign in with the account initialized in step 7.

## Database design

The required `products`, `customers`, `users`, and `sales` tables are created by migrations. The `sales` table retains the required relationships to all three parent tables.

GROOVE adds an `is_archived` field to products, customers, and users. This small extension preserves historical transactions instead of deleting referenced rows. Archived products are excluded from new sales, and archived staff cannot sign in.

Database constraints reject negative prices or stock, non-positive sale quantities, and negative totals. Foreign-key indexes and an index on transaction time support joined history queries.

## Authentication and sessions

- `POST /login` verifies a password hash with `password_verify()`.
- Successful login regenerates the session ID and stores only the staff ID, username, display name, and avatar filename.
- Every management route uses the `auth` filter.
- Logout is a CSRF-protected POST request and destroys the authenticated session.
- Session-based CSRF protection is enabled globally.

For production, set the public base URL, enable HTTPS, and set `cookie.secure = true` in the production environment.

## Features and routes

- Dashboard: `/dashboard`
- Products: `/products`
- Customers: `/customers`
- Staff: `/staff`
- Record Sale: `/sales/new`
- Sales History: `/sales/history`

Each management area supports create, read, update, and archive workflows with server-side validation and old-input preservation. Password inputs are never repopulated.

## File uploads

Product images and staff avatars accept genuine JPG/JPEG or PNG files up to 2 MB. Images are resized and cropped to a display-ready 800 by 800 pixels, renamed with a random server-generated filename, and saved under:

- `public/uploads/products`
- `public/uploads/avatars`

Only the generated filename is stored in MySQL. Executable file extensions are blocked in the upload directory for Apache deployments. Ensure these directories are writable on the target host and back them up separately from the database.

## Sale processing and inventory safety

`SaleService` treats database values as authoritative. The browser never chooses the staff ID and cannot submit a trusted price or total.

Within one MySQL transaction, the service:

1. Confirms the active staff account and optional active customer.
2. Locks the selected product row with `SELECT ... FOR UPDATE`.
3. Checks active status and current stock.
4. Performs a conditional stock decrement.
5. Calculates the total using integer cent arithmetic.
6. Inserts exactly one sale and commits.

Any failure rolls back both the inventory change and sale insert. Competing requests are serialized by the row lock and cannot oversell the product.

## Automated tests

Create a separate disposable test database and configure the `database.tests.*` values in `.env`.

```sql
CREATE DATABASE groove_pos_test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run the suite:

```bash
vendor/bin/phpunit --testdox --no-coverage
```

The suite verifies schema and foreign keys, seed integrity, model CRUD and archiving, password hashing, login success and failure, protected routes and POST actions, image preparation, the required 10-to-7 controlled sale, rollback on an 8-unit oversell attempt, invalid quantities, and a real two-process inventory race.

## Clean database reproduction

To verify a clean installation without touching the working database, point `database.default.database` temporarily to a disposable database and run:

```bash
php spark migrate
php spark db:seed GrooveSeeder
```

Do not run `migrate:refresh` against a database containing meaningful data.

## Troubleshooting

- **Database connection refused:** confirm the MySQL service is running and the `.env` host, port, username, and password are correct.
- **Image preparation fails:** enable the PHP GD extension and confirm the upload directories are writable.
- **Login always fails after seeding:** run `groove:staff-password` with a temporary `GROOVE_DEMO_PASSWORD` value.
- **CSRF error after leaving a form open:** reload the form to obtain a fresh token.
- **Port 8082 is busy:** choose another port and update `app.baseURL` in `.env`.

## Project status

Part 1 provides the functional standalone foundation. Hosting, production credentials, deployment configuration, final accessibility review, and the broader visual refinement belong to Part 2. This repository does not claim to be hosted or production-ready.
