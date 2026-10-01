<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_contrato')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('restrict');
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->onDelete('set null');
            $table->date('fecha_contrato');
            $table->time('hora_contrato')->nullable();
            $table->decimal('monto_total', 10, 2)->default(0.00);
            $table->decimal('pago_inicial', 10, 2)->default(0.00);
            $table->decimal('total_pagado', 10, 2)->default(0.00);
            $table->decimal('saldo_pendiente', 10, 2)->default(0.00);
            $table->enum('estado', ['Borrador', 'Pendiente de Confirmacion', 'Confirmado', 'En Ejecucion', 'Realizado', 'Cancelado', 'Finalizado'])->default('Pendiente de Confirmacion');
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
