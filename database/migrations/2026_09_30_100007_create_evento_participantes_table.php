<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('persona_id')->constrained('musicos_personal')->onDelete('cascade');
            $table->string('funcion')->default('Músico');
            $table->string('concepto_pago')->default('Comisión');
            $table->decimal('monto_asignado', 10, 2)->default(0.00);
            $table->enum('estado_distribucion', ['Pendiente', 'Distribuido', 'Ajustado'])->default('Pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_participantes');
    }
};
