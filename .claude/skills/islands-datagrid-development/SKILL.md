---
name: islands-datagrid-development
description: Implement a data table with aaix/laravel-islands-datagrid — the JSON endpoint contract, the useDataTable composable and the DataTable shell with its toolbar components. Use whenever a list view is added or an existing table gains search, filters, sorting, grouping, tabs or pagination.
---

# Implementing a Data Table

> **Starting a new list view?** Read
> [`base-datagrid-recipe.md`](./base-datagrid-recipe.md) first — a neutral,
> step-by-step blueprint for a datagrid that carries its own weight: search,
> sort, filters, tabs, pagination, column picker, saved views, card mode, empty
> state and skeleton. Copy the shape, then trim what the feature does not need.
>
> **Before adding any component,** consult the helpers/composables inventory in
> the sibling skill (`islands-development/helpers-index.md`) — this package's
> exports are listed there alongside the base helpers.

Two parts: an endpoint answering with rows and meta, and a Vue component
rendering it. How the surrounding page is routed and rendered is the host
application's business, not this package's.

## Endpoint

`useDataTable` sends every key of `defaults` as a query parameter and expects:

```json
{ "data": {
    "rows": [{ "id": 1 }],
    "meta": { "paginated": true, "total": 240, "page": 1, "perPage": 30, "lastPage": 8, "from": 1, "to": 30 }
} }
```

The `data` envelope is required. `paginated: false` hides the pagination bar,
`total`/`from`/`to` write the "1 – 30 of 240" line, `page` and `lastPage` drive
the page buttons; the page *size* comes from the `perPage` prop, not from
`meta`. Anything else inside `data` — tab counts, groups, aggregates — reaches
the component as `payload`; never make a second request.

Validate what the table sends, it comes from the URL:

```php
$sort = in_array($request->get('sort'), self::SORTABLE, true) ? $request->get('sort') : 'created_at';
$dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
$perPage = max(10, min(200, (int) $request->integer('perPage', 30)));
```

An unchecked `sort` goes straight into `orderBy`. Authorize on the endpoint
itself — it is directly reachable.

## Component

```vue
<script setup>
import { onMounted } from 'vue';
import { DataTable, SearchInput, useDataTable } from '@aaix/laravel-islands-datagrid/vue';
import InvoiceRow from './components/InvoiceRow.vue';

const props = defineProps({ dataUrl: String, initial: Object });

const DEFAULTS = { q: '', status: '', sort: 'created_at', dir: 'desc', page: 1, perPage: 30 };

const { state, rows, meta, loading, error, onSearchInput, clearSearch, setFilter, setSort, goToPage, setPerPage, reload, fetchData } =
    useDataTable(props.dataUrl, { defaults: DEFAULTS, initial: props.initial, filterKeys: ['status'] });

onMounted(() => fetchData());
</script>

<template>
    <DataTable
        :rows="rows" :meta="meta" :per-page="state.perPage" :col-count="4"
        :loading="loading" :error="error" error-message="Could not load invoices"
        @retry="reload()" @page-change="goToPage" @per-page-change="setPerPage"
    >
        <template #toolbar>
            <SearchInput :model-value="state.q" placeholder="Search…"
                @update:model-value="onSearchInput" @clear="clearSearch()" />
        </template>

        <template #head>
            <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Number</th>
            <!-- one <th> per column; the <tr> is supplied -->
        </template>

        <InvoiceRow v-for="row in rows" :key="row.id" :row="row" />

        <template #empty><p>No invoices found</p></template>
    </DataTable>
</template>
```

`DEFAULTS` is the contract — declare the full shape up front; a key added to
`state` later is missing from the request and the URL. `<DataTable>` is the
card only: page headers, tabs and side panels go around it. The row component's
root is a `<tr>`.

## API

**`useDataTable(dataUrl, options)`** — options: `defaults` (required), `initial`,
`clientOnly` (in state and URL, never sent), `filterKeys` (for
`activeFilterCount` / `resetFilters`), `filterParams` (sent as `filter[key]`
instead of a bare parameter), `searchKey` (`'q'`), `searchDelay` (`350`), `http`.

Returns `state`, `rows`, `meta`, `payload`, `loading`, `error`,
`activeFilterCount`, `fetchData`, `reload`, `syncUrl`, `onSearchInput`,
`clearSearch`, `setFilter`, `resetFilters`, `setSort`, `goToPage`, `setPerPage`.

`syncUrl()` writes state into the address bar without fetching — that is how a
client-only change (columns, card size) becomes deep-linkable.

**`DataTable`** — props `rows`, `meta`, `perPage`, `perPageOptions`, `colCount`,
`loading`, `error`, `errorMessage`, `skeletonRows`, `skeletonCellClass`,
`skeletonBarClass`, `floatingToolbar`, `floatingFooter`, `floatTopOffset`
(`12`), `floatBottomOffset` (`12`), `mode` (`'table'` | `'cards'`, default
`'table'`), `cardsMinWidth` (`'260px'`), `cardsGap` (`'0.75rem'`),
`cardSkeletonHeight` (`'240px'`), `cardSkeletonCount` (`8`), `fixedHeight`
(`false`); emits `retry`,
`page-change`, `per-page-change`; slots `toolbar`, `head`, default, `cards`,
`empty`. Extra classes land on the card. Set `--table-toolbar-h` on an
ancestor to pin the toolbar height.

**Cards mode:** setting `mode="cards"` renders a responsive auto-fit grid from
the `#cards` slot instead of the `<table>`. Toolbar, error banner and
pagination stay the same. The `#head` and default slots are ignored in that
mode; sorting moves into the toolbar via `SortMenu` (below).

`floatingToolbar` / `floatingFooter` lift toolbar and pagination into glass
pills once they would leave the screen, while the page keeps its own scroll.
Reach for them instead of turning the table into an inner scroll container —
but only for a bar that has something to keep on screen: `floatingFooter` says
nothing in a view that does not paginate, and a bar that would come up empty
stays down.

An application shell with a sticky header of its own sets `--table-float-top`
once (its height plus the gap); the bars read it, so no view has to know what
floats above it.

**`fixedHeight`** is the other answer to the same question, for a table that
lives in a region of a set size rather than on a page that scrolls. It gives the
card a height of its own and lets the rows scroll inside it: toolbar above,
column header stuck to the top of the scroll region, pagination below, all three
always on screen. `true` takes the room left below the card's own top edge (so
the table ends at the bottom of the window); a CSS length or a number of pixels
sets it outright, which is how two tables share one screen at `45vh` each. The
floating bars switch themselves off — a table that never leaves the screen has
nothing to lift. Off by default: a list normally scrolls with its page.

**`SearchInput`** — `modelValue`, `placeholder`, `clearLabel`; emits
`update:modelValue` (→ `onSearchInput`) and `clear` (→ `clearSearch`).

**`Combobox`** — `modelValue`, `options` (`{value: label}` or `[{value, label}]`),
`placeholder`, `searchPlaceholder`, `allLabel`, `emptyLabel`, `emptyValue`,
`searchValues`, `clearOption`, `maxOptions` (`60`, zero shows all),
`keepAncestors`, `menuWidth` (`288`), `menuHeight` (`240`); slots `option` and
`selected`. For long lists pass `fetchOptions: (query) => Promise<options>` with
`fetchDelay` and `loadingLabel` — then `options` holds only the preloaded first
page and the server answers each search. Pair it with `selectedLabel`, or a
deep-linked selection outside that page shows the placeholder.

**`variant` decides the look, and the default is a form field.** `field` stays
neutral, `filter` and `filter-card` tint a set value. A picker inside a form
must not look like an active filter, so only filters opt in.

**On a narrow screen the toolbar collapses rather than wraps** — one row of four
icons, the search field as a row of its own below it, every filter in the
`FilterPanel`. `base-datagrid-recipe.md` carries the shape under *The toolbar
with no room*; `useAutoMobileMode` switches the rows to list mode alongside it.

**`useViewWidth()`** — returns `root` and `rootStyle` for the island's outermost
element. It holds the one width every list in the app shares and publishes
`--table-toolbar-h` for the sticky toolbar and the floating bars. Bind it on
every list view; never write a `max-w-*` class instead. A view with a
`FilterPanel` uses `useFilterPanelDock`, which returns the same two and widens
them while the panel is docked.

For everything else the package exports — `FilterPanel`, `TreeSelect`,
`MultiSelect`, `OptionStrip`, `ColumnPicker`, `SortButton`, `SortMenu`,
`GridCard`, `Pagination`, `useViewProfiles`, `useAutoMobileMode`,
`useFilterPanelDock`, `useSelection`, `httpClient` / `sendJson`, the shipped
icon components — see the inventory in the sibling skill
(`islands-development/helpers-index.md`).

**`provideDatagrid({ t, locale })`** — optional; hands the package your
translator. Without it, English source strings and locale `en`.

## Saved views, on the PHP side

The three endpoints behind `ViewProfileMenu` ship with the package. Use the
trait instead of writing them:

```php
use Aaix\LaravelIslandsDatagrid\Concerns\HandlesViewProfiles;
use Aaix\LaravelIslandsDatagrid\ViewProfiles\ViewProfileStore;

class InvoicesIslandController extends Controller
{
    use HandlesViewProfiles;

    protected function viewProfiles(): ViewProfileStore
    {
        return InvoiceViewProfiles::store();
    }
}
```

The trait supplies `storeViewProfile`, `updateViewProfile` and
`destroyViewProfile`; point three routes at them and override
`authorizeViewProfiles()`. Ownership, the per-user limit and the public
reference are the store's business.

A `ViewProfileSchema` declares what a saved payload may carry — `text`, `id`,
`flag`, `tristate`, `date`, `choice`, `keyList`, each with its allowed values —
and `sanitize()` drops the rest, so a shared view cannot smuggle anything into
the next reader's table. Declare it there, not in the controller.

## Harder cases

- **Tabs** are a filter with a different shape: set `state.tab`, `state.page = 1`, then `reload()`.
- **Groups** come from `payload.groups` / `payload.grouped`; emit the header `<tr>` between rows in the default slot.
- **Mutually exclusive filters**: mutate `state` directly, then `reload({ resetPage: true })` **once**, so one request goes out.
- **View toggles** that must not reach the server go in `defaults` *and* `clientOnly`.

## Pitfalls

- **`emptyValue` must match the filter's default.** Defaults to `0`; pass `""`
  for string filters, or clearing writes `0` into state and the URL.
- **`colCount` must match the number of `<th>`**, conditional ones included — it
  drives the skeleton and empty-state `colspan`.
- **A filter change resets to page 1.** `setFilter` does it; when mutating
  `state` yourself, pass `reload({ resetPage: true })`.
- **Register the package with Tailwind** via `@source`, or its utility classes
  are dropped and the table renders with subtly wrong padding — looks like a
  layout bug, is a config bug.
- **Never call `fetch` directly.** The composable owns fetching, including the
  race guard and the non-2xx throw that bare `fetch` lacks. Custom headers:
  `createHttpClient({ headers })` as `options.http`.
- **Verify with content, not an empty table.** Clear buttons, badges and empty
  states only exist in the DOM once there is state.
- **A control in one table but not the other is a bug in waiting.** Move it into
  the package instead of preserving the difference.
- **`filterParams` decides how a filter travels.** Listed keys go out as
  `filter[key]=value` and land in the URL that way; without it the key is sent
  bare. Pick one shape per table and declare it up front.
