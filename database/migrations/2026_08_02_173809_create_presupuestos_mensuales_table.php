<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos_mensuales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');
            $table->decimal('importe', 10, 2);
            $table->timestamps();

            $table->unique(['user_id', 'categoria_id', 'mes', 'anio']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categoria_gastos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos_mensuales');
    }
};
