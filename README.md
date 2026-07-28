# TAG-GH Registration

Laravel 10 application for managing event registration: registrant sign-up (individual and batch), online/offline payment processing (Paystack, GTBank), accommodation and room allocation, a dynamic form builder, and admin reporting — gated behind role-based access control (`spatie/laravel-permission`).

## Requirements

- PHP 8.1+ (extensions: `pdo_mysql`, `pdo_sqlite` for tests, `mbstring`, `bcmath`, `gd`, `zip`)
- Composer
- Node 18+ / npm
- MySQL (or another Laravel-supported DB) for local/production use — tests run against an in-memory sqlite DB and don't need this

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Fill in `.env`:
- `DB_*` — your local database connection
- `GTPAY_*` — GTBank/myghpay gateway credentials (sandbox values from your provider)
- `PAYSTACK_*` — Paystack gateway credentials
- `WHATSAPP_*` — waapi.app WhatsApp notification credentials
- `APIV2_MNOTIFY_API_KEY`, `MNOTIFY_*` — mNotify SMS credentials

Then:

```bash
php artisan migrate
npm run dev    # or `npm run build` for production assets
php artisan serve
```

### Bootstrapping the first admin

The initial `users` migration seeds one user (`admin@admin.com`) but does **not** assign it a role, and there is no seeder for roles/permissions — roles are normally created through the admin UI (`/admin/roles`, `/admin/permissions`), which itself requires a role to access. On a fresh database, bootstrap the first Super Admin manually:

```bash
php artisan tinker
```
```php
$role = \Spatie\Permission\Models\Role::create(['name' => \App\Enums\RolesEnum::SUPERADMIN->value]);
\App\Models\User::first()->assignRole($role);
```

**Immediately change the seeded admin's password** — the migration ships with a hardcoded credential (`admin@admin.com`), so rotate it before using this in any shared environment:

```php
\App\Models\User::first()->update(['password' => \Illuminate\Support\Facades\Hash::make('a-new-strong-password')]);
```

## Testing

Tests run against an isolated in-memory sqlite database (configured in `phpunit.xml`), independent of whatever `DB_CONNECTION` is set in `.env`.

```bash
php artisan test
```

## Code style

```bash
vendor/bin/pint          # auto-fix
vendor/bin/pint --test   # check only, no changes
```

CI (`.github/workflows/ci.yml`) runs `pint --test` and the test suite on every push and pull request against `main`.

## Roles

Defined in `App\Enums\RolesEnum`: System Admin, System Developer, Room Allocator, Finance, Registrar, Super Admin. Route access is gated per group in `routes/admin.php` and `routes/auth.php` via Spatie's `role:` middleware.

## Payment gateways

- **Paystack** — `App\Helpers\PayStackPayment`, orchestrated through `App\Services\Admin\PaymentService`.
- **GTBank/myghpay** — configured via `GTPAY_*` env vars, also handled through `PaymentService`.

Offline payments are recorded manually by Finance-role users via financial clearance/entry screens (`FinanceController`).

## Notifications

SMS (mNotify) and WhatsApp (waapi.app) notifications are dispatched as queued jobs (`App\Jobs\SmsNotificationJob`, `App\Jobs\WhatsappNotificationJob`). The queue worker runs on a schedule (`queue:work --stop-when-empty` every minute, see `App\Console\Kernel`).

**Required in production:** wire `php artisan schedule:run` into a real system cron, or queued jobs will never be processed and no SMS/WhatsApp notifications will send. Add this to the crontab of the user running the app (`crontab -e`):

```
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

An earlier version of this app worked around a missing cron by running `queue:work` inside a global HTTP middleware on every request whenever jobs were pending. That middleware has been removed — it made arbitrary, unrelated page loads pay the full cost (including live SMS/WhatsApp API calls) of draining someone else's notification backlog. The scheduled command above is the only mechanism that processes the queue now, so this cron entry is not optional.
