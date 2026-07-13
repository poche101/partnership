# Partnership Tracker (Laravel + Blade + Tailwind + SQLite)

Rebuild of the original React/Supabase Partnership Tracker as a plain Laravel app:
Blade views, Tailwind CSS, SQLite database, and role-based access control enforced
at the application layer with Laravel **Gates** (see `app/Providers/AppServiceProvider.php`)
and per-controller `role:` middleware instead of Postgres Row-Level Security.

## Stack

- Laravel 11, Blade, Tailwind CSS (via Vite)
- SQLite (default connection, `database/database.sqlite`)
- Session-based auth (`users` table with a `role` column: `zone_admin`, `group_admin`, `church_admin`)
- Optional AI features (semantic partner search, AI-written giving statements) via the
  Anthropic API — both gracefully fall back to plain substring search / a templated
  statement when `ANTHROPIC_API_KEY` isn't set, exactly like the original app's fallback.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate

# SQLite file already exists at database/database.sqlite — just migrate:
php artisan migrate --seed

npm install
npm run build   # or `npm run dev` while working on styles/JS

php artisan serve
```

Seeded login (from `SEED_SUPERADMIN_EMAIL` / `SEED_SUPERADMIN_PASSWORD` in `.env`):

- **Email:** `superadmin@partnership.app`
- **Password:** `SuperAdmin#2026`

Sign in at `/auth`, then use the **Group Churches** page (zone admin only) to create
group churches + group admins, and **Churches** to create churches + church admins —
the same cascading admin-creation flow as the original app.

## Roles & scope

| Role | Sees |
|---|---|
| `zone_admin` | Everything: all groups, churches, partners, givings; manages arms, thresholds, audit log, AI search, statements |
| `group_admin` | Only churches/partners/givings under their group church; can create churches |
| `church_admin` | Only their own church's partners/givings |

## Data model

- `group_churches`, `churches`, `users` (role + scoping columns)
- `partners` — partner + spouse fields
- `partnership_entries` — one row per giving record, 13 fixed arm columns
  (`rhapsody`, `healing_school`, ... `lca_rebuild`) + an auto-computed `total_espees`
  (kept in sync by `App\Models\PartnershipEntry::booted()` on every save)
- `partnership_arms` — the configurable arm labels/ordering shown in the UI
  (adding a *new* arm still needs a matching migration column on `partnership_entries`,
  same limitation the original app had — noted on the Arms page)
- `giving_alert_thresholds` / `giving_alerts` — per-arm thresholds, auto-raised alerts
- `giving_statements` — generated statement letters
- `audit_logs` — activity trail

## Features carried over 1:1

- Dashboard: totals, arm breakdown bars, top churches, 30-day trend (zone admin)
- Group churches / churches: create + cascading admin account creation
- Partners: list, filter/search, plain-text or AI semantic search (zone admin), create
- Givings: record giving across all 13 arms, filter by arm
- Bulk upload: client-side Excel/CSV parsing (SheetJS) with a preview step before import
- Giving statements: generate (AI or templated) + client-side PDF download (jsPDF)
- Giving alerts: configurable thresholds, auto-raised alerts, acknowledge
- Partnership arms admin (zone admin)
- Audit log (zone admin)

## Notes

- Tailwind theme uses the same deep-navy/cream/gold palette as the original login page
  (`tailwind.config.js`).
- Charts use Chart.js via CDN (dashboard trend); PDF export uses jsPDF via CDN; Excel
  parsing uses SheetJS via CDN — no extra PHP packages required for those.
