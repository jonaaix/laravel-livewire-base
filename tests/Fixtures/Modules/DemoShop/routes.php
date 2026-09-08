<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('dashboard', fn () => view('demo-shop::dashboard'))->name('dashboard');
