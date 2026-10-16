<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla de pagos a participantes
        Schema::create('pagos_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_participante_id')->constrained('evento_participantes')->onDelete('restrict');
            $table->foreignId('distribucion_economica_id')->nullable()->constrained('distribuciones_economicas')->onDelete('set null');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('persona_id')->constrained('musicos_personal')->onDelete('restrict');
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago', 20)->default('Efectivo'); // 'Efectivo', 'QR'
            $table->string('glosa', 255);
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_pago');
            $table->string('estado', 20)->default('Valido'); // 'Valido', 'Revertido'
            $table->string('motivo_reversion', 255)->nullable();
            $table->dateTime('fecha_reversion')->nullable();
            $table->foreignId('revertido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Ampliar columna de estados en tablas relacionadas para admitir 'Pago Parcial' de forma segura
        DB::statement("ALTER TABLE evento_participantes MODIFY COLUMN estado_distribucion VARCHAR(50) NOT NULL DEFAULT 'Pendiente'");
        DB::statement("ALTER TABLE distribuciones_economicas MODIFY COLUMN estado VARCHAR(50) NOT NULL DEFAULT 'Pendiente'");
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_participantes');
        DB::statement("ALTER TABLE evento_participantes MODIFY COLUMN estado_distribucion ENUM('Pendiente','Distribuido','Ajustado') NOT NULL DEFAULT 'Pendiente'");
        DB::statement("ALTER TABLE distribuciones_economicas MODIFY COLUMN estado ENUM('Pendiente','Distribuido','Ajustado') NOT NULL DEFAULT 'Distribuido'");
    }
};
