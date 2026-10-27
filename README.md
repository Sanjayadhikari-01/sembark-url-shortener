# Sembark URL Shortener

A multi-company URL shortener built with Laravel 12, PHP 8.2+, Blade, Eloquent, and MySQL. It supports SuperAdmin, Admin, and Member roles, tenant-scoped URL lists, invitation-based onboarding, and public short-link redirects.

## Features

- Secure login/logout; public self-registration is disabled.
- SuperAdmin can see links across companies and invite a first Admin while creating a company.
- Admin can see company links and invite Admins or Members into that company.
- Member can create and see their own links.
- Random short codes with a unique database constraint, HTTP/HTTPS destination validation, and public `/r/{code}` redirects.
- Single-use invitation tokens stored as SHA-256 hashes, configurable expiration, normalized email, transactional acceptance, duplicate-active-invite protection, revocation, and no account transfer.
- Email delivery through Laravel's configured mail transport. Use `MAIL_MAILER=log` locally to inspect invitation messages in `storage/logs/laravel.log`.

Sales and Manager are mentioned in the assignment without defined permissions. They are intentionally not implemented; only the specified three roles are supported.

## Requirements

PHP 8.2+, Composer, and MySQL 8+ (SQLite also works for local development/tests). Required PHP extensions are those required by Laravel 12, including PDO and the applicable database driver.

## Setup

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Set database connection values in `.env` (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) and set the initial SuperAdmin credentials:

```dotenv
SUPERADMIN_EMAIL=owner@example.com
SUPERADMIN_PASSWORD=use-a-long-unique-secret
INVITATION_EXPIRY_DAYS=7
MAIL_MAILER=log
```

Never commit `.env` or real credentials. Set `APP_URL` to the local application URL. For production, set `APP_DEBUG=false` and use a secure mail transport. Then run:

```sh
php artisan migrate --seed
php artisan serve
```

The SuperAdminSeeder uses a parameterized raw SQL insert, hashes its password with Laravel Hash, and does not duplicate an existing email. It fails if the email or password is missing (production password must be at least 16 characters). Change credentials only through a secure operational process; do not place secrets in source control.

## Workflows

1. Sign in as the seeded SuperAdmin. Invite an Admin by entering a company name and email. In local development, the invitation URL appears in the Laravel log.
2. The invited person opens the link, sets their name and password, and is assigned to the new company as Admin.
3. An Admin can invite an Admin or Member to the same company. Members can create links and only see their own links.
4. Admins see all links in their company; the SuperAdmin sees links for every company. Anyone can open a short link at `/r/{code}`.

Login is at `/login`. Links live at `/urls`; invitations live at `/invitations` for Admins and SuperAdmins.

## Tests

```sh
php artisan test
```

The feature tests use in-memory SQLite and cover authentication boundaries, URL creation and scheme validation, role-based listing, redirect behavior, and core invitation flows.

## Security and limitations

Authorization is enforced server-side and tenant IDs are taken from the authenticated user. URL destinations accept only HTTP/HTTPS. Invitation tokens are never stored in plaintext, expire after seven days by default, and accepted accounts cannot be transferred between companies. Login, invitation, creation, and redirect routes are rate-limited. Email is sent synchronously using Laravel Mail. Password reset UI, email verification enforcement, and richer operational audit logging are not included. MySQL is the target deployment database; SQLite is used for tests.

## GitHub preparation

Review `git status` and the generated project files; ensure `.env`, database files containing local data, logs, and secrets are excluded. This workspace has a local `main` branch and no commits. Create an empty repository on GitHub, then run:

```sh
git add .
git commit -m "Build Sembark URL shortener"
git branch -M main
git remote add origin <your-repository-url>
git push -u origin main
```

Do not commit local credentials or publish until you have reviewed the diff and `.gitignore`.

## AI usage disclosure

Suggested disclosure: “I used OpenAI Codex as an AI tool for implementation assistance, debugging, and code review. If Antigravity was also used during this assignment, include it here. I reviewed the generated project and remain responsible for understanding and validating its behavior.” Review the authorization paths, invitation lifecycle, generated migrations, and deployment configuration carefully before submission; run the test suite in the target environment and be prepared to explain the implementation.

## Deploy (Render)

The repo includes a `Dockerfile` and `render.yaml`. In Render, choose **New → Blueprint**, select this repository, and set `SUPERADMIN_EMAIL` and `SUPERADMIN_PASSWORD` (16+ characters) when prompted. The free plan uses SQLite on ephemeral disk, so data resets on redeploy or restart. Invitation emails are written to the service logs (`MAIL_MAILER=log`, `LOG_CHANNEL=stderr`).
