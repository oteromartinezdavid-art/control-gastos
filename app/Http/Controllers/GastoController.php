<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;
use App\Models\CategoriaGasto; 
use App\Models\FuenteIngreso;  
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GastoController extends Controller
{
    // Mostrar la lista de gastos del usuario
    public function index(Request $request)
    {
        $user_id = auth()->id();

        // 1. Detectar el mes y año de la URL o usar el actual por defecto
        $mesActual = $request->get('mes', now()->month);
        $anioActual = $request->get('anio', now()->year);

        // 2. Crear un objeto Carbon para facilitar la navegación
        $fechaObjeto = \Carbon\Carbon::create($anioActual, $mesActual, 1);

        // 3. Listado filtrado por el mes seleccionado
        $gastos = Gasto::with('categoriaGasto')
            ->where('user_id', $user_id)
            ->whereMonth('fecha', $mesActual)
            ->whereYear('fecha', $anioActual)
            ->orderBy('fecha', 'desc')
            ->get();

        // 4. El total sumará solo los gastos de ese mes específico
        $total = $gastos->sum('monto');

        // 5. Gráfico filtrado por mes seleccionado
        $gastosPorCategoria = Gasto::where('gastos.user_id', $user_id)
            ->whereMonth('gastos.fecha', $mesActual)
            ->whereYear('gastos.fecha', $anioActual)
            ->join('categoria_gastos', 'gastos.categoria_id', '=', 'categoria_gastos.id')
            ->select('categoria_gastos.nombre as categoria', 'categoria_gastos.color', \DB::raw('SUM(monto) as total'))
            ->groupBy('categoria_gastos.nombre', 'categoria_gastos.color') // Añadido color al groupBy para evitar errores SQL
            ->get();

        // 6. Categorías para el desplegable
        $categorias = CategoriaGasto::where('user_id', $user_id)
            ->orderBy('nombre')
            ->get();

        // Creamos una instancia vacía de Gasto para que el formulario @include('gastos.form-fields') 
        // tenga un objeto donde buscar (aunque esté vacío) y no lance error.
        $gasto = new Gasto(); 
        
        return view('gastos.index', compact(
            'gastos', 
            'total', 
            'gastosPorCategoria', 
            'categorias', 
            'mesActual', 
            'anioActual', 
            'fechaObjeto',
            'gasto' // No olvides añadirlo al compact
        ));
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
            'categoria_id' => 'required|exists:categoria_gastos,id', // Validamos que el ID existe
            'fecha' => 'required|date',
        ]);

        Gasto::create([
            'user_id' => auth()->id(),
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'categoria_id' => $request->categoria_id, // Guardamos el ID
            'fecha' => $request->fecha,
        ]);

        return redirect()->back()->with('success', 'Gasto guardado correctamente');
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
    public function edit(Gasto $gasto)
    {
        // Seguridad: verificar que el gasto es del usuario
        if ($gasto->user_id !== auth()->id()) abort(403);
        
        $categorias = CategoriaGasto::where('user_id', auth()->id())->get();
        return view('gastos.edit', compact('gasto', 'categorias'));
    }

    // Procesa el cambio en la base de datos
    public function update(Request $request, Gasto $gasto)
    {
        if ($gasto->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'categoria_id' => 'required|exists:categoria_gastos,id',
            'fecha' => 'required|date',
        ]);

        $gasto->update($validated);

        return redirect()->route('gastos.index')->with('success', 'Gasto actualizado correctamente.');
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
