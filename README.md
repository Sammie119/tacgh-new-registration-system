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

SMS (mNotify) and WhatsApp (waapi.app) notifications (`App\Jobs\SmsNotificationJob`, `App\Jobs\WhatsappNotificationJob`) are dispatched onto a real Laravel queue (`ShouldQueue`, `QUEUE_CONNECTION=database`). Dispatching a job is just a fast row insert into the `jobs` table — the web request never waits on the SMS/WhatsApp API call. Something still has to periodically drain that table, though; pick one of the two options below depending on server access.

### Option A — webcron (no shell/cron access required)

A protected endpoint, `GET /tasks/run-queue/{secret}`, drains the queue when hit. It's meant to be pinged every 1–2 minutes by a free external service (e.g. [cron-job.org](https://cron-job.org), EasyCron, UptimeRobot) — no server access needed at all.

1. Generate a secret: `php artisan tinker --execute="echo Str::random(40);"`
2. Set it in `.env`: `CRON_SECRET=<the generated value>`
3. Point the external service at `https://yourapp.com/tasks/run-queue/<the same value>`, every 1–2 minutes.

Without the correct secret the endpoint always returns 404 (and if `CRON_SECRET` is unset, it 404s unconditionally — fails closed). Each hit processes up to 20 jobs or 25 seconds' worth, then stops; overlapping hits are skipped via a cache lock rather than stacking up. The secret is a bearer credential — don't post the real URL anywhere public (issue tracker, chat, etc).

### Option B — Supervisor (if you have shell/persistent-process access)

A persistent `queue:work` process, managed by Supervisor so it survives crashes and restarts on deploy. A ready-to-use config template is at `deploy/supervisor-queue-worker.conf` — copy it to `/etc/supervisor/conf.d/`, fill in the real app path and user, then:

```
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tacgh-queue-worker:*
```

After every deploy that changes code, restart the workers so they pick it up (`php artisan queue:restart`, or `supervisorctl restart tacgh-queue-worker:*`) — a running worker keeps the old code loaded in memory otherwise. This scales further than Option A (dedicated always-on workers vs. periodic short bursts), so it's worth switching to if notification volume grows.

### Either way

Both `sendSms()` and `sendWhatsApp()` (`App\Http\Traits\SMSNotify`) have a 5s connect / 10s total cURL timeout, and both jobs retry up to 3 times with a 10s backoff on failure (permanently-failed jobs land in `failed_jobs` — inspect with `php artisan queue:failed`). This app previously ran the queue worker inline inside a global HTTP middleware on every request whenever jobs were pending, which made arbitrary unrelated page loads pay the full cost — including live SMS/WhatsApp API calls — of someone else's notification backlog. That middleware has been removed for exactly this reason.
