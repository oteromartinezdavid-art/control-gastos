<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    protected $fillable = ['user_id', 'ticker', 'nombre', 'sector', 'mercado', 'moneda'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function operaciones()
    {
        return $this->hasMany(OperacionInversion::class);
    }

    public function dividendos()
    {
        return $this->hasMany(Dividendo::class);
    }
}
