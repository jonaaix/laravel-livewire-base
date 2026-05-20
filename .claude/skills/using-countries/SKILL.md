---
name: using-countries
description: Query, display, and build features with aaix/laravel-countries. Covers Country model lookups, query scopes, accessors, locale-aware names, relations, and the recommended pattern for extending the model with app-specific code.
---

# Using aaix/laravel-countries

## When to use this skill

Use this skill when writing code that reads or displays country data — for example: country dropdowns, ISO-code lookups, flag rendering, locale-specific country names, filtering by currency/region/language, or wiring `belongsTo(Country::class)` onto an app model.

The model is `Aaix\LaravelCountries\Models\Country`. There is no `App\Models\Country` shipped by the package.

## Lookups

```php
use Aaix\LaravelCountries\Models\Country;

Country::getByCode('DE');           // alpha-2 lookup, case-insensitive
Country::getByCode('DEU');          // alpha-3 also works
$country->nameInLang('de');         // explicit locale
$country->native_name;              // country's own language: "Deutschland", "日本"
Country::listInLang('de');          // [iso => name], N+1-free, ideal for <select>
```

Use `Country::listInLang($locale)` for dropdowns. Do **not** use `Country::all()->map(fn ($c) => $c->translate($locale)->name)` — that is N+1.

## Query scopes

`whereIso`, `whereIsoAlpha2`, `whereIsoAlpha3`, `whereIsoNumeric`, `whereWmo`, `whereCurrency`, `whereBorders`, `whereDomain`, `whereDomains`, `whereLanguages`, `whereFlagColors`, `wherePhoneCode`, `whereIndependenceDay`, `whereStatistics`, `whereName`, `whereSlug`.

```php
Country::whereCurrency('EUR')->get();
Country::whereBorders('DE')->get();          // neighbours of Germany
Country::whereLanguages('fr')->get();
```

## Accessors

- `$country->name` — current app locale, auto-fallback
- `$country->official_name` — locale-independent
- `$country->flag_emoji`
- `$country->flag_colors` (plus `flag_colors_hex`, `flag_colors_rgb`, `flag_colors_cmyk`, `flag_colors_hsl`, `flag_colors_hsv`, `flag_colors_pantone`, `flag_colors_web`, `flag_colors_contrast`)
- `$country->currency`, `$country->borders`, `$country->timezones`

## Relations (package-side)

`region`, `coordinates`, `extras`, `geographical`. These return the package's own related models — no consumer setup needed.

## Extending the model (recommended for apps)

When you need country-side relations or app-specific behavior, subclass the package model:

```php
namespace App\Models;

class Country extends \Aaix\LaravelCountries\Models\Country
{
    public function users()    { return $this->hasMany(User::class); }
    public function offices()  { return $this->hasMany(Office::class); }

    public function getIsEuAttribute(): bool
    {
        return in_array($this->iso_alpha_2, ['DE', 'FR', 'NL', /* ... */], true);
    }
}
```

Then use your subclass throughout the app:

```php
App\Models\Country::getByCode('DE')->users;        // works
App\Models\Country::query()->withCount('users');   // works
```

Late static binding (`new static` inside Eloquent) ensures package-level static methods like `getByCode` and `listInLang` return your subclass when you call them on it. No configuration, no resolver — just extend.

For `belongsTo` from app models either class works:

```php
class User extends Model
{
    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class);   // preferred if you extended
        // or: return $this->belongsTo(\Aaix\LaravelCountries\Models\Country::class);
    }
}
```
