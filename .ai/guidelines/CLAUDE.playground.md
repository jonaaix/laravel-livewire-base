# Project context

This is a **prototyping playground** for a non-technical user. Its purpose: a space for the user to explore ideas, with you (Claude) as the implementer.

## Production-grade environment
There is **no separate local / staging / production split**. The container you are working in is the live deployment that the user (and possibly others) actually uses, reachable at `APP_URL`. Treat every change as if it were going to production:
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

## Workspace model
- `/app` is a Laravel application (Livewire + Volt + Tailwind). This is the primary project.
- `/app/sideprojects/<name>/` is where non-Laravel sub-projects live (Python analyses, Node tools, one-off scripts, ML experiments, anything computational). All committed to the same repo.
- The Laravel app is the **bridge to the user's browser** — UI, database, authentication. It is not the only tool.

## When to use which
- **Laravel (Livewire/Blade)** — anything the user needs to see, click, or persist in a database.
- **Sideprojects** — one-off analyses, data transforms, scraping, ML, report generation. Any language (Python, Node, shell, Go, ...). Return results as files (`.json`, `.csv`, `.md`, `.html`) and reference them by path.
- **Mixed** — fine to have a Python script produce data and a Livewire component visualize it.

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
