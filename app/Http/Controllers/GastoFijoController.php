<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GastoFijo;
use App\Models\Gasto;
use App\Models\CategoriaGasto;
use Carbon\Carbon;

class GastoFijoController extends Controller
{
    public function index(Request $request)
    {
        $user_id = auth()->id();

        $mes  = (int) $request->get('mes',  now()->month);
        $anio = (int) $request->get('anio', now()->year);
        $fechaObjeto = Carbon::create($anio, $mes, 1);

        // Gastos fijos activos del usuario, filtrados al mes seleccionado
        $gastosFijos = GastoFijo::where('user_id', $user_id)
            ->with('categoriaGasto')
            ->orderBy('dia_pago')
            ->get()
            ->filter(function ($fijo) use ($mes) {
                // meses_cobro vacío o null → mensual (aparece siempre)
                if (empty($fijo->meses_cobro)) {
                    return true;
                }
                return in_array($mes, array_map('intval', $fijo->meses_cobro));
            });

        // Gastos reales del mes para detectar pagos
        $gastosRealesDelMes = Gasto::where('user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->get();

        $nombresMeses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $listadoFinal = $gastosFijos->map(function ($fijo) use ($gastosRealesDelMes, $nombresMeses) {

            $pagoRealizado = $gastosRealesDelMes->first(function ($gastoReal) use ($fijo) {
                return str_contains(
                    strtolower($gastoReal->descripcion),
                    strtolower($fijo->nombre)
                );
            });

            // Etiqueta de meses de cobro (solo para no-mensuales)
            $etiquetaMeses = null;
            if (!empty($fijo->meses_cobro)) {
                $sorted = array_map('intval', $fijo->meses_cobro);
                sort($sorted);
                $etiquetaMeses = implode(' · ', array_map(fn($m) => $nombresMeses[$m], $sorted));
            }

            return (object) [
                'id'             => $fijo->id,
                'nombre'         => $fijo->nombre,
                'dia_pago'       => $fijo->dia_pago,
                'monto_previsto' => $fijo->monto_previsto,
                'categoria'      => $fijo->categoriaGasto,
                'etiqueta_meses' => $etiquetaMeses,
                'pagado'         => !is_null($pagoRealizado),
                'monto_real'     => $pagoRealizado ? $pagoRealizado->monto : null,
                'fecha_pago_real'=> $pagoRealizado ? $pagoRealizado->fecha : null,
            ];
        });

        $totalPrevisto  = $listadoFinal->sum('monto_previsto');
        $totalPagado    = $listadoFinal->filter(fn($item) =>  $item->pagado)->sum('monto_previsto');
        $pendienteCobro = $listadoFinal->filter(fn($item) => !$item->pagado)->sum('monto_previsto');

        $categorias = CategoriaGasto::where('user_id', $user_id)->get();

        return view('gastos-fijos.index', compact(
            'listadoFinal',
            'totalPrevisto',
            'totalPagado',
            'pendienteCobro',
            'fechaObjeto',
            'categorias'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'             => 'required|string|max:255',
            'monto_previsto'     => 'required|numeric|min:0',
            'dia_pago'           => 'required|integer|between:1,31',
            'categoria_gasto_id' => 'required|exists:categoria_gastos,id',
            'meses_cobro'        => 'nullable|array',
            'meses_cobro.*'      => 'integer|between:1,12',
        ]);

        GastoFijo::create([
            'user_id'            => auth()->id(),
            'nombre'             => $request->nombre,
            'monto_previsto'     => $request->monto_previsto,
            'dia_pago'           => $request->dia_pago,
            'categoria_gasto_id' => $request->categoria_gasto_id,
            'meses_cobro'        => !empty($request->meses_cobro) ? array_map('intval', $request->meses_cobro) : null,
        ]);

        return redirect()->back()->with('success', 'Gasto fijo configurado correctamente.');
    }

    public function edit(GastoFijo $gastoFijo)
    {
        abort_if($gastoFijo->user_id !== auth()->id(), 403);

        $categorias = CategoriaGasto::where('user_id', auth()->id())->get();

        return view('gastos-fijos.edit', compact('gastoFijo', 'categorias'));
    }

    public function update(Request $request, GastoFijo $gastoFijo)
    {
        abort_if($gastoFijo->user_id !== auth()->id(), 403);

        $request->validate([
            'nombre'             => 'required|string|max:255',
            'monto_previsto'     => 'required|numeric|min:0',
            'dia_pago'           => 'required|integer|between:1,31',
            'categoria_gasto_id' => 'required|exists:categoria_gastos,id',
            'meses_cobro'        => 'nullable|array',
            'meses_cobro.*'      => 'integer|between:1,12',
        ]);

        $gastoFijo->update([
            'nombre'             => $request->nombre,
            'monto_previsto'     => $request->monto_previsto,
            'dia_pago'           => $request->dia_pago,
            'categoria_gasto_id' => $request->categoria_gasto_id,
            'meses_cobro'        => !empty($request->meses_cobro) ? array_map('intval', $request->meses_cobro) : null,
        ]);

        return redirect()->route('gastos-fijos.index')->with('success', 'Gasto fijo actualizado correctamente.');
    }

    public function destroy(GastoFijo $gastoFijo)
    {
        abort_if($gastoFijo->user_id !== auth()->id(), 403);

        $gastoFijo->delete();

        return redirect()->back()->with('success', 'Gasto fijo eliminado correctamente.');
    }
}
