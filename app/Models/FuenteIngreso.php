<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuenteIngreso extends Model
{
    use HasFactory;

    // Estos son los campos que Laravel permitirá guardar mediante FuenteIngreso::create()
    protected $fillable = ['user_id', 'nombre', 'color'];

    /**
     * Relación: Una categoría pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);

    }

}
