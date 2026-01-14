<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            // Relacionamos el gasto con el usuario que está logueado
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('descripcion');
            $table->decimal('monto', 10, 2); // Ejemplo: 1500.50
            $table->string('categoria');    // Ejemplo: Comida, Transporte, Ocio
            $table->date('fecha');          // Cuándo se hizo el gasto
            $table->timestamps();           // Crea 'created_at' y 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
