<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FuenteIngreso;

class FuenteIngresoController extends Controller
{
    public function index()
    {
        $fuentes = FuenteIngreso::where('user_id', auth()->id())->get();
        return view('fuentes.index', compact('fuentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'color'  => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        FuenteIngreso::create([
            'user_id' => auth()->id(),
            'nombre'  => $request->nombre,
            'color'   => $request->color,
        ]);

        return redirect()->back()->with('success', 'Fuente de ingreso creada correctamente');
    }

    public function update(Request $request, FuenteIngreso $fuenteIngreso)
    {
        abort_if($fuenteIngreso->user_id !== null && $fuenteIngreso->user_id !== auth()->id(), 403);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'color'  => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $fuenteIngreso->update([
            'nombre' => $request->nombre,
            'color'  => $request->color,
        ]);

        return redirect()->back()->with('success', 'Fuente de ingreso actualizada.');
    }

    public function destroy(FuenteIngreso $fuenteIngreso)
    {
        abort_if($fuenteIngreso->user_id !== null && $fuenteIngreso->user_id !== auth()->id(), 403);
        $fuenteIngreso->delete();
        return redirect()->back()->with('success', 'Fuente de ingreso eliminada.');
    }
}
