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

    public function edit($id) // Cambiamos a $id para buscarlo manualmente si el Model Binding falla
    {
        $categoriaGasto = \App\Models\CategoriaGasto::findOrFail($id);

        // Verificamos propiedad
        if ((int) $categoriaGasto->user_id !== (int) auth()->id()) {
            abort(403, 'No tienes permiso para editar esta categoría.');
        }
        
        return view('categorias.edit', compact('categoriaGasto'));
    }

    public function update(Request $request, $id) // Recibimos el $id directamente
    {
        $categoriaGasto = CategoriaGasto::findOrFail($id);

        if ((int) $categoriaGasto->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => 'required|string|max:7', // Validación para el código hex #000000
            'presupuesto_mensual' => 'nullable|numeric',
        ]);

        $categoriaGasto->update([
            'nombre' => $request->nombre,
            'color' => $request->color,
            'presupuesto_mensual' => $request->presupuesto_mensual ?? $categoriaGasto->presupuesto_mensual,
        ]);

        return redirect()->route('categorias-gastos.index')->with('success', 'Categoría actualizada correctamente');
    }

    public function destroy($id)
    {
        $categoria = CategoriaGasto::findOrFail($id);

        // 1. Seguridad: Verificar propiedad
        if ((int) $categoria->user_id !== (int) auth()->id()) {
            abort(403);
        }

        // 2. Validación: comprobar registros vinculados antes de borrar
        if ($categoria->gastos()->count() > 0) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar "' . $categoria->nombre . '": tiene gastos asociados. Reasígnalos primero.');
        }

        if ($categoria->gastosFijos()->count() > 0) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar "' . $categoria->nombre . '": tiene gastos fijos asociados. Reasígnalos primero.');
        }

        if ($categoria->financiaciones()->count() > 0) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar "' . $categoria->nombre . '": tiene financiaciones asociadas. Reasígnalas primero.');
        }

        // 3. Borrado si está limpia
        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
