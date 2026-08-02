<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operaciones_inversion', function (Blueprint $table) {
            // comision existente pasa a ser comision_bancaria conceptualmente.
            // Añadimos los campos nuevos desglosados.
            $table->decimal('comision_bolsa',  10, 2)->default(0)->after('comision');
            $table->decimal('impuestos',       10, 2)->default(0)->after('comision_bolsa');
            $table->decimal('comision_divisa', 10, 2)->default(0)->after('impuestos');
        });
    }

    public function down(): void
    {
        Schema::table('operaciones_inversion', function (Blueprint $table) {
            $table->dropColumn(['comision_bolsa', 'impuestos', 'comision_divisa']);
        });
    }
};
