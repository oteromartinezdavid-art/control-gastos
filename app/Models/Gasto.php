<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $fillable = ['user_id', 'descripcion', 'monto', 'categoria_id', 'fecha', 'hash'];

    public function categoriaGasto() {
        // Un gasto pertenece a una categoría (usando el campo categoria_id)
        return $this->belongsTo(CategoriaGasto::class, 'categoria_id');
    }
}
