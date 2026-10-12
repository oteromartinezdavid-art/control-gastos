<?php

namespace App\Services\MarketData;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FinnhubProvider implements MarketDataProvider
{
    private const BASE = 'https://finnhub.io/api/v1';

    public function __construct(private ?string $apiKey)
    {
    }

    public function nombre(): string
    {
        return 'Finnhub';
    }

    public function buscar(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));
        $profile = $this->get('/stock/profile2', ['symbol' => $ticker]);

        // Finnhub devuelve {} para un ticker inexistente
        if (empty($profile) || empty($profile['name'] ?? null)) {
            return null;
        }

        return [
            'ticker' => $ticker,
            'nombre' => $profile['name'] ?? $ticker,
            'moneda' => $profile['currency'] ?? 'USD',
        ];
    }

    public function datos(string $ticker): array
    {
        $ticker = strtoupper(trim($ticker));

        $quote   = $this->get('/quote', ['symbol' => $ticker]);
        $profile = $this->get('/stock/profile2', ['symbol' => $ticker]);
        $metric  = $this->get('/stock/metric', ['symbol' => $ticker, 'metric' => 'all']);
        $m       = $metric['metric'] ?? [];

        return [
            'precio_actual'   => $this->num($quote['c'] ?? null),
            'fecha_precio'    => now()->toDateTimeString(),
            'moneda'          => $profile['currency'] ?? 'USD',

            // Dividendo
            'yield_pct'       => $this->num($m['currentDividendYieldTTM'] ?? $m['dividendYieldIndicatedAnnual'] ?? null),
            'payout_ratio'    => $this->num($m['payoutRatioTTM'] ?? $m['payoutRatioAnnual'] ?? null),
            'racha_dividendo' => null, // no disponible en Finnhub free; se rellenará con otro proveedor

            // Valoración
            'per_actual'      => $this->num($m['peTTM'] ?? $m['peBasicExclExtraTTM'] ?? null),
            'per_historico'   => $this->num($m['peAnnual'] ?? null),

            // Salud financiera
            'deuda_ebitda'    => $this->num($m['netDebtToTotalCapitalAnnual'] ?? $m['totalDebt/totalEquityAnnual'] ?? null),
            'fcf'             => $this->num($m['freeCashFlowTTM'] ?? $m['freeCashFlowAnnual'] ?? null),
            'margen_neto'     => $this->num($m['netProfitMarginTTM'] ?? $m['netProfitMarginAnnual'] ?? null),
            'crecimiento_bpa' => $this->num($m['epsGrowthTTMYoy'] ?? $m['epsGrowth5Y'] ?? null),

            // Cache crudo para depurar / enriquecer luego
            'datos_json'      => ['quote' => $quote, 'profile' => $profile, 'metric' => $m],
        ];
    }

    private function get(string $path, array $query): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Falta la API key de Finnhub. Configúrala en Configuración → Agente Fiduciario.');
        }

        $resp = Http::timeout(15)->get(self::BASE . $path, array_merge($query, ['token' => $this->apiKey]));

        if ($resp->status() === 401 || $resp->status() === 403) {
            throw new RuntimeException('API key de Finnhub inválida o sin permisos.');
        }
        if ($resp->status() === 429) {
            throw new RuntimeException('Límite de peticiones de Finnhub alcanzado. Inténtalo en un minuto.');
        }
        if ($resp->failed()) {
            throw new RuntimeException('Error al contactar con Finnhub (HTTP ' . $resp->status() . ').');
        }

        return $resp->json() ?? [];
    }

    private function num($v): ?float
    {
        return is_numeric($v) ? (float) $v : null;
    }
}
