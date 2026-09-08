---
title: Base Datagrid Recipe
scope: aaix/laravel-islands-datagrid
---

# A Good Base Datagrid

The shape of a list view that carries its own weight — search, sort, filters,
tabs, pagination, column picker, saved views, card mode, empty state, skeleton.
Not a template to paste; a checklist of the pieces and the order they belong in.
Consult the SKILL, the docs (`docs/quickstart.md`, `docs/toolbar.md`) and the helpers index for how each
piece works.

Use `Records` as the placeholder feature name below.

## Files

```text
app/Islands/Records/
├── Records.island.vue
├── RecordsIslandController.php   uses HandlesViewProfiles
├── RecordsProps.php              validates every URL-supplied value
├── Routes.php                    data · preferences · profiles.store · profiles.update
├── Queries/RecordsListQuery.php  SORTABLE · TABS · applyTabScope/Filters/Search
├── Presenters/RecordRowPresenter.php   the wire shape a row draws
├── State/RecordsPreferences.php  per-user allowlisted view state
├── State/RecordsViewProfiles.php ViewProfileStore + ViewProfileSchema
├── Support/{Columns,Sorts,Preferences}.js
└── Components/RecordRow.vue      root <tr>
```

Skip a folder when its role never grows past one file.

## Backend, the pieces

- **Controller.** `use HandlesViewProfiles`. One `authorizeAccess()` guard called
  from every action, including `authorizeViewProfiles()`. `data()` returns
  `['data' => $this->query->data($request)]`. `updatePreference()` validates
  `key ∈ Preferences::ALLOWED` and writes through `UserSetting::set()`.
- **Query.** Owns the whitelists: `const SORTABLE`, `const TABS`. `data()` validates
  `tab`/`sort`/`dir`/`perPage`/`page` against them, then calls
  `applyTabScope()`, `applyFilters()`, `applySearch()`, orders and paginates,
  guards `page > lastPage`, and returns `{ rows, meta, tabs, payload… }`. Tab
  counts come from **one** aggregate query, not one-per-tab.
- **Presenter.** Turns a model into what the wire carries — labels, colours,
  verdicts already resolved, so the row stays dumb.
- **Props.** Validates the same URL values a second time so a deep link opens the
  same view the URL describes. `TAB_KEYS`, `SORT_KEYS`, `PER_PAGE_OPTIONS` are
  class constants shared with the query. Returns
  `dataUrl`, `preferencesUrl`, `profilesUrl`, `profileUrl`, `profiles`,
  `activeProfile`, `preferences`, `tabs`, `filters`, `sortOptions`, `initial`.
- **State.** `Preferences` allowlists which per-user keys the frontend may
  write. `ViewProfiles` declares a `ViewProfileSchema` — `text`, `id`, `flag`,
  `choice`, `date`, `keyList` — so a shared view cannot smuggle anything into
  the next reader's table.

Miss the schema and a saved view becomes a stored-XSS shape. Not optional.

## Frontend

### DEFAULTS — the whole contract

```js
const DEFAULTS = {
    // Table state
    tab: 'active', q: '', sort: 'created_at', dir: 'desc', page: 1, perPage: 30,
    // Filters — one key per URL parameter
    status: '', category: 0, created_from: '', created_until: '',
    // View-only state (kept in URL, never sent)
    cols: DEFAULT_COLUMN_KEYS.join(','), mode: 'table', view: '',
};

const FILTER_PARAMS = ['q', 'status', 'category', 'created_from', 'created_until'];
const CLIENT_ONLY   = ['cols', 'mode', 'view'];
```

- **Never add a key to `state` afterwards** — it disappears from the request and
  the URL.
- **Pick one URL shape per table.** `filterParams` sends listed keys as
  `filter[key]=value`; unlisted keys go bare. Mixing them silently is the most
  common cause of "the deep link opens the wrong page."
- **`filterKeys` is the set counted by `activeFilterCount`** and cleared by
  `resetFilters()` — filters only; `sort`/`page`/`perPage` never.
- **`clientOnly` covers columns, view mode, expanded rows and the saved-view
  ref.** A key must appear in `defaults` *and* in `clientOnly`, or the endpoint
  sees it and the URL loses it.

### Wiring

```js
const table = useDataTable(props.dataUrl, {
    defaults: DEFAULTS,
    initial: props.initial,
    filterParams: FILTER_PARAMS,
    filterKeys: ['status', 'category', 'created_from', 'created_until'],
    clientOnly: CLIENT_ONLY,
});
```

Providers at the island root, once: `provideIcons(ICONS)`,
`provideDatagrid({ t, locale })`, `provideConfirm()`, `provideToasts()`.
Mount `<ConfirmHost />` and `<ToastHost />` in the template.

### Saved views

```js
const views = useViewProfiles({
    state, defaults: DEFAULTS,
    keys: [...FILTER_PARAMS, 'sort', 'dir', 'perPage', 'cols'],
    storeUrl: props.profilesUrl,
    profileUrl: props.profileUrl,
    initial: props.profiles,
    shared: props.activeProfile,
    plain: { cols: BASELINE_COLUMNS },   // reader's own baseline, not system default
    apply: () => reload({ resetPage: true }),
    onError: (m) => toast.danger(m || t('Could not save this view')),
});
```

`plain` is what a "reset view" lands on — a remembered column choice, not the
package's neutral defaults.

### Template shape

**The outermost element of the island carries the view width — always.**

```js
const { root, rootStyle } = useViewWidth();
```
```html
<div ref="root" class="mx-auto w-full" :style="rootStyle">
```

`useViewWidth()` owns the one maximum every list in the app shares and publishes
`--table-toolbar-h`, which the sticky toolbar, a docked panel and the floating
bars all measure against. A view that writes its own `max-w-*` class or a literal
width runs wider than every other list and leaves the toolbar without a height.

A view with a `FilterPanel` calls `useFilterPanelDock` instead: it returns the
same `root` and `rootStyle`, and widens them while the panel is docked.

Around the `<DataTable>`: page header, `<Tabs>` (tab is a filter, not a column),
`<FilterPanel>` beside it via `useFilterPanelDock`.

**Layout conventions — the same across every list in the app:**

- **Search is always there**, first in the toolbar. No feature turns it off,
  even if the endpoint ignores an empty `q`.
- **Standard filters live in the toolbar** — the two to four that almost every
  reader reaches for, rendered as `Combobox`es right next to the search. They
  reflect the way the list is scanned day-to-day.
- **Advanced / dimensional / rarely-used filters live in the `FilterPanel`** —
  date ranges, count thresholds, cross-cutting flags, everything that would
  make the toolbar too dense. The toolbar carries a filter-toggle
  `IconButton` with an `activeFilterCount` badge to open the panel.
- **The `FilterPanel` docks on the right** of the `<DataTable>` (the table
  comes first in the flex row, the panel after it), so the toolbar and its
  primary filters stay on the reader's natural left. `useFilterPanelDock` keeps
  it docked while there is room and lifts it into an overlay otherwise.
- **View profiles, sort menu, column picker, filter toggle, view-mode strip
  cluster on the right** of the toolbar, in that order — so they land in the
  same spot in every table and a reader's muscle memory carries over. The
  view menu opens the cluster and carries the `ml-auto`; the sort menu sits
  to its right.

Toolbar slot, in order:
`SearchInput` · filter `Combobox`es · `ViewProfileMenu` ·
`SortMenu` (cards mode only) · `ColumnPicker` (table mode only) · filter-toggle
`IconButton` with an `activeFilterCount` badge · `OptionStrip` for
table/cards mode.

### The toolbar with no room

That row is seven or eight controls wide. Given 1536px it reads as one line;
given 390px it wraps into three stacked rows and pushes the first record below
the fold — the reader now scrolls past the controls to reach what they came
for. Narrow, the toolbar collapses to **one row of four icons**: a search
toggle on the left, and the cluster that was already icons on the right.
Everything that is not an icon leaves.

**Search collapses to its icon and comes back as a row of its own.**

```html
<IconButton
    class="sm:hidden" :label="t('Search')" size="lg"
    :tone="searchOpen ? 'active' : 'quiet'" :tooltip="false"
    @click="toggleSearch">
    <IconSearch />
</IconButton>

<SearchInput
    ref="searchInputRef"
    class="max-sm:order-1 max-sm:basis-full"
    :class="!searchOpen && 'max-sm:hidden'"
    :model-value="state.q" @update:model-value="onSearchInput" @clear="clearSearch()" />
```

```js
function toggleSearch() {
    searchOpen.value = !searchOpen.value;

    if (searchOpen.value) {
        nextTick(() => searchInputRef.value?.focus());
    }
}
```

`basis-full` gives the field the whole row and `order-1` puts that row *under*
the icons, so the controls keep their place instead of being pushed down by a
field that appeared above them. The focus on open saves the second tap, and the
toggle tints while the field is out — otherwise the toolbar looks like it grew
by itself.

**The standard filters go to the panel; they do not wrap.** Every filter
`Combobox` and the quick-filter strip carry `max-sm:hidden`. A toolbar that
wraps its filters onto two more rows has not been made mobile, it has been made
taller.

**The panel becomes the only filter surface, so it gains tabs.** Docked, the
panel holds the advanced filters and the toolbar holds the standard ones.
Narrow, both sets live in the panel and have to be told apart:

```js
const filterTab = ref('standard');
const activeFilterTab = computed(() => (isNarrow.value ? filterTab.value : 'advanced'));
```
```html
<FilterPanel …>
    <template v-if="isNarrow" #tabs>
        <Tabs :items="filterTabs" :model-value="filterTab" @update:model-value="filterTab = $event" />
    </template>
```

The strip exists only while the panel carries both sets. On the docked panel it
would name a distinction that is not there.

**The view-mode strip goes** (`max-sm:hidden`). `useAutoMobileMode` has already
decided the mode from the screen; a switch beside it offers a choice the view
overrules on the next resize.

**Sort, views, columns and the filter toggle stay** — they are already icons at
`size="lg"`, the 36px every toolbar control shares, and four of them fit a
390px row with the search toggle. The filter badge keeps a
`ring-2 ring-white dark:ring-gray-900`: a count that sits half over its icon and
half over the toolbar has to separate itself from both.

**The rows bleed.** `-mx-4` on the `<DataTable>` plus `:bleed="true"` drop the
card's rounding, ring and side padding, so a 390px screen spends all 390 on
content.

**The tab strip above the table loses its words, not its tabs.** Four labelled
scopes with counts run past a phone screen. Letting the strip scroll is the
documented fallback, but it hides how many scopes there are and can leave the
chosen one off-screen. Give the word to the chosen tab and let the rest keep
their icon and their count — all of them stay on screen, and switching is still
one tap:

```html
<span :class="narrow && state.tab !== tab.key ? 'sr-only' : ''">{{ tab.label }}</span>
```

`sr-only` rather than `v-if`, so the word is still there for anything reading
the page aloud, and — being out of flow — it does not leave a gap behind.
Tighten the tab padding to `px-2` in the same breath; at `px-3` the widest scope
pushes the last counter past the edge. This holds to about five scopes; beyond
that the strip belongs in a menu that names the current one.

**What counts as "narrow" is one number, not two.** `max-sm:` utilities see the
viewport at 640px; `useAutoMobileMode` sees it at 767px; `useViewWidth()`
returns `availableWidth`, the room the view actually has — which is the honest
measure, because a folded-out sidebar and a zoomed page take room away exactly
as a smaller screen does. Prefer that one and drive the template with `v-if` and
`:class`, the same way a dropped column is decided. Where `max-sm:` is used
anyway, hold the JS breakpoint to the same number: a window between the two
hands out list rows with a desktop toolbar sitting above them.

Head slot: one `<th>` per visible column; sortable ones wrap `<SortButton>`.

Body: `<RecordRow v-for="row in rows" :row="row" :columns="visibleColumns" />`.

`#cards`, `#empty`, `#error-message` slots. `col-count` **must** match the
visible-column count including conditional ones — it drives skeleton and empty
`colspan`.

### Small pieces the template refers to

- `visibleColumns` derived from `state.cols` via `resolveColumnKeys()` against
  the column registry from `Support/Columns.js`.
- `colCount` = sum of `column.span` over visibleColumns.
- `TAB_ITEMS` built from `props.tabs` counts.
- `MODE_OPTIONS` = `[{ value: 'table', icon: IconModeTable }, { value: 'cards',
  icon: IconModeCards }]` for `<OptionStrip variant="segmented">`.
- `setTab(tab)`: sets `state.tab`, resets `state.page = 1`, then `reload()`.
- `setColumns(keys)`: writes `state.cols`, `syncUrl()`, `sendJson(preferencesUrl,
  { key: 'columns', value: state.cols })`.
- `setMode(mode)`: same shape, key `'mode'`.
- `onMounted(() => fetchData())`.

## Rules of thumb

- **Every URL value is validated twice** — once in `Props::build()` for the
  initial state, once in the query for each request. The two lists have to
  name the same options.
- **A filter has four homes** to be complete: `DEFAULTS`, `filterKeys`,
  `filterParams` (if it travels as `filter[]`), and a validated `where()` in
  the query. Miss one and it stops working; miss another and it works too much.
- **Tabs are a filter with a different shape.** Mutate `state.tab`, reset
  `state.page`, then `reload()`.
- **Card mode needs `SortMenu`** in the toolbar — header `<th>`s are ignored
  there.
- **Preferences persist per user, not per browser.** `localStorage` may cache,
  the server owns the truth (`preferencesUrl` in the props).
- **Ship the empty state and the skeleton on day one.** They are not polish; a
  filter that matched nothing needs to say so.
- **The width comes from a helper, never from a class.** `useViewWidth()` (or
  `useFilterPanelDock`, which wraps it) on the island's root element. A list
  without a filter panel needs it just as much as one with.
- **The toolbar is the first thing that breaks with no room.** One row of four
  icons, filters in the panel, the search field as its own row below — not the
  same eight controls wrapped onto three lines. See *The toolbar with no room*.
- **A floating bar is for a view that has one worth floating.** `floating-footer`
  belongs to a view that paginates; a view that answers in one go floats
  nothing. Switching a bar on because the recipe names it is how an empty pill
  ends up over the rows.

## What already lives in the package

Do not rewrite these — see `helpers-index.md` for the full inventory:

`DataTable`, `useDataTable`, `SearchInput`, `Combobox`/`MultiSelect`/`TreeSelect`,
`OptionStrip`, `SortButton`/`SortMenu`, `ColumnPicker`, `useViewWidth`,
`FilterPanel` + `useFilterPanelDock`, `ViewProfileMenu` + `useViewProfiles`,
`useAutoMobileMode`,
`GridCard`/`GridCardMedia`, `SelectionBox` + `useSelection`, `httpClient` /
`sendJson`; PHP: `HandlesViewProfiles`, `ViewProfileStore`, `ViewProfileSchema`.
