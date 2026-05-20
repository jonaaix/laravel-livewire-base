---
name: laravel-patches
description: Build and run one-off Artisan "patches" with the aaix/laravel-patches package — trackable, disposable commands for data migrations and one-time fixes (not schema changes — those are still migrations).
---

# Laravel Patches

A patch is a hidden Artisan command that extends `Aaix\LaravelPatches\Commands\PatchCommand` and is logged in the `patch_logs` table after a successful run, so it isn't executed twice by accident.

## Scaffolding

Always generate via Artisan — never hand-write the file:

```bash
php artisan make:patch FixUserEmails
```

Creates `app/Console/Commands/Patches/Patch_{Y_m_d}_FixUserEmails.php` with signature `patch:{Y_m_d}_fix-user-emails`. Implement logic in `handle(): int` and return `self::SUCCESS` / `self::FAILURE`. Wrap multi-step writes in `DB::transaction(...)`; chunk large backfills.

## Running

- `php artisan patch` — interactive checklist of pending patches (recommended).
- `php artisan patch:{Y_m_d}_fix-user-emails` — run a specific patch by signature.
- `php artisan patch --patch=Patch_…` / `--all` — non-interactive. Warn before suggesting `--all` in production.

Already-applied patches prompt for confirmation and increment `run_count`.

## Status & sync

- `php artisan patch:status` — lists ran vs. pending.
- `php artisan patch:sync` — for onboarding an existing codebase: marks existing patch files as run **without** executing them. Never suggest `patch --all` for that case.

## Config (`config/patches.php`, optional)

`table` (default `patch_logs`), `path` (default `app/Console/Commands/Patches`). The namespace of generated patches follows `path` automatically.

## Pitfalls

- Don't use migrations for one-off data fixes — migrations re-run on fresh setups, patches don't.
- Don't rename a patch file after it has been logged — `patch_logs.patch_class` stores the FQCN, so it'll look pending again.
- Don't delete a pending patch to skip it — use `patch:sync`.
