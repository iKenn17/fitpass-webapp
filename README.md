# FitPass Gym — Owner Portal (Laravel overlay)

Server-rendered Laravel version of the **owner** screens in the FITPASS Figma file. No REST API: plain controllers and Blade views, SQLite database, no npm build step.

Screens: Login, Dashboard, Analytics, Members (+ manual entry), Staff Management (+ add / deactivate), Check-in (+ manual entry, CSV export), Reports & Audit Logs.

This folder is **not a full Laravel app**. It's a set of files to merge into a fresh Laravel 11 or 12 install.

## Setup

Needs PHP 8.2+, Composer, and the `pdo_sqlite` extension.

```bash
# 1. Create a fresh Laravel app
composer create-project laravel/laravel fitpass
cd fitpass

# 2. Copy this overlay on top of it (merge folders, overwrite when asked)
#    macOS/Linux:  cp -R /path/to/fitpass-overlay/. .
#    Windows:      xcopy /E /Y C:\path\to\fitpass-overlay\* .

# 3. Use SQLite and Philippine time. In .env set:
#      DB_CONNECTION=sqlite
#      APP_TIMEZONE=Asia/Manila
#    (Laravel 11+ already defaults to SQLite and creates database/database.sqlite.)

# 4. Build the database with sample data and run it
php artisan migrate:fresh --seed
php artisan serve
```

Open <http://127.0.0.1:8000> and sign in:

| Field | Value |
|---|---|
| Administrator | `owner@fitpass.test` |
| Password | `password` |

Change this password before putting anything real in the database.

## What gets overwritten

These three files exist in a fresh Laravel app and are **replaced**:

- `routes/web.php`
- `app/Models/User.php` (adds role, shift and active fields)
- `database/seeders/DatabaseSeeder.php`

Everything else is new. If you've already customised those three files, merge by hand instead of overwriting.

## Logo

The Figma logo emblem couldn't be exported automatically. Until you add it, the sidebar shows a plain "FP" badge. To use the real one: in Figma, select the emblem (layer `Rectangle 1` on any owner screen), export as PNG, and save it as `public/images/logo.png`. It's picked up automatically.

## How it's put together

| Piece | Where |
|---|---|
| Owner-only guard | `app/Http/Middleware/EnsureOwner.php`, applied in `routes/web.php` |
| Numbers behind Dashboard/Analytics | `app/Services/GymStats.php` |
| Pages | `app/Http/Controllers/Owner/*`, `resources/views/owner/*` |
| Charts (no JS library) | `resources/views/components/{line-chart,bars,donut}.blade.php` |
| Styling (from Figma values) | `public/css/fitpass.css` |
| Schema | `database/migrations/2026_10_09_*` |
| Sample data | `database/seeders/FitPassSeeder.php` |

Only users with `role = owner` can sign in here. Staff accounts created from Staff Management are records for the owner to manage; they can't log in to this portal.

## Things to know

- **Figma gaps.** The Staff Management frame has no designed table, so it reuses the Check-ins table layout. The Reports tab has no design either; it shows a simple Today / This week / This month summary.
- **System Status.** Only the Database check is real. QR Scanner, Payment Gateway and Backup always show Online until those integrations exist (`GymStats::systemStatus()`).
- **Deltas.** "vs yesterday" compares today so far against yesterday up to the same time of day; "vs last month" compares month-to-date against the same span of last month.
- **Sample data size.** The seeder creates 150 members and about 60 days of check-ins. Change `MEMBER_COUNT` in `FitPassSeeder.php` for more.
- **Fonts.** Holtwood One SC and Hind load from Google Fonts, so the pages need internet access to look right.
- **Not yet built.** Member mobile app and Staff portal screens from the Figma file, plus the Payments page.
