<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prepara pagos_participantes para la pantalla "Pagos a personal":
 *  - Un anticipo puede no pertenecer a ningún show todavía, por eso los vínculos pasan a admitir NULL.
 *  - Nueva columna "tipo" (pago / anticipo) para distinguir lo que se entrega antes del show.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pagos_participantes', 'tipo')) {
            Schema::table('pagos_participantes', function ($table) {
                $table->string('tipo', 20)->default('pago')->after('monto');
            });
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            // Se mantienen las llaves foráneas existentes; solo se permite NULL.
            foreach (['evento_participante_id', 'distribucion_economica_id', 'evento_id', 'persona_id'] as $col) {
                if (Schema::hasColumn('pagos_participantes', $col)) {
                    DB::statement("ALTER TABLE pagos_participantes MODIFY {$col} BIGINT UNSIGNED NULL");
                }
            }
        }

        // Los pagos antiguos que no tenían persona_id la toman de su participante.
        DB::statement('UPDATE pagos_participantes
            SET persona_id = (SELECT ep.persona_id FROM evento_participantes ep WHERE ep.id = pagos_participantes.evento_participante_id)
            WHERE persona_id IS NULL AND evento_participante_id IS NOT NULL');
    }

    public function down(): void
    {
        if (Schema::hasColumn('pagos_participantes', 'tipo')) {
            Schema::table('pagos_participantes', function ($table) {
                $table->dropColumn('tipo');
            });
        }
    }
};
