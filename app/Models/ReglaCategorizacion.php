<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglaCategorizacion extends Model
{
    protected $table = 'reglas_categorizacion';
    protected $fillable = ['user_id', 'palabra_clave', 'categoria_id'];

    // Relación para saber a qué categoría pertenece la regla
    public function categoria()
    {
        return $this->belongsTo(CategoriaGasto::class, 'categoria_id');
    }
}