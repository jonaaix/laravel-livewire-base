# Playground Project

This repository is a prototyping playground. The user explores ideas, you implement them.
Role, code style, architecture, planning, design and communication rules come from the
TALL Architect guidelines below — this file only adds what is specific to this workspace.

## Workspace model

**The Laravel app IS the playground.** It is meant to grow continuously — new routes,
models, migrations, islands and services are the default, not the exception. Do not invent
reasons to keep new work out of it.

Three places code can live:

- **Core app** — shared infrastructure: auth, users, settings, layout, generic utilities.
  Stays small. Only small additions with no own data model land here.
- **Internal module** (`app/Modules/<Module>/`) — the default as soon as a feature has its
  own table(s). The threshold is low: a single dedicated table is enough. A small module
  beats scattered core code.
- **Sideproject** (`sideprojects/<name>/`) — anything outside the Laravel stack: one-shot
  non-interactive work whose output is files, or another language entirely. Self-contained
  per folder, with its own isolated dependencies (venv, own `package.json`).

A sideproject producing data that a module consumes and visualizes is a fine split.

## Module layout

A module owns the backend half of a feature: its tables, models, services and jobs.

- **Namespace:** `App\Modules\<Module>\…` — already covered by the root `App\` → `app/`
  PSR-4 mapping, so a new module needs no `composer.json` change.
- **Folder:** `Models/`, `Services/`, `Jobs/`, `routes.php`, `resources/views/`.
- **Routes:** the module's own `routes.php`, mounted by the provider under the kebab-cased
  module name with a matching route-name prefix and the `web` middleware — `Gads` serves
  `/gads/…` as `gads.…`. These are the pages that host the module's islands.
- **Migrations:** stay in `database/migrations/`; filename and table carry the module prefix.
- **Translations:** `lang/<locale>/<module>.php` → `__('gads.dashboard.title')`. The root
  lang directory is loaded by Laravel itself; nothing to wire.
- **Views:** `resources/views/` in the module, registered under the kebab-cased namespace
  → `view('gads::dashboard')`.
- **No per-module `composer.json` or ServiceProvider.** `App\Providers\ModulesServiceProvider`
  scans `app/Modules/*` and wires routes and views. Both files are optional — a module with
  neither is still a valid module.
- **The module's UI lives in `app/Islands/`, not inside the module folder.** Islands are
  discovered from that one configured root. Name them after the module so the pair is
  obvious, and keep the island thin: it reads and writes through the module's services.

## Live environment

The container you work in is the deployment the user actually uses, reachable at `APP_URL`.
There is no local / staging / production split. This is not a reason to be conservative
about extending the app — it is a reason to protect the user's data.

- Never seed test users, fake records or demo content into the database. The only exception
  is your own `Claude` user for app access.
- Rows created to populate a screenshot are deleted in the same task.
- Demo seeders belong in tests, never in `database/seeders/DatabaseSeeder`.

## Frontend

**Islands are the primary frontend.** A feature view is an island — `aaix/laravel-islands`
for the view itself, `aaix/laravel-islands-datagrid` for anything list-shaped. Both ship a
full set of helper components, composables and hosts; read the `islands-development`,
`islands-datagrid-development` and `ui-patterns` skills before writing markup. A
hand-rolled modal, tooltip, toolbar or sort menu that already exists behind a one-line
import is the most common avoidable diff here.

- **Blade + Alpine** for static pages and local interactivity that owns no server state.
- **Livewire and Flux carry the existing shell only** — auth pages, settings pages, layout
  and navigation under `resources/views/pages/` and `resources/views/layouts/`. Work on
  those where they are, and follow their conventions. Do not extend them into feature
  territory, and do not reach for `<flux:*>` or a new Livewire component for a new view.
- **Filament** only when the user explicitly asks for an admin panel or heavy CRUD.
- **React and Inertia** are not used in this project.

### Public pages are server-rendered

**An island renders nothing on the server.** `<x-island>` emits an empty `<div>` carrying a
JSON payload in a data attribute; the markup only exists once Vue has mounted. A crawler,
a link preview or anything else that does not run JavaScript sees an empty page.

So the boundary is indexability, not complexity:

- **Anything that must be found or previewed is Blade** — a blog post, a landing page,
  documentation, a public product page. Body copy, headings, links, `<head>` metadata,
  canonical and structured data are rendered server-side, always.
- **Islands own what sits behind a login or behind an interaction** — app views, dashboards,
  data tables, onboarding and checkout forms. Nobody needs to find those in a search engine.
- **A public page may host an island** for its interactive part, as long as the content that
  matters for indexing lives in the Blade around it. A blog post is Blade; its comment box
  can be an island.

When a feature is public-facing, say which half is which before building it.

Need a widget that neither the island helpers nor Alpine cover? Build it in the island's
own `Components/` folder in the project's design language, or propose a package when the
component is a beast on its own (calendars, WYSIWYG editors, charts, file uploaders). Do
not install general-purpose UI libraries.

## Available stack

Everything below is installed and ready — no setup required.

- **UI:** Laravel Islands (+ Datagrid), Tailwind CSS 4, Alpine, ApexCharts, Echo + Pusher.js.
  Livewire 4 and Flux UI are present for the shell — see Frontend above.
- **Infrastructure:** Horizon, Reverb, Scout + Meilisearch, Fortify, Laravel scheduler via
  supercronic.
- **AI:** `laravel/ai` — text and image generation, agents, embeddings, structured output.
- **Data & files:** spatie/laravel-data, spatie/laravel-pdf + Browsershot,
  spatie/simple-excel, spatie/temporary-directory, Intervention Image,
  aaix/laravel-patches, webklex/laravel-imap, Pandoc.
- **Notifications:** WebPush.
- **Observability:** opcodesio/log-viewer, aaix/laravel-easy-backups.
- **i18n:** aaix/eloquent-translatable, outhebox/blade-flags, aaix/laravel-countries.
- **Services (docker-compose):** MariaDB (`mariadb`), Redis (`redis`),
  Meilisearch (`meilisearch`).
- **MCP:** Laravel Boost, Playwright (browser automation, screenshots), Context7 (library docs).

## Dependencies

Install freely with `composer`, `npm`, `apt-get`, `pip`, `uv` — sudo needs no password.
Adding one to the Laravel app is not an implementation detail: propose it and say what it
buys before installing.

## Output

- Data and reports → files in the workspace, referenced by path. Never paste large file
  contents or long tool output into chat.
- Interactive things → a route, handed over as a URL.
