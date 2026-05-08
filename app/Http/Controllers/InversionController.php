<?php

namespace App\Http\Controllers;

use App\Services\InversionService;
use Illuminate\Http\JsonResponse;

class InversionController extends Controller
{
    public function __construct(private InversionService $inversionService) {}

    public function index()
    {
        $userId     = auth()->id();
        $posiciones = $this->inversionService->getResumenCartera($userId);
        $kpis       = $this->inversionService->getKPIs($userId);

        $palette = ['#6366f1', '#f97316', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f59e0b', '#84cc16'];

        $chartLabels = collect($posiciones)->pluck('activo.ticker')->values();
        $chartData   = collect($posiciones)->map(fn($p) => round($p['valor_actual'] ?? $p['inversion_total'], 2))->values();
        $chartColors = collect($posiciones)->keys()->map(fn($i) => $palette[$i % count($palette)])->values();

        return view('inversiones.index', compact('posiciones', 'kpis', 'chartLabels', 'chartData', 'chartColors'));
    }

    public function getCotizacion(string $ticker): JsonResponse
    {
        $precio = $this->inversionService->getCotizacion(strtoupper($ticker));
        return response()->json(['ticker' => strtoupper($ticker), 'precio' => $precio]);
    }
}
