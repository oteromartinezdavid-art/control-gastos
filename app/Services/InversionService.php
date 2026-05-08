<?php

namespace App\Services;

use App\Models\Activo;
use App\Models\Dividendo;
use App\Models\OperacionInversion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class InversionService
{
    /**
     * Returns the FIFO-remaining buy lots for a given asset.
     * Each lot includes cantidad_disponible (units not yet sold).
     */
    public function getLotesDisponibles(int $userId, int $activoId): array
    {
        $compras = OperacionInversion::where('user_id', $userId)
            ->where('activo_id', $activoId)
            ->where('tipo', 'compra')
            ->orderBy('fecha')
            ->orderBy('id')
            ->get()
            ->map(fn($op) => [
                'id'                  => $op->id,
                'fecha'               => $op->fecha->format('Y-m-d'),
                'precio_unitario'     => (float) $op->precio_unitario,
                'comision'            => (float) $op->comision,
                'cantidad_original'   => (float) $op->cantidad,
                'cantidad_disponible' => (float) $op->cantidad,
            ])
            ->toArray();

        $ventas = OperacionInversion::where('user_id', $userId)
            ->where('activo_id', $activoId)
            ->where('tipo', 'venta')
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        foreach ($ventas as $venta) {
            $pendiente = (float) $venta->cantidad;
            foreach ($compras as &$lote) {
                if ($pendiente <= 0) break;
                $consumir = min($lote['cantidad_disponible'], $pendiente);
                $lote['cantidad_disponible'] -= $consumir;
                $pendiente -= $consumir;
            }
        }

        return array_values(array_filter($compras, fn($l) => $l['cantidad_disponible'] > 0.0001));
    }

    public function getCantidadDisponible(int $userId, int $activoId): float
    {
        return array_sum(array_column($this->getLotesDisponibles($userId, $activoId), 'cantidad_disponible'));
    }

    /**
     * Weighted average cost of open position (including proportional commissions).
     */
    public function getPrecioMedio(int $userId, int $activoId): float
    {
        $lotes = $this->getLotesDisponibles($userId, $activoId);
        if (empty($lotes)) return 0;

        $totalCosto = 0;
        $totalCantidad = 0;

        foreach ($lotes as $lote) {
            $proporcion = $lote['cantidad_disponible'] / $lote['cantidad_original'];
            $totalCosto += $lote['precio_unitario'] * $lote['cantidad_disponible']
                + $proporcion * $lote['comision'];
            $totalCantidad += $lote['cantidad_disponible'];
        }

        return $totalCantidad > 0 ? $totalCosto / $totalCantidad : 0;
    }

    /**
     * Fetches the current price from Yahoo Finance (cached 15 min).
     */
    public function getCotizacion(string $ticker): ?float
    {
        return Cache::remember("cotizacion_{$ticker}", 900, function () use ($ticker) {
            try {
                $response = Http::timeout(8)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ControlGastos/1.0)'])
                    ->get("https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}", [
                        'interval' => '1d',
                        'range'    => '1d',
                    ]);

                if ($response->successful()) {
                    $price = $response->json('chart.result.0.meta.regularMarketPrice');
                    return $price ? (float) $price : null;
                }
            } catch (\Throwable $e) {
                // Silently degrade — show N/D in the UI
            }
            return null;
        });
    }

    /**
     * Calculates the realized P&L for a single sell operation (FIFO cost basis).
     */
    public function calcularPnLVenta(int $userId, OperacionInversion $venta): float
    {
        $compras = OperacionInversion::where('user_id', $userId)
            ->where('activo_id', $venta->activo_id)
            ->where('tipo', 'compra')
            ->orderBy('fecha')
            ->orderBy('id')
            ->get()
            ->map(fn($op) => [
                'precio_unitario'     => (float) $op->precio_unitario,
                'comision'            => (float) $op->comision,
                'cantidad_original'   => (float) $op->cantidad,
                'cantidad_disponible' => (float) $op->cantidad,
            ])
            ->toArray();

        // Consume all sales that come before this one (FIFO order)
        $ventasAnteriores = OperacionInversion::where('user_id', $userId)
            ->where('activo_id', $venta->activo_id)
            ->where('tipo', 'venta')
            ->where(function ($q) use ($venta) {
                $q->where('fecha', '<', $venta->fecha)
                    ->orWhere(fn($q2) => $q2->where('fecha', '=', $venta->fecha)->where('id', '<', $venta->id));
            })
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        foreach ($ventasAnteriores as $v) {
            $pendiente = (float) $v->cantidad;
            foreach ($compras as &$lote) {
                if ($pendiente <= 0) break;
                $consumir = min($lote['cantidad_disponible'], $pendiente);
                $lote['cantidad_disponible'] -= $consumir;
                $pendiente -= $consumir;
            }
        }

        // Calculate the cost basis for the units being sold
        $pendiente  = (float) $venta->cantidad;
        $costoBasis = 0;

        foreach ($compras as $lote) {
            if ($pendiente <= 0) break;
            $consumir   = min($lote['cantidad_disponible'], $pendiente);
            $proporcion = $lote['cantidad_original'] > 0 ? $consumir / $lote['cantidad_original'] : 0;
            $costoBasis += $consumir * $lote['precio_unitario'] + $proporcion * $lote['comision'];
            $pendiente  -= $consumir;
        }

        $valorTransmision = (float) $venta->cantidad * (float) $venta->precio_unitario - (float) $venta->comision;

        return $valorTransmision - $costoBasis;
    }

    /**
     * Same FIFO logic as calcularPnLVenta but also returns the consumed buy lots.
     * Used for generating detailed fiscal reports.
     */
    public function getDetalleFIFOVenta(int $userId, OperacionInversion $venta): array
    {
        $compras = OperacionInversion::where('user_id', $userId)
            ->where('activo_id', $venta->activo_id)
            ->where('tipo', 'compra')
            ->orderBy('fecha')
            ->orderBy('id')
            ->get()
            ->map(fn($op) => [
                'fecha'               => $op->fecha->format('d/m/Y'),
                'precio_unitario'     => (float) $op->precio_unitario,
                'comision'            => (float) $op->comision,
                'cantidad_original'   => (float) $op->cantidad,
                'cantidad_disponible' => (float) $op->cantidad,
            ])
            ->toArray();

        $ventasAnteriores = OperacionInversion::where('user_id', $userId)
            ->where('activo_id', $venta->activo_id)
            ->where('tipo', 'venta')
            ->where(function ($q) use ($venta) {
                $q->where('fecha', '<', $venta->fecha)
                    ->orWhere(fn($q2) => $q2->where('fecha', '=', $venta->fecha)->where('id', '<', $venta->id));
            })
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        foreach ($ventasAnteriores as $v) {
            $pendiente = (float) $v->cantidad;
            foreach ($compras as &$lote) {
                if ($pendiente <= 0) break;
                $consumir = min($lote['cantidad_disponible'], $pendiente);
                $lote['cantidad_disponible'] -= $consumir;
                $pendiente -= $consumir;
            }
        }

        $pendiente       = (float) $venta->cantidad;
        $lotesConsumidos = [];
        $costoBasis      = 0;

        foreach ($compras as $lote) {
            if ($pendiente <= 0) break;
            $consumir     = min($lote['cantidad_disponible'], $pendiente);
            $proporcion   = $lote['cantidad_original'] > 0 ? $consumir / $lote['cantidad_original'] : 0;
            $comisionProp = $proporcion * $lote['comision'];
            $coste        = $consumir * $lote['precio_unitario'] + $comisionProp;

            $costoBasis += $coste;
            $pendiente  -= $consumir;

            $lotesConsumidos[] = [
                'fecha_compra'          => $lote['fecha'],
                'cantidad'              => $consumir,
                'precio_unitario'       => $lote['precio_unitario'],
                'comision_proporcional' => $comisionProp,
                'coste_total'           => $coste,
            ];
        }

        $valorTransmision = (float) $venta->cantidad * (float) $venta->precio_unitario - (float) $venta->comision;

        return [
            'lotes_consumidos'  => $lotesConsumidos,
            'valor_transmision' => $valorTransmision,
            'coste_adquisicion' => $costoBasis,
            'pnl'               => $valorTransmision - $costoBasis,
        ];
    }

    /**
     * Returns one entry per held asset with cost/market/P&L data.
     */
    public function getResumenCartera(int $userId): array
    {
        $activos    = Activo::where('user_id', $userId)->get();
        $posiciones = [];

        foreach ($activos as $activo) {
            $lotes     = $this->getLotesDisponibles($userId, $activo->id);
            $cantidad  = array_sum(array_column($lotes, 'cantidad_disponible'));

            if ($cantidad < 0.0001) continue;

            $precioMedio    = $this->getPrecioMedio($userId, $activo->id);
            $inversionTotal = $cantidad * $precioMedio;
            $cotizacion     = $this->getCotizacion($activo->ticker);
            $valorActual    = $cotizacion !== null ? $cantidad * $cotizacion : null;
            $pnlLatente     = $valorActual !== null ? $valorActual - $inversionTotal : null;
            $pnlPct         = ($inversionTotal > 0 && $pnlLatente !== null)
                ? ($pnlLatente / $inversionTotal) * 100
                : null;

            $posiciones[] = [
                'activo'         => $activo,
                'cantidad'       => $cantidad,
                'precio_medio'   => $precioMedio,
                'inversion_total'=> $inversionTotal,
                'cotizacion'     => $cotizacion,
                'valor_actual'   => $valorActual,
                'pnl_latente'    => $pnlLatente,
                'pnl_pct'        => $pnlPct,
                'lotes'          => $lotes,
            ];
        }

        return $posiciones;
    }

    /**
     * Aggregated KPIs for the portfolio dashboard.
     */
    public function getKPIs(int $userId): array
    {
        $posiciones = $this->getResumenCartera($userId);

        $inversionTotal = array_sum(array_column($posiciones, 'inversion_total'));

        $valorCartera = 0;
        foreach ($posiciones as $p) {
            $valorCartera += $p['valor_actual'] ?? $p['inversion_total'];
        }

        $pnlLatente    = $valorCartera - $inversionTotal;
        $pnlLatentePct = $inversionTotal > 0 ? ($pnlLatente / $inversionTotal) * 100 : 0;

        // Realized P&L across all sell operations
        $ventas       = OperacionInversion::where('user_id', $userId)
            ->where('tipo', 'venta')
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();
        $pnlRealizado = 0;
        foreach ($ventas as $venta) {
            $pnlRealizado += $this->calcularPnLVenta($userId, $venta);
        }

        $totalDividendosNetos  = (float) Dividendo::where('user_id', $userId)->sum('monto_neto');
        $totalDividendosBrutos = (float) Dividendo::where('user_id', $userId)->sum('monto_bruto');
        $totalComisiones       = (float) OperacionInversion::where('user_id', $userId)->sum('comision');

        // YoC = total gross dividends / total historical investment cost
        $costoHistorico = (float) OperacionInversion::where('user_id', $userId)
            ->where('tipo', 'compra')
            ->selectRaw('SUM(cantidad * precio_unitario + comision) as total')
            ->value('total');
        $yieldOnCost = $costoHistorico > 0 ? ($totalDividendosBrutos / $costoHistorico) * 100 : 0;

        return [
            'inversion_total'        => $inversionTotal,
            'valor_cartera'          => $valorCartera,
            'pnl_latente'            => $pnlLatente,
            'pnl_latente_pct'        => $pnlLatentePct,
            'pnl_realizado'          => $pnlRealizado,
            'total_dividendos_netos' => $totalDividendosNetos,
            'total_comisiones'       => $totalComisiones,
            'yield_on_cost'          => $yieldOnCost,
        ];
    }
}
