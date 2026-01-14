<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        // Obtenemos todos los ingresos del usuario logueado
        $ingresos = Ingreso::where('user_id', auth()->id())->orderBy('fecha', 'desc')->get();
        
        // Calculamos el total de ingresos
        $total_ingresos = $ingresos->sum('monto');

        // Agrupamos por categoría y sumamos el monto de cada una para el gráfico
        $ingresosPorCategoria = Ingreso::where('user_id', Auth::id())
            ->selectRaw('fuente, SUM(monto) as total')
            ->groupBy('fuente')
            ->get();

        // Pasamos la variable a la vista (fíjate que ahora se llama $total_ingresos)
        return view('ingresos.index', compact('ingresos', 'total_ingresos', 'ingresosPorCategoria'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion' => 'required|string',
            'monto' => 'required|numeric',
            'fuente' => 'required|string',
            'fecha' => 'required|date',
        ]);

        $data['user_id'] = auth()->id();
        Ingreso::create($data);

        return redirect()->back()->with('success', 'Ingreso registrado');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ingreso $ingreso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingreso $ingreso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ingreso $ingreso)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ingreso $ingreso)
    {
        //
    }
}
