<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\IngresoController;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Http\Controllers\CategoriaGastoController;
use App\Http\Controllers\FuenteIngresoController;
use Carbon\Carbon;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    $user_id = auth()->id();
    
    // Capturamos mes y año o usamos los actuales
    $mes = $request->get('mes', Carbon::now()->month);
    $año = $request->get('año', Carbon::now()->year);

    // Creamos un objeto Carbon con la fecha actual de consulta
    $fechaConsulta = Carbon::createFromDate($año, $mes, 1);

    // Calculamos mes anterior y siguiente
    $fechaAnterior = $fechaConsulta->copy()->subMonth();
    $fechaSiguiente = $fechaConsulta->copy()->addMonth();

    // Consultas a la DB
    $totalIngresos = App\Models\Ingreso::where('user_id', $user_id)
        ->whereMonth('fecha', $mes)
        ->whereYear('fecha', $año)
        ->sum('monto');

    $totalGastos = App\Models\Gasto::where('user_id', $user_id)
        ->whereMonth('fecha', $mes)
        ->whereYear('fecha', $año)
        ->sum('monto');

    $saldo = $totalIngresos - $totalGastos;

    $movimientos = App\Models\Ingreso::where('user_id', $user_id)
        ->whereMonth('fecha', $mes)
        ->whereYear('fecha', $año)
        ->get()
        ->map(fn($i) => tap($i, fn($i) => $i->tipo = 'ingreso'))
        ->concat(
            App\Models\Gasto::where('user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $año)
            ->get()
            ->map(fn($g) => tap($g, fn($g) => $g->tipo = 'gasto'))
        )
        ->sortByDesc('fecha');

        // Obtener gastos agrupados por categoría para el mes seleccionado (CORREGIDO)
        $datosGrafico = App\Models\Gasto::where('gastos.user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $año)
            ->join('categoria_gastos', 'gastos.categoria_id', '=', 'categoria_gastos.id')
            ->selectRaw('categoria_gastos.nombre as categoria_nombre, SUM(monto) as total')
            ->groupBy('categoria_gastos.nombre')
            ->get();

        $categoriasLabels = $datosGrafico->pluck('categoria_nombre');
        $categoriasTotales = $datosGrafico->pluck('total');


        // --- Lógica para la Gráfica Lineal Diaria ---
        $diasDelMes = $fechaConsulta->daysInMonth; // Detecta si el mes tiene 28, 30 o 31 días
        $gastosDiariosRaw = App\Models\Gasto::where('user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $año)
            ->selectRaw('strftime("%d", fecha) as dia, SUM(monto) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia') // Crea un array tipo ["01" => 100, "05" => 20]
            ->toArray();

        $labelsDias = [];
        $datosDias = [];

        for ($i = 1; $i <= $diasDelMes; $i++) {
            $diaFormateado = str_pad($i, 2, '0', STR_PAD_LEFT); // Convierte 1 en "01"
            $labelsDias[] = $i; // El número del día para el eje X
            $datosDias[] = $gastosDiariosRaw[$diaFormateado] ?? 0; // Si no hay gasto, ponemos 0
        }
        // --------------------------------------------

        return view('dashboard', compact(
            'labelsDias', 
            'datosDias',
            'totalIngresos', 
            'totalGastos', 
            'saldo', 
            'movimientos', 
            'mes', 
            'año', 
            'fechaAnterior', 
            'fechaSiguiente',
            'categoriasLabels',
            'categoriasTotales'
        ));

    return view('dashboard', compact(
        'totalIngresos', 'totalGastos', 'saldo', 'movimientos', 
        'mes', 'año', 'fechaAnterior', 'fechaSiguiente'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('gastos', GastoController::class);
    Route::resource('ingresos', IngresoController::class)->middleware('auth');
    Route::resource('categorias-gastos', CategoriaGastoController::class)->middleware(['auth']);
    Route::resource('fuentes-ingresos', FuenteIngresoController::class)->middleware(['auth']);
});

require __DIR__.'/auth.php';