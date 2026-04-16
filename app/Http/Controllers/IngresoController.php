<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingreso;          
use App\Models\FuenteIngreso;    
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IngresoController extends Controller
{
    
public function index(Request $request)
    {
        $user_id = auth()->id();

        // 1. Capturar mes y año de la URL o usar el actual por defecto
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        // 2. Obtener ingresos filtrados
        $ingresos = \App\Models\Ingreso::with('fuenteIngreso')
            ->where('user_id', $user_id)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->latest()
            ->get();

        $total_ingresos = $ingresos->sum('monto');

        // 3. Datos para el gráfico (también filtrados)
        $ingresosPorFuente = \App\Models\Ingreso::where('ingresos.user_id', $user_id)
            ->whereMonth('ingresos.fecha', $mes)
            ->whereYear('ingresos.fecha', $anio)
            ->join('fuente_ingresos', 'ingresos.fuente_ingreso_id', '=', 'fuente_ingresos.id')
            ->selectRaw('fuente_ingresos.nombre as fuente_nombre, SUM(monto) as total')
            ->groupBy('fuente_ingresos.nombre')
            ->get();

        $fuentes = \App\Models\FuenteIngreso::where('user_id', $user_id)->get();

        // 4. Crear un objeto Carbon para mostrar el nombre del mes en la vista
        $fechaObjeto = \Carbon\Carbon::create($anio, $mes, 1);


        $ingreso = new Ingreso();

        return view('ingresos.index', compact(
            'ingresos', 'ingresosPorFuente', 'fuentes', 
            'total_ingresos', 'mes', 'anio', 'fechaObjeto'
        ));
    }

    public function store(Request $request){
        // 1. Validamos que el ID de la fuente sea obligatorio y exista en la tabla
        $request->validate([
            'descripcion' => 'required|string',
            'monto' => 'required|numeric',
            'fuente_ingreso_id' => 'required|exists:fuente_ingresos,id', // Validamos el ID
            'fecha' => 'required|date',
        ]);

        // 2. Creamos el registro usando el ID
        \App\Models\Ingreso::create([
            'user_id' => auth()->id(),
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'fuente_ingreso_id' => $request->fuente_ingreso_id, // Guardamos el ID
            'fecha' => $request->fecha,
        ]);

        return redirect()->back()->with('success', 'Ingreso registrado correctamente');
    }

    public function destroy(Ingreso $ingreso)
    {
        // Seguridad: Verificar que el ingreso pertenece al usuario
        if ($ingreso->user_id === auth()->id()) {
            $ingreso->delete();
        }

        return redirect()->back()->with('success', 'Ingreso eliminado');
    }

    public function edit(Ingreso $ingreso)
    {
        if ($ingreso->user_id !== auth()->id()) abort(403);
        
        $fuentes = \App\Models\FuenteIngreso::where('user_id', auth()->id())->get();
        return view('ingresos.edit', compact('ingreso', 'fuentes'));
    }

    public function update(Request $request, Ingreso $ingreso)
    {
        if ($ingreso->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'descripcion' => 'required|string',
            'monto' => 'required|numeric',
            'fuente_ingreso_id' => 'required|exists:fuente_ingresos,id',
            'fecha' => 'required|date',
        ]);

        $ingreso->update($validated);

        return redirect()->route('ingresos.index')->with('success', 'Ingreso actualizado correctamente');
    }

    
}