<?php

namespace App\Services\IA;

use App\Models\AnalisisIa;
use App\Models\Watchlist;
use RuntimeException;

class AgenteAnalista
{
    public function __construct(private ClaudeClient $claude)
    {
    }

    private const SYSTEM = <<<'TXT'
Eres un analista fiduciario de inversiones para un inversor particular de LARGO PLAZO
que busca buenas empresas, con buenos dividendos sostenibles, compradas a buen precio.
Actúas siempre en el mejor interés del inversor.

REGLAS ABSOLUTAS:
- Razona ÚNICAMENTE con los datos numéricos que se te proporcionan. NUNCA inventes ni
  estimes cifras financieras (precios, yield, payout, PER, deuda, etc.) que no estén en los datos.
- Si un dato relevante falta (null o ausente), dilo explícitamente en los riesgos y NO lo supongas.
- No des certezas de futuro. Esto es análisis informativo, no asesoramiento financiero personalizado.

Devuelve EXCLUSIVAMENTE un objeto JSON válido (sin markdown, sin texto adicional) con esta forma:
{
  "score": <entero 0-100, idoneidad para este inversor de dividendos a largo plazo>,
  "veredicto": "<Comprar | Vigilar | Mantener | Evitar>",
  "tesis": "<2-4 frases en español con la tesis de inversión basada en los datos>",
  "riesgos": ["<riesgo 1>", "<riesgo 2>", ...],
  "banderas_rojas": ["<alerta grave o dato que falta>", ...],
  "precio_objetivo": <número o null, precio de compra razonable según los datos, en la moneda dada>
}
TXT;

    public function analizar(Watchlist $accion): AnalisisIa
    {
        $datos = [
            'ticker'          => $accion->ticker,
            'nombre'          => $accion->nombre,
            'moneda'          => $accion->moneda,
            'precio_actual'   => $accion->precio_actual,
            'yield_pct'       => $accion->yield_pct,
            'payout_ratio'    => $accion->payout_ratio,
            'racha_dividendo' => $accion->racha_dividendo,
            'per_actual'      => $accion->per_actual,
            'per_historico'   => $accion->per_historico,
            'deuda_ratio'     => $accion->deuda_ebitda,
            'margen_neto'     => $accion->margen_neto,
            'crecimiento_bpa' => $accion->crecimiento_bpa,
            'precio_objetivo_usuario' => $accion->precio_objetivo_manual,
        ];

        $userContent = "Analiza esta acción con los siguientes datos reales de mercado "
            . "(los campos en null significan dato no disponible):\n\n"
            . json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $res = $this->claude->mensaje(self::SYSTEM, $userContent, 1500);

        $parsed = $this->parseJson($res['texto']);

        return AnalisisIa::create([
            'user_id'          => $accion->user_id,
            'watchlist_id'     => $accion->id,
            'modelo'           => $res['modelo'],
            'score'            => isset($parsed['score']) ? (int) $parsed['score'] : null,
            'veredicto'        => $parsed['veredicto'] ?? null,
            'tesis'            => $parsed['tesis'] ?? null,
            'riesgos'          => $parsed['riesgos'] ?? [],
            'banderas_rojas'   => $parsed['banderas_rojas'] ?? [],
            'precio_objetivo'  => isset($parsed['precio_objetivo']) && is_numeric($parsed['precio_objetivo'])
                                    ? $parsed['precio_objetivo'] : null,
            'datos_analizados' => $datos,
            'tokens_in'        => $res['tokens_in'],
            'tokens_out'       => $res['tokens_out'],
            'coste'            => $res['coste'],
            'raw'              => $res['raw'],
        ]);
    }

    /** Extrae el JSON de la respuesta, tolerando fences markdown o texto alrededor. */
    private function parseJson(string $texto): array
    {
        $t = trim($texto);

        // Quita fences ```json ... ```
        $t = preg_replace('/^```(?:json)?\s*/i', '', $t);
        $t = preg_replace('/\s*```$/', '', $t);

        $data = json_decode($t, true);
        if (is_array($data)) {
            return $data;
        }

        // Fallback: busca el primer objeto {...} dentro del texto
        if (preg_match('/\{.*\}/s', $t, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) {
                return $data;
            }
        }

        throw new RuntimeException('La IA no devolvió un JSON válido. Respuesta: ' . mb_substr($t, 0, 200));
    }
}
