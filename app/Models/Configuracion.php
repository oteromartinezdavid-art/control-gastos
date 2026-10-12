<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = ['user_id', 'clave', 'valor'];

    // Encripta/desencripta automáticamente el valor en BD
    protected $casts = [
        'valor' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Lee un valor de configuración del usuario (o null). */
    public static function obtener(int $userId, string $clave): ?string
    {
        return static::where('user_id', $userId)->where('clave', $clave)->value('valor');
    }

    /** Crea o actualiza un valor de configuración del usuario. */
    public static function guardar(int $userId, string $clave, ?string $valor): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'clave' => $clave],
            ['valor' => $valor]
        );
    }
}
