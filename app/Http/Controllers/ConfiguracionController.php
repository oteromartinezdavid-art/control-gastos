<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Services\MarketData\MarketDataProvider;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function edit()
    {
        $userId = auth()->id();
        $apiKey = Configuracion::obtener($userId, 'finnhub_api_key');

        return view('configuracion.index', [
            'finnhubKeyMask' => $this->enmascarar($apiKey),
            'tieneFinnhub'   => (bool) $apiKey,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'finnhub_api_key' => 'nullable|string|max:255',
        ]);

        $userId = auth()->id();

        // Solo guarda si el usuario escribió algo (no borra al dejar en blanco)
        if ($request->filled('finnhub_api_key')) {
            Configuracion::guardar($userId, 'finnhub_api_key', trim($request->finnhub_api_key));
            return back()->with('success', 'API key de Finnhub guardada.');
        }

        return back()->with('success', 'Sin cambios.');
    }

    public function destroyFinnhub()
    {
        Configuracion::where('user_id', auth()->id())->where('clave', 'finnhub_api_key')->delete();
        return back()->with('success', 'API key de Finnhub eliminada.');
    }

    /** Prueba la API key guardada contra el proveedor con un ticker conocido. */
    public function probarFinnhub(MarketDataProvider $market)
    {
        try {
            $info = $market->buscar('AAPL');
            if ($info) {
                return back()->with('success', "Conexión OK con {$market->nombre()} (AAPL → {$info['nombre']}).");
            }
            return back()->with('error', 'Respuesta inesperada del proveedor.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function enmascarar(?string $valor): ?string
    {
        if (!$valor) {
            return null;
        }
        $len = strlen($valor);
        if ($len <= 4) {
            return str_repeat('•', $len);
        }
        return str_repeat('•', max(0, $len - 4)) . substr($valor, -4);
    }
}
