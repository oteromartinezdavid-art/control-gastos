<?php

namespace App\Services\MarketData;

interface MarketDataProvider
{
    /**
     * Valida un ticker y devuelve su identidad, o null si no existe.
     * @return array{ticker:string,nombre:?string,moneda:string}|null
     */
    public function buscar(string $ticker): ?array;

    /**
     * Devuelve los datos normalizados de un ticker (precio + fundamentales).
     * Las claves ausentes en el proveedor vienen como null.
     * @return array{
     *   precio_actual:?float, fecha_precio:?string, moneda:string,
     *   yield_pct:?float, payout_ratio:?float, racha_dividendo:?int,
     *   per_actual:?float, per_historico:?float,
     *   deuda_ebitda:?float, fcf:?float, margen_neto:?float, crecimiento_bpa:?float,
     *   datos_json:array
     * }
     */
    public function datos(string $ticker): array;

    /** Nombre legible del proveedor (para UI / logs). */
    public function nombre(): string;
}
