<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ticker', 20);
            $table->string('nombre');
            $table->string('sector', 100)->nullable();
            $table->string('mercado', 50)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'ticker']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
