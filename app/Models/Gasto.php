<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $fillable = ['user_id', 'descripcion', 'monto', 'categoria', 'fecha'];
}
