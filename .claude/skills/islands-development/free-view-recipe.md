# Recipe: a free island view

A *free* view is any island that is not a datagrid: a settings page, a statistics
page, a form, a small dashboard. The datagrid recipe does not apply — there is no
shell to fill, so the view decides its own layout. That freedom is where views
drift apart, and this recipe is the shape that keeps them together.

Copy it, then drop what the feature does not need.

## 1 The skeleton

```text
app/Islands/InboundStats/
├── InboundStats.island.vue            the view
├── InboundStatsIslandController.php   one method per action, each guarded
├── InboundStatsProps.php              what it starts up with, including its endpoints
├── Routes.php                         its endpoints
├── Page.blade.php                     the mount point
└── Components/                        the view's own pieces
```

```vue
<script setup>
import { useViewWidth } from '@aaix/laravel-islands/vue';

const { root, rootStyle } = useViewWidth();
</script>

<template>
    <div ref="root" class="island-view inbound-stats mx-auto w-full space-y-4" :style="rootStyle">
        <h1>{{ t('Inbound performance') }}</h1>

        <div class="island-card">
            <h2>{{ t('Per person') }}</h2>
            <p class="max-w-prose">{{ t('One sentence saying what this answers.') }}</p>
            …
        </div>
    </div>
</template>
```

Two classes on the root: **the shared view class** the host application styles,
and **the view's own name** for the rare exception. Everything below is plain
elements — no repeated font utilities.

**The width comes from `useViewWidth()`, never from a `max-w-*` class.** Take the
maximum it gives; switching between two views then does not move the content under
the reader. A free view needs it exactly as much as a list does — a number written
on the root makes this one view wider than every other, and nothing says by how
much or why. A view that genuinely needs more passes `baseWidth`, so the deviation
has a name and a place instead of hiding in a utility class; today only the
products list does. `useFilterPanelDock` returns the same `root` and `rootStyle`
for a view that has a filter panel.

## 2 Open with data, not with a spinner

A free view usually knows everything it needs at mount. Build the first payload
in the props class and hand it over; fetch only what a later interaction asks
for.

```php
public function build(Request $request): array
{
    return [
        'endpoints' => ['save' => route('islands.app-settings.inbound-gauge')],
        'inboundGauge' => $this->inboundGauge(),
    ];
}
```

The endpoint list travels in the props. A view never builds a path of its own.

## 3 Type belongs to the scope, not to the element

The single most common reason a hand-built view looks restless: every block
brings its own font size. Each one is defensible, the set is not.

Decide it once, in the host application's theme, scoped to the view class,
wrapped in `:where()` and **inside the base layer**:

```css
@layer base {
    :where(.island-view) h1 { font-size: 1.5rem; font-weight: 700; }
    :where(.island-view) h2 { font-size: 0.875rem; font-weight: 600; }
    :where(.island-view) p  { font-size: 0.875rem; color: … }
    :where(.island-view) th { font-size: 0.75rem; text-transform: uppercase; … }
    :where(.island-view) td { font-size: 0.875rem; … }
    :where(.island-view .island-card) { border-radius: 0.75rem; padding: 1rem; … }
}
```

Both halves matter. `:where()` drops the specificity so a utility class on one
element outranks the scope — but specificity only decides between rules in the
same layer, and an unlayered rule beats every utility whatever it looks like.
Left outside a layer, the scope silently swallows the `p-2` on the one element
that needed it. Note also that a scope written as
`:where(.island-view) .island-card` keeps the class's own specificity; wrap the
whole selector to give it up.

The same scope carries the view's own top and bottom room, so a heading never
sits against the topbar, and the card look, so no view invents its own.

**The page title is the application's title, not the view's.** Take the size,
weight and the icon box beside it from a list view that already ships — a free
view that scales its own heading reads as a page from another product, whether
it lands too big or too small.

## 4 Layout rules that only show up in a screenshot

- **A grouping card takes the full width it is given.** A card that ends early
  reads as a column and the eye starts looking for the second one. Cap the
  *content* inside it if a form reads better narrow — never the card.
- **A control that appears or disappears must not move what is below it.** Give
  the two states the same slot: a grid with fixed columns whose second cell holds
  a field in one mode and a figure in the other. Measure it — the button below
  must move by 0 px.
- **Captions are inline.** `FieldCaption` is a `<span>`; in a row it stays on the
  line of the control it names. In a form, wrap it so the label sits above its
  field. In a toolbar, leave it inline on purpose and drop the margin that
  pretends otherwise.
- **Reference lines have to fit inside the axis.** A goal above every bar is not
  drawn at all unless the axis is stretched to include it.

## 5 Forms

- The save button **waits for a change**: keep the loaded state and compare.
- If the view replaced a framework form, **keep its keyboard shortcut** — a
  `mod+s` that silently stops working is a regression nobody reports.
- A rejected value is **explained at the field it is about**, not in a message
  that floats away.
- `provideToasts()` belongs in the view that renders `ToastHost`; `useToast()`
  alone hands the caller a store the host never sees, and the message lands
  nowhere.
- Show what a setting **works out to** before it is saved, when the effect is not
  obvious from the number itself.

## 6 Figures

- Format centrally — `formatCount`, `formatDate`, `formatDuration`. A view that
  formats a duration by hand is a view whose numbers will one day disagree with
  the next view's.
- Figures carry tabular numerals, so a column does not jitter as values change.
- **Every number is a door.** If a figure summarises something, clicking it opens
  the entries behind it. A figure nobody can check is a feeling with a decimal
  point.
- Say **since when** the data exists whenever a view mixes a long-running count
  with something newly measured.

## 7 Charts

- Import the charting library in the component. A global set up elsewhere may not
  exist yet when the island mounts.
- Fixed pixel height, never `100%` — a percentage feeds back with flex parents.
- Grid, text and series colours come from the theme and adapt to dark mode.

## 8 Before calling it done

Measure, then look:

1. Read the computed padding, radius, font sizes and control heights — they must
   match the house scale, and the distinct font sizes on one view should be
   countable on one hand.
2. Take a screenshot. Numbers do not reveal a label that slipped beside its
   field, a card that ends early, or a jump when a toggle is switched.
3. Switch every toggle and period and watch what moves.
4. Resize to the widest screen the view will meet.
