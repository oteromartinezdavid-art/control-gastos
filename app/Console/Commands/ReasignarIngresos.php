<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReasignarIngresos extends Command
{
    protected $signature = 'ingresos:reasignar {user_id=1}';
    protected $description = 'Aplica reglas de importación de ingresos a todos los ingresos existentes';

    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $reglas = DB::table('reglas_fuente_ingreso')->where('user_id', $userId)->get()
            ->sortByDesc(fn($r) => strlen($r->palabra_clave))->values();

        if ($reglas->isEmpty()) {
            $this->error('No hay reglas de ingresos definidas.');
            return;
        }

        $ingresos = DB::table('ingresos')->where('user_id', $userId)->get();
        $actualizados = 0;

        foreach ($ingresos as $ingreso) {
            $desc = strtoupper($ingreso->descripcion);
            foreach ($reglas as $regla) {
                if (str_contains($desc, strtoupper($regla->palabra_clave))) {
                    DB::table('ingresos')
                        ->where('id', $ingreso->id)
                        ->update(['fuente_ingreso_id' => $regla->fuente_ingreso_id]);
                    $actualizados++;
                    break;
                }
            }
        }

        $this->info("{$actualizados} ingresos actualizados de {$ingresos->count()} totales.");
    }
}
