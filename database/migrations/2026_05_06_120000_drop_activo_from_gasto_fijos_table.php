<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gasto_fijos', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }

    public function down(): void
    {
        Schema::table('gasto_fijos', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('meses_cobro');
        });
    }
};
