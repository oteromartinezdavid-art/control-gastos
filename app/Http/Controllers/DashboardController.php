<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\Ingreso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\CategoriaGastoController;
use App\Http\Controllers\FuenteIngresoController;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user_id = auth()->id();
        
        // Capturamos mes y año o usamos los actuales
        $mes = $request->get('mes', Carbon::now()->month);
        $anio = $request->get('anio', Carbon::now()->year);

        // Objeto Carbon para navegación
        $fechaConsulta = Carbon::createFromDate($anio, $mes, 1);
        $fechaAnterior = $fechaConsulta->copy()->subMonth();
        $fechaSiguiente = $fechaConsulta->copy()->addMonth();

        // Totales
        $totalIngresos = Ingreso::where('user_id', $user_id)->whereMonth('fecha', $mes)->whereYear('fecha', $anio)->sum('monto');
        $totalGastos = Gasto::where('user_id', $user_id)->whereMonth('fecha', $mes)->whereYear('fecha', $anio)->sum('monto');
        $saldo = $totalIngresos - $totalGastos;

        // Movimientos recientes
        $movimientos = Ingreso::where('user_id', $user_id)->whereMonth('fecha', $mes)->whereYear('fecha', $anio)->get()->map(fn($i) => tap($i, fn($i) => $i->tipo = 'ingreso'))
            ->concat(
                Gasto::where('user_id', $user_id)->whereMonth('fecha', $mes)->whereYear('fecha', $anio)->get()->map(fn($g) => tap($g, fn($g) => $g->tipo = 'gasto'))
            )->sortByDesc('fecha');

        // --- Gráfico de Categorías (CON COLORES EDITABLES) ---
        $datosGrafico = Gasto::where('gastos.user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->join('categoria_gastos', 'gastos.categoria_id', '=', 'categoria_gastos.id')
            ->selectRaw('categoria_gastos.nombre as categoria_nombre, categoria_gastos.color as categoria_color, SUM(monto) as total')
            ->groupBy('categoria_gastos.nombre', 'categoria_gastos.color')
            ->get();

        $categoriasLabels = $datosGrafico->pluck('categoria_nombre');
        $categoriasTotales = $datosGrafico->pluck('total');
        $categoriasColores = $datosGrafico->pluck('categoria_color'); // <--- Colores de la DB

        // --- Gráfica Lineal Diaria ---
        $diasDelMes = $fechaConsulta->daysInMonth;
        $gastosDiariosRaw = Gasto::where('user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->selectRaw('strftime("%d", fecha) as dia, SUM(monto) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia')
            ->toArray();

        $labelsDias = [];
        $datosDias = [];

        for ($i = 1; $i <= $diasDelMes; $i++) {
            $diaFormateado = str_pad($i, 2, '0', STR_PAD_LEFT);
            $labelsDias[] = $i;
            $datosDias[] = $gastosDiariosRaw[$diaFormateado] ?? 0;
        }

        return view('dashboard', compact(
            'labelsDias', 'datosDias', 'totalIngresos', 'totalGastos', 
            'saldo', 'movimientos', 'mes', 'anio', 'fechaAnterior', 
            'fechaSiguiente', 'categoriasLabels', 'categoriasTotales', 'categoriasColores'
        ));
    }
}