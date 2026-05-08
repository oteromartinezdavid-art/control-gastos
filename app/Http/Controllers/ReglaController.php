<?php

namespace App\Http\Controllers;

use App\Models\ReglaCategorizacion;
use App\Models\CategoriaGasto;
use Illuminate\Http\Request;

class ReglaController extends Controller
{
    public function index()
    {
        $user_id = auth()->id();
        $reglas = ReglaCategorizacion::with('categoria')->where('user_id', $user_id)->get();
        $categorias = CategoriaGasto::where('user_id', $user_id)->get();
        
        return view('reglas.index', compact('reglas', 'categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'palabra_clave' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categoria_gastos,id',
        ]);

        ReglaCategorizacion::create([
            'user_id' => auth()->id(),
            'palabra_clave' => strtoupper($request->palabra_clave), // Siempre en mayúsculas
            'categoria_id' => $request->categoria_id,
        ]);

        return redirect()->back()->with('success', 'Regla añadida correctamente.');
    }

    public function destroy(ReglaCategorizacion $regla)
    {
        if ($regla->user_id === auth()->id()) {
            $regla->delete();
        }
        return redirect()->back()->with('success', 'Regla eliminada.');
    }
}