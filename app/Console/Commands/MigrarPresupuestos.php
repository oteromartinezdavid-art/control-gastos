<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarPresupuestos extends Command
{
    protected $signature = 'presupuesto:migrar';
    protected $description = 'Copia presupuesto_mensual de categorias a presupuestos_mensuales para el mes actual';

    public function handle()
    {
        $mes  = now()->month;
        $anio = now()->year;

        $categorias = DB::table('categoria_gastos')->where('presupuesto_mensual', '>', 0)->get();
        $count = 0;
        foreach ($categorias as $cat) {
            DB::table('presupuestos_mensuales')->insertOrIgnore([
                'user_id'     => $cat->user_id,
                'categoria_id'=> $cat->id,
                'mes'         => $mes,
                'anio'        => $anio,
                'importe'     => $cat->presupuesto_mensual,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $count++;
        }
        $this->info("{$count} categorías migradas a {$mes}/{$anio}.");
    }
}
