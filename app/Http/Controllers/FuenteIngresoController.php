<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FuenteIngreso;
use Illuminate\Support\Facades\Auth;

class FuenteIngresoController extends Controller
{
    public function index()
    {
        $fuentes = FuenteIngreso::where('user_id', Auth::id())->get();
        return view('fuentes.index', compact('fuentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        FuenteIngreso::create([
            'user_id' => Auth::id(),
            'nombre' => $request->nombre,
        ]);

        return redirect()->back()->with('success', 'Fuente de ingreso creada correctamente');
    }

    public function destroy(FuenteIngreso $fuenteIngreso)
    {
        if ($fuenteIngreso->user_id === Auth::id()) {
            $fuenteIngreso->delete();
        }
        return redirect()->back();
    }
}