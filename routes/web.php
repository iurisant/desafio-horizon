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
Route::patch('fornecedores/{fornecedor}/restaurar', [FornecedorController::class, 'restore'])->name('fornecedores.restaurar')->withTrashed();
Route::delete('fornecedores/{fornecedor}/excluir-permanente', [FornecedorController::class, 'forceDestroy'])->name('fornecedores.excluir-permanente')->withTrashed();
Route::patch('fornecedores/{fornecedor}/inativar', [FornecedorController::class, 'inativar'])->name('fornecedores.inativar');
Route::patch('fornecedores/{fornecedor}/reativar', [FornecedorController::class, 'reativar'])->name('fornecedores.reativar');

Route::get('produtos', [ProdutoController::class, 'index'])->name('produtos');
Route::post('produtos', [ProdutoController::class, 'store'])->name('produtos.store');
Route::put('produtos/{produto}', [ProdutoController::class, 'update'])->name('produtos.update');
Route::delete('produtos/{produto}', [ProdutoController::class, 'destroy'])->name('produtos.destroy');
Route::patch('produtos/{produto}/restaurar', [ProdutoController::class, 'restore'])->name('produtos.restaurar')->withTrashed();
Route::delete('produtos/{produto}/excluir-permanente', [ProdutoController::class, 'forceDestroy'])->name('produtos.excluir-permanente')->withTrashed();
Route::patch('produtos/{produto}/inativar', [ProdutoController::class, 'inativar'])->name('produtos.inativar');
Route::patch('produtos/{produto}/reativar', [ProdutoController::class, 'reativar'])->name('produtos.reativar');

Route::inertia('design-system', 'DesignSystem')->name('design-system');

require __DIR__.'/settings.php';
