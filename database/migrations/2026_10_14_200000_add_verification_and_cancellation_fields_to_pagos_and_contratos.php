<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'codigo_verificacion')) {
                $table->string('codigo_verificacion', 64)->nullable()->unique()->after('numero_recibo');
            }
            if (!Schema::hasColumn('pagos', 'estado')) {
                $table->enum('estado', ['Valido', 'Anulado'])->default('Valido')->after('monto');
            }
            if (!Schema::hasColumn('pagos', 'motivo_anulacion')) {
                $table->text('motivo_anulacion')->nullable()->after('observaciones');
            }
            if (!Schema::hasColumn('pagos', 'fecha_anulacion')) {
                $table->dateTime('fecha_anulacion')->nullable()->after('motivo_anulacion');
            }
            if (!Schema::hasColumn('pagos', 'anulado_por')) {
                $table->foreignId('anulado_por')->nullable()->constrained('users')->onDelete('set null')->after('fecha_anulacion');
            }
        });

        Schema::table('contratos', function (Blueprint $table) {
            if (!Schema::hasColumn('contratos', 'codigo_verificacion')) {
                $table->string('codigo_verificacion', 64)->nullable()->unique()->after('numero_contrato');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'anulado_por')) {
                $table->dropForeign(['anulado_por']);
                $table->dropColumn(['anulado_por']);
            }
            if (Schema::hasColumn('pagos', 'fecha_anulacion')) {
                $table->dropColumn('fecha_anulacion');
            }
            if (Schema::hasColumn('pagos', 'motivo_anulacion')) {
                $table->dropColumn('motivo_anulacion');
            }
            if (Schema::hasColumn('pagos', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('pagos', 'codigo_verificacion')) {
                $table->dropColumn('codigo_verificacion');
            }
        });

        Schema::table('contratos', function (Blueprint $table) {
            if (Schema::hasColumn('contratos', 'codigo_verificacion')) {
                $table->dropColumn('codigo_verificacion');
            }
        });
    }
};
