<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaGasto extends Model
{
    use HasFactory;

    // Estos son los campos que Laravel permitirá guardar mediante CategoriaGasto::create()
    protected $fillable = [
        'user_id', 
        'nombre', 
        'presupuesto_mensual', 
        'color'
    ];

    /**
     * Relación: Una categoría pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'categoria_id');
    }

    public function gastosFijos()
    {
        return $this->hasMany(GastoFijo::class, 'categoria_gasto_id');
    }

    public function financiaciones()
    {
        return $this->hasMany(Financiacion::class, 'categoria_gasto_id');
    }
}