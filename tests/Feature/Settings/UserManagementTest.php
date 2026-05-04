<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('user management page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('users.edit'))->assertOk();
});

test('user management requires authentication', function () {
    $this->get(route('users.edit'))->assertRedirect(route('login'));
});

test('users list renders all users', function () {
    $this->actingAs(User::factory()->create(['name' => 'Admin']));
    User::factory()->create(['name' => 'Bob']);
    User::factory()->create(['name' => 'Charlie']);

    Livewire::test('pages::settings.users')
        ->assertSee('Admin')
        ->assertSee('Bob')
        ->assertSee('Charlie');
});

test('users table shows status columns', function () {
    $this->actingAs(User::factory()->create(['name' => 'Admin']));
    User::factory()->create([
        'name' => 'Verified User',
        'email_verified_at' => now()->subDay(),
        'last_login_at' => now()->subHour(),
    ]);
    User::factory()->unverified()->create([
        'name' => 'Unverified User',
        'is_disabled' => true,
        'last_login_at' => null,
    ]);

    Livewire::test('pages::settings.users')
        ->assertSee('Active')
        ->assertSee('Disabled')
        ->assertSee('—');
});

test('search filters users by name and email', function () {
    $this->actingAs(User::factory()->create(['name' => 'Admin']));
    User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);

    Livewire::test('pages::settings.users')
        ->set('search', 'alice')
        ->assertSee('Alice')
        ->assertDontSee('Bob');
});

test('create user persists with verified email and hashed password', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::settings.users')
        ->call('startCreate')
        ->set('name', 'New Person')
        ->set('email', 'new@example.com')
        ->set('password', 'secret-pass')
        ->set('isDisabled', false)
        ->call('save')
        ->assertHasNoErrors();

    $user = User::where('email', 'new@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('New Person');
    expect($user->email_verified_at)->not->toBeNull();
    expect(Hash::check('secret-pass', $user->password))->toBeTrue();
    expect($user->is_disabled)->toBeFalse();
});

test('create user requires password and validates email uniqueness', function () {
    $this->actingAs(User::factory()->create());
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test('pages::settings.users')
        ->call('startCreate')
        ->set('name', 'Test')
        ->set('email', 'taken@example.com')
        ->set('password', '')
        ->call('save')
        ->assertHasErrors(['email', 'password']);
});

test('edit updates user without changing password when blank', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create([
        'name' => 'Original',
        'password' => Hash::make('old-password'),
    ]);

    Livewire::test('pages::settings.users')
        ->call('startEdit', $user->id)
        ->set('name', 'Updated')
        ->set('password', '')
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated');
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});

test('edit updates password when filled', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create();

    Livewire::test('pages::settings.users')
        ->call('startEdit', $user->id)
        ->set('password', 'brand-new-pass')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('brand-new-pass', $user->fresh()->password))->toBeTrue();
});

test('edit can toggle is_disabled on another user', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create(['is_disabled' => false]);

    Livewire::test('pages::settings.users')
        ->call('startEdit', $user->id)
        ->set('isDisabled', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->is_disabled)->toBeTrue();
});

test('user cannot disable their own account', function () {
    $self = User::factory()->create(['is_disabled' => false]);
    $this->actingAs($self);

    Livewire::test('pages::settings.users')
        ->call('startEdit', $self->id)
        ->set('isDisabled', true)
        ->call('save');

    expect($self->fresh()->is_disabled)->toBeFalse();
});

test('user cannot delete their own account', function () {
    $self = User::factory()->create();
    $this->actingAs($self);

    Livewire::test('pages::settings.users')
        ->call('delete', $self->id);

    expect(User::find($self->id))->not->toBeNull();
});

test('delete removes another user', function () {
    $this->actingAs(User::factory()->create());
    $target = User::factory()->create();

    Livewire::test('pages::settings.users')
        ->call('delete', $target->id)
        ->assertHasNoErrors();

    expect(User::find($target->id))->toBeNull();
});

test('edit can revoke email verification', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create(['email_verified_at' => now()]);

    Livewire::test('pages::settings.users')
        ->call('startEdit', $user->id)
        ->set('emailVerified', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('edit can mark email verified', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->unverified()->create();

    Livewire::test('pages::settings.users')
        ->call('startEdit', $user->id)
        ->set('emailVerified', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});
