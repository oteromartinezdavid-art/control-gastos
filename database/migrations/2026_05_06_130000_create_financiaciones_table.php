<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financiaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoria_gasto_id')->constrained('categoria_gastos')->onDelete('cascade');
            $table->string('nombre');           // debe coincidir con descripción del gasto
            $table->string('entidad')->nullable(); // banco o financiera
            $table->decimal('cuota_mensual', 10, 2);
            $table->integer('cuotas_pendientes');
            $table->integer('dia_cobro')->default(1);
            // Meses ya procesados (auto-decremento). Formato: ["2026-01", "2026-02", ...]
            $table->json('meses_procesados')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financiaciones');
    }
};
