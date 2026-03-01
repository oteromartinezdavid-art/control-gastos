<?php

namespace App\Services;

use App\Models\CategoriaGasto;
use Illuminate\Support\Facades\Auth;

class FinanzasService
{
    public function obtenerOCrearCategoria(string $nombre, $userId = null)
    {
        $id = $userId ?? Auth::id();

        // Esta función busca la categoría por nombre para ese usuario.
        // Si no existe, la crea con los valores por defecto.
        return CategoriaGasto::firstOrCreate(
            ['user_id' => $id, 'nombre' => trim($nombre)],
            ['presupuesto_mensual' => 0, 'color' => '#f97316']
        );
    }
}