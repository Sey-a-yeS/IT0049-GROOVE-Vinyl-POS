# GROOVE production deployment guide

This guide is provider-neutral. It prepares GROOVE for a standard PHP/MySQL host without inventing provider credentials or claiming that localhost satisfies the hosted-project requirement.

## 1. Hosting capabilities

Confirm the provider supports:

- PHP 8.2 or newer
- `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `mysqli`, and `openssl`
- MySQL with InnoDB, foreign keys, transactions, and row locks
- Composer 2 or a deployment pipeline that can upload the built `vendor/` tree
- HTTPS and a configurable document root
- Persistent writable directories for sessions, logs, caches, and uploaded images
- Scheduled/off-site database and uploaded-file backups

Run `composer check-platform-reqs --no-dev` in the release environment before switching traffic.

## 2. Release layout

Upload the repository without the local `.env`, development logs, sessions, caches, test artifacts, or local runtime uploads. Install production dependencies:

```bash
composer install --no-dev --classmap-authoritative
```

Set the virtual host/document root to `<release>/public`. The repository root, `app/`, private `.env`, and `writable/` must not be web-accessible.

For Apache, the tracked `public/.htaccess` provides front-controller rewriting when `mod_rewrite` and overrides are enabled. For Nginx, use an equivalent rule such as:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php-fpm.sock;
}
```

Adapt the PHP-FPM socket to the provider. Deny direct PHP execution anywhere under `public/uploads/` in the server configuration; the included Apache rule is defense in depth.

## 3. Private production environment

Copy `.env.production.example` to a private `.env` on the host and replace the example origin and database account. Keep:

```ini
CI_ENVIRONMENT = production
app.forceGlobalSecureRequests = true
app.CSPEnabled = true
cookie.secure = true
cookie.httponly = true
cookie.samesite = Lax
```

Use the final HTTPS URL with a trailing slash. If a trusted reverse proxy terminates TLS, configure `app.proxyIPs` precisely; never trust arbitrary forwarded headers.

Production mode disables detailed browser errors. Keep `writable/logs` outside public access and monitor it without exposing stack traces to users.

## 4. Database and first account

Create an empty UTF-8 database and a least-privilege application account. Back up before every schema change.

```bash
php spark migrate --all
php spark db:seed GrooveSeeder
```

Run the seeder only for a new intended installation. It is guarded against duplicate non-empty tables but should not be part of every release.

Initialize exactly one named active staff account without putting a password in shell history:

```bash
printf 'Enter a new staff password: '
IFS= read -r -s GROOVE_STAFF_PASSWORD
printf '\n'
export GROOVE_STAFF_PASSWORD
php spark groove:staff-password grooveadmin
unset GROOVE_STAFF_PASSWORD
```

The command never exposes the secret through a web route or prints it to the terminal.

## 5. Writable and persistent data

Grant the PHP process write access only where required:

- `writable/cache`
- `writable/logs`
- `writable/session`
- `writable/uploads`
- `public/uploads/products`
- `public/uploads/avatars`

Use provider-appropriate ownership and least-privilege modes; avoid world-writable `0777`. Keep public upload directories persistent across deployments. Back up MySQL and both upload collections together so database filenames and files stay consistent.

## 6. Release verification

Before directing real traffic:

1. Run `composer validate --strict` and `composer check-platform-reqs --no-dev`.
2. Run migrations and confirm all four tables and three sales foreign keys.
3. Confirm production mode, HTTPS redirect, secure/HTTP-only/SameSite cookies, CSRF, CSP, and security headers.
4. Sign in with the initialized account.
5. Open Dashboard, Products, Customers, Staff, Record Sale, and Sales History.
6. Upload one genuine JPG product image and one PNG avatar; confirm 800 × 800 prepared files persist after a release/restart.
7. Record a controlled sale and verify stock, staff, customer/walk-in, total, timestamp, and history.
8. Attempt an oversale and confirm neither stock nor sale count changes.
9. Verify the domain at 1440, 1024, 768, and 390 px widths.
10. Configure and test database plus uploaded-file restoration.

## 7. Rollback

Keep the previous release and a pre-migration database backup. Application files can be switched back only when their code remains compatible with the migrated schema. Never run destructive migration rollback commands on production without reviewing the exact migration and restoring from a tested backup when required.

## Current deployment status

No hosting provider, production domain, database, or credentials have been supplied. The application is locally verified and deployment-prepared, but live hosting is still pending and must be tested on the selected provider before it can be marked complete.
