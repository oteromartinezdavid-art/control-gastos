<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Watchlist;
use App\Services\MarketData\MarketDataProvider;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WatchlistController extends Controller
{
    public function __construct(private MarketDataProvider $market)
    {
    }

    public function index()
    {
        $userId = auth()->id();

        $items = Watchlist::where('user_id', $userId)
            ->orderBy('ticker')
            ->get();

        $tieneApiKey = (bool) Configuracion::obtener($userId, 'finnhub_api_key');

        return view('agente-fiduciario.vigilancia', [
            'items'       => $items,
            'tieneApiKey' => $tieneApiKey,
            'proveedor'   => $this->market->nombre(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticker' => ['required', 'string', 'max:20'],
        ]);

        $ticker = strtoupper(trim($request->ticker));
        $userId = auth()->id();

        if (Watchlist::where('user_id', $userId)->where('ticker', $ticker)->exists()) {
            return back()->with('error', "{$ticker} ya está en tu lista de vigilancia.");
        }

        try {
            $info = $this->market->buscar($ticker);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (!$info) {
            return back()->with('error', "No se encontró el ticker «{$ticker}» en {$this->market->nombre()}.");
        }

        $item = Watchlist::create([
            'user_id' => $userId,
            'ticker'  => $info['ticker'],
            'nombre'  => $info['nombre'],
            'moneda'  => $info['moneda'],
        ]);

        $this->refrescarUno($item);

        return back()->with('success', "{$ticker} añadido a vigilancia.");
    }

    public function update(Request $request, Watchlist $watchlist)
    {
        abort_if($watchlist->user_id !== auth()->id(), 403);

        $request->validate([
            'precio_objetivo_manual' => 'nullable|numeric|min:0',
            'precio_alerta'          => 'nullable|numeric|min:0',
            'notas'                  => 'nullable|string|max:2000',
        ]);

        $watchlist->update($request->only([
            'precio_objetivo_manual', 'precio_alerta', 'notas',
        ]));

        return back()->with('success', "{$watchlist->ticker} actualizado.");
    }

    public function destroy(Watchlist $watchlist)
    {
        abort_if($watchlist->user_id !== auth()->id(), 403);
        $ticker = $watchlist->ticker;
        $watchlist->delete();

        return back()->with('success', "{$ticker} eliminado de vigilancia.");
    }

    /** Refresca los datos de mercado de todas las acciones vigiladas. */
    public function refrescar()
    {
        $items = Watchlist::where('user_id', auth()->id())->get();

        $ok = 0;
        $errores = [];
        foreach ($items as $item) {
            try {
                $this->refrescarUno($item);
                $ok++;
            } catch (\RuntimeException $e) {
                $errores[] = "{$item->ticker}: {$e->getMessage()}";
            }
        }

        if ($errores) {
            return back()->with('error', "Actualizadas {$ok}. Errores: " . implode(' · ', array_slice($errores, 0, 3)));
        }

        return back()->with('success', "Datos actualizados ({$ok} acciones).");
    }

    private function refrescarUno(Watchlist $item): void
    {
        $d = $this->market->datos($item->ticker);

        $item->update([
            'precio_actual'   => $d['precio_actual'],
            'fecha_precio'    => $d['fecha_precio'],
            'moneda'          => $d['moneda'] ?: $item->moneda,
            'yield_pct'       => $d['yield_pct'],
            'payout_ratio'    => $d['payout_ratio'],
            'racha_dividendo' => $d['racha_dividendo'],
            'per_actual'      => $d['per_actual'],
            'per_historico'   => $d['per_historico'],
            'deuda_ebitda'    => $d['deuda_ebitda'],
            'fcf'             => $d['fcf'],
            'margen_neto'     => $d['margen_neto'],
            'crecimiento_bpa' => $d['crecimiento_bpa'],
            'datos_json'      => $d['datos_json'],
            'actualizado_en'  => now(),
        ]);
    }
}
