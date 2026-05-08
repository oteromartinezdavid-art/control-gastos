<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gasto_fijos', function (Blueprint $table) {
            $table->dropColumn(['periodicidad', 'mes_referencia']);
        });

        Schema::table('gasto_fijos', function (Blueprint $table) {
            // Array JSON con los meses de cobro (1-12). Null = mensual (todos los meses).
            $table->json('meses_cobro')->nullable()->after('dia_pago');
        });
    }

    public function down(): void
    {
        Schema::table('gasto_fijos', function (Blueprint $table) {
            $table->dropColumn('meses_cobro');
        });

        Schema::table('gasto_fijos', function (Blueprint $table) {
            $table->string('periodicidad')->default('mensual')->after('dia_pago');
            $table->tinyInteger('mes_referencia')->nullable()->after('periodicidad');
        });
    }
};
