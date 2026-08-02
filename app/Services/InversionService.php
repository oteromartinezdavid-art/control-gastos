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
                'comision'            => $op->total_gastos,
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
     * Fetches price + currency from Yahoo Finance (cached 15 min).
     * Returns ['precio' => ?float, 'moneda' => string]
     */
    public function getCotizacionConMoneda(string $ticker): array
    {
        return Cache::remember("cotizacion_full_{$ticker}", 900, function () use ($ticker) {
            try {
                $response = Http::timeout(8)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ControlGastos/1.0)'])
                    ->get("https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}", [
                        'interval' => '1d',
                        'range'    => '1d',
                    ]);

                if ($response->successful()) {
                    $meta   = $response->json('chart.result.0.meta');
                    $precio = $meta['regularMarketPrice'] ?? null;
                    $moneda = strtoupper($meta['currency'] ?? 'EUR');
                    return ['precio' => $precio ? (float) $precio : null, 'moneda' => $moneda];
                }
            } catch (\Throwable $e) {}
            return ['precio' => null, 'moneda' => 'EUR'];
        });
    }

    /**
     * Returns the current price in its native currency (backward compat).
     */
    public function getCotizacion(string $ticker): ?float
    {
        return $this->getCotizacionConMoneda($ticker)['precio'];
    }

    /**
     * Returns the current price converted to EUR.
     * Non-EUR prices are multiplied by the live exchange rate.
     */
    public function getCotizacionEnEur(string $ticker): ?float
    {
        $info = $this->getCotizacionConMoneda($ticker);
        if ($info['precio'] === null) return null;
        if ($info['moneda'] === 'EUR') return $info['precio'];

        $tasa = $this->getTipoCambio($info['moneda'], 'EUR');
        return $info['precio'] * $tasa;
    }

    /**
     * Fetches a spot FX rate from Yahoo Finance (cached 15 min).
     * E.g. getTipoCambio('USD','EUR') queries USDEUR=X.
     */
    private function getTipoCambio(string $from, string $to): float
    {
        return Cache::remember("tasa_{$from}_{$to}", 900, function () use ($from, $to) {
            try {
                $pair     = strtoupper("{$from}{$to}=X");
                $response = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ControlGastos/1.0)'])
                    ->get("https://query1.finance.yahoo.com/v8/finance/chart/{$pair}", [
                        'interval' => '1d',
                        'range'    => '1d',
                    ]);

                if ($response->successful()) {
                    $tasa = $response->json('chart.result.0.meta.regularMarketPrice');
                    return $tasa ? (float) $tasa : 1.0;
                }
            } catch (\Throwable $e) {}
            return 1.0; // fallback: sin conversión
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
                'comision'            => $op->total_gastos,
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

        $valorTransmision = (float) $venta->cantidad * (float) $venta->precio_unitario - $venta->total_gastos;

        return $valorTransmision - $costoBasis;
    }

    /**
     * Same as calcularPnLVenta but works from an already-loaded collection,
     * avoiding N+1 queries when processing multiple sales in bulk (e.g. getKPIs).
     */
    public function calcularPnLVentaDesdeColeccion(\Illuminate\Support\Collection $operaciones, OperacionInversion $venta): float
    {
        $compras = $operaciones
            ->where('activo_id', $venta->activo_id)
            ->where('tipo', 'compra')
            ->sortBy(fn($op) => [$op['fecha'] ?? '', $op['id'] ?? 0])
            ->map(fn($op) => [
                'precio_unitario'     => (float) $op->precio_unitario,
                'comision'            => $op->total_gastos,
                'cantidad_original'   => (float) $op->cantidad,
                'cantidad_disponible' => (float) $op->cantidad,
            ])
            ->values()
            ->toArray();

        $ventasAnteriores = $operaciones
            ->where('activo_id', $venta->activo_id)
            ->where('tipo', 'venta')
            ->filter(fn($v) =>
                $v->fecha < $venta->fecha ||
                ($v->fecha == $venta->fecha && $v->id < $venta->id)
            )
            ->sortBy(fn($op) => [$op['fecha'] ?? '', $op['id'] ?? 0]);

        foreach ($ventasAnteriores as $v) {
            $pendiente = (float) $v->cantidad;
            foreach ($compras as &$lote) {
                if ($pendiente <= 0) break;
                $consumir = min($lote['cantidad_disponible'], $pendiente);
                $lote['cantidad_disponible'] -= $consumir;
                $pendiente -= $consumir;
            }
        }

        $pendiente  = (float) $venta->cantidad;
        $costoBasis = 0;

        foreach ($compras as $lote) {
            if ($pendiente <= 0) break;
            $consumir   = min($lote['cantidad_disponible'], $pendiente);
            $proporcion = $lote['cantidad_original'] > 0 ? $consumir / $lote['cantidad_original'] : 0;
            $costoBasis += $consumir * $lote['precio_unitario'] + $proporcion * $lote['comision'];
            $pendiente  -= $consumir;
        }

        $valorTransmision = (float) $venta->cantidad * (float) $venta->precio_unitario - $venta->total_gastos;

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
                'comision'            => $op->total_gastos,
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

        $valorTransmision = (float) $venta->cantidad * (float) $venta->precio_unitario - $venta->total_gastos;

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

            $precioMedio       = $this->getPrecioMedio($userId, $activo->id);
            $inversionTotal    = $cantidad * $precioMedio;
            $infoMoneda        = $this->getCotizacionConMoneda($activo->ticker);
            $cotizacionNativa  = $infoMoneda['precio'];
            $moneda            = $infoMoneda['moneda'];
            $cotizacionEur     = $this->getCotizacionEnEur($activo->ticker);
            $valorActual       = $cotizacionEur !== null ? $cantidad * $cotizacionEur : null;
            $pnlLatente        = $valorActual !== null ? $valorActual - $inversionTotal : null;
            $pnlPct            = ($inversionTotal > 0 && $pnlLatente !== null)
                ? ($pnlLatente / $inversionTotal) * 100
                : null;

            $posiciones[] = [
                'activo'          => $activo,
                'cantidad'        => $cantidad,
                'precio_medio'    => $precioMedio,
                'inversion_total' => $inversionTotal,
                'cotizacion'      => $cotizacionEur,
                'cotizacion_nativa' => $cotizacionNativa,
                'moneda'          => $moneda,
                'valor_actual'    => $valorActual,
                'pnl_latente'     => $pnlLatente,
                'pnl_pct'         => $pnlPct,
                'lotes'           => $lotes,
            ];
        }

        return $posiciones;
    }

    /**
     * Portfolio cost basis (FIFO) at a given date, using only operations up to that date.
     * Returns ['total_coste' => float, 'posiciones' => [...]]
     */
    public function getCarteraEnFecha(int $userId, string $fecha): array
    {
        $operaciones = OperacionInversion::where('user_id', $userId)
            ->with('activo')
            ->where('fecha', '<=', $fecha)
            ->orderBy('fecha')->orderBy('id')
            ->get();

        $activoIds = $operaciones->pluck('activo_id')->unique();
        $posiciones = [];
        $totalCoste = 0;

        foreach ($activoIds as $activoId) {
            $compras = $operaciones->where('activo_id', $activoId)->where('tipo', 'compra')
                ->map(fn($op) => [
                    'precio_unitario'     => (float)$op->precio_unitario,
                    'comision'            => $op->total_gastos,
                    'cantidad_original'   => (float)$op->cantidad,
                    'cantidad_disponible' => (float)$op->cantidad,
                ])->values()->toArray();

            foreach ($operaciones->where('activo_id', $activoId)->where('tipo', 'venta') as $v) {
                $pend = (float)$v->cantidad;
                foreach ($compras as &$lote) {
                    if ($pend <= 0) break;
                    $c = min($lote['cantidad_disponible'], $pend);
                    $lote['cantidad_disponible'] -= $c;
                    $pend -= $c;
                }
            }

            $cantidad = array_sum(array_column($compras, 'cantidad_disponible'));
            if ($cantidad < 0.0001) continue;

            $coste = 0;
            foreach ($compras as $lote) {
                if ($lote['cantidad_disponible'] < 0.0001) continue;
                $prop = $lote['cantidad_original'] > 0 ? $lote['cantidad_disponible'] / $lote['cantidad_original'] : 0;
                $coste += $lote['cantidad_disponible'] * $lote['precio_unitario'] + $prop * $lote['comision'];
            }

            $activo = $operaciones->where('activo_id', $activoId)->first()->activo;
            $posiciones[] = ['activo' => $activo, 'cantidad' => $cantidad, 'coste' => $coste];
            $totalCoste += $coste;
        }

        return ['total_coste' => $totalCoste, 'posiciones' => $posiciones];
    }

    /**
     * Annual summary: operations, dividends, realized P&L, commissions for a given year.
     */
    public function getResumenAnual(int $userId, int $anio): array
    {
        $inicio = "{$anio}-01-01";
        $fin    = "{$anio}-12-31";

        $carteraInicio = $this->getCarteraEnFecha($userId, ($anio - 1) . '-12-31');
        $carteraFin    = $this->getCarteraEnFecha($userId, $fin);

        // All operations ever (needed for FIFO cost basis of sells in this year)
        $todasOperaciones = OperacionInversion::where('user_id', $userId)
            ->with('activo')
            ->orderBy('fecha')->orderBy('id')
            ->get();

        // Operations within the year
        $operacionesAnio = $todasOperaciones->filter(
            fn($op) => (int) $op->fecha->format('Y') === $anio
        );

        $comprasAnio = $operacionesAnio->where('tipo', 'compra');
        $ventasAnio  = $operacionesAnio->where('tipo', 'venta');

        // Realized P&L for each sell in this year (using full history for FIFO)
        $pnlRealizado = 0;
        $costeAdquisicionVentas = 0;
        $ventasDetalle = [];
        foreach ($ventasAnio as $venta) {
            $pnl = $this->calcularPnLVentaDesdeColeccion($todasOperaciones, $venta);
            $valorTransmision = (float)$venta->cantidad * (float)$venta->precio_unitario - $venta->total_gastos;
            $coste = $valorTransmision - $pnl;
            $pnlRealizado += $pnl;
            $costeAdquisicionVentas += $coste;
            $ventasDetalle[] = [
                'operacion' => $venta,
                'pnl'       => $pnl,
                'pnl_pct'   => $coste > 0 ? ($pnl / $coste) * 100 : null,
            ];
        }
        $pnlPct = $costeAdquisicionVentas > 0 ? ($pnlRealizado / $costeAdquisicionVentas) * 100 : null;

        // Dividends in the year
        $dividendosAnio = Dividendo::where('user_id', $userId)
            ->with('activo')
            ->whereBetween('fecha', [$inicio, $fin])
            ->orderBy('fecha')
            ->get();

        $dividendosPorActivo = $dividendosAnio->groupBy('activo_id')->map(function ($grupo) {
            return [
                'activo'  => $grupo->first()->activo,
                'bruto'   => $grupo->sum('monto_bruto'),
                'retencion' => $grupo->sum('retencion'),
                'neto'    => $grupo->sum('monto_neto'),
                'count'   => $grupo->count(),
            ];
        })->values();

        $totalDivBruto   = $dividendosAnio->sum('monto_bruto');
        $totalDivRetencion = $dividendosAnio->sum('retencion');
        $totalDivNeto    = $dividendosAnio->sum('monto_neto');

        $comisionesAnio = $operacionesAnio->sum(fn($op) =>
            (float)$op->comision + (float)$op->comision_bolsa + (float)$op->impuestos + (float)$op->comision_divisa
        );

        // Years with any activity
        $anosConActividad = OperacionInversion::where('user_id', $userId)
            ->selectRaw("strftime('%Y', fecha) as anio")
            ->groupBy('anio')
            ->pluck('anio')
            ->map(fn($a) => (int) $a)
            ->merge(
                Dividendo::where('user_id', $userId)
                    ->selectRaw("strftime('%Y', fecha) as anio")
                    ->groupBy('anio')
                    ->pluck('anio')
                    ->map(fn($a) => (int) $a)
            )
            ->unique()
            ->sortDesc()
            ->values();

        if (!$anosConActividad->contains(now()->year)) {
            $anosConActividad->prepend(now()->year);
        }

        return [
            'anio'               => $anio,
            'anos_disponibles'   => $anosConActividad,
            'compras'            => $comprasAnio->values(),
            'ventas'             => collect($ventasDetalle),
            'pnl_realizado'      => $pnlRealizado,
            'pnl_pct'            => $pnlPct,
            'dividendos_por_activo' => $dividendosPorActivo,
            'total_div_bruto'    => $totalDivBruto,
            'total_div_retencion'=> $totalDivRetencion,
            'total_div_neto'     => $totalDivNeto,
            'comisiones'         => $comisionesAnio,
            'resultado_fiscal'   => $pnlRealizado + $totalDivNeto,
            'cartera_inicio'     => $carteraInicio,
            'cartera_fin'        => $carteraFin,
        ];
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
        // Pre-load all operations in one query to avoid N+1 inside calcularPnLVenta.
        $todasOperaciones = OperacionInversion::where('user_id', $userId)
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $ventas       = $todasOperaciones->where('tipo', 'venta');
        $pnlRealizado = 0;
        foreach ($ventas as $venta) {
            $pnlRealizado += $this->calcularPnLVentaDesdeColeccion($todasOperaciones, $venta);
        }

        $totalDividendosNetos  = (float) Dividendo::where('user_id', $userId)->sum('monto_neto');
        $totalDividendosBrutos = (float) Dividendo::where('user_id', $userId)->sum('monto_bruto');
        $totalComisiones = (float) OperacionInversion::where('user_id', $userId)
            ->selectRaw('SUM(comision + comision_bolsa + impuestos + comision_divisa) as total')
            ->value('total');

        // YoC = total gross dividends / total historical investment cost
        $costoHistorico = (float) OperacionInversion::where('user_id', $userId)
            ->where('tipo', 'compra')
            ->selectRaw('SUM(cantidad * precio_unitario + comision + comision_bolsa + impuestos + comision_divisa) as total')
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
