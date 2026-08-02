<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuente_ingresos', function (Blueprint $table) {
            $table->string('color')->default('#059669')->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('fuente_ingresos', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
