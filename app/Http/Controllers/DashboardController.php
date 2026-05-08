<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\GastoFijo;
use App\Models\Financiacion;
use App\Models\Activo;
use App\Models\OperacionInversion;
use App\Models\Dividendo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user_id = auth()->id();
        $mes = $request->get('mes', Carbon::now()->month);
        $anio = $request->get('anio', Carbon::now()->year);

        $fechaConsulta = Carbon::createFromDate($anio, $mes, 1);
        $fechaAnterior = $fechaConsulta->copy()->subMonth();
        $fechaSiguiente = $fechaConsulta->copy()->addMonth();

        // Totales
        $totalIngresos = Ingreso::where('user_id', $user_id)->whereMonth('fecha', $mes)->whereYear('fecha', $anio)->sum('monto');
        $totalGastosRealizados = Gasto::where('user_id', $user_id)->whereMonth('fecha', $mes)->whereYear('fecha', $anio)->sum('monto');

        // Lógica de Gastos Fijos Pendientes
        $gastosFijosConfigurados = GastoFijo::where('user_id', $user_id)
            ->get()
            ->filter(function ($fijo) use ($mes) {
                if (empty($fijo->meses_cobro)) return true;
                return in_array((int) $mes, array_map('intval', $fijo->meses_cobro));
            });

        $gastosRealesDelMes = Gasto::where('user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->get();

        $pendienteFijos = 0;
        foreach ($gastosFijosConfigurados as $fijo) {
            $yaPagado = $gastosRealesDelMes->contains(function ($gasto) use ($fijo) {
                return str_contains(strtolower($gasto->descripcion), strtolower($fijo->nombre));
            });
            if (!$yaPagado) {
                $pendienteFijos += $fijo->monto_previsto;
            }
        }

        $saldoRealFinal = $totalIngresos - $totalGastosRealizados - $pendienteFijos;

        // Financiaciones activas
        $financiacionesActivas = Financiacion::where('user_id', $user_id)
            ->where('cuotas_pendientes', '>', 0)->get();
        $numFinanciaciones    = $financiacionesActivas->count();
        $totalCuotaMensual    = $financiacionesActivas->sum('cuota_mensual');
        $totalDeudaPendiente  = $financiacionesActivas->sum(fn($f) => $f->cuota_mensual * $f->cuotas_pendientes);

        // Inversiones (sin cotización en tiempo real)
        $numActivos       = Activo::where('user_id', $user_id)->count();
        $totalInvertidoCartera = OperacionInversion::where('user_id', $user_id)
            ->where('tipo', 'compra')
            ->get()->sum(fn($o) => $o->cantidad * $o->precio_unitario + $o->comision);
        $totalVentasCartera = OperacionInversion::where('user_id', $user_id)
            ->where('tipo', 'venta')
            ->get()->sum(fn($o) => $o->cantidad * $o->precio_unitario - $o->comision);
        $totalDividendosCartera = Dividendo::where('user_id', $user_id)->sum('monto_neto');

        // Gráficos (Categorías)
        $datosGrafico = Gasto::where('gastos.user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->join('categoria_gastos', 'gastos.categoria_id', '=', 'categoria_gastos.id')
            ->selectRaw('categoria_gastos.nombre, categoria_gastos.color, SUM(monto) as total')
            ->groupBy('categoria_gastos.nombre', 'categoria_gastos.color')->get();

        // Gráfico Lineal (Gasto Diario)
        $gastosDiariosRaw = Gasto::where('user_id', $user_id)->whereMonth('fecha', $mes)->whereYear('fecha', $anio)
            ->selectRaw('strftime("%d", fecha) as dia, SUM(monto) as total')
            ->groupBy('dia')->pluck('total', 'dia')->toArray();

        $labelsDias = []; $datosDias = [];
        for ($i = 1; $i <= $fechaConsulta->daysInMonth; $i++) {
            $diaFormateado = str_pad($i, 2, '0', STR_PAD_LEFT);
            $labelsDias[] = $i;
            $datosDias[] = $gastosDiariosRaw[$diaFormateado] ?? 0;
        }

        return view('dashboard', compact(
            'totalIngresos', 'totalGastosRealizados', 'saldoRealFinal', 'pendienteFijos',
            'labelsDias', 'datosDias', 'mes', 'anio', 'fechaAnterior', 'fechaSiguiente',
            'datosGrafico',
            'numFinanciaciones', 'totalCuotaMensual', 'totalDeudaPendiente',
            'numActivos', 'totalInvertidoCartera', 'totalVentasCartera', 'totalDividendosCartera'
        ));
    }
}