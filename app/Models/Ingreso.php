<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingreso extends Model
{
    //
    protected $fillable = ['user_id', 'descripcion', 'monto', 'fuente_ingreso_id', 'fecha', 'hash'];

    /**
     * Define la relación con la tabla de fuentes.
     * Esta es la función que el controlador está buscando.
     */
    public function fuenteIngreso(): BelongsTo
    {
        return $this->belongsTo(FuenteIngreso::class, 'fuente_ingreso_id');
    }
}
