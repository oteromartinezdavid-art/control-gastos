<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoriaGasto;
use Illuminate\Support\Facades\Auth;

class CategoriaGastoController extends Controller
{
    public function index() {
        $categorias = CategoriaGasto::where('user_id', auth()->id())->get();
        return view('categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'presupuesto_mensual' => 'nullable|numeric',
            'color' => 'required|string|max:7',
        ]);

        CategoriaGasto::create([
            'user_id' => auth()->id(),
            'nombre' => $request->nombre,
            'presupuesto_mensual' => $request->presupuesto_mensual ?? 0,
            'color' => $request->color,
        ]);

        return redirect()->back()->with('success', 'Categoría creada correctamente');
    }
}
