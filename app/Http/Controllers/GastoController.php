<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GastoController extends Controller
{
    // Mostrar la lista de gastos del usuario
   public function index()
    {
        $gastos = Gasto::where('user_id', Auth::id())->orderBy('fecha', 'desc')->get();
        $total = $gastos->sum('monto');

        // Agrupamos por categoría y sumamos el monto de cada una para el gráfico
        $gastosPorCategoria = Gasto::where('user_id', Auth::id())
            ->selectRaw('categoria, SUM(monto) as total')
            ->groupBy('categoria')
            ->get();

        return view('gastos.index', compact('gastos', 'total', 'gastosPorCategoria'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    // Guardar el gasto en la BD
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'categoria' => 'required|string',
            'fecha' => 'required|date',
        ]);

        Gasto::create([
            'user_id' => Auth::id(),
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'categoria' => $request->categoria,
            'fecha' => $request->fecha,
        ]);

        return redirect()->route('gastos.index')->with('success', 'Gasto guardado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gasto $gasto)
    {
        // Verificamos que el gasto pertenezca al usuario logueado
        if ($gasto->user_id !== Auth::id()) {
            abort(403);
        }

        $gasto->delete();

        return redirect()->route('gastos.index')->with('success', 'Gasto eliminado');
    }
}
