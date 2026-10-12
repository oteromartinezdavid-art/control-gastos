<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $table = 'watchlist';

    protected $fillable = [
        'user_id', 'ticker', 'nombre', 'moneda',
        'precio_actual', 'fecha_precio',
        'yield_pct', 'payout_ratio', 'racha_dividendo',
        'per_actual', 'per_historico',
        'deuda_ebitda', 'fcf', 'margen_neto', 'crecimiento_bpa',
        'precio_objetivo_manual', 'precio_alerta', 'notas',
        'datos_json', 'actualizado_en',
    ];

    protected $casts = [
        'datos_json'     => 'array',
        'fecha_precio'   => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** True si el precio actual está en o por debajo del precio de alerta fijado. */
    public function getEnAlertaAttribute(): bool
    {
        return $this->precio_alerta
            && $this->precio_actual
            && $this->precio_actual <= $this->precio_alerta;
    }

    /** % de descuento frente al precio objetivo manual (positivo = cotiza por debajo). */
    public function getDescuentoObjetivoAttribute(): ?float
    {
        if (!$this->precio_objetivo_manual || !$this->precio_actual) {
            return null;
        }
        return round((($this->precio_objetivo_manual - $this->precio_actual) / $this->precio_objetivo_manual) * 100, 2);
    }
}
