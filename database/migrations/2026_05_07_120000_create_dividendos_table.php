<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dividendos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('activo_id')->constrained('activos')->onDelete('cascade');
            $table->date('fecha');
            $table->decimal('monto_bruto', 10, 2);
            $table->decimal('retencion', 10, 2)->default(0);
            $table->decimal('monto_neto', 10, 2);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividendos');
    }
};
