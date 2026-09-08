---
name: islands-development
description: Build a feature view with aaix/laravel-islands — scaffolding an island, where each kind of file belongs, the props contract, guarding its endpoints, real-time subscriptions and the shipped Vue helpers. Use whenever an island is created, moved, renamed or extended, or when a Blade/Filament page should host a Vue component.
---

# Working on an Island

> **Building a view that is not a datagrid** — settings, statistics, a form, a
> small dashboard? Read [`free-view-recipe.md`](./free-view-recipe.md) first: the
> shape that keeps free views from drifting apart, and the four layout faults that
> only show up in a screenshot.
>
> **Before writing any markup,** open [`helpers-index.md`](./helpers-index.md) — a
> full inventory of the components, composables and hosts shipped by
> `@aaix/laravel-islands` and `@aaix/laravel-islands-datagrid`. The single most
> common avoidable diff is a hand-rolled modal, tooltip, sort menu or toolbar
> layer that already exists behind a one-line import.

An island is a feature folder that owns both halves of a view: the endpoints it
reads and writes, and the Vue component that draws them. Filament or Blade only
provides the page it is mounted into.

## Scaffolding

```bash
php artisan make:island Products
```

Never hand-create these files. The generated folder names its own
responsibilities, and that layout is the contract:

```text
app/Islands/Products/
├── Products.island.vue            the island itself
├── ProductsIslandController.php   the only door: validate, guard, hand over
├── ProductsProps.php              what it starts up with
├── Routes.php                     its endpoints
├── Page.blade.php                 the mount point, if it owns a page
├── Queries/                       reading
├── Writers/                       writing
├── Presenters/                    a record turned into what the wire carries
├── State/                         what a user remembers: preferences, saved views
├── Support/                       the rest: URL lists, column definitions, helpers
└── Components/                    the island's own Vue components
```

- **A folder from the first file on.** The point is that the next reader can
  guess where something is, so `Queries/` with one query beats a query at the
  root.
- **Nothing new at the root.** Those five files are the wiring; everything else
  belongs to a role.
- **Subfolders are namespace segments:** `Queries/ProductsQuery.php` is
  `App\Islands\Products\Queries\ProductsQuery`. Moving a class means editing its
  `namespace` line and importing it where it is used.
- **An exception lives with whoever throws it** — no folder for one file.
- **`Support/` holds both languages**, PHP helpers and loose `.js` modules;
  `Components/` is `.vue` only.

## Endpoints

`Routes.php` in the island directory is auto-loaded into a group of its own.
Prefix, name prefix and base middleware come from `config/laravel-islands.php`;
the island's kebab-cased folder name completes them — `ShopOrders` answers under
`admin/islands/shop-orders/…` named `islands.shop-orders.*`.

**The file name is fixed.** The package looks for exactly `Routes.php` per
island directory (`routes.file` renames it globally, never per island). It
cannot move into a subfolder.

**Authorization is the application's, not the package's.** The configured
middleware is the only thing an island route brings along; who may read or write
*this* island's data has to be decided in its controller, which is why the stub
carries an `authorizeAccess()` for every endpoint to call:

```php
private function authorizeAccess(): void
{
    if (! (Auth::user()?->hasPermission('view.inventory.products') ?? false)) {
        throw new AccessDeniedHttpException();
    }
}
```

## Props

`<Island>Props` builds the payload the island starts with. Hand over
route-generated URLs, never paths assembled in the browser:

```php
return [
    'dataUrl' => route('islands.products.data'),
    'updateUrl' => route('islands.products.update', ['product' => '__ID__']),
    'initial' => $this->initial($request),
];
```

`__ID__` is replaced client-side. Put the state the view should open with into
`initial`, so a deep link renders the same view the URL describes.

## Mounting

```blade
<x-island name="Products" :props="$islandProps" :subscribe="$product" />
```

`name` resolves to the entry component, `:props` is serialized into it,
`:subscribe` takes a model or a `['key' => $model]` map — subscribed models are
serialized into the props and their broadcast channel resolved for the
frontend. `adapter` defaults to `vue`.

`startVueIslands(registry)` resolves `name` against the registry the host hands
it — `./islands/<name>.island.vue`, or `./<name>.island.vue`. Whatever an
application normalises its glob keys to decides how deep a `name` may reach; a
component the registry does not hold is reported as a console warning and the
element stays empty.

## In the component

```js
import { useIsland, useTranslations } from '@aaix/laravel-islands/vue';

const island = useIsland();       // { props, _island }
const props = island.props;
const { t } = useTranslations();  // English source strings are the keys
```

`t()` reads the application's JSON translation lines for the current locale,
shipped with the payload — a string that never passes through `t()` can never
be translated. `php artisan islands:translations` collects every `t('…')` key
from the islands and appends the missing ones to `lang/{locale}.json` (key as
value, `--locale=`, `--dry-run`); run it after adding strings instead of editing
the file by hand.

Every composable, helper component and host shipped by the package (plus the
datagrid additions) is listed in [`helpers-index.md`](./helpers-index.md).
Read it before writing markup — tables, filters and pagination live in the
sibling `islands-datagrid-development` skill.

## Stubs

Publish them to adjust the house style; a published stub wins over the
package's own:

```bash
php artisan vendor:publish --tag=laravel-islands-stubs
```
