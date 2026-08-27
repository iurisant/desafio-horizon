<?php

use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('dashboard', 'Dashboard')->name('dashboard');

Route::get('fornecedores', [FornecedorController::class, 'index'])->name('fornecedores');
Route::post('fornecedores', [FornecedorController::class, 'store'])->name('fornecedores.store');
Route::put('fornecedores/{fornecedor}', [FornecedorController::class, 'update'])->name('fornecedores.update');
Route::delete('fornecedores/{fornecedor}', [FornecedorController::class, 'destroy'])->name('fornecedores.destroy');

Route::get('produtos', [ProdutoController::class, 'index'])->name('produtos');
Route::post('produtos', [ProdutoController::class, 'store'])->name('produtos.store');
Route::put('produtos/{produto}', [ProdutoController::class, 'update'])->name('produtos.update');
Route::delete('produtos/{produto}', [ProdutoController::class, 'destroy'])->name('produtos.destroy');

Route::inertia('design-system', 'DesignSystem')->name('design-system');

require __DIR__.'/settings.php';
