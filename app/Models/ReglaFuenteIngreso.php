<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglaFuenteIngreso extends Model
{
    protected $table = 'reglas_fuente_ingreso';
    protected $fillable = ['user_id', 'palabra_clave', 'fuente_ingreso_id'];

    public function fuente()
    {
        return $this->belongsTo(FuenteIngreso::class, 'fuente_ingreso_id');
    }
}
