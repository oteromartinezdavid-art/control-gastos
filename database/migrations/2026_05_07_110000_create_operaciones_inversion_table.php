<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operaciones_inversion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('activo_id')->constrained('activos')->onDelete('cascade');
            $table->enum('tipo', ['compra', 'venta']);
            $table->date('fecha');
            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio_unitario', 12, 4);
            $table->decimal('comision', 10, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operaciones_inversion');
    }
};
