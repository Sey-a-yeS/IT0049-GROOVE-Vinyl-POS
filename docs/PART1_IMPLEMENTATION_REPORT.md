# GROOVE Part 1 Implementation Report

## A Project location and independence

GROOVE is located in the standalone `groove-pos` directory. It has its own Composer dependencies, environment configuration, MySQL databases, uploads, application structure, and Git boundary. No runtime dependency points to an earlier assessment application.

## B CodeIgniter PHP and MySQL environment

- CodeIgniter 4.7.5
- PHP 8.5.10
- Composer 2.10.3
- Homebrew MySQL 26.7.0
- Local development URL: `http://localhost:8082/`

## C Database tables and relationships

The application uses the required `products`, `customers`, `users`, and `sales` tables. `sales.product_id`, `sales.customer_id`, and `sales.sold_by` are indexed foreign keys. The customer relationship remains nullable for walk-in transactions. All tables use InnoDB.

The `is_archived` extension on products, customers, and users preserves related sales rows while removing inactive records from operational selections.

## D Migrations and seeders

Two application migrations create the core records and sales ledger. `GrooveSeeder` inserts 20 fictional products, 10 fictional customers, and 4 staff accounts without duplicating a non-empty table. Seeded staff hashes are generated at seed time from random unknown values.

## E Authentication and sessions

The login controller retrieves an active staff record, verifies the submitted password, regenerates the session ID, and stores minimal staff identity data. An authentication filter protects all management GET and POST routes. Logout uses POST, CSRF protection, and session destruction.

## F Product CRUD

Products support listing, detail, creation, editing, and archiving. Validation enforces a required 100-character name, nonnegative decimal price, nonnegative whole-number stock, and a valid optional image.

## G Customer CRUD

Customers support listing, detail, creation, editing, and archiving. Full name, email, and optional phone validation follow the official field lengths.

## H Staff CRUD

Staff accounts support listing, detail, creation, editing, password change, avatar replacement, and archiving. Usernames are unique. Passwords are hashed. The current account and the last active account cannot be archived.

## I Product image and avatar handling

The shared upload service accepts genuine JPG or PNG content up to 2 MB after controller validation, creates a random filename, prepares an 800 by 800 image, and stores only its filename. Existing images remain when no replacement is submitted. SVG placeholders cover missing images.

## J Record Sale implementation

The protected sale form accepts an active product, optional active customer, and positive whole-number quantity. Staff identity comes exclusively from the authenticated session. Unit price and total are calculated from the database record.

## K Inventory transaction safety

`SaleService` uses a single connection and MySQL transaction. It locks the product row, rejects insufficient stock, performs a conditional decrement, inserts the sale, and commits. Failures roll back all changes. Money multiplication uses integer cents.

## L Sales History

Sales History uses one joined query across the four required tables, shows walk-in customers clearly, formats Philippine peso totals, and sorts newest transactions first. Archived related records remain readable.

## M Dashboard

The dashboard shows real active-product, customer, staff, and sale counts, low-stock records, recent joined transactions, and direct operational navigation. It includes no synthetic growth statistics.

## N Files created and modified

Part 1 added application migrations, seed data, four models, six controllers, one authentication filter, two focused services, one password setup command, shared and feature views, branded CSS and JavaScript, safe upload directories, automated tests, environment templates, and project documentation.

## O Actual test results

The final automated run completed 20 tests and 94 assertions with zero failures. It covered authentication, authorization, protected HTTP CRUD workflows, validation failures, schema, foreign keys, seed integrity, password hashing, image preparation, exact sale totals, rollback, and competing sales.

The controlled product began with 10 units. A sale of 3 created one row, recorded the authenticated staff member and a total of PHP 3,750.00, and left 7 units. A subsequent request for 8 units was rejected; stock remained 7 and the sale count remained one.

The concurrency test launched two independent PHP processes that each attempted to sell 4 of 5 available units. Exactly one succeeded, one was rejected, final stock was 1, and exactly one sale row existed.

## P Clean install verification

The disposable `groove_pos_test_db` is used for destructive migration refreshes and automated verification. The working `groove_pos_db` is not dropped or refreshed during testing.

## Q README and Git status

The README documents setup, environment variables, migrations, seeders, local password initialization, routes, uploads, transaction behavior, tests, clean reproduction, and troubleshooting. An independent Git repository has been initialized on `main`; no commit or remote was created automatically. Git ignores `.env`, runtime uploads, logs, sessions, caches, and vendor dependencies.

## R Known issues

- The application is not deployed or hosted.
- Production HTTPS, credentials, permissions, and host-specific rewrite rules are not yet verified.
- Part 2 visual, accessibility, responsive, and production hardening passes remain.

## S Manual actions remaining

1. Choose production database credentials and base URL.
2. Run migrations and seed only the intended environment.
3. Initialize at least one known staff password through the CLI command.
4. Configure writable upload directories and persistent storage on the host.
5. Review the new files, create the first commit, and add a GitHub remote when ready.
6. Complete Part 2 acceptance testing and deployment.

## T Part 2 readiness

Part 1 is functionally ready for the Part 2 refinement and deployment phase. Required application behavior is implemented and locally testable, but the project is not yet production-ready or hosted.

## Verification matrix

| Requirement | Implementation location | Test performed | Actual result | Status |
|---|---|---|---|---|
| Four required tables | `app/Database/Migrations` | MySQL migration and table inspection | All four tables created | PASS |
| Required foreign keys | Sales migration | Driver metadata inspection | Three foreign keys present | PASS |
| Seed data | `GrooveSeeder` | Count and hash assertions | 20 products, 10 customers, 4 valid hashes | PASS |
| Login success and failure | `Auth` controller | Feature requests | Valid accepted, invalid rejected | PASS |
| Protected routes and POST | `AuthFilter` and routes | Anonymous GET and POST | Both blocked before data access | PASS |
| Product CRUD and archive | `Products`, `ProductModel` | Authenticated HTTP create, update, archive, and invalid input | All persisted correctly; negative values rejected | PASS |
| Customer CRUD and archive | `Customers`, `CustomerModel` | Authenticated HTTP create, update, and archive | All three actions persisted correctly | PASS |
| Staff CRUD and hashes | `Staff`, `UserModel` | Authenticated HTTP create, update, and archive | Hash stored, retained on blank edit, and account archived | PASS |
| Image preparation | `ImageUploadService` | Generated genuine PNG | Safe name and 800 by 800 output | PASS |
| Required 10 to 7 sale | `SaleService` | MySQL transaction test | One sale, stock 7, exact total and staff | PASS |
| Oversell rollback | `SaleService` | Attempted sale of 8 from stock 7 | Rejected with no partial change | PASS |
| Concurrent oversell prevention | `SaleService` | Two independent competing processes | One success, one rejection, stock 1 | PASS |
| Sales History joins | `SaleModel::withDetails` | Query exercised by dashboard and history code review | Single joined query, newest first | PASS |
| Local browser workflow | Application routes and views | Part 1 browser smoke test | Login and protected pages rendered | PASS |
| Hosted deployment | Not in Part 1 | Not performed | No hosted URL | NOT TESTED |
