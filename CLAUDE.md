<laravel-boost-guidelines>
=== .ai/CLAUDE.playground rules ===

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

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Test every code change by adding or updating a test.
- Run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

=== aaix/laravel-tall-architect/tall-architect rules ===

# TALL Architect

The rules below come from the `aaix/laravel-tall-architect` package and are authoritative for this project — where they
and generic framework guidance disagree, these win.

# Role: TALL Stack Engineer & Architect

You work on this codebase — architecture, implementation, and review.

## Modes

### Discussion (default)

Clarify, propose, name trade-offs. No file writes. Snippet requests stay here — isolated code only.

### Implementation (on request)

Atomic, scoped, no adjacent cleanup.

### Switching

Explicit instruction only. Ambiguous → ask. After the change, back to discussion.

## Tech Stack Standards

PHP >= 8.5, Laravel >= 13.x, Filament >= 5.x, Livewire, Alpine.js, Tailwind CSS >= 4.x, Vue.js >= 3.x

## Code Style

- **PSR-12 Compliance:** All PHP code must strictly adhere to PSR-12
- Follow clean code after Robert C. Martin's principles.
- **NEVER ADD ANY CODE COMMENTS OR DOCBLOCK, except:**
    1. Very complex abstract mathematical algorithms that absolutely need explanation. => Block comment
    2. Structural dividers in very long code files (e.g.: // ----- Step: 1: Doing X ... -----, // ----- Step: 2: Doing Y ... -----) => Single line comment
    3. A deliberate restriction that would otherwise look like a bug or oversight — hardcoded value, skipped case, narrowed scope. State why, never what. => One single line comment never several
    4. Array shapes / generics that PHP types cannot express. => Docblock
- Existing comments stay, unless they are neither necessary under the rules above nor a marker (`TODO`, `NOTE`, …) or tool directive.
- `*_id` is always an internal FK. Any other reference uses `*_ref`.
- Jobs must be suffixed with `Job`.
- Enums must be suffixed with `Enum`.
- Commands must use the suffix `Cmd` instead of `Command` or nothing.
- **Enums vs Constants:** Use PHP backed enums for typed values that need methods (e.g., `label()`, `icon()`). Use `const` classes for simple key-value lookups (IDs, disk names, icons). Follow existing conventions — both patterns coexist in this codebase.
- Every PHP file declares `declare(strict_types=1)`.
- Prefer a DTO over an array when the structure is stable — as a `spatie/laravel-data` object.

## i18n & UI

- Prepare all strings for translations using Laravel's default translation function `__('...')`. The English text is the translation key. However don't create JSON translation keys if you are not explicitly asked for it. Keep API response messages in English only.
- Never use the native html title attribute as tooltip. Use a proper tooltip component.
- SVG is always wrapped in a component. Never inline SVG markup — reuse the existing icon component or create one.
- Custom UI follows Tailwind UI (or adapted Tailwind UI) style. Don't mix in other UI styles.

## Architectural Standards

- **Modular Monolith:** A feature area with its own table(s) belongs in a local module, not the root app. Even a single dedicated table is enough. Tables carry the module prefix (`<module>_<table>`), views and translations their own namespace — a module must be deletable as a unit: drop the prefixed tables, delete the folder. Modules may use shared root capabilities; implementation and boundaries stay outside root. Before writing code that adds a new area to root, name it and propose the module — the user decides.
- **Filament vs. Islands:** Filament for CRUD record management (list, create, edit, delete). Islands (`aaix/laravel-islands`, tables via `aaix/laravel-islands-datagrid`) for full Vue views and stateful widgets — own state, server-driven data, subscriptions. Alpine for local interactivity inside Filament (toggles, modals, small UI state). Outside Filament, Blade + Alpine is the default — propose an island when state, server data or subscriptions are involved.

### Decomposition & Reuse

- **Soft limit ~500 lines per file**, hard limit ~1500. These are warnings to reassess, not mandates to split. A coherent 800-line Filament Resource beats six fragmented 150-line files connected by parameter chains.
- **Split when it actually pays off.** Extract when there is a clear coherent unit with a stable interface (a card, a form section, a service method with few args and a focused return). Don't split just to hit a line count — fragmentation that creates indirection, prop-drilling, or scattered logic is worse than a longer file.
- **Reuse before building.** Search project components first — `resources/views/components/`, `app/Services/`. For islands and data tables, consult the `laravel-islands` and `laravel-islands-datagrid` skills with their component indexes and blueprints. Name what you found and why it does or doesn't fit. Copy-pasting an existing pattern instead of using it is worse than a long file.
- Check the installed dependencies first. Build it yourself unless edge cases or outside maintenance make a package the better bet — then propose one, don't add it silently.
- **Name by role, not by location.** `<x-stat-tile>` not `<x-dashboard-top-row-item>`; `InvoiceTotalCalculator` not `OrderPageHelper`. Role names survive moves; location names don't.

## Behavior & Interaction

- Never add or remove features proactively; always confirm it explicitly with the user first.
- Interact in the user's language, produce strictly in English.
- Ask when the answer depends on it — missing context, ambiguous scope, unclear domain logic. Don't ask what the codebase can tell you.
- When you need a decision or information, ask as a numbered list of concrete questions at the end of the response — one question per item.

## Workflow

- **Never destroy or reset the dev database** — no `migrate:fresh`/`refresh`/`reset`, `db:wipe`, rollbacks, dropped tables, however broken the schema looks. It may hold cleaned data pending export. Fix forward with a new migration or ask. A separate test database is yours to manage.
- Migrations are forward-only. Never edit one that has already run.
- Seeders and factories in `database/seeders` must be safe to run against real data. Demo and test data belong in tests.
- If you need populated data for a screenshot, create the rows, take it, and delete them in the same task.
- Prefer official `artisan` / Filament generators over manual file creation. Name the command.
- **Migration timestamps:** never chain migration-creating commands with `&&` or `;` — identical timestamps. One command, wait, next.
- When troubleshooting, read the log and reproduce (Tinker, test, or route) before proposing a cause. Don't guess.
- When files are created or moved, show the target tree — in the plan and before writing.
- Prefer MCP over shell execution when both can do it.
- Create your own test user `Claude` / `claude` if you need app access.
- Playwright defaults to 1920×1080, or iPhone 16 Pro for mobile checks.

### Git

- **Commits at feature boundaries.** One commit per feature, never per file or per edit. An uncommitted prior feature stays its own unit.
- **Commit messages:** `Area: Subject` in English, imperative, no period. Area is the module, island or resource, spelled as in the codebase; `Build`, `Deps` or `Docs` when there is no domain. Body only when the *why* isn't obvious from the diff.
- **Branches:** work on the active branch, never directly on `main`. `main` ← `dev` ← `feature`, merged with merge commits. No force push, no rebase of shared branches.

## Contract

Discussion by default. Reuse before building. Never reset the dev database.

---

# Planning

Multi-step work is tracked in a file under `.ai/planning/`, so any developer or agent can
take over from a cold start.

## Procedure

- Once work turns out to have more than one step, write the file before continuing.
  One file per feature, named after it (`form-modal-shell.md`).
- Update it as the work moves: state, milestones, decisions. Overwrite, don't append —
  the file describes how things *are*, not what happened. The history is in the git log.
- Commit it with the code it belongs to, not separately.
- When the feature is merged, move the file to `.ai/planning/archive/`.
- When starting work in an area, check the archive for an earlier file on the same subject.

## File structure

- **Goal** — what this feature does, and how you can tell it's finished.
- **Milestones** — the steps to get there, in order, each marked open or done.
- **State** — where the work stands right now: what exists in the code, what is still missing.
- **Decisions** — what was settled and why, including what was rejected. Only what explains
  the current state; not the back and forth that led to it.
- **Open** — what still needs a decision from the user.

Keep it short enough to stay accurate. A file nobody trusts is worse than no file.

---

# Design System

This project's visual decisions. Principles live in the UX Principles rules below, concrete
class recipes in the `ui-patterns` skill — read it before building UI.

## Visual language

We adapt shadcn/ui by hand in Tailwind — the package is not installed.

- **Radius scale:** cards `rounded-xl`, pills `rounded-md`, icon boxes `rounded-lg`,
  floating bars `rounded-full`.
- **Flat rings, no shadows on cards.** Shadows are reserved for things that genuinely float,
  where the shadow is what lifts them.
- **Padding `p-3`–`p-5`.** Wasteful padding looks dated.
- **Dark mode is native.** Never ship a background, text, border or ring class without its
  `dark:` variant.

## Wording

Page titles are Title Case, everything else sentence case — buttons, labels, tabs,
columns, menu items.

## Colour

- **Derive, don't hardcode.** Accents come from the active primary via HSL hue shifts
  (+60°, +120°, +180°, plus a desaturated muted variant). Hex values only as sentinels the
  theme layer replaces.
- **Neutrals for the chrome.** Text, borders and disabled states stay true gray.

## Icons

- **Heroicons only.** Outline 24 is the default. Solid mini 20 is sanctioned for compact
  toolbar strips, where outline strokes go blurry and read as faded. Pick a lane per strip
  and stay in it — never mix within one strip.
- **Every icon is its own component** with a stable name and fixed viewBox. Never inline
  `<svg>` in a consumer; that forks the visual set between callsites.
- The datagrid ships its own toolbar icons from `@aaix/laravel-islands-datagrid/vue` —
  islands import them rather than redrawing.
- **Icon boxes only beside a heading or a stat value.** In tabs, buttons, cells and hover
  affordances icons ship bare.

## Controls

**36px (`h-9`) for every control a pointer aims at** — inputs, select triggers, comboboxes,
dropdown buttons, the pills beside them. One height across toolbar and panel, so a row of
controls reads as one line.

Set the height, never the vertical padding — padding drifts with the line height and stops
matching when the font changes.

Two deliberate exceptions: micro-controls stay smaller, and multi-line fields grow from 36
rather than starting taller.

## Stacking order

One ladder for the whole app, so a new layer never lands under an old one. A panel *beside*
content belongs under the toolbar it scrolls past, not above it.

| Layer | z |
| --- | --- |
| Panels beside content | 10 |
| Table toolbar, floating bars | 20 |
| Application chrome (topbar, sidebar) | 30 |
| Dropdown backdrop / menu | 60 / 61 |
| Modal | 70 |
| Tooltip | 9999 |

## Motion

| What | Duration | Curve |
| --- | --- | --- |
| Panel unfolding | 350ms | `cubic-bezier(0.22, 1, 0.36, 1)` |
| Content fading in behind it | 260ms | ease-out |
| Floating bar in / out | 180 / 160ms | ease-out / ease-in |
| Hover affordances | 500ms | ease-out |
| Tooltip | 120ms | ease |

## Tables

- One `<table>` look per view: frame on the wrapper, internals in a single class wrapped in
  `:where()` so a utility class on a cell still wins.
- **Seven page numbers.**

## Formatting

Numbers, dates, money and weights go through `@shared/format.js` — `formatCurrency`,
`formatDate`, `formatRelative`, `formatWeight`. Figures use `tabular-nums`.
Times display in the user's timezone, 24-hour format — never the server's.

## Charts (ApexCharts)

Fixed pixel height (`height: 300`), never `'100%'` — that feeds back with flex parents.
Primary series uses the derived primary, further series the derived palette. Grid colours
adapt to dark mode, legend on top, labels inherit the font family.

## Alerts

Inside the view, never as a toast: a tinted block with an icon, a bold first line naming the
state, one sentence for the consequence. It sits next to what it talks about. Amber warns,
emerald confirms, a quiet variant carries neutral information.

## Modals

Three parts, both edges drawn: header with title and close button above a
`border-b border-gray-200 dark:border-white/10`, content, footer above a
`border-t border-gray-200 dark:border-white/10 pt-4`. Cancel (`tone="secondary"`) left of
the primary action (`tone="cta"`), both at default size — never `size="sm"` in a modal
footer. Laravel Islands and Filament both ship modal helpers — use them rather than
rebuilding this by hand.

---

# UX Principles

Portable rules for admin panels and ERPs. No framework, no project specifics.
Rules of thumb — deviate knowingly, not by accident.

## State & feedback

- **Three states, always.** Loading shows a skeleton in the target's shape, never a spinner
  on an empty page. Errors say what failed and offer retry. Empty says why, and offers to
  clear the filter that caused it.
- **Reserve the room before the data arrives**, or the container unfolds to a sliver and jumps.
- **What the user just did stays on screen** until the server echoes it back.
- **Confirmation never moves the layout** — the message replaces the value in place.

## Data display

- **Never truncate a value in a table.** Let the region scroll; a clipped part number is
  worse than a scrollbar. Ellipsis is for prose.
- **Fixed-width figures**, so columns don't jitter as numbers change.
- **Format centrally** — never by hand, never with a hardcoded locale.
- **Relative time in lists, absolute where the exact moment matters** — never both in one column.
- **Density beats spacing in data-dense views.** More per screen wins over generous padding.

## Colour & status

- **Status overrides theme.** Red is wrong, amber is worth a look, green is fine, grey means
  nothing is known. A green brand colour must never break "wrong is red".
- **Colour where the status *is* the message** — tint the whole surface, not just a dot.
  **Shape where the surface must stay quiet** (toolbars, tab strips): a neutral outlined
  icon with the wording in the tooltip.
- **One verdict, one source.** The same helper decides colour, sentence and icon.

## Actions

- **Every number is a door.** If a value summarises something, clicking it leads there.
- **Edit in place** — no detail page for a single field, and the affordance stays quiet.
  Same contract everywhere: Enter saves, Esc cancels, modifier+Enter for multi-line, a
  spinner in the value's place, errors replacing the value rather than sitting beside it.
- **Confirm what cannot be undone, and only that.** Cancel, Escape and a click outside all
  mean no.
- **The whole control is the target**, not the words in it.
- **Two tiers of action:** attention-worthy gets a tint, repeat actions stay neutral,
  saturated brand colour is for one-off CTAs. Micro-controls stay small enough not to
  compete with content.
- **Dropdowns over button rows** beyond three options.
- **One thing open per row.** Opening a second closes the first; switching siblings keeps
  the active tab.
- **Icon-only buttons carry an accessible label** and a tooltip with the same words. Never
  a native `title` tooltip.
- **A drop target is the whole region**, with an outline and one line of text saying what
  dropping will do.
- **Modals have three parts:** a header naming what this is, the content, and a footer
  carrying the actions. Actions never hang off the last field.
- **A form modal always has a close affordance in the header**, even when a cancel action
  exists — clicking outside is often blocked to prevent data loss.

## Motion

Movement explains a change; it never announces itself.

- **What appears must also disappear.** An animated entrance with an abrupt exit reads as
  a glitch.
- **Rows have no height to animate.** Grow a shell inside them instead.

## Navigation & persistence

- **Deep-link the view, not the page.** Expanded row, active tab, filters, page and sort
  belong in the URL.
- **View choices are remembered per user, not per browser.** Cache locally, but the server
  owns the value.
- **Give the scroll position back.** Rows arrive after the page, so native restore lands at
  the top — remember it and re-apply once the content has taken its space.
- **Prefetch on intent.** Resting on a control briefly starts loading what a click would
  need; share in-flight requests, abort when the pointer leaves, never on touch.

## Layout

- **Auto-fit over fixed grids.** Don't hardcode column counts unless content demands it.
  Equal heights within a row.
- **Keep the page's natural scroll.** Never turn a table into an inner scroll container to
  pin its header — let toolbars float above the rows once they would leave the screen, with
  the originals staying in place so nothing shifts.
- **Sliding pagination window.** A window that slides rather than grows, so buttons don't
  move under the pointer. Arrows keep their distance from the numbers.
- **Filters beside the table** where the viewport allows, floating over it when not — never
  above the table's own toolbar.

## Components & wording

- **Two call sites is a coincidence, three is a component.** Search before building; a
  near-duplicate is worse than a long file.
- **Wording never goes into a shared component.** It takes labels as props — the
  application owns the strings.
- **Every string goes through the translation layer**, English as the key.
- **UI text names things, it doesn't explain them.** Labels are terms, not phrases —
  "Slowest", not "Takes the longest". All UI text is product copy: if it wouldn't pass
  in a professional SaaS interface, rewrite it.

---

# Non-technical user mode

You are working with someone who is building an app but is not a developer. They think in
features, outcomes and what a visitor sees. Match that level.

This changes *what* you talk about, not *how* you work — modes, questions and planning stay
as they are.

- Describe what the app does differently, not what you changed. Leave out technical
  artefacts unless asked.
- When the user asks how something works, follow them. Technical detail is not forbidden,
  just not the default.
- Ask about goals and outcomes, never to make an implementation decision for you (which
  class to extract, which pointer to reset). Only ask what the user alone can answer — a
  credential, a design call, a business rule.
- Adding a dependency is not an implementation detail. Propose it, say what it buys, and
  wait for approval.
- Frame trade-offs in product terms: cost, speed, maintenance, what users will feel.
- Translate errors into symptoms, not exceptions.
- Verify by using the app like a visitor would, then describe what you saw. Never ask the
  user to read code or logs.
- When something is done, name one concrete thing they can try.

</laravel-boost-guidelines>
