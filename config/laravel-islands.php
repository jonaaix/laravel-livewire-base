<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Broadcast authorization endpoint
    |--------------------------------------------------------------------------
    |
    | The endpoint islands use to authorize private channel subscriptions.
    | Defaults to the standard Laravel broadcasting auth route.
    |
    */
    'broadcast_auth_endpoint' => '/broadcasting/auth',

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    |
    | When enabled, the app's JSON translation lines (lang/{locale}.json) for
    | the current locale are shipped with every island payload and made
    | available in the frontend via the `useTranslations()` composable. The
    | English source string doubles as the translation key.
    |
    */
    'translations' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Where islands live
    |--------------------------------------------------------------------------
    |
    | The directory holding one sub-directory per island, relative to the project
    | root, together with the namespace its classes are autoloaded under. Both are
    | read by route discovery and by `make:island`.
    |
    */
    'path' => 'app/Islands',

    'namespace' => 'App\\Islands',

    /*
    |--------------------------------------------------------------------------
    | Island routes
    |--------------------------------------------------------------------------
    |
    | An island keeps its own endpoints next to its markup: a `Routes.php` in
    | the island's directory is loaded into a group of its own, prefixed with
    | the island's kebab-cased directory name. `admin/islands` plus `ShopOrders`
    | therefore serves `admin/islands/shop-orders/...` under the route name
    | `islands.shop-orders.`.
    |
    | Scoping every island below one path keeps these endpoints clear of routes
    | owned by anything else — a wildcard segment elsewhere in the application
    | can no longer swallow them.
    |
    */
    'routes' => [
        'enabled' => true,

        // The file inside an island directory that declares its routes.
        'file' => 'Routes.php',

        'prefix' => 'islands',

        'name' => 'islands.',

        'middleware' => ['web'],
    ],
];
