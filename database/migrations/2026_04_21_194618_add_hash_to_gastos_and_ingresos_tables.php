<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->string('hash')->nullable()->unique(); // unique evita duplicados a nivel DB
        });
        Schema::table('ingresos', function (Blueprint $table) {
            $table->string('hash')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gastos_and_ingresos_tables', function (Blueprint $table) {
            //
        });
    }
};
