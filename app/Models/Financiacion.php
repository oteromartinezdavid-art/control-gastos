<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Financiacion extends Model
{
    protected $table = 'financiaciones';

    protected $fillable = [
        'user_id',
        'categoria_gasto_id',
        'nombre',
        'entidad',
        'cuota_mensual',
        'cuotas_pendientes',
        'dia_cobro',
        'meses_procesados',
    ];

    protected $casts = [
        'meses_procesados' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoriaGasto()
    {
        return $this->belongsTo(CategoriaGasto::class);
    }
}
