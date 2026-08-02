<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\CategoriaGasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        $user_id = auth()->id();

        // 1. Navegación por Mes/Año (Mantener compatibilidad con tu vista actual)
        $mesActual = $request->get('mes', now()->month);
        $anioActual = $request->get('anio', now()->year);
        $fechaObjeto = Carbon::create($anioActual, $mesActual, 1);

        // 2. Iniciar Query con Relaciones
        $query = Gasto::with('categoriaGasto')->where('user_id', $user_id);

        // 3. --- APLICACIÓN DE FILTROS ---
        
        // Filtro por Rango de Fechas (Si existen, sobreescriben el mesActual)
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            if ($request->filled('fecha_inicio')) {
                $query->where('fecha', '>=', $request->fecha_inicio);
            }
            if ($request->filled('fecha_fin')) {
                $query->where('fecha', '<=', $request->fecha_fin);
            }
        } else {
            // Si no hay rango específico, aplicamos tu filtro de mes/año por defecto
            $query->whereMonth('fecha', $mesActual)->whereYear('fecha', $anioActual);
        }

        // Filtro por Categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por Descripción (Buscador)
        if ($request->filled('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        // 4. Obtener resultados (Cambiamos a get() para el total, o paginate() si prefieres)
        $gastos = $query->orderBy('fecha', 'desc')->get();
        $total = $gastos->sum('monto');

        // 5. --- DATOS PARA EL GRÁFICO (Basado en la misma Query filtrada) ---
        // Clonamos la query para obtener el agrupamiento sin afectar al listado
        $gastosPorCategoria = Gasto::where('gastos.user_id', $user_id)
            ->join('categoria_gastos', 'gastos.categoria_id', '=', 'categoria_gastos.id')
            // Reaplicamos los mismos filtros de fecha para que el rosco sea coherente
            ->when(!$request->filled('fecha_inicio') && !$request->filled('fecha_fin'), function($q) use ($mesActual, $anioActual) {
                return $q->whereMonth('gastos.fecha', $mesActual)->whereYear('gastos.fecha', $anioActual);
            })
            ->when($request->filled('fecha_inicio'), fn($q) => $q->where('gastos.fecha', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'), fn($q) => $q->where('gastos.fecha', '<=', $request->fecha_fin))
            ->when($request->filled('categoria_id'), fn($q) => $q->where('gastos.categoria_id', $request->categoria_id))
            ->when($request->filled('descripcion'), fn($q) => $q->where('gastos.descripcion', 'like', '%' . $request->descripcion . '%'))
            ->select('categoria_gastos.nombre as categoria', 'categoria_gastos.color', DB::raw('SUM(monto) as total'))
            ->groupBy('categoria_gastos.nombre', 'categoria_gastos.color')
            ->get();

        // 6. Categorías para el desplegable
        $categorias = CategoriaGasto::where('user_id', $user_id)->orderBy('nombre')->get();

        $gasto = new Gasto(); 
        
        return view('gastos.index', compact(
            'gastos', 
            'total', 
            'gastosPorCategoria', 
            'categorias', 
            'mesActual', 
            'anioActual', 
            'fechaObjeto',
            'gasto',
            'request' // Importante para que el formulario mantenga los valores
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'categoria_id' => ['required', Rule::exists('categoria_gastos', 'id')->where('user_id', auth()->id())],
            'fecha' => 'required|date',
        ]);

        Gasto::create([
            'user_id' => auth()->id(),
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'categoria_id' => $request->categoria_id,
            'fecha' => $request->fecha,
            // Aquí podrías generar el hash si decides implementarlo también en el manual
            'hash' => md5($request->fecha . strtoupper(trim($request->descripcion)) . round((float)$request->monto, 2) . auth()->id()),
        ]);

        return redirect()->back()->with('success', 'Gasto guardado correctamente');
    }

    // Los métodos edit, update y destroy se mantienen igual...
    public function edit(Request $request, Gasto $gasto)
    {
        if ($gasto->user_id !== auth()->id()) abort(403);
        $categorias = CategoriaGasto::where('user_id', auth()->id())->get();
        $mes  = $request->get('mes',  \Carbon\Carbon::parse($gasto->fecha)->month);
        $anio = $request->get('anio', \Carbon\Carbon::parse($gasto->fecha)->year);
        return view('gastos.edit', compact('gasto', 'categorias', 'mes', 'anio'));
    }

    public function update(Request $request, Gasto $gasto)
    {
        if ($gasto->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'categoria_id' => ['required', Rule::exists('categoria_gastos', 'id')->where('user_id', auth()->id())],
            'fecha' => 'required|date',
        ]);

        $validated['hash'] = md5($request->fecha . strtoupper(trim($request->descripcion)) . round((float)$request->monto, 2) . auth()->id());

        $gasto->update($validated);

        $params = array_filter(['mes' => $request->_mes, 'anio' => $request->_anio]);
        return redirect()->route('gastos.index', $params)->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(Gasto $gasto)
    {
        if ($gasto->user_id !== auth()->id()) abort(403);
        $mes  = request('mes');
        $anio = request('anio');
        $gasto->delete();
        $params = array_filter(['mes' => $mes, 'anio' => $anio]);
        return redirect()->route('gastos.index', $params)->with('success', 'Gasto eliminado');
    }
}