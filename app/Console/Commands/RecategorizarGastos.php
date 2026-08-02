<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecategorizarGastos extends Command
{
    protected $signature = 'gastos:recategorizar {user_id=1}';
    protected $description = 'Aplica reglas de importación a todos los gastos existentes';

    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $reglas = DB::table('reglas_categorizacion')->where('user_id', $userId)->get()
            ->sortByDesc(fn($r) => strlen($r->palabra_clave))->values();

        if ($reglas->isEmpty()) {
            $this->error('No hay reglas definidas.');
            return;
        }

        $gastos = DB::table('gastos')->where('user_id', $userId)->get();
        $actualizados = 0;

        foreach ($gastos as $gasto) {
            $desc = strtoupper($gasto->descripcion);
            foreach ($reglas as $regla) {
                if (str_contains($desc, strtoupper($regla->palabra_clave))) {
                    DB::table('gastos')
                        ->where('id', $gasto->id)
                        ->update(['categoria_id' => $regla->categoria_id]);
                    $actualizados++;
                    break;
                }
            }
        }

        $this->info("{$actualizados} gastos actualizados de {$gastos->count()} totales.");
    }
}
