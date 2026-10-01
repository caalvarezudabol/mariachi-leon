<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_evento')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('restrict');
            $table->string('contacto_evento')->nullable();
            $table->string('telefono_contacto')->nullable();
            $table->date('fecha_evento');
            $table->time('hora_evento');
            $table->decimal('duracion_horas', 5, 2)->default(1.00);
            $table->text('direccion_evento')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->enum('estado', ['Pendiente', 'Cotizado', 'Reservado', 'Confirmado', 'Realizado', 'Cancelado', 'Finalizado'])->default('Pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
