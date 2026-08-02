<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperacionInversion extends Model
{
    protected $table = 'operaciones_inversion';

    protected $fillable = [
        'user_id', 'activo_id', 'tipo', 'fecha',
        'cantidad', 'precio_unitario',
        'comision', 'comision_bolsa', 'impuestos', 'comision_divisa',
        'notas',
    ];

    protected $casts = [
        'fecha'           => 'date',
        'cantidad'        => 'decimal:4',
        'precio_unitario' => 'decimal:4',
        'comision'        => 'decimal:2',
        'comision_bolsa'  => 'decimal:2',
        'impuestos'       => 'decimal:2',
        'comision_divisa' => 'decimal:2',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Suma de todos los gastos asociados a la operación.
     */
    public function getTotalGastosAttribute(): float
    {
        return (float)$this->comision
            + (float)$this->comision_bolsa
            + (float)$this->impuestos
            + (float)$this->comision_divisa;
    }

    /**
     * Importe neto: en compra suma todos los gastos al bruto;
     * en venta los resta (reducen el valor de transmisión).
     */
    public function getImporteNetoAttribute(): float
    {
        $bruto = (float)$this->cantidad * (float)$this->precio_unitario;
        return $this->tipo === 'compra'
            ? $bruto + $this->total_gastos
            : $bruto - $this->total_gastos;
    }
}
