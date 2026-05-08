<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gasto_fijos', function (Blueprint $table) {
            // mensual | trimestral | semestral | anual
            $table->string('periodicidad')->default('mensual')->after('dia_pago');
            // Mes del primer cobro del ciclo (1-12). Null = mensual.
            $table->tinyInteger('mes_referencia')->nullable()->after('periodicidad');
        });
    }

    public function down(): void
    {
        Schema::table('gasto_fijos', function (Blueprint $table) {
            $table->dropColumn(['periodicidad', 'mes_referencia']);
        });
    }
};
