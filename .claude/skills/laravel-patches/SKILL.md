---
name: laravel-patches
description: Build and run tracked Artisan "patches" with the aaix/laravel-patches package — manually triggered commands that repair or clean up data at a specific point in the project's history. Not for schema evolution (a schema change and the data move it implies stay together in one migration) and not for permanent tooling (recurring or scheduled work is a normal Artisan command).
---

# Laravel Patches

A patch is a hidden Artisan command that extends `Aaix\LaravelPatches\Commands\PatchCommand` and is logged in the `patch_logs` table after a successful run, so it isn't executed twice by accident.

## Choosing the right tool

A patch repairs data at a point in the project's history. It is neither a companion step for architectural change nor a permanent piece of tooling.

Two questions decide:

1. Must every environment run it for the release to be correct?
   → Migration. This includes the data move a schema change implies: backfilling a new column before `NOT NULL`, copying rows into a restructured table, splitting or merging columns. Schema and its data move stay in one migration — never split across a migration and a patch.

2. Will it still be needed once every environment has it applied?
   → Artisan command. Recurring cleanups, scheduled normalisation and anything ops runs on demand are permanent tooling, not patches.

Otherwise it is a patch: correcting values a since-fixed bug corrupted, normalising a bad import, cleanup nothing in the code depends on.

Re-running is allowed. If the same damage reappears before the root cause was fixed, run the patch again — `run_count` tracks it. A patch that is expected to run indefinitely, however, is a command.

## Scaffolding

Always generate via Artisan — never hand-write the file:

```bash
php artisan make:patch FixUserEmails
```

Creates `app/Console/Commands/Patches/Patch_{Y_m_d}_FixUserEmails.php` with signature `patch:{Y_m_d}_fix-user-emails`. Implement logic in `handle(): int` and return `self::SUCCESS` / `self::FAILURE`. Wrap multi-step writes in `DB::transaction(...)`; chunk large backfills.

Replace the generated `$description` placeholder with what the patch repairs — it is shown next to the file name in the `patch` picker and in `patch:status`, where the class name alone is rarely enough.

## Running

- `php artisan patch` — interactive checklist of pending patches (recommended).
- `php artisan patch:{Y_m_d}_fix-user-emails` — run a specific patch by signature.
- `php artisan patch --patch=Patch_…` / `--all` — non-interactive. Warn before suggesting `--all` in production.

Already-applied patches prompt for confirmation and increment `run_count`.

## Status & sync

- `php artisan patch:status` — lists ran vs. pending, with each patch's description.
- `php artisan patch:sync` — for onboarding an existing codebase: marks existing patch files as run **without** executing them. Never suggest `patch --all` for that case.

## Config (`config/patches.php`, optional)

`table` (default `patch_logs`), `path` (default `app/Console/Commands/Patches`). The namespace of generated patches follows `path` automatically.

## Pitfalls

- Don't use a patch for a data move that a schema change requires — keep both in the migration. Patches repair data; they don't complete a refactor.
- Don't rename a patch file after it has been logged — `patch_logs.patch_class` stores the FQCN, so it'll look pending again.
- Don't delete a pending patch to skip it — use `patch:sync`.
