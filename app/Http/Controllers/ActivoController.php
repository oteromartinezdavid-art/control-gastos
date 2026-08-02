<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Dividendo;
use App\Models\OperacionInversion;
use App\Services\InversionService;
use Illuminate\Http\Request;

class ActivoController extends Controller
{
    public function __construct(private InversionService $inversionService) {}

    public function index()
    {
        $activos = Activo::where('user_id', auth()->id())->orderBy('ticker')->get();
        return view('inversiones.activos.index', compact('activos'));
    }

    public function show(Activo $activo)
    {
        if ($activo->user_id !== auth()->id()) abort(403);

        $userId = auth()->id();

        $operaciones = OperacionInversion::where('activo_id', $activo->id)
            ->where('user_id', $userId)
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        foreach ($operaciones as $op) {
            if ($op->tipo === 'venta') {
                $op->pnl = $this->inversionService->calcularPnLVenta($userId, $op);
            }
        }

        $dividendos = Dividendo::where('activo_id', $activo->id)
            ->where('user_id', $userId)
            ->orderBy('fecha')
            ->get();

        // Posición abierta
        $lotes        = $this->inversionService->getLotesDisponibles($userId, $activo->id);
        $cantidad     = array_sum(array_column($lotes, 'cantidad_disponible'));
        $precioMedio  = $this->inversionService->getPrecioMedio($userId, $activo->id);

        $infoMoneda       = $this->inversionService->getCotizacionConMoneda($activo->ticker);
        $moneda           = $infoMoneda['moneda'];
        $cotizacionNativa = $infoMoneda['precio'];
        $cotizacion       = $this->inversionService->getCotizacionEnEur($activo->ticker);

        if ($activo->moneda !== $moneda) {
            $activo->update(['moneda' => $moneda]);
        }

        $invActual    = $cantidad * $precioMedio;
        $valorActual  = $cotizacion !== null ? $cantidad * $cotizacion : null;
        $pnlLatente   = $valorActual !== null ? $valorActual - $invActual : null;
        $pnlLatentePct = ($invActual > 0 && $pnlLatente !== null)
            ? ($pnlLatente / $invActual) * 100
            : null;

        // KPIs históricos
        $totalInvertido  = $operaciones->where('tipo', 'compra')
            ->sum(fn($o) => (float)$o->cantidad * (float)$o->precio_unitario + $o->total_gastos);
        $pnlRealizado    = $operaciones->where('tipo', 'venta')->sum('pnl');
        $totalDivNeto    = $dividendos->sum('monto_neto');
        $totalDivBruto   = $dividendos->sum('monto_bruto');
        $totalComisiones = $operaciones->sum('total_gastos');
        $desglosGastos   = [
            'bancaria' => $operaciones->sum(fn($o) => (float)$o->comision),
            'bolsa'    => $operaciones->sum(fn($o) => (float)$o->comision_bolsa),
            'impuestos'=> $operaciones->sum(fn($o) => (float)$o->impuestos),
            'divisa'   => $operaciones->sum(fn($o) => (float)$o->comision_divisa),
        ];
        $totalReturn     = $pnlRealizado + ($pnlLatente ?? 0) + $totalDivNeto;

        return view('inversiones.activos.show', compact(
            'activo', 'operaciones', 'dividendos', 'lotes',
            'cantidad', 'precioMedio', 'cotizacion', 'cotizacionNativa', 'moneda',
            'invActual', 'valorActual', 'pnlLatente', 'pnlLatentePct',
            'totalInvertido', 'pnlRealizado', 'totalDivNeto', 'totalDivBruto',
            'totalComisiones', 'desglosGastos', 'totalReturn'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticker'  => 'required|string|max:20',
            'nombre'  => 'required|string|max:255',
            'sector'  => 'nullable|string|max:100',
            'mercado' => 'nullable|string|max:50',
        ]);

        $ticker = strtoupper(trim($request->ticker));

        if (Activo::where('user_id', auth()->id())->where('ticker', $ticker)->exists()) {
            return back()->withErrors(['ticker' => 'Ya tienes registrado un activo con ese ticker.'])->withInput();
        }

        Activo::create([
            'user_id' => auth()->id(),
            'ticker'  => $ticker,
            'nombre'  => $request->nombre,
            'sector'  => $request->sector,
            'mercado' => $request->mercado,
        ]);

        return redirect()->route('inversiones.activos.index')->with('success', 'Activo añadido correctamente.');
    }

    public function update(Request $request, Activo $activo)
    {
        if ($activo->user_id !== auth()->id()) abort(403);

        $request->validate([
            'ticker'  => 'required|string|max:20',
            'nombre'  => 'required|string|max:255',
            'sector'  => 'nullable|string|max:100',
            'mercado' => 'nullable|string|max:50',
        ]);

        $activo->update([
            'ticker'  => strtoupper(trim($request->ticker)),
            'nombre'  => $request->nombre,
            'sector'  => $request->sector,
            'mercado' => $request->mercado,
        ]);

        return redirect()->route('inversiones.activos.index')->with('success', 'Activo actualizado.');
    }

    public function destroy(Activo $activo)
    {
        if ($activo->user_id !== auth()->id()) abort(403);
        $activo->delete();
        return redirect()->route('inversiones.activos.index')->with('success', 'Activo eliminado.');
    }
}
