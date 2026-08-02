<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Dividendo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DividendoController extends Controller
{
    public function index(Request $request)
    {
        $userId  = auth()->id();
        $activos = Activo::where('user_id', $userId)->orderBy('ticker')->get();

        $query = Dividendo::with('activo')->where('user_id', $userId);

        if ($request->filled('activo_id')) {
            $query->where('activo_id', $request->activo_id);
        }

        $dividendos  = $query->orderBy('fecha', 'desc')->get();
        $totalNeto   = $dividendos->sum('monto_neto');
        $totalBruto  = $dividendos->sum('monto_bruto');
        $totalRet    = $dividendos->sum('retencion');

        return view('inversiones.dividendos.index', compact(
            'dividendos', 'activos', 'totalNeto', 'totalBruto', 'totalRet', 'request'
        ));
    }

    public function store(Request $request)
    {
        $userId = auth()->id();

        $request->validate([
            'activo_id'   => ['required', Rule::exists('activos', 'id')->where('user_id', auth()->id())],
            'fecha'       => 'required|date',
            'monto_bruto' => 'required|numeric|min:0',
            'retencion'   => 'nullable|numeric|min:0',
            'monto_neto'  => 'required|numeric|min:0',
            'notas'       => 'nullable|string|max:500',
        ]);

        Activo::where('id', $request->activo_id)->where('user_id', $userId)->firstOrFail();

        Dividendo::create([
            'user_id'     => $userId,
            'activo_id'   => $request->activo_id,
            'fecha'       => $request->fecha,
            'monto_bruto' => $request->monto_bruto,
            'retencion'   => $request->retencion ?? 0,
            'monto_neto'  => $request->monto_neto,
            'notas'       => $request->notas,
        ]);

        return redirect()->route('inversiones.dividendos.index')->with('success', 'Dividendo registrado correctamente.');
    }

    public function destroy(Dividendo $dividendo)
    {
        if ($dividendo->user_id !== auth()->id()) abort(403);
        $dividendo->delete();
        return redirect()->route('inversiones.dividendos.index')->with('success', 'Dividendo eliminado.');
    }
}
