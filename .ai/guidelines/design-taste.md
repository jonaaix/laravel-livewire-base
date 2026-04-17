# Design Taste

General visual preferences the project owner has approved. Apply these broadly
to any new UI — admin panel, settings pages, custom forms, analytics views,
etc. These are rules of thumb, not absolutes; use judgement when a specific
context calls for something different.

Utility-class names below use Tailwind vocabulary because it's the most
compact way to express spacing, color, and size decisions. Translate to your
styling tool of choice — the *values* (sizes, hues, rhythm) are what matter,
not the syntax.

## Baseline

Tailwind UI-adapted styling in a professional business schema — subtle,
data-dense, not flashy.

## Color

- **Derive, don't hardcode.** When multiple accent colors are needed, derive
  them from the active primary color via HSL hue shifts (+60°, +120°, +180°,
  and a desaturated muted variant). Hardcoded hex values are acceptable only
  as **sentinels** that a theme layer later replaces with derived values.
- Keep neutrals (true gray) for text, borders, disabled states. Don't tint
  every gray with primary — it gets noisy.
- Status semantics (red = error, green = success) override derived palettes.
  Don't let a green primary break "error is red."

## Containers & cards

- Rounded corners: `rounded-xl` for cards, `rounded-md` for small pills,
  `rounded-lg` for icon boxes.
- Cards: white background + subtle ring, not heavy shadows —
  `bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10`.
- Compact internal padding — `p-3` for sub-cards, `p-5` for full dashboard
  cards. More padding feels wasteful.

## Typography & density

- Data-dense views (tables, lists): prefer small text (`text-sm` or `text-xs`)
  with tight vertical rhythm (`py-0.5` to `py-1.5` per row) — more info visible
  without scrolling beats generous spacing.
- Avoid large vertical padding inside cards. Users should see content, not
  whitespace.
- Long user-generated strings: truncate with ellipsis rather than wrap.
  Layout integrity > full text visibility.

## Icons

- Always pair an icon with a heading or stat value — icons anchor the eye and
  make scanning faster.
- Two standard sizes:
  - **Prominent** (stat numbers, card headers): 32×32 box, 16×16 SVG,
    `rounded-lg`, tinted primary background (`bg-primary-50
    dark:bg-primary-900/30`). This is the default — use it unless you have a
    reason to go smaller.
  - **Compact** (dense table rows, micro headers): 24×24 box, 14×14 SVG, same
    tint. Exception, not the rule.
- Never a bare SVG — the tinted box gives consistency.

## Card headers

Every dashboard card uses the same header shape: a tinted icon box + title
on the left, optional count badge + action slot on the right, separated by a
bottom border that spans edge to edge of the card.

Implementation note: if the card uses internal padding (e.g. `p-5`), the
header needs to cancel that padding with negative margins (`-mx-5 -mt-5`) so
the border-b spans the full width. Wrap the pattern in a reusable component
so every card calls it the same way.

Header content belongs in one of two places:

- **Header row (action slot)**: actions that belong to the whole card —
  create buttons, reset, a settings menu. Keep it to 3–4 items.
- **Dedicated filter row below**: search inputs, type/period selects, year
  pickers. Filters need breathing room; don't cram them into the header
  action slot unless the card is known to be wide enough (it usually isn't —
  when it gets narrow, filters wrap and look broken).

## Interactive controls

- Prefer **compact dropdowns** (active-only + chevron) over full button rows
  when the list of options is > 3. Button rows are visually loud.
- Two-tier action styling:
  - **Primary action** or attention-worthy: tinted primary —
    `bg-primary-100 text-primary-800 hover:bg-primary-200` (light tint, not
    the saturated `primary-500`).
  - **Secondary action** or repeat use: neutral gray —
    `bg-gray-100 text-gray-600 hover:bg-gray-200`.
- Pure saturated `primary-500` backgrounds on controls feel too loud for
  persistent UI. Reserve for one-off CTAs (submit buttons, confirmations).
- Micro-controls (pill-style period switchers, badges): `px-1.5 py-0.5
  text-[10px] font-medium rounded`.

### Card header controls (unified style)

Inside a card header, buttons, selects, and search inputs share one base
style so they line up:

- Background `bg-gray-50` (dark `bg-gray-800`), border `border-gray-200`
  (dark `border-gray-700`), `rounded-lg`, `px-2 py-1 text-xs`.
- Focus ring uses primary.
- Buttons add a hover tint (`hover:bg-gray-100` / dark `hover:bg-gray-700`);
  inputs/selects don't.

Apply consistently:

- Use a plus icon for every "create new" button — the icon signals the verb,
  not the noun (Cart, Project, Invoice all use plus).
- Search input: magnifier icon absolute-positioned inside the input on the
  left, input padded left to make room. Never a separate icon button.
- Reset-filters button: icon-only, neutral gray, only render when at least
  one filter is active.

## Layout & grids

- **Auto-fit over fixed grids.** For card grids, use `grid-template-columns:
  repeat(auto-fit, minmax(<min>, 1fr))` so cards reflow with viewport width.
  Don't hardcode "3 columns on xl, 2 on md" unless content genuinely demands
  it.
- Items in the same row should have equal heights — use `h-full` on the child
  + a sensible `max-h-[Npx]` on the card root to prevent runaway growth.
- Internal scrolling (tables within cards) belongs on the **inner scrollable
  region**, not the card itself. Header + controls stay fixed, only the
  content scrolls.

## Charts

- Fixed pixel height for charts, not `100%` — the latter triggers feedback
  loops with flex containers.
- Primary series uses the derived primary; additional series pull from the
  derived palette (see "Color"). Never hardcode violet/green/etc. beyond the
  sentinels.
- Subtle themes: grid border adapts to dark mode, legend stays top, labels
  inherit the page font.

## Read-only vs. CRUD pages

- **Read-only analytics/overview pages**: prefer a single JSON endpoint with
  client-side state and `localStorage` stale-while-revalidate. Don't hydrate
  widget-by-widget; don't recompute on every visit.
- **CRUD resources**: use whatever admin-panel tool the stack provides — it
  handles listing, filtering, forms, authorization better than rolling your
  own.
- Cache expensive computations at the backend (e.g. Redis until end-of-day)
  and cache display-ready payloads at the frontend.

## Client-side state vs. server roundtrips

- For **pure UI state** (tab switching, accordion toggling, dropdown
  open/close, show/hide of preloaded content), flip visibility on the client.
  A server roundtrip for "switch which div is visible" feels laggy and
  wastes server time.
- Pre-render all variants in the DOM and toggle visibility client-side. The
  DOM size cost is almost always cheaper than the latency of a round trip.
- Persist tab/expand state to `localStorage` (scoped per-record so different
  entities don't share state) so reloads don't lose context.
- Reserve server roundtrips for things that genuinely need fresh data:
  re-querying with new filters, mutating data, validating forms.
- Mixed pattern is often best: client handles cheap UI ops instantly, server
  handles expensive data ops. Example — tabs swap client-side, but "Show all
  (N)" goes through the server because it changes the DB-query limit.
