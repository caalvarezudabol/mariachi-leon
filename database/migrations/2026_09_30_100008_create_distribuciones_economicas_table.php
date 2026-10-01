<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribuciones_economicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('contrato_id')->nullable()->constrained('contratos')->onDelete('set null');
            $table->foreignId('persona_id')->constrained('musicos_personal')->onDelete('cascade');
            $table->foreignId('evento_participante_id')->nullable()->constrained('evento_participantes')->onDelete('cascade');
            $table->string('funcion');
            $table->string('concepto');
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->dateTime('fecha_distribucion');
            $table->enum('estado', ['Pendiente', 'Distribuido', 'Ajustado'])->default('Distribuido');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribuciones_economicas');
    }
};
