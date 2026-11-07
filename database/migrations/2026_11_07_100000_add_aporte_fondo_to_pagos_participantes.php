<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Permite dejar una parte de un pago a personal en el fondo común.
 *  - aporte_fondo: cuánto de ese pago se quedó en el fondo (la persona recibe monto - aporte_fondo).
 *  - fondo_movimiento_id: aporte generado en fondo_comun_movimientos (se anula junto con el pago).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_participantes', function ($table) {
            if (!Schema::hasColumn('pagos_participantes', 'aporte_fondo')) {
                $table->decimal('aporte_fondo', 12, 2)->default(0)->after('monto');
            }
            if (!Schema::hasColumn('pagos_participantes', 'fondo_movimiento_id')) {
                $table->unsignedBigInteger('fondo_movimiento_id')->nullable()->after('aporte_fondo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos_participantes', function ($table) {
            foreach (['fondo_movimiento_id', 'aporte_fondo'] as $col) {
                if (Schema::hasColumn('pagos_participantes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
