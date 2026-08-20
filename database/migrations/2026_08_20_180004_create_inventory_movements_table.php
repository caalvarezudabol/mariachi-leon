<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('fecha_movimiento');
            $table->enum('tipo_movimiento', ['entrada', 'salida']);
            $table->enum('motivo', [
                'compra',
                'donacion',
                'devolucion',
                'reposicion',
                'asignacion',
                'prestamo',
                'baja',
                'perdida',
                'deterioro',
                'transferencia',
                'ajuste_positivo',
                'ajuste_negativo'
            ]);
            $table->decimal('cantidad', 12, 2);
            $table->decimal('costo_unitario', 12, 2);
            $table->decimal('costo_total', 12, 2);
            $table->decimal('cantidad_saldo', 12, 2);
            $table->decimal('costo_ppp_saldo', 12, 2);
            $table->decimal('valor_total_saldo', 12, 2);
            $table->foreignId('responsable_id')->nullable()->constrained('musicos_personal')->onDelete('set null');
            $table->string('documento_referencia')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
