<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModulesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerModulesFrom(app_path('Modules'));
    }

    public function registerModulesFrom(string $basePath): void
    {
        foreach (self::discover($basePath) as $name => $path) {
            $this->registerModule($name, $path);
        }
    }

    /**
     * @return array<string, string>
     */
    public static function discover(string $basePath): array
    {
        if (! is_dir($basePath)) {
            return [];
        }

        $modules = [];

        foreach (glob($basePath.'/*', GLOB_ONLYDIR) ?: [] as $path) {
            $modules[basename($path)] = $path;
        }

        ksort($modules);

        return $modules;
    }

    private function registerModule(string $name, string $path): void
    {
        $slug = Str::kebab($name);

        if (is_dir($views = $path.'/resources/views')) {
            $this->loadViewsFrom($views, $slug);
        }

        if (is_file($routes = $path.'/routes.php')) {
            Route::middleware('web')
                ->prefix($slug)
                ->name($slug.'.')
                ->group($routes);
        }
    }
}
