<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dividendo extends Model
{
    protected $fillable = [
        'user_id', 'activo_id', 'fecha',
        'monto_bruto', 'retencion', 'monto_neto', 'notas',
    ];

    protected $casts = ['fecha' => 'date'];

    public function activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
