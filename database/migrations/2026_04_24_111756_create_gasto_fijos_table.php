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
        Schema::create('gasto_fijos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Relacionamos con tus categorías existentes
            $table->foreignId('categoria_gasto_id')->constrained('categoria_gastos')->onDelete('cascade');
            
            $table->string('nombre'); // Ej: Alquiler, Gimnasio, Fibra Óptica
            $table->decimal('monto_previsto', 10, 2);
            $table->integer('dia_pago')->default(1); // Día del 1 al 31
            
            // Esto nos servirá para saber si el gasto sigue activo o si ya no lo pagas
            $table->boolean('activo')->default(true);
            
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gasto_fijos');
    }
};
