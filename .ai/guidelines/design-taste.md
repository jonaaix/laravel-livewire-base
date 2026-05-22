# Design Taste

Visual baseline for any new UI. Tailwind class names are used as shorthand
for spacing/color/size decisions — values matter, syntax adapts.

## Snappy UI — JS first

UI must feel instant. Page turns, full reloads, and visible server
roundtrips are bugs, not defaults.

- **JS first.** Interactions (toggle, filter, sort, expand, tab switch,
  inline edit) run client-side. Alpine.js is default; standalone Vue island
  when Alpine becomes unreadable. Server only for actual mutations or data
  not yet in the DOM.
- **Optimistic UI.** Mutations flip UI state immediately; server confirms
  in background. On failure: rollback + toast. Never spinner-until-200.
- **Lazy loading.** Above-the-fold first; rest via `wire:lazy`,
  `loading="lazy"`, or IntersectionObserver. Long lists → virtual scroll or
  paginate-on-scroll.
- **Skeletons over spinners.** Show layout shape, not a centered loader —
  prevents CLS, feels faster.
- **Preload next.** Hover-prefetch on links/pagination (`wire:navigate`
  default, leave on).
- **No page turns for in-place updates.** Filters, sort, tabs → partial or
  client-side. Full reload only when route truly changes content.
- **Pre-render variants, toggle visibility.** DOM cost beats roundtrip
  latency. Persist tab/expand state to `localStorage` scoped per-record.
- **Mixed is fine.** Tabs swap client-side; "Show all (N)" hits server
  because it changes the query limit.

## Baseline

Tailwind UI-adapted styling, professional business schema — subtle,
data-dense, not flashy.

## Color

- **Derive, don't hardcode.** Multiple accents → HSL hue shifts off primary
  (+60°, +120°, +180°, muted). Hex literals only as sentinels for a theme
  layer to replace.
- Neutrals stay true gray for text/borders/disabled. Don't tint every gray.
- Status semantics (red=error, green=success) override derived palettes.

## Containers & cards

- Corners: `rounded-xl` cards, `rounded-md` pills, `rounded-lg` icon boxes.
- Cards: `bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5
  dark:ring-white/10`. Ring, not heavy shadow.
- Padding: `p-3` sub-cards, `p-5` dashboard cards. More feels wasteful.

## Typography & density

- Data-dense views: `text-sm`/`text-xs`, `py-0.5`–`py-1.5` rows. Info beats
  whitespace.
- Long user strings: truncate with ellipsis, never wrap.

## Icons

- Always pair an icon with a heading or stat value.
- **Prominent** (default): 32×32 box, 16×16 SVG, `rounded-lg`,
  `bg-primary-50 dark:bg-primary-900/30`.
- **Compact** (dense rows only): 24×24 box, 14×14 SVG, same tint.
- Never a bare SVG — the tinted box gives consistency.

## Card headers

Tinted icon box + title left, count badge + action slot right, bottom
border edge to edge. Wrap as a reusable component.

If the card uses `p-5`, the header cancels it with `-mx-5 -mt-5` so the
border spans full width.

- **Action slot:** whole-card actions (create, reset, settings). Max 3–4.
- **Filter row below:** search, selects, year pickers. Filters need
  breathing room — don't cram into action slot.

## Data tables

Plain `<table>` over `<flux:table>` — header tint and divider control
matter more.

- Container: `overflow-hidden rounded-xl ring-1 ring-zinc-950/5
  dark:ring-white/10`.
- Header: `bg-zinc-50 dark:bg-zinc-800/50`, `text-zinc-500 font-medium`,
  cells `px-4 py-2.5`.
- Body: `bg-white dark:bg-zinc-900`, `tbody` gets `divide-y divide-zinc-200
  dark:divide-zinc-700`. No stripes.
- Cells `px-4 py-3 text-sm`; secondary columns `text-zinc-600
  dark:text-zinc-400`. First column `font-medium`.
- Inline one-bit signals next to the data (verified icon next to email),
  not a dedicated column.
- Relative dates with absolute in `title`. `—` for null, not "Never".
- Actions rightmost: ghost `xs` icon+label. Hide destructive on self-rows.
- Empty state: single row, `colspan` full, muted center, `py-8`.
- Destructive: `wire:confirm` for single-step, modal only when prompt
  needs body content.

## Settings forms

- **Wrap every control in a container.** A lone control without boundary
  reads as decoration.
- **Label beside control in sparse layouts**, stacked only in dense forms.
- Group related settings in one container with dividers; separate
  unrelated ones into distinct containers.

## Interactive controls

- Compact dropdowns (active + chevron) over button rows when options > 3.
- Two-tier styling:
  - **Primary:** tinted — `bg-primary-100 text-primary-800
    hover:bg-primary-200`. Not saturated `primary-500`.
  - **Secondary:** neutral — `bg-gray-100 text-gray-600 hover:bg-gray-200`.
- Saturated `primary-500` only for one-off CTAs (submit, confirm).
- Micro-controls (period pills, badges): `px-1.5 py-0.5 text-[10px]
  font-medium rounded`.

### Card header controls (unified)

Buttons, selects, search inputs share one base so they align:
`bg-gray-50 dark:bg-gray-800`, `border-gray-200 dark:border-gray-700`,
`rounded-lg`, `px-2 py-1 text-xs`, primary focus ring. Buttons get hover
tint; inputs/selects don't.

- "Create new" buttons always plus-icon (verb signal, noun-independent).
- Search input: magnifier icon absolute-positioned inside, padded left.
  Never a separate icon button.
- Reset-filters: icon-only, neutral gray, only when ≥1 filter active.

## Layout & grids

- **Auto-fit over fixed grids:** `repeat(auto-fit, minmax(<min>, 1fr))`.
  Don't hardcode column counts unless content demands it.
- Same-row items equal height: `h-full` + sensible `max-h-[Npx]` on card
  root.
- Internal scroll belongs on the inner region. Header + controls stay
  fixed, content scrolls.

## Charts

- Fixed pixel height, not `100%` (latter triggers flex feedback loops).
- Primary series = derived primary; additional series from derived
  palette. Never hardcode violet/green/etc.
- Grid adapts to dark mode, legend top, labels inherit page font.

## Read-only vs. CRUD pages

- **Read-only analytics/overview:** single JSON endpoint, client state,
  `localStorage` stale-while-revalidate. Don't hydrate widget-by-widget.
- **CRUD:** use the stack's admin tool — handles listing, filtering,
  forms, authz better than rolling your own.
- Cache expensive computations backend (Redis until end-of-day) and cache
  display-ready payloads frontend.
