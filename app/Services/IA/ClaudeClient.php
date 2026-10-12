<?php

namespace App\Services\IA;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeClient
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const VERSION  = '2023-06-01';

    // Modelos permitidos y su precio USD por millón de tokens [input, output]
    public const MODELOS = [
        'claude-opus-5-5'   => ['label' => 'Opus 5.5 (mejor)',        'in' => 4.00, 'out' => 20.00],
        'claude-sonnet-5-5' => ['label' => 'Sonnet 5.5 (equilibrio)', 'in' => 2.00, 'out' => 10.00],
        'claude-haiku-5-5'  => ['label' => 'Haiku 5.5 (económico)',   'in' => 0.10, 'out' => 0.50],
    ];

    public const MODELO_DEFECTO = 'claude-opus-5-5';

    public function __construct(private int $userId)
    {
    }

    public function modelo(): string
    {
        $m = Configuracion::obtener($this->userId, 'claude_model');
        return array_key_exists($m, self::MODELOS) ? $m : self::MODELO_DEFECTO;
    }

    /**
     * Envía un mensaje y devuelve texto + uso + coste estimado.
     * @return array{texto:string, modelo:string, tokens_in:int, tokens_out:int, coste:float, raw:array}
     */
    public function mensaje(string $system, string $userContent, int $maxTokens = 2000): array
    {
        $key = Configuracion::obtener($this->userId, 'claude_api_key');
        if (empty($key)) {
            throw new RuntimeException('Falta la API key de Claude. Configúrala en Configuración → Claves API.');
        }

        $modelo = $this->modelo();

        $resp = Http::timeout(90)->withHeaders([
            'x-api-key'         => $key,
            'anthropic-version' => self::VERSION,
            'content-type'      => 'application/json',
        ])->post(self::ENDPOINT, [
            'model'      => $modelo,
            'max_tokens' => $maxTokens,
            'system'     => $system,
            'messages'   => [
                ['role' => 'user', 'content' => $userContent],
            ],
        ]);

        if ($resp->status() === 401) {
            throw new RuntimeException('API key de Claude inválida (401).');
        }
        if ($resp->status() === 429) {
            throw new RuntimeException('Límite de peticiones de Claude alcanzado. Inténtalo en un minuto.');
        }
        if ($resp->failed()) {
            $msg = $resp->json('error.message') ?? ('HTTP ' . $resp->status());
            throw new RuntimeException('Error de Claude: ' . $msg);
        }

        $json = $resp->json();

        // Concatena los bloques de texto de la respuesta
        $texto = '';
        foreach ($json['content'] ?? [] as $bloque) {
            if (($bloque['type'] ?? null) === 'text') {
                $texto .= $bloque['text'];
            }
        }

        $tokensIn  = (int) ($json['usage']['input_tokens'] ?? 0);
        $tokensOut = (int) ($json['usage']['output_tokens'] ?? 0);
        $precio    = self::MODELOS[$modelo] ?? self::MODELOS[self::MODELO_DEFECTO];
        $coste     = ($tokensIn / 1_000_000 * $precio['in']) + ($tokensOut / 1_000_000 * $precio['out']);

        return [
            'texto'      => trim($texto),
            'modelo'     => $modelo,
            'tokens_in'  => $tokensIn,
            'tokens_out' => $tokensOut,
            'coste'      => round($coste, 6),
            'raw'        => $json,
        ];
    }
}
