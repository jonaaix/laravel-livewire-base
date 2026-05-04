<?php

use App\Models\AppSetting;
use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

test('workspace settings page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('workspace.edit'))->assertOk();
});

test('workspace settings page requires authentication', function () {
    $this->get(route('workspace.edit'))->assertRedirect(route('login'));
});

test('toggle persists when changed', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::settings.workspace')
        ->assertSet('registrationEnabled', true)
        ->set('registrationEnabled', false)
        ->assertHasNoErrors();

    expect(AppSetting::get('registration_enabled'))->toBeFalse();

    Livewire::test('pages::settings.workspace')
        ->assertSet('registrationEnabled', false)
        ->set('registrationEnabled', true);

    expect(AppSetting::get('registration_enabled'))->toBeTrue();
});

test('register routes are accessible when registration is enabled', function () {
    $this->skipUnlessFortifyHas(Features::registration());

    AppSetting::set('registration_enabled', true);

    $this->get(route('register'))->assertOk();
});

test('register GET returns 404 when registration is disabled', function () {
    $this->skipUnlessFortifyHas(Features::registration());

    AppSetting::set('registration_enabled', false);

    $this->get(route('register'))->assertNotFound();
});

test('register POST returns 404 when registration is disabled', function () {
    $this->skipUnlessFortifyHas(Features::registration());

    AppSetting::set('registration_enabled', false);

    $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});

test('welcome page shows register link when registration is enabled', function () {
    AppSetting::set('registration_enabled', true);

    $this->get('/')
        ->assertOk()
        ->assertSee(route('register'));
});

test('welcome page hides register link when registration is disabled', function () {
    AppSetting::set('registration_enabled', false);

    $this->get('/')
        ->assertOk()
        ->assertDontSee(route('register'));
});
