<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('dashboard', 'Dashboard')->name('dashboard');
Route::inertia('design-system', 'DesignSystem')->name('design-system');

require __DIR__.'/settings.php';
