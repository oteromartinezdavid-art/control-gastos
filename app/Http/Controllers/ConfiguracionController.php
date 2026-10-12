<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Services\MarketData\MarketDataProvider;
use App\Services\IA\ClaudeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class ConfiguracionController extends Controller
{
    public function edit()
    {
        $userId = auth()->id();

        return view('configuracion.index', [
            'finnhubKeyMask' => $this->enmascarar(Configuracion::obtener($userId, 'finnhub_api_key')),
            'tieneFinnhub'   => (bool) Configuracion::obtener($userId, 'finnhub_api_key'),
            'claudeKeyMask'  => $this->enmascarar(Configuracion::obtener($userId, 'claude_api_key')),
            'tieneClaude'    => (bool) Configuracion::obtener($userId, 'claude_api_key'),
            'modelos'        => ClaudeClient::MODELOS,
            'modeloActual'   => (new ClaudeClient($userId))->modelo(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'finnhub_api_key' => 'nullable|string|max:255',
            'claude_api_key'  => 'nullable|string|max:255',
            'claude_model'    => ['nullable', Rule::in(array_keys(ClaudeClient::MODELOS))],
        ]);

        $userId = auth()->id();
        $guardadas = [];

        // Solo guarda si el usuario escribió algo (no borra al dejar en blanco)
        if ($request->filled('finnhub_api_key')) {
            Configuracion::guardar($userId, 'finnhub_api_key', trim($request->finnhub_api_key));
            $guardadas[] = 'Finnhub';
        }
        if ($request->filled('claude_api_key')) {
            Configuracion::guardar($userId, 'claude_api_key', trim($request->claude_api_key));
            $guardadas[] = 'Claude';
        }
        if ($request->filled('claude_model')) {
            Configuracion::guardar($userId, 'claude_model', $request->claude_model);
            $guardadas[] = 'modelo IA';
        }

        return back()->with('success', $guardadas
            ? 'Guardado: ' . implode(', ', $guardadas) . '.'
            : 'Sin cambios.');
    }

    public function destroyFinnhub()
    {
        Configuracion::where('user_id', auth()->id())->where('clave', 'finnhub_api_key')->delete();
        return back()->with('success', 'API key de Finnhub eliminada.');
    }

    public function destroyClaude()
    {
        Configuracion::where('user_id', auth()->id())->where('clave', 'claude_api_key')->delete();
        return back()->with('success', 'API key de Claude eliminada.');
    }

    /** Prueba la API key de Claude contra la API de Anthropic (listado de modelos). */
    public function probarClaude()
    {
        $key = Configuracion::obtener(auth()->id(), 'claude_api_key');
        if (!$key) {
            return back()->with('error', 'No hay API key de Claude configurada.');
        }

        try {
            $resp = Http::timeout(15)->withHeaders([
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
            ])->get('https://api.anthropic.com/v1/models');

            if ($resp->status() === 401) {
                return back()->with('error', 'API key de Claude inválida (401).');
            }
            if ($resp->failed()) {
                return back()->with('error', 'Error al contactar con Anthropic (HTTP ' . $resp->status() . ').');
            }

            $n = count($resp->json('data') ?? []);
            return back()->with('success', "Conexión OK con Claude ({$n} modelos disponibles).");
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo conectar con Anthropic: ' . $e->getMessage());
        }
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
