<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Movimientos Financieros (Gastos Operativos, Gastos por Evento e Ingresos Adicionales)
        if (!Schema::hasTable('movimientos_financieros')) {
            Schema::create('movimientos_financieros', function (Blueprint $table) {
                $table->id();
                $table->string('tipo', 20)->default('EGRESO'); // 'EGRESO', 'INGRESO'
                $table->string('clasificacion', 30)->default('OPERATIVO'); // 'OPERATIVO', 'EVENTO'
                $table->string('categoria', 100);
                $table->foreignId('evento_id')->nullable()->constrained('eventos')->onDelete('set null');
                $table->foreignId('contrato_id')->nullable()->constrained('contratos')->onDelete('set null');
                $table->string('proveedor', 150)->nullable();
                $table->string('concepto', 255);
                $table->text('descripcion')->nullable();
                $table->decimal('monto', 12, 2);
                $table->string('metodo_pago', 50)->default('Efectivo');
                $table->string('numero_comprobante', 100)->nullable();
                $table->date('fecha');
                $table->string('glosa', 255)->nullable();
                $table->text('observaciones')->nullable();
                $table->string('estado', 20)->default('Valido'); // 'Valido', 'Anulado'
                $table->string('motivo_anulacion', 255)->nullable();
                $table->foreignId('anulado_por')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('fecha_anulacion')->nullable();
                $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
                $table->timestamps();
            });
        }

        // 2. Tabla de Movimientos del Fondo Común
        if (!Schema::hasTable('fondo_comun_movimientos')) {
            Schema::create('fondo_comun_movimientos', function (Blueprint $table) {
                $table->id();
                $table->string('tipo', 20); // 'INGRESO', 'EGRESO'
                $table->string('concepto', 255);
                $table->decimal('monto', 12, 2);
                $table->decimal('saldo_anterior', 12, 2)->default(0);
                $table->decimal('saldo_resultante', 12, 2)->default(0);
                $table->date('fecha');
                $table->string('metodo', 50)->default('Efectivo');
                $table->string('glosa', 255)->nullable();
                $table->text('observaciones')->nullable();
                $table->string('estado', 20)->default('Valido'); // 'Valido', 'Anulado'
                $table->string('motivo_anulacion', 255)->nullable();
                $table->foreignId('anulado_por')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('fecha_anulacion')->nullable();
                $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fondo_comun_movimientos');
        Schema::dropIfExists('movimientos_financieros');
    }
};
