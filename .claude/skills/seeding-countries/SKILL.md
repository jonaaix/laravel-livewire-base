---
name: seeding-countries
description: Seed or re-seed country data from aaix/laravel-countries on demand. Covers the one-shot master seeder invocation, idempotent re-runs, native-name backfill, and the rule for keeping per-country seeder files merge-friendly with upstream.
---

# Seeding aaix/laravel-countries

## When to use this skill

Use this skill when setting up the package in a fresh app, refreshing country data after a package update, troubleshooting why countries are missing, or working on the bundled per-country data.

## Run the seeder on demand — not on every `db:seed`

Country data is **reference data**. It changes rarely (only when this package ships updated data). The full seed is also expensive — 245 countries × 9 locales + native names + extras takes noticeable time.

Run the master seeder **directly**, once, when you actually need it:

```bash
php artisan db:seed --class='Aaix\LaravelCountries\Database\Seeders\DatabaseSeeder'
```

Typical situations to run it:
- After `composer require aaix/laravel-countries` + `php artisan migrate` on a fresh app.
- After pulling a package update that ships new or changed country data.
- When restoring a database that was created without the country tables seeded.

The seeder is **idempotent** — safe to re-run; it will not duplicate rows.

## What gets seeded

- 245 countries with ISO codes, phone codes, currencies, flag colors, coordinates, and structured extras.
- Translations for 9 locales: `ar`, `de`, `en`, `es`, `fr`, `it`, `nl`, `pt`, `ru`.
- A separate `NativeNamesSeeder` populates `native_name` on each country (the country's own language: `Deutschland`, `日本`, `Россия`). Runs as part of the master seeder.

## DO NOT

- **Do not edit per-country seeder files** (e.g. `DE_Germany.php`, `JP_Japan.php`) to set `native_name` or anything else. Those files must stay merge-friendly with upstream. `native_name` is populated by `NativeNamesSeeder`.
- **Do not run `php artisan w-countries:install` or `w-countries:languages`** — these commands do not exist in this fork.
- **Do not `vendor:publish`** the migrations — they auto-load from the service provider.

## Adding or overriding country data in the consuming app

If you need to attach app-specific data to a country (vat rate, shipping zone, internal flag), do **not** modify the package seeder files. Either:

1. Create your own model with a `country_id` foreign key (`country_settings`, `country_vat`, etc.) and seed it from your app's seeders.
2. Or extend the country model in your app (see the `using-countries` skill) and add accessors / casts there.
3. If the data belongs in the package itself (corrected ISO data, missing translation, updated flag colors), open a pull request at the package repository instead of patching locally.
