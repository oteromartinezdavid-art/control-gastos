<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GastoFijo;
use App\Models\Gasto;
use App\Models\CategoriaGasto;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class GastoFijoController extends Controller
{
    public function index(Request $request)
    {
        $user_id = auth()->id();

        $mes  = max(1, min(12,   (int) $request->get('mes',  now()->month)));
        $anio = max(2000, min(2100, (int) $request->get('anio', now()->year)));
        $fechaObjeto   = Carbon::create($anio, $mes, 1);
        $inicioDeMes   = $fechaObjeto->copy()->startOfMonth();
        $finDeMes      = $fechaObjeto->copy()->endOfMonth();

        // Solo gastos activos en el rango del mes seleccionado
        $gastosFijos = GastoFijo::where('user_id', $user_id)
            ->with('categoriaGasto')
            ->where('fecha_inicio', '<=', $finDeMes)
            ->where(function ($q) use ($inicioDeMes) {
                $q->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>=', $inicioDeMes);
            })
            ->orderBy('dia_pago')
            ->get()
            ->filter(function ($fijo) use ($mes) {
                if (empty($fijo->meses_cobro)) return true;
                return in_array($mes, array_map('intval', $fijo->meses_cobro));
            });

        $gastosRealesDelMes = Gasto::where('user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->get();

        $nombresMeses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $listadoFinal = $gastosFijos->map(function ($fijo) use ($gastosRealesDelMes, $nombresMeses) {

            $pagoRealizado = $gastosRealesDelMes->first(function ($gastoReal) use ($fijo) {
                return str_contains(strtolower($gastoReal->descripcion), strtolower($fijo->nombre));
            });

            $etiquetaMeses = null;
            if (!empty($fijo->meses_cobro)) {
                $sorted = array_map('intval', $fijo->meses_cobro);
                sort($sorted);
                $etiquetaMeses = implode(' · ', array_map(fn($m) => $nombresMeses[$m], $sorted));
            }

            return (object) [
                'id'              => $fijo->id,
                'nombre'          => $fijo->nombre,
                'dia_pago'        => $fijo->dia_pago,
                'monto_previsto'  => $fijo->monto_previsto,
                'categoria'       => $fijo->categoriaGasto,
                'etiqueta_meses'  => $etiquetaMeses,
                'pagado'          => !is_null($pagoRealizado),
                'monto_real'      => $pagoRealizado ? $pagoRealizado->monto : null,
                'fecha_pago_real' => $pagoRealizado ? $pagoRealizado->fecha : null,
                'fecha_inicio'    => $fijo->fecha_inicio,
                'fecha_fin'       => $fijo->fecha_fin,
                'dado_de_baja'    => !is_null($fijo->fecha_fin),
            ];
        });

        $totalPrevisto  = $listadoFinal->sum('monto_previsto');
        $totalPagado    = $listadoFinal->filter(fn($item) =>  $item->pagado)->sum('monto_previsto');
        $pendienteCobro = $listadoFinal->filter(fn($item) => !$item->pagado)->sum('monto_previsto');

        $categorias = CategoriaGasto::where('user_id', $user_id)->get();

        return view('gastos-fijos.index', compact(
            'listadoFinal', 'totalPrevisto', 'totalPagado', 'pendienteCobro',
            'fechaObjeto', 'categorias'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'             => 'required|string|max:255',
            'monto_previsto'     => 'required|numeric|min:0',
            'dia_pago'           => 'required|integer|between:1,31',
            'categoria_gasto_id' => ['required', Rule::exists('categoria_gastos', 'id')->where('user_id', auth()->id())],
            'meses_cobro'        => 'nullable|array',
            'meses_cobro.*'      => 'integer|between:1,12',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        GastoFijo::create([
            'user_id'            => auth()->id(),
            'nombre'             => $request->nombre,
            'monto_previsto'     => $request->monto_previsto,
            'dia_pago'           => $request->dia_pago,
            'categoria_gasto_id' => $request->categoria_gasto_id,
            'meses_cobro'        => !empty($request->meses_cobro) ? array_map('intval', $request->meses_cobro) : null,
            'fecha_inicio'       => $request->fecha_inicio,
            'fecha_fin'          => $request->fecha_fin ?: null,
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
            'categoria_gasto_id' => ['required', Rule::exists('categoria_gastos', 'id')->where('user_id', auth()->id())],
            'meses_cobro'        => 'nullable|array',
            'meses_cobro.*'      => 'integer|between:1,12',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $gastoFijo->update([
            'nombre'             => $request->nombre,
            'monto_previsto'     => $request->monto_previsto,
            'dia_pago'           => $request->dia_pago,
            'categoria_gasto_id' => $request->categoria_gasto_id,
            'meses_cobro'        => !empty($request->meses_cobro) ? array_map('intval', $request->meses_cobro) : null,
            'fecha_inicio'       => $request->fecha_inicio,
            'fecha_fin'          => $request->fecha_fin ?: null,
        ]);

        return redirect()->route('gastos-fijos.index')->with('success', 'Gasto fijo actualizado correctamente.');
    }

    public function darDeBaja(GastoFijo $gastoFijo)
    {
        abort_if($gastoFijo->user_id !== auth()->id(), 403);

        $gastoFijo->update(['fecha_fin' => now()->toDateString()]);

        return redirect()->back()->with('success', 'Gasto fijo dado de baja. El histórico se conserva.');
    }

    public function destroy(GastoFijo $gastoFijo)
    {
        abort_if($gastoFijo->user_id !== auth()->id(), 403);

        $gastoFijo->delete();

        return redirect()->back()->with('success', 'Gasto fijo eliminado definitivamente.');
    }
}
