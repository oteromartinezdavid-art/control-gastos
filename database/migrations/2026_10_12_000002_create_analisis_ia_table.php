<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_ia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('watchlist_id')->constrained('watchlist')->onDelete('cascade');

            $table->string('modelo');                     // ej: claude-opus-5-5
            $table->unsignedTinyInteger('score')->nullable(); // 0-100
            $table->string('veredicto')->nullable();      // Comprar / Mantener / Evitar / Vigilar
            $table->text('tesis')->nullable();
            $table->json('riesgos')->nullable();
            $table->json('banderas_rojas')->nullable();
            $table->decimal('precio_objetivo', 14, 4)->nullable();

            $table->json('datos_analizados')->nullable(); // snapshot de los datos usados
            $table->integer('tokens_in')->nullable();
            $table->integer('tokens_out')->nullable();
            $table->decimal('coste', 10, 6)->nullable();  // USD estimado

            $table->json('raw')->nullable();              // respuesta cruda para depurar

            $table->timestamps();

            $table->index(['user_id', 'watchlist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_ia');
    }
};
