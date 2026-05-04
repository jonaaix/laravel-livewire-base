<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('disabled user cannot log in', function () {
    User::factory()->create([
        'email' => 'blocked@example.com',
        'password' => Hash::make('password'),
        'is_disabled' => true,
    ]);

    $response = $this->post(route('login'), [
        'email' => 'blocked@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('active user can still log in', function () {
    User::factory()->create([
        'email' => 'active@example.com',
        'password' => Hash::make('password'),
        'is_disabled' => false,
    ]);

    $this->post(route('login'), [
        'email' => 'active@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
});

test('successful login updates last_login_at', function () {
    $user = User::factory()->create([
        'email' => 'tracker@example.com',
        'password' => Hash::make('password'),
        'last_login_at' => null,
    ]);

    $this->post(route('login'), [
        'email' => 'tracker@example.com',
        'password' => 'password',
    ]);

    expect($user->fresh()->last_login_at)->not->toBeNull();
});
