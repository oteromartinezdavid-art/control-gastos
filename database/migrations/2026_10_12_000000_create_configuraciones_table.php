<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('clave');          // ej: finnhub_api_key
            $table->text('valor')->nullable(); // encriptado en el modelo
            $table->timestamps();

            $table->unique(['user_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
