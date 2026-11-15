# PHP 8.5 and Laravel 13 upgrade

## Target versions

As of November 11, 2026, this application targets:

- **PHP 8.5.x**, enforced by `~8.5.0` in `composer.json` and indicated by
  `.php-version`. PHP provides active and security support, not LTS editions.
  PHP 8.5 receives security support through December 31, 2029.
- **Laravel 13.x**, with Laravel **13.42.0** resolved in `composer.lock`.
  Current Laravel releases use a uniform support policy instead of LTS
  editions. Laravel 13 receives security fixes through March 17, 2028.

Sources: [PHP support policy](https://www.php.net/supported-versions.php) and
[Laravel 13 release notes](https://laravel.com/docs/13.x/releases).

The local PHP installation was already 8.5.12. This change updates the
application requirements; it does not install or modify system PHP.
Deploy the latest available PHP 8.5 patch release.

## Application changes

- Upgraded Laravel 5.2 to Laravel 13, Socialite 2 to 5, Predis 1 to 3,
  Guzzle to 7, and the Symfony components to 8.
- Moved Debugbar to development dependencies and disabled it by default.
  Set `DEBUGBAR_ENABLED=true` only in a trusted local environment.
- Replaced the abandoned Faker package with `fakerphp/faker`, replaced
  closure-based factories with class-based factories, and namespaced seeders.
- Removed the unused Doctrine DBAL and language package dependencies.
  Existing English and Korean translation files remain in use.
- Added `composer.lock` for reproducible installations, PHPUnit 12 support,
  and the `composer test` command.
- Updated service providers, exception handling, middleware, route parameter
  access, and the base controller to supported APIs.
- Replaced removed authentication traits and `Route::auth()` with explicit
  login, registration, logout, password reset, and GitHub callback handlers.
- Preserved password hashing, password-reset token verification, OAuth state
  verification, and session regeneration on authentication. GitHub email
  matches do not implicitly link existing local accounts.
- Updated Eloquent date casting and converted reminder day arguments to
  integers for Carbon 3, including arguments passed from the CLI and routes.
- Updated mail configuration for Symfony Mailer, including Mailgun support.
  Added logging and hashing configuration and current cache/queue settings.
- Preserved the nested-task ownership checks from
  [security-fix.md](security-fix.md).

The existing application directory structure and database migration history
were retained. No existing database was reset or migrated during development.
Local browser checks use a newly created, ignored `database/local.sqlite`;
automated tests use an isolated in-memory SQLite database.

## Behavior and configuration changes

- **Logout is POST-only.** The navigation now submits a CSRF-protected form.
  Update clients that previously navigated to `GET /logout`.
- New registrations and password resets require at least eight password
  characters. Existing passwords can still be used to log in.
- Login, registration, reset-link requests, and password resets are limited
  to six requests per minute using Laravel's throttle middleware.
- Sessions use JSON serialization. Expect existing users to sign in again.
  Custom integrations must not store arbitrary PHP objects in sessions.
- New installations use `CACHE_STORE`, `QUEUE_CONNECTION`, and `MAIL_MAILER`.
  Configuration retains fallbacks for the old `CACHE_DRIVER`, `QUEUE_DRIVER`,
  and `MAIL_DRIVER` variables.
- New installations log mail locally instead of contacting an SMTP server.
  Set `MAIL_MAILER=smtp` and the SMTP connection variables, or
  `MAIL_MAILER=mailgun` with `MAILGUN_DOMAIN` and `MAILGUN_SECRET`.
  Use `MAIL_SCHEME=smtps` for implicit TLS when required by your mail server.
- Redis uses the Predis client by default. Existing counter names and the
  unprefixed Redis key default are retained. Counter updates only run when
  `CACHE_STORE=redis`, matching the welcome page's counter reads. Other cache
  stores use database counts and do not require a Redis server.
- Keep the existing `password_resets` table. The password broker explicitly
  uses it, so no table rename is needed.

## Installation and deployment

Use PHP 8.5 and Composer 2.9 or newer. Composer checks required extensions.
Tests also require PDO SQLite. Production needs a Laravel-supported database
and access to Redis if `CACHE_STORE=redis`.

Before upgrading an existing deployment, back up the database, environment
configuration, and application files. Test the release against a staging copy
of the real database. SQLite test results do not establish compatibility with
every production database or third-party service.

For an existing deployment, preserve `.env` and `APP_KEY`, review the settings
above, then install the locked production dependencies and clear old caches:

```sh
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not use `migrate:fresh`, `migrate:refresh`, or demo seeders on production
data. The demo seeders create publicly known example credentials.
Avoid flushing the Redis database during deployment: the application counters
share that database. The commands above clear compiled configuration, routes,
and views without flushing application cache data.
Ensure only the web-server account and appropriate operators can write
`storage/` and `bootstrap/cache/`; do not make them world-writable.
Restart queue workers and PHP application processes after deployment.
Production must use `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, and
`SESSION_SECURE_COOKIE=true`.

## Validation

Verified on PHP 8.5.12 with Laravel 13.42.0:

| Check | Result |
| --- | --- |
| Feature suite | 42 tests, 372 assertions passed |
| PHP syntax | 71 application/configuration/test and entry-point files passed |
| Composer manifest, lockfile, and platform requirements | Passed |
| Dependency audit | No known advisories or abandoned packages reported |
| Production install without development dependencies | Passed |
| Production CLI and web entry points | Passed |
| Configuration, route, and Blade view cache builds | Passed |

The feature suite covers the task authorization regression, project/task CRUD,
authentication pages, registration, login, logout, throttling, password reset,
GitHub callback behavior, production-mode CSRF enforcement, translations,
factories/seeders, and reminder mail through the array transport. Local runtime
regressions also cover operation without Redis, every supported task priority,
schema-length limits, invalid project/task input, and malformed date filters.
Invalid forms now display validation errors rather than database exceptions;
invalid date searches return to the task list without a redirect loop.

Browser checks on `http://127.0.0.1:8000` verified registration, login/logout,
project/task creation, editing, filtering, deletion, validation messages, and
the date picker's stylesheet and interaction. The task form no longer offers
the unsupported `즉시` priority.

Run from the repository root:

```sh
composer install
composer validate --strict
composer check-platform-reqs
composer test
composer audit
php artisan --version
php artisan route:list --except-vendor
```

Tests mock Redis counters and GitHub provider responses. Real Redis, SMTP,
Mailgun delivery, and a live GitHub OAuth exchange require staging validation.
The legacy Gulp/Elixir frontend build was not upgraded; the current Blade
layout continues to load its existing CDN assets.
