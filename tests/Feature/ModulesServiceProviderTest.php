<?php

declare(strict_types=1);

use App\Providers\ModulesServiceProvider;
use Illuminate\Support\Facades\Route;

function registerFixtureModules(): void
{
    (new ModulesServiceProvider(app()))
        ->registerModulesFrom(base_path('tests/Fixtures/Modules'));

    Route::getRoutes()->refreshNameLookups();
}

it('discovers every module directory', function (): void {
    $modules = ModulesServiceProvider::discover(base_path('tests/Fixtures/Modules'));

    expect($modules)->toHaveKey('DemoShop')
        ->and($modules['DemoShop'])->toEndWith('tests/Fixtures/Modules/DemoShop');
});

it('returns no modules when the directory is absent', function (): void {
    expect(ModulesServiceProvider::discover(base_path('tests/Fixtures/Nope')))->toBe([]);
});

it('mounts module routes under the kebab-cased module name', function (): void {
    registerFixtureModules();

    expect(route('demo-shop.dashboard', absolute: false))->toBe('/demo-shop/dashboard');
});

it('serves a module route through its own view namespace', function (): void {
    registerFixtureModules();

    $this->get('/demo-shop/dashboard')
        ->assertOk()
        ->assertSee('Demo shop dashboard');
});

it('registers no modules for the empty application directory', function (): void {
    expect(ModulesServiceProvider::discover(app_path('Modules')))->toBe([]);
});
