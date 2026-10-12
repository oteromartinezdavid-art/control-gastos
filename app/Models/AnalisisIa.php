<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalisisIa extends Model
{
    protected $table = 'analisis_ia';

    protected $fillable = [
        'user_id', 'watchlist_id', 'modelo',
        'score', 'veredicto', 'tesis', 'riesgos', 'banderas_rojas', 'precio_objetivo',
        'datos_analizados', 'tokens_in', 'tokens_out', 'coste', 'raw',
    ];

    protected $casts = [
        'riesgos'          => 'array',
        'banderas_rojas'   => 'array',
        'datos_analizados' => 'array',
        'raw'              => 'array',
    ];

    public function watchlist()
    {
        return $this->belongsTo(Watchlist::class, 'watchlist_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
