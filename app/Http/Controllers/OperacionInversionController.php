<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Dividendo;
use App\Models\OperacionInversion;
use App\Services\InversionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperacionInversionController extends Controller
{
    public function __construct(private InversionService $inversionService) {}

    public function index(Request $request)
    {
        $userId  = auth()->id();
        $activos = Activo::where('user_id', $userId)->orderBy('ticker')->get();

        // Años disponibles para el selector fiscal (compatible SQLite y MySQL)
        $aniosDisponibles = OperacionInversion::where('user_id', $userId)
            ->pluck('fecha')
            ->map(fn($f) => \Carbon\Carbon::parse($f)->year)
            ->unique()
            ->sortDesc()
            ->values();

        $query = OperacionInversion::with('activo')
            ->where('user_id', $userId);

        if ($request->filled('activo_id')) {
            $query->where('activo_id', $request->activo_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('anio')) {
            $query->whereYear('fecha', $request->anio);
        }

        $operaciones = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->get();

        foreach ($operaciones as $op) {
            if ($op->tipo === 'venta') {
                $op->pnl = $this->inversionService->calcularPnLVenta($userId, $op);
            }
        }

        $totalComisiones     = $operaciones->sum('total_gastos');
        $desglosGastos = [
            'bancaria' => $operaciones->sum(fn($o) => (float)$o->comision),
            'bolsa'    => $operaciones->sum(fn($o) => (float)$o->comision_bolsa),
            'impuestos'=> $operaciones->sum(fn($o) => (float)$o->impuestos),
            'divisa'   => $operaciones->sum(fn($o) => (float)$o->comision_divisa),
        ];
        $totalInvertido  = $operaciones->where('tipo', 'compra')->sum(fn($o) => (float)$o->cantidad * (float)$o->precio_unitario);
        $totalVendido    = $operaciones->where('tipo', 'venta')->sum(fn($o) => (float)$o->cantidad * (float)$o->precio_unitario);

        // P&L total de ventas del período filtrado (ganancias patrimoniales IRPF)
        $pnlPeriodo = $operaciones->where('tipo', 'venta')->sum('pnl');

        // Dividendos del año filtrado (rendimientos capital mobiliario IRPF)
        $dividendosAnio   = collect();
        $totalDivBruto    = 0;
        $totalDivRetencion = 0;
        $totalDivNeto     = 0;

        if ($request->filled('anio')) {
            $dividendosAnio = Dividendo::with('activo')
                ->where('user_id', $userId)
                ->whereYear('fecha', $request->anio)
                ->orderBy('fecha')
                ->get();

            $totalDivBruto     = $dividendosAnio->sum('monto_bruto');
            $totalDivRetencion = $dividendosAnio->sum('retencion');
            $totalDivNeto      = $dividendosAnio->sum('monto_neto');
        }

        // Desglose FIFO por venta y compras del año — solo para informe fiscal imprimible
        $ventasDetalle = collect();
        $comprasAnio   = collect();
        if ($request->filled('anio')) {
            foreach ($operaciones->where('tipo', 'venta') as $v) {
                $detalle = $this->inversionService->getDetalleFIFOVenta($userId, $v);
                $ventasDetalle->push(array_merge(['operacion' => $v], $detalle));
            }
            $comprasAnio = $operaciones->where('tipo', 'compra')->sortBy('fecha')->values();
        }

        return view('inversiones.operaciones.index', compact(
            'operaciones', 'activos', 'request',
            'totalComisiones', 'desglosGastos', 'totalInvertido', 'totalVendido',
            'pnlPeriodo', 'aniosDisponibles',
            'dividendosAnio', 'totalDivBruto', 'totalDivRetencion', 'totalDivNeto',
            'ventasDetalle', 'comprasAnio'
        ));
    }

    public function store(Request $request)
    {
        $userId = auth()->id();

        $request->validate([
            'activo_id'       => ['required', Rule::exists('activos', 'id')->where('user_id', auth()->id())],
            'tipo'            => 'required|in:compra,venta',
            'fecha'           => 'required|date',
            'cantidad'        => 'required|numeric|min:0.0001',
            'precio_unitario' => 'required|numeric|min:0',
            'comision'        => 'nullable|numeric|min:0',
            'comision_bolsa'  => 'nullable|numeric|min:0',
            'impuestos'       => 'nullable|numeric|min:0',
            'comision_divisa' => 'nullable|numeric|min:0',
            'notas'           => 'nullable|string|max:500',
        ]);

        $activo = Activo::where('id', $request->activo_id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($request->tipo === 'venta') {
            $disponible = $this->inversionService->getCantidadDisponible($userId, $activo->id);
            if ((float)$request->cantidad > $disponible + 0.0001) {
                return back()
                    ->withErrors(['cantidad' => "Unidades insuficientes. Disponibles: " . number_format($disponible, 4)])
                    ->withInput();
            }
        }

        OperacionInversion::create([
            'user_id'         => $userId,
            'activo_id'       => $request->activo_id,
            'tipo'            => $request->tipo,
            'fecha'           => $request->fecha,
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'comision'        => $request->comision        ?? 0,
            'comision_bolsa'  => $request->comision_bolsa  ?? 0,
            'impuestos'       => $request->impuestos        ?? 0,
            'comision_divisa' => $request->comision_divisa ?? 0,
            'notas'           => $request->notas,
        ]);

        return redirect()->route('inversiones.operaciones.index')->with('success', 'Operación registrada correctamente.');
    }

    public function edit(OperacionInversion $operacion)
    {
        if ($operacion->user_id !== auth()->id()) abort(403);
        $activos = Activo::where('user_id', auth()->id())->orderBy('ticker')->get();
        return view('inversiones.operaciones.edit', compact('operacion', 'activos'));
    }

    public function update(Request $request, OperacionInversion $operacion)
    {
        if ($operacion->user_id !== auth()->id()) abort(403);

        $userId = auth()->id();

        $request->validate([
            'tipo'            => 'required|in:compra,venta',
            'fecha'           => 'required|date',
            'cantidad'        => 'required|numeric|min:0.0001',
            'precio_unitario' => 'required|numeric|min:0',
            'comision'        => 'nullable|numeric|min:0',
            'comision_bolsa'  => 'nullable|numeric|min:0',
            'impuestos'       => 'nullable|numeric|min:0',
            'comision_divisa' => 'nullable|numeric|min:0',
            'notas'           => 'nullable|string|max:500',
        ]);

        // Si es venta, verificar que hay suficientes unidades (excluyendo esta operación)
        if ($request->tipo === 'venta') {
            $disponible = $this->inversionService->getCantidadDisponible($userId, $operacion->activo_id);
            // Sumar de vuelta las unidades de esta operación si ya era venta
            $ajuste = $operacion->tipo === 'venta' ? (float)$operacion->cantidad : 0;
            if ((float)$request->cantidad > $disponible + $ajuste + 0.0001) {
                return back()
                    ->withErrors(['cantidad' => "Unidades insuficientes. Disponibles: " . number_format($disponible + $ajuste, 4)])
                    ->withInput();
            }
        }

        $operacion->update([
            'tipo'            => $request->tipo,
            'fecha'           => $request->fecha,
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'comision'        => $request->comision        ?? 0,
            'comision_bolsa'  => $request->comision_bolsa  ?? 0,
            'impuestos'       => $request->impuestos        ?? 0,
            'comision_divisa' => $request->comision_divisa ?? 0,
            'notas'           => $request->notas,
        ]);

        // Redirigir al detalle del activo si venimos de ahí, si no al listado de operaciones
        $referer = $request->headers->get('referer', '');
        if (str_contains($referer, '/inversiones/activos/')) {
            return redirect()->back()->with('success', 'Operación actualizada correctamente.');
        }
        return redirect()->route('inversiones.operaciones.index')->with('success', 'Operación actualizada correctamente.');
    }

    public function destroy(OperacionInversion $operacion)
    {
        if ($operacion->user_id !== auth()->id()) abort(403);
        $operacion->delete();
        return redirect()->route('inversiones.operaciones.index')->with('success', 'Operación eliminada.');
    }
}
