<?php

namespace App\Http\Controllers;

use App\Models\ReglaFuenteIngreso;
use App\Models\FuenteIngreso;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReglaIngresoController extends Controller
{
    public function index()
    {
        $user_id = auth()->id();
        $reglas = ReglaFuenteIngreso::with('fuente')->where('user_id', $user_id)->get();
        $fuentes = FuenteIngreso::where('user_id', $user_id)->get();

        return view('reglas-ingresos.index', compact('reglas', 'fuentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'palabra_clave'     => 'required|string|max:255',
            'fuente_ingreso_id' => ['required', Rule::exists('fuente_ingresos', 'id')->where('user_id', auth()->id())],
        ]);

        ReglaFuenteIngreso::create([
            'user_id'           => auth()->id(),
            'palabra_clave'     => strtoupper($request->palabra_clave),
            'fuente_ingreso_id' => $request->fuente_ingreso_id,
        ]);

        return redirect()->back()->with('success', 'Regla de ingreso añadida correctamente.');
    }

    public function destroy(ReglaFuenteIngreso $reglaIngreso)
    {
        abort_if($reglaIngreso->user_id !== auth()->id(), 403);
        $reglaIngreso->delete();
        return redirect()->back()->with('success', 'Regla eliminada.');
    }
}
