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

SMS (mNotify) and WhatsApp (waapi.app) notifications (`App\Jobs\SmsNotificationJob`, `App\Jobs\WhatsappNotificationJob`) are dispatched and run synchronously, in-process, as part of the request or Artisan command that triggers them (registration, batch import, payment confirmation) — there's no queue, cron, or Supervisor-managed worker involved. `::dispatch()` on these job classes runs `handle()` immediately since neither implements `ShouldQueue`.

The tradeoff: the triggering request blocks briefly on the live mNotify/waapi.app HTTP call before returning a response. Both `sendSms()` and `sendWhatsApp()` (`App\Http\Traits\SMSNotify`) set a 5s connect / 10s total cURL timeout, so a stalled or unreachable provider adds at most ~10s to the request rather than hanging indefinitely. There's no retry — a failed call just returns an error payload/string that isn't currently surfaced back to the end user (see the `catch` block in `sendSms()` and the `$err` check in `sendWhatsApp()` if you want to add failure handling later).

This app previously ran notifications through a real queue drained by a global HTTP middleware on every request, which made arbitrary unrelated page loads pay the full cost — including live SMS/WhatsApp API calls — of someone else's notification backlog. That middleware has been removed; sending synchronously as part of the same request that needs the notification avoids that problem entirely, at the cost of that one request being slightly slower.

**Known issue (as of 2026-07-28):** WhatsApp sends are currently failing. A local test registration confirmed SMS delivers correctly (mNotify returned a real success response), but `sendWhatsApp()` gets back `"Instance is not ready (status: qr). Please wait until the instance is in ready status before sending messages."` from waapi.app. This is not a code bug — the WhatsApp Business session on waapi.app's dashboard needs to be (re-)connected by scanning a QR code before sends will work again. SMS is unaffected and can be relied on in the meantime.
