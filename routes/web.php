<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\ReglaController;
use App\Http\Controllers\CategoriaGastoController;
use App\Http\Controllers\FuenteIngresoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GastoFijoController;
use App\Http\Controllers\FinanciacionController;
use App\Http\Controllers\InversionController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\OperacionInversionController;
use App\Http\Controllers\DividendoController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Recursos Principales
    Route::resource('gastos', GastoController::class);
    Route::resource('ingresos', IngresoController::class);
    Route::resource('importar', ImportacionController::class);
    Route::resource('reglas', ReglaController::class);
    
    // Configuración de Categorías y Fuentes
    Route::resource('categorias-gastos', CategoriaGastoController::class);
    Route::resource('fuentes-ingresos', FuenteIngresoController::class);
    // Alias opcionales por si los usas en otras vistas
    Route::resource('categorias', CategoriaGastoController::class);
    Route::resource('fuentes', FuenteIngresoController::class);

    // Financiaciones (préstamos y pagos a plazos)
    Route::resource('financiaciones', FinanciacionController::class)
        ->except(['create', 'show'])
        ->parameters(['financiaciones' => 'financiacion']);

    // Inversiones (Cartera de acciones)
    Route::prefix('inversiones')->name('inversiones.')->group(function () {
        Route::get('/', [InversionController::class, 'index'])->name('index');
        Route::get('/cotizacion/{ticker}', [InversionController::class, 'getCotizacion'])->name('cotizacion');
        Route::resource('activos', ActivoController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::resource('operaciones', OperacionInversionController::class)
            ->only(['index', 'store', 'destroy'])
            ->parameters(['operaciones' => 'operacion']);
        Route::resource('dividendos', DividendoController::class)
            ->only(['index', 'store', 'destroy'])
            ->parameters(['dividendos' => 'dividendo']);
    });

    // Gastos Fijos (Control de suscripciones y pagos recurrentes)
    Route::get('/gastos-fijos', [GastoFijoController::class, 'index'])->name('gastos-fijos.index');
    Route::post('/gastos-fijos', [GastoFijoController::class, 'store'])->name('gastos-fijos.store');
    Route::get('/gastos-fijos/{gastoFijo}/edit', [GastoFijoController::class, 'edit'])->name('gastos-fijos.edit');
    Route::patch('/gastos-fijos/{gastoFijo}', [GastoFijoController::class, 'update'])->name('gastos-fijos.update');
    Route::delete('/gastos-fijos/{gastoFijo}', [GastoFijoController::class, 'destroy'])->name('gastos-fijos.destroy');
});

require __DIR__.'/auth.php';