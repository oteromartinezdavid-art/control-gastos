<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoFijo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'categoria_gasto_id',
        'nombre',
        'monto_previsto',
        'dia_pago',
        'meses_cobro',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'meses_cobro'  => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con la categoría que ya usas en Gastos
    public function categoriaGasto()
    {
        return $this->belongsTo(CategoriaGasto::class);
    }
}
