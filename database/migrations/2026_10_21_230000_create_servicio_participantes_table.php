<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('servicio_participantes')) {
            Schema::create('servicio_participantes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
                $table->foreignId('persona_id')->constrained('musicos_personal')->onDelete('cascade');
                $table->string('funcion', 100)->default('Músico');
                $table->string('concepto_pago', 100)->default('Comisión por Evento');
                $table->string('tipo_calculo', 20)->default('monto_fijo'); // 'monto_fijo' | 'porcentaje'
                $table->decimal('valor', 10, 2)->default(0.00);
                $table->boolean('activo')->default(true);
                $table->text('observaciones')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->unique(['servicio_id', 'persona_id'], 'srv_part_unique');
            });
        }

        if (Schema::hasTable('evento_participantes')) {
            Schema::table('evento_participantes', function (Blueprint $table) {
                if (!Schema::hasColumn('evento_participantes', 'tipo_calculo')) {
                    $table->string('tipo_calculo', 20)->default('monto_fijo')->nullable()->after('concepto_pago');
                }
                if (!Schema::hasColumn('evento_participantes', 'valor_sugerido')) {
                    $table->decimal('valor_sugerido', 10, 2)->nullable()->after('tipo_calculo');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_participantes');

        if (Schema::hasTable('evento_participantes')) {
            Schema::table('evento_participantes', function (Blueprint $table) {
                if (Schema::hasColumn('evento_participantes', 'valor_sugerido')) {
                    $table->dropColumn('valor_sugerido');
                }
                if (Schema::hasColumn('evento_participantes', 'tipo_calculo')) {
                    $table->dropColumn('tipo_calculo');
                }
            });
        }
    }
};
