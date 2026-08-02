<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGasto;
use App\Models\Gasto;
use App\Models\PresupuestoMensual;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PresupuestoController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $mes  = max(1, min(12,   (int) $request->get('mes',  now()->month)));
        $anio = max(2000, min(2100, (int) $request->get('anio', now()->year)));

        $fechaConsulta  = Carbon::createFromDate($anio, $mes, 1);
        $fechaAnterior  = $fechaConsulta->copy()->subMonth();
        $fechaSiguiente = $fechaConsulta->copy()->addMonth();

        // Presupuestos definidos para este mes/año
        $presupuestos = PresupuestoMensual::where('user_id', $userId)
            ->where('mes', $mes)->where('anio', $anio)
            ->with('categoria')
            ->get()
            ->keyBy('categoria_id');

        // Todas las categorías del usuario para el formulario de añadir
        $todasCategorias = CategoriaGasto::where('user_id', $userId)
            ->orderBy('nombre')->get();

        // Categorías ya en presupuesto este mes
        $categoriasEnPresupuesto = $presupuestos->pluck('categoria_id');

        // Categorías disponibles para añadir (las que no tienen presupuesto este mes)
        $categoriasDisponibles = $todasCategorias->whereNotIn('id', $categoriasEnPresupuesto);

        $gastosReales = Gasto::where('user_id', $userId)
            ->whereMonth('fecha', $mes)->whereYear('fecha', $anio)
            ->whereIn('categoria_id', $presupuestos->keys())
            ->selectRaw('categoria_id, SUM(monto) as total')
            ->groupBy('categoria_id')
            ->pluck('total', 'categoria_id');

        $lineas = $presupuestos->map(function ($p) use ($gastosReales) {
            $presupuesto = (float) $p->importe;
            $gastado     = (float) ($gastosReales[$p->categoria_id] ?? 0);
            $restante    = $presupuesto - $gastado;
            $pctReal     = $presupuesto > 0 ? ($gastado / $presupuesto) * 100 : 0;

            return [
                'id'          => $p->id,
                'categoria_id'=> $p->categoria_id,
                'nombre'      => $p->categoria->nombre,
                'color'       => $p->categoria->color ?? '#6366f1',
                'presupuesto' => $presupuesto,
                'gastado'     => $gastado,
                'restante'    => $restante,
                'pctReal'     => $pctReal,
                'pctBarra'    => min($pctReal, 100),
            ];
        })->sortByDesc('pctReal')->values();

        $totalPresupuesto = $lineas->sum('presupuesto');
        $totalGastado     = $lineas->sum('gastado');
        $totalRestante    = $totalPresupuesto - $totalGastado;
        $totalPct         = $totalPresupuesto > 0 ? ($totalGastado / $totalPresupuesto) * 100 : 0;

        return view('presupuesto.index', compact(
            'lineas', 'mes', 'anio',
            'fechaAnterior', 'fechaSiguiente', 'fechaConsulta',
            'totalPresupuesto', 'totalGastado', 'totalRestante', 'totalPct',
            'categoriasDisponibles'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categoria_gastos,id',
            'importe'      => 'required|numeric|min:0.01',
            'mes'          => 'required|integer|between:1,12',
            'anio'         => 'required|integer|between:2000,2100',
        ]);

        PresupuestoMensual::updateOrCreate(
            ['user_id' => auth()->id(), 'categoria_id' => $request->categoria_id, 'mes' => $request->mes, 'anio' => $request->anio],
            ['importe' => $request->importe]
        );

        return redirect()->route('presupuesto.index', ['mes' => $request->mes, 'anio' => $request->anio])
            ->with('success', 'Presupuesto añadido.');
    }

    public function update(Request $request, PresupuestoMensual $presupuesto)
    {
        abort_if($presupuesto->user_id !== auth()->id(), 403);
        $request->validate(['importe' => 'required|numeric|min:0.01']);
        $presupuesto->update(['importe' => $request->importe]);

        return redirect()->route('presupuesto.index', ['mes' => $presupuesto->mes, 'anio' => $presupuesto->anio])
            ->with('success', 'Presupuesto actualizado.');
    }

    public function destroy(PresupuestoMensual $presupuesto)
    {
        abort_if($presupuesto->user_id !== auth()->id(), 403);
        $mes  = $presupuesto->mes;
        $anio = $presupuesto->anio;
        $presupuesto->delete();

        return redirect()->route('presupuesto.index', ['mes' => $mes, 'anio' => $anio])
            ->with('success', 'Categoría eliminada del presupuesto.');
    }

    public function copiarMesAnterior(Request $request)
    {
        $userId = auth()->id();
        $mes  = (int) $request->mes;
        $anio = (int) $request->anio;
        $fecha = Carbon::createFromDate($anio, $mes, 1)->subMonth();

        $anteriores = PresupuestoMensual::where('user_id', $userId)
            ->where('mes', $fecha->month)->where('anio', $fecha->year)->get();

        $copiados = 0;
        foreach ($anteriores as $p) {
            PresupuestoMensual::firstOrCreate(
                ['user_id' => $userId, 'categoria_id' => $p->categoria_id, 'mes' => $mes, 'anio' => $anio],
                ['importe' => $p->importe]
            );
            $copiados++;
        }

        return redirect()->route('presupuesto.index', ['mes' => $mes, 'anio' => $anio])
            ->with('success', "{$copiados} categorías copiadas del mes anterior.");
    }
}
