# GROOVE deployment on Render

This guide prepares the standalone GROOVE CodeIgniter 4 application for a Render Docker web service. It does not create a Render service, purchase resources, run production migrations, or claim that the application is already hosted.

## 1. Deployment architecture

Use three persistent components:

1. **GROOVE web service:** a Render Docker web service built from this repository's `Dockerfile`.
2. **MySQL 8 database:** either a Render private MySQL service backed by its own persistent disk or an external managed MySQL provider reachable from Render.
3. **GROOVE data disk:** a persistent disk mounted on the web service at `/var/data` for product images, staff avatars, and file sessions.

Do not store MySQL data on the web-service disk. The database requires its own service/storage, lifecycle, credentials, and backups.

## 2. What the container provides

The image uses PHP 8.3 with Apache and installs:

- `curl`;
- GD with JPEG/PNG/FreeType support;
- Intl;
- Mbstring;
- MySQLi;
- OPcache;
- Fileinfo and OpenSSL from the PHP base image.

Composer installs from `composer.lock` using `--no-dev --classmap-authoritative`. Apache serves `/var/www/html/public`, honors CodeIgniter's front-controller rewrite rules, denies script-like files under `/uploads`, and binds to the runtime `PORT` environment variable. The image defaults to port `10000` for local compatibility with Render.

The container maps:

- `/var/www/html/public/uploads` → `/var/data/uploads`;
- `/var/www/html/writable/session` → `/var/data/session`.

Cache and logs remain ephemeral and are written to the container's `writable/` tree and standard error. Application logs should be inspected through Render logs.

## 3. Create or connect MySQL

GROOVE requires MySQL 8 or a compatible current MariaDB release with InnoDB, transactions, foreign keys, row locking, and `utf8mb4` support.

### Render-hosted MySQL option

Render does not provide managed MySQL. Its documented pattern is a private MySQL Docker service with a persistent disk mounted at `/var/lib/mysql`. Place that service in the same region as GROOVE and use its private hostname from the web service.

Configure the MySQL service with its own private values:

- `MYSQL_DATABASE`;
- `MYSQL_USER`;
- `MYSQL_PASSWORD`;
- `MYSQL_ROOT_PASSWORD`.

Use a least-privilege application user. Do not expose port 3306 publicly. Use `mysqldump` or another MySQL-aware backup process; do not treat a raw database disk snapshot as a transaction-consistent backup.

### External MySQL option

Confirm the provider accepts connections from Render and supplies a MySQL hostname, port, database, username, password, and TLS requirement. Set `DB_SSL=true` only when the provider supports/requires the corresponding MySQL TLS connection.

## 4. Create the Render web service

The optional `render.yaml` describes a paid Docker web service, a 1 GB disk at `/var/data`, a `/login` health check, and secret placeholders. Review pricing and values before applying the Blueprint.

For manual setup:

1. In Render, create a **Web Service** from `Sey-a-yeS/IT0049-GROOVE-Vinyl-POS`.
2. Select the `main` branch and the **Docker** runtime.
3. Keep the Dockerfile path as `./Dockerfile`.
4. Set the health check path to `/login`.
5. Add a paid persistent disk mounted exactly at `/var/data`.
6. Keep one web-service instance when using the attached disk.
7. Add the environment variables listed below.

Do not set a custom Docker start command; the image's entrypoint validates `PORT`, initializes the persistent directories, and launches Apache.

## 5. Required environment variables

| Variable | Required | Example/meaning |
|---|---:|---|
| `CI_ENVIRONMENT` | Yes | `production` |
| `DB_HOST` | Yes | Private MySQL hostname or provider hostname |
| `DB_PORT` | Yes | Usually `3306` |
| `DB_DATABASE` | Yes | Empty/new or existing GROOVE database name |
| `DB_USERNAME` | Yes | Least-privilege application user |
| `DB_PASSWORD` | Yes | Secret value entered only in Render |
| `DB_SSL` | Provider-specific | `true` when the MySQL provider requires TLS; otherwise `false` |
| `GROOVE_BASE_URL` | Optional | Canonical custom HTTPS origin, e.g. `https://groove.example.com/` |

Render supplies these automatically:

- `PORT` — the port Apache binds to;
- `RENDER=true` — enables Render HTTPS/security behavior;
- `RENDER_EXTERNAL_URL` — used as the base URL when `GROOVE_BASE_URL` is absent.

Never place database credentials or a staff password in `Dockerfile`, `render.yaml`, a committed `.env`, a build argument, or a screenshot.

## 6. HTTPS and reverse proxy behavior

Render redirects public HTTP to HTTPS, terminates TLS at its load balancer, and forwards the request to the container over HTTP. Apache recognizes `X-Forwarded-Proto: https` and marks the PHP request secure. In Render mode GROOVE:

- forces secure application URLs;
- uses the HTTPS Render/custom base URL;
- issues Secure, HttpOnly, SameSite=Lax cookies;
- retains global session-based CSRF protection;
- retains the authentication filter on every protected route;
- retains the Content Security Policy and secure response headers.

The container port is not meant to be directly exposed outside Render's ingress.

## 7. Non-destructive database initialization

Migrations and seeders intentionally do **not** run during ordinary container startup. This prevents every restart or scaled instance from changing production data.

Before the first schema change:

1. Verify the database target and credentials.
2. Take a MySQL-aware backup if the database is not empty.
3. Open the Render Shell for the GROOVE web service.
4. Inspect pending migrations:

   ```bash
   php spark migrate:status
   ```

5. Apply forward migrations:

   ```bash
   php spark migrate --all
   ```

6. For a new empty demonstration database only, seed once:

   ```bash
   php spark db:seed GrooveSeeder
   ```

Do not run the seeder on every deploy. Do not run migration rollback commands against production without reviewing the migration and preparing a tested database restore.

### Initialize the first staff password

After seeding, use the Render Shell and enter a private password of at least 10 characters:

```bash
printf 'Enter a new staff password: '
IFS= read -r -s GROOVE_STAFF_PASSWORD
printf '\n'
export GROOVE_STAFF_PASSWORD
php spark groove:staff-password grooveadmin
unset GROOVE_STAFF_PASSWORD
```

Do not save `GROOVE_STAFF_PASSWORD` as a permanent Render environment variable.

## 8. Upload and session persistence

Render's ordinary service filesystem is ephemeral. Without the `/var/data` disk:

- newly uploaded product images and staff avatars disappear after a restart or deploy;
- file-backed login sessions disappear after a restart or deploy;
- database rows can retain filenames whose corresponding images no longer exist.

The Blueprint mounts one disk at `/var/data`; the entrypoint creates `uploads/products`, `uploads/avatars`, and `session` below it with restricted permissions. Back up MySQL and `/var/data/uploads` together so database filenames and image files remain consistent.

A persistent disk is not shared object storage. Keep the web service at one instance. If horizontal scaling is later required, replace local upload storage and file sessions with shared services through a separately designed migration.

## 9. Local Docker verification

Build the production image:

```bash
docker build --pull -t groove-pos:render .
```

Verify installed modules and Composer requirements:

```bash
docker run --rm groove-pos:render php -m
docker run --rm groove-pos:render composer check-platform-reqs --no-dev
```

Run against a reachable non-production MySQL database:

```bash
docker run --rm -p 10000:10000 \
  -e CI_ENVIRONMENT=production \
  -e RENDER=true \
  -e RENDER_EXTERNAL_URL=https://groove-local.example \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=3306 \
  -e DB_DATABASE=groove_pos_db \
  -e DB_USERNAME=your_mysql_user \
  -e DB_PASSWORD=your_mysql_password \
  -v groove-data:/var/data \
  groove-pos:render
```

Use only disposable/test credentials in shell history. For a simple HTTP smoke check that does not require a login:

```bash
curl -I -H 'X-Forwarded-Proto: https' http://127.0.0.1:10000/login
```

## 10. Live regression checklist

After the first successful deploy and database setup:

1. Confirm `/login` returns successfully and uses HTTPS.
2. Verify Secure, HttpOnly, SameSite=Lax session cookies, CSP, CSRF, and security headers.
3. Sign in with the initialized active staff account and sign out again.
4. Create, view, edit, and archive controlled product, customer, and staff records.
5. Upload a genuine JPEG product image and PNG staff avatar; verify both display after a service restart/redeploy.
6. Edit both records without replacement files and verify their existing images remain.
7. Record a controlled sale and verify the database price, total, staff attribution, timestamp, and exact stock reduction.
8. Attempt an oversale and verify neither inventory nor Sales History changes.
9. Refresh the success page and verify the one-time sale token prevents duplication.
10. Archive a related record and verify the historical sale remains readable.
11. Verify Dashboard, Products, Customers, Staff, Record Sale, and Sales History at desktop and mobile widths.
12. Test coordinated restoration of the MySQL backup and uploaded-image backup.

## 11. Remaining deployment actions

- Review and merge/push this configuration.
- Create the Render MySQL/private or external database and backup policy.
- Create the Docker web service and paid `/var/data` disk.
- Enter the private environment values in Render.
- Deploy the image and confirm the `/login` health check.
- Back up and run forward migrations deliberately.
- Seed only if the chosen database is new and empty.
- Initialize the named staff account privately.
- Complete the live regression checklist and record the real URL only after it passes.

Until those actions are complete, GROOVE is Render-ready source code—not a verified hosted application.
