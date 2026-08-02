<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\ReglaController;
use App\Http\Controllers\ReglaIngresoController;
use App\Http\Controllers\CategoriaGastoController;
use App\Http\Controllers\FuenteIngresoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GastoFijoController;
use App\Http\Controllers\FinanciacionController;
use App\Http\Controllers\InversionController;
use App\Http\Controllers\ResumenAnualInversionController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\OperacionInversionController;
use App\Http\Controllers\DividendoController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PresupuestoController;
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
    Route::resource('reglas-ingresos', ReglaIngresoController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['reglas-ingresos' => 'reglaIngreso']);
    
    // Configuración de Categorías y Fuentes
    Route::resource('categorias-gastos', CategoriaGastoController::class);
    Route::resource('fuentes-ingresos', FuenteIngresoController::class)
        ->parameters(['fuentes-ingresos' => 'fuenteIngreso']);
    // Alias cortos (apuntan al mismo controller; usan nombres de ruta distintos)
    Route::resource('categorias', CategoriaGastoController::class)->names([
        'index'   => 'categorias.index',
        'create'  => 'categorias.create',
        'store'   => 'categorias.store',
        'show'    => 'categorias.show',
        'edit'    => 'categorias.edit',
        'update'  => 'categorias.update',
        'destroy' => 'categorias.destroy',
    ]);
    Route::resource('fuentes', FuenteIngresoController::class)->names([
        'index'   => 'fuentes.index',
        'create'  => 'fuentes.create',
        'store'   => 'fuentes.store',
        'show'    => 'fuentes.show',
        'edit'    => 'fuentes.edit',
        'update'  => 'fuentes.update',
        'destroy' => 'fuentes.destroy',
    ]);

    // Financiaciones (préstamos y pagos a plazos)
    Route::resource('financiaciones', FinanciacionController::class)
        ->except(['create', 'show'])
        ->parameters(['financiaciones' => 'financiacion']);

    // Inversiones (Cartera de acciones)
    Route::prefix('inversiones')->name('inversiones.')->group(function () {
        Route::get('/', [InversionController::class, 'index'])->name('index');
        Route::get('/proyeccion', [InversionController::class, 'proyeccion'])->name('proyeccion');
        Route::get('/cotizacion/{ticker}', [InversionController::class, 'getCotizacion'])->name('cotizacion');
        Route::resource('activos', ActivoController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::resource('operaciones', OperacionInversionController::class)
            ->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['operaciones' => 'operacion']);
        Route::resource('dividendos', DividendoController::class)
            ->only(['index', 'store', 'destroy'])
            ->parameters(['dividendos' => 'dividendo']);
    });

    // Resumen Anual Inversiones
    Route::get('/inversiones/resumen-anual', [ResumenAnualInversionController::class, 'index'])->name('inversiones.resumen-anual');

    // Presupuesto
    Route::get('/presupuesto', [PresupuestoController::class, 'index'])->name('presupuesto.index');
    Route::post('/presupuesto', [PresupuestoController::class, 'store'])->name('presupuesto.store');
    Route::put('/presupuesto/{presupuesto}', [PresupuestoController::class, 'update'])->name('presupuesto.update');
    Route::delete('/presupuesto/{presupuesto}', [PresupuestoController::class, 'destroy'])->name('presupuesto.destroy');
    Route::post('/presupuesto/copiar-mes-anterior', [PresupuestoController::class, 'copiarMesAnterior'])->name('presupuesto.copiar');

    // Backup
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup', [BackupController::class, 'store'])->name('backup.store');
    Route::get('/backup/{filename}/download', [BackupController::class, 'download'])
        ->name('backup.download')
        ->where('filename', '[a-zA-Z0-9_\-\.]+');
    Route::get('/backup/{filename}/restore', [BackupController::class, 'restoreConfirm'])
        ->name('backup.restore.confirm')
        ->where('filename', '[a-zA-Z0-9_\-\.]+');
    Route::put('/backup/{filename}/restore', [BackupController::class, 'restore'])
        ->name('backup.restore')
        ->where('filename', '[a-zA-Z0-9_\-\.]+');
    Route::delete('/backup/{filename}', [BackupController::class, 'destroy'])
        ->name('backup.destroy')
        ->where('filename', '[a-zA-Z0-9_\-\.]+');

    // Gastos Fijos (Control de suscripciones y pagos recurrentes)
    Route::get('/gastos-fijos', [GastoFijoController::class, 'index'])->name('gastos-fijos.index');
    Route::post('/gastos-fijos', [GastoFijoController::class, 'store'])->name('gastos-fijos.store');
    Route::get('/gastos-fijos/{gastoFijo}/edit', [GastoFijoController::class, 'edit'])->name('gastos-fijos.edit');
    Route::patch('/gastos-fijos/{gastoFijo}', [GastoFijoController::class, 'update'])->name('gastos-fijos.update');
    Route::patch('/gastos-fijos/{gastoFijo}/dar-de-baja', [GastoFijoController::class, 'darDeBaja'])->name('gastos-fijos.dar-de-baja');
    Route::delete('/gastos-fijos/{gastoFijo}', [GastoFijoController::class, 'destroy'])->name('gastos-fijos.destroy');
});

require __DIR__.'/auth.php';