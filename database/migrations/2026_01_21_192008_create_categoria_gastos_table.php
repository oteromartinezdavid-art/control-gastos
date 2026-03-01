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
        Schema::create('categoria_gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->decimal('presupuesto_mensual', 10, 2)->default(0);
            $table->string('color')->default('#1e1b4b'); // Color para los gráficos
            $table->timestamps();
            
            // Evitamos que un usuario tenga dos categorías con el mismo nombre
            $table->unique(['user_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_gastos');
    }
};
