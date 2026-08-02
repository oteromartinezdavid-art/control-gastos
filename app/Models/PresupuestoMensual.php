<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoMensual extends Model
{
    protected $table = 'presupuestos_mensuales';
    protected $fillable = ['user_id', 'categoria_id', 'mes', 'anio', 'importe'];

    public function categoria()
    {
        return $this->belongsTo(CategoriaGasto::class, 'categoria_id');
    }
}
