<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperacionInversion extends Model
{
    protected $table = 'operaciones_inversion';

    protected $fillable = [
        'user_id', 'activo_id', 'tipo', 'fecha',
        'cantidad', 'precio_unitario', 'comision', 'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:4',
        'comision' => 'decimal:2',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImporteNetoAttribute(): float
    {
        $bruto = (float)$this->cantidad * (float)$this->precio_unitario;
        return $this->tipo === 'compra'
            ? $bruto + (float)$this->comision
            : $bruto - (float)$this->comision;
    }
}
