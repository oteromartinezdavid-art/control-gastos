<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\ImportacionController;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Http\Controllers\CategoriaGastoController;
use App\Http\Controllers\FuenteIngresoController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('gastos', GastoController::class)->middleware('auth');;
    Route::resource('ingresos', IngresoController::class)->middleware('auth');
    Route::resource('importar', ImportacionController::class)->middleware('auth');
    Route::resource('categorias-gastos', CategoriaGastoController::class)->middleware(['auth']);
    Route::resource('fuentes-ingresos', FuenteIngresoController::class)->middleware(['auth']);
    Route::resource('categorias', CategoriaGastoController::class)->middleware(['auth']);
    Route::resource('fuentes', FuenteIngresoController::class)->middleware(['auth']);
});

require __DIR__.'/auth.php';