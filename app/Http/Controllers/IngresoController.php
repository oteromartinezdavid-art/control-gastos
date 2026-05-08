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

        // 1. Navegación por Mes/Año (Valores por defecto)
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        $fechaObjeto = Carbon::create($anio, $mes, 1);

        // 2. Iniciar Query base
        $query = Ingreso::with('fuenteIngreso')->where('user_id', $user_id);

        // 3. --- APLICACIÓN DE FILTROS ---
        
        // Filtro por Rango de Fechas (Si se usan, ignoramos el selector de mes/año)
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            if ($request->filled('fecha_inicio')) {
                $query->where('fecha', '>=', $request->fecha_inicio);
            }
            if ($request->filled('fecha_fin')) {
                $query->where('fecha', '<=', $request->fecha_fin);
            }
        } else {
            // Filtro por mes/año de la navegación
            $query->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
        }

        // Filtro por Fuente de Ingreso
        if ($request->filled('fuente_ingreso_id')) {
            $query->where('fuente_ingreso_id', $request->fuente_ingreso_id);
        }

        // Filtro por Descripción (Buscador)
        if ($request->filled('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        // 4. Obtener resultados finales
        $ingresos = $query->latest('fecha')->get();
        $total_ingresos = $ingresos->sum('monto');

        // 5. Datos para el gráfico (Reaplicando los mismos filtros)
        $ingresosPorFuente = Ingreso::where('ingresos.user_id', $user_id)
            ->join('fuente_ingresos', 'ingresos.fuente_ingreso_id', '=', 'fuente_ingresos.id')
            ->when(!$request->filled('fecha_inicio') && !$request->filled('fecha_fin'), function($q) use ($mes, $anio) {
                return $q->whereMonth('ingresos.fecha', $mes)->whereYear('ingresos.fecha', $anio);
            })
            ->when($request->filled('fecha_inicio'), fn($q) => $q->where('ingresos.fecha', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'), fn($q) => $q->where('ingresos.fecha', '<=', $request->fecha_fin))
            ->when($request->filled('fuente_ingreso_id'), fn($q) => $q->where('ingresos.fuente_ingreso_id', $request->fuente_ingreso_id))
            ->when($request->filled('descripcion'), fn($q) => $q->where('ingresos.descripcion', 'like', '%' . $request->descripcion . '%'))
            ->selectRaw('fuente_ingresos.nombre as fuente_nombre, SUM(monto) as total')
            ->groupBy('fuente_ingresos.nombre')
            ->get();

        $fuentes = FuenteIngreso::where('user_id', $user_id)->orderBy('nombre')->get();
        $ingreso = new Ingreso();

        return view('ingresos.index', compact(
            'ingresos', 'ingresosPorFuente', 'fuentes', 
            'total_ingresos', 'mes', 'anio', 'fechaObjeto', 
            'ingreso', 'request'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'monto' => 'required|numeric',
            'fuente_ingreso_id' => 'required|exists:fuente_ingresos,id',
            'fecha' => 'required|date',
        ]);

        // Generamos el hash preventivo (igual que en gastos)
        $hash = md5($request->fecha . strtoupper(trim($request->descripcion)) . round((float)$request->monto, 2) . auth()->id());

        Ingreso::create([
            'user_id' => auth()->id(),
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'fuente_ingreso_id' => $request->fuente_ingreso_id,
            'fecha' => $request->fecha,
            'hash' => $hash,
        ]);

        return redirect()->back()->with('success', 'Ingreso registrado correctamente');
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

        // Recalcular hash al actualizar
        $validated['hash'] = md5($request->fecha . strtoupper(trim($request->descripcion)) . round((float)$request->monto, 2) . auth()->id());

        $ingreso->update($validated);

        return redirect()->route('ingresos.index')->with('success', 'Ingreso actualizado correctamente');
    }

    public function destroy(Ingreso $ingreso)
    {
        if ($ingreso->user_id === auth()->id()) {
            $ingreso->delete();
        }
        return redirect()->back()->with('success', 'Ingreso eliminado');
    }

    public function edit(Ingreso $ingreso)
    {
        if ($ingreso->user_id !== auth()->id()) abort(403);
        $fuentes = FuenteIngreso::where('user_id', auth()->id())->get();
        return view('ingresos.edit', compact('ingreso', 'fuentes'));
    }
}