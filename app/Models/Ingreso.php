<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    //
    protected $fillable = ['user_id', 'descripcion', 'monto', 'fuente', 'fecha'];
}
