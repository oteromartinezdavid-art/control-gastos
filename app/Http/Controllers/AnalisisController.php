<?php

namespace App\Http\Controllers;

use App\Models\AnalisisIa;
use App\Models\Configuracion;
use App\Models\Watchlist;
use App\Services\IA\AgenteAnalista;
use App\Services\IA\ClaudeClient;

class AnalisisController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $items = Watchlist::where('user_id', $userId)->orderBy('ticker')->get();

        // Último análisis por acción
        $analisis = AnalisisIa::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('watchlist_id')
            ->map(fn($g) => $g->first());

        $tieneClaude = (bool) Configuracion::obtener($userId, 'claude_api_key');
        $modelo      = (new ClaudeClient($userId))->modelo();

        return view('agente-fiduciario.analisis', [
            'items'       => $items,
            'analisis'    => $analisis,
            'tieneClaude' => $tieneClaude,
            'modelo'      => $modelo,
        ]);
    }

    /** Analiza UNA acción y devuelve el resultado en JSON (para el bucle AJAX). */
    public function analizar(Watchlist $watchlist)
    {
        abort_if($watchlist->user_id !== auth()->id(), 403);

        try {
            $agente   = new AgenteAnalista(new ClaudeClient(auth()->id()));
            $analisis = $agente->analizar($watchlist);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'     => false,
                'ticker' => $watchlist->ticker,
                'error'  => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok'     => true,
            'ticker' => $watchlist->ticker,
            'analisis' => [
                'score'           => $analisis->score,
                'veredicto'       => $analisis->veredicto,
                'tesis'           => $analisis->tesis,
                'riesgos'         => $analisis->riesgos,
                'banderas_rojas'  => $analisis->banderas_rojas,
                'precio_objetivo' => $analisis->precio_objetivo,
                'modelo'          => $analisis->modelo,
                'coste'           => $analisis->coste,
                'fecha'           => $analisis->created_at->diffForHumans(),
            ],
        ]);
    }
}
