<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Identidad
            $table->string('ticker');
            $table->string('nombre')->nullable();
            $table->string('moneda', 8)->default('USD');

            // Precio
            $table->decimal('precio_actual', 14, 4)->nullable();
            $table->timestamp('fecha_precio')->nullable();

            // Básicos de dividendo
            $table->decimal('yield_pct', 8, 4)->nullable();
            $table->decimal('payout_ratio', 8, 4)->nullable();
            $table->integer('racha_dividendo')->nullable(); // años subiendo dividendo

            // Valoración
            $table->decimal('per_actual', 10, 4)->nullable();
            $table->decimal('per_historico', 10, 4)->nullable();

            // Salud financiera
            $table->decimal('deuda_ebitda', 10, 4)->nullable();
            $table->decimal('fcf', 18, 2)->nullable();
            $table->decimal('margen_neto', 8, 4)->nullable();
            $table->decimal('crecimiento_bpa', 8, 4)->nullable();

            // Manual del usuario
            $table->decimal('precio_objetivo_manual', 14, 4)->nullable();
            $table->decimal('precio_alerta', 14, 4)->nullable();
            $table->text('notas')->nullable();

            // Cache crudo del proveedor
            $table->json('datos_json')->nullable();
            $table->timestamp('actualizado_en')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'ticker']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist');
    }
};
