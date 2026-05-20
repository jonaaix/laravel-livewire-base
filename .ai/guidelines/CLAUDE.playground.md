# Project context

This is a **prototyping playground** for a non-technical user. Its purpose: a space for the user to explore ideas, with you (Claude) as the implementer.

## Workspace model
**The Laravel app at `/app` IS the playground.** It is meant to grow continuously — new routes, models, migrations, Livewire components, services are the default, not the exception. Most user features land here. Do not invent reasons to keep new work out of the Laravel app.

Three places code can live:
- **Core app** — shared infrastructure: auth, users, settings, layout, generic utilities. Stays small.
- **Internal modules** (`app/Modules/<Module>/`) — coherent feature areas with their own data model. Mandatory once a feature introduces its own tables.
- **Sideprojects** (`/app/sideprojects/<name>/`) — anything that doesn't live inside the Laravel app. Any language. Self-contained per folder.

## When to use which
- **Core app** — small additions with no own data model.
- **Internal module — DEFAULT once a feature has its own table(s).** Threshold is low: even a single dedicated table → module. Better small-module than scattered core code.
- **Sideproject** — one-shot non-interactive work (output: files), or anything that doesn't fit in the Laravel stack. Otherwise: module.
- **Mixed** — fine to have a sideproject produce data and a module consume/visualize it.

## Module structure
Once a feature gets its own table(s), it lives in `app/Modules/<ModuleName>/`.

- **Table prefix mandatory:** `<module>_<table>` (e.g. `gads_keywords`). Non-negotiable.
- **Folder:** `Models/`, `Livewire/`, `Services/`, `Jobs/`, `routes.php`, `resources/views/`.
- **Namespace:** `App\Modules\<Module>\…` (PSR-4 wildcard in root `composer.json`).
- **Routes:** module's own `routes.php`, mounted with path prefix matching module name.
- **Migrations:** stay in `database/migrations/`; filename + table carry the prefix.
- **Translations:** `lang/<locale>/<module>.php` → `__('gads.dashboard.title')`.
- **Views:** view namespace `<module>::` (e.g. `view('gads::dashboard')`).
- **No per-module composer.json / ServiceProvider.** A central `ModulesServiceProvider` auto-wires routes/views/translations/Livewire by scanning `app/Modules/*`.
- A module is **deletable as a unit:** drop prefixed tables + delete folder.

## Production-grade environment
The container you are working in is the live deployment the user (and possibly others) actually uses, reachable at `APP_URL`. There is **no separate local / staging / production split**.

**This does not mean "be conservative about extending the app."** Adding features, modules, routes, migrations is exactly what this environment exists for. What it means: **protect the user's data and the integrity of the live system.**

- **Never seed test users, fake records, lorem-ipsum content, or demo data into the database.** The DB belongs to the real user, not to you. The only legitimate exception is your own dedicated `Claude Bot` user (see "Authenticated browser testing").
- If you need realistic data to verify a feature visually (e.g. for a screenshot of a populated table), create the rows transiently, take the screenshot, and then **delete them immediately** in the same task. Do not leave them lying around for later cleanup.
- Never run `migrate:fresh`, `migrate:refresh`, `db:wipe`, or anything that drops data. If a destructive DB operation seems necessary, stop and ask the user first.
- Treat seeders and factories the same way: only commit ones that are safe to run on a production DB (idempotent, package data, etc.). Demo seeders belong in tests, not in `database/seeders/DatabaseSeeder`.
- Schema changes go through normal forward-only migrations. No editing already-applied migrations after the fact.

## App capabilities
This is a full-featured Laravel base project. Everything listed below is installed and ready to use — no setup required.

### UI & Frontend
- **Livewire 4 + Volt** — interactive UI as single-file components
- **Flux UI** — default component library (`<flux:*>`)
- **Tailwind CSS 4** — utility-first styling
- **Echo + Pusher.js** — client-side real-time event listening

### Backend & Infrastructure
- **Horizon** — queue management and monitoring dashboard
- **Reverb** — WebSocket server for real-time broadcasting, presence channels, live updates
- **Scout + Meilisearch** — full-text search (add `Searchable` trait to any model)
- **Fortify** — authentication (login, registration, 2FA, email verification)
- **Scheduled tasks** — Laravel scheduler via supercronic

### AI
- **laravel/ai** — first-party AI SDK. Text generation, image generation, agents, embeddings, structured output. Supports OpenAI, Anthropic, Gemini, and more.

### Data & Files
- **spatie/laravel-data** — typed DTOs and transformation
- **spatie/laravel-pdf + Browsershot** — render Blade views as PDFs
- **spatie/simple-excel** — Excel/CSV import and export
- **spatie/temporary-directory** — temp directories with auto-cleanup
- **Intervention Image** — image manipulation (resize, crop, watermark, format conversion)
- **aaix/laravel-patches** — data patches (schema-independent data migrations, run once)
- **webklex/laravel-imap** — read and process incoming emails
- **Pandoc** — universal document format conversion (Markdown, DOCX, HTML, LaTeX, EPUB, ...)

### Notifications
- **WebPush** — browser push notifications

### Observability & Maintenance
- **opcodesio/log-viewer** — web-based log viewer
- **aaix/laravel-easy-backups** — database and file backups

### Internationalization
- **aaix/eloquent-translatable** — model translations
- **outhebox/blade-flags** — country flag icons
- **aaix/laravel-countries** — A comprehensive country package

### Services (docker-compose)
- **MariaDB** — primary database (MySQL-compatible), host: `mariadb`
- **Redis** — cache, sessions, queues, host: `redis`
- **Meilisearch** — search engine, host: `meilisearch`

### MCP Servers
- **Playwright MCP** — browser automation, screenshots, scraping, PDF generation
- **Context7 MCP** — up-to-date library documentation lookup

## UI framework
- **Flux UI** is the default component library. Use `<flux:input>`, `<flux:button>`, `<flux:modal>`, etc. Check the Flux docs before building anything UI-related.
- If Flux does not have a suitable component, **choose based on effort**:
  - **Self-build** a custom Blade/Livewire component if it's a reasonable amount of work (simple form widgets, custom cards, layout pieces). Place it under `resources/views/components/` and match the Flux design language (same spacing, colors, radius). Reuse it.
  - **Reach for a plugin** when the component is a complex beast on its own — full-featured calendars, rich-text/WYSIWYG editors, advanced data tables, charts, file uploaders, drag-and-drop kanbans. Reinventing these is overkill; pick a well-maintained package and integrate it cleanly.
- Do NOT write raw Tailwind one-offs for UI elements that could be reused — extract into a component.
- Do NOT install other general-purpose UI libraries (Flowbite, daisyUI, etc.) — stay on Flux. Specialized plugins for complex widgets are fine.

## Frontend stack boundaries
- **Livewire + Volt** is the interactivity layer. Single-file Volt components are the fastest path — prefer them.
- **Plain Blade + Tailwind** for static pages with no interactivity.
- **Filament** — install on demand ONLY when the user explicitly asks for an admin panel or heavy CRUD management. Do not reach for it for public pages, dashboards, or custom flows — it is opinionated and fights non-CRUD use cases.
- **Vue / React / Inertia** — do not use unless the user explicitly asks.

## Output back to the user
- Data/reports → files in the workspace, reference by path. The user can retrieve them from the container.
- Interactive things → Livewire route, give the user the URL.
- Do NOT paste large file contents or long tool outputs into chat — files are cheaper and persistent.

## Dependencies
- Install freely with `composer`, `npm`, `apt-get`, `pip`, `uv`. Sudo is available without password.
- Isolate sideproject deps (Python venv, separate `package.json`) to avoid conflicts with the Laravel app.
- When adding a composer/npm package to the Laravel app, explain briefly why it's needed.
