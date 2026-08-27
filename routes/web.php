<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('dashboard', 'Dashboard')->name('dashboard');
Route::inertia('fornecedores', 'Fornecedores')->name('fornecedores');
Route::inertia('produtos', 'Produtos')->name('produtos');
Route::inertia('design-system', 'DesignSystem')->name('design-system');

require __DIR__.'/settings.php';
