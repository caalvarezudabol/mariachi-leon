<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('musicos_personal', function (Blueprint $table) {
            if (!Schema::hasColumn('musicos_personal', 'modalidad_pago')) {
                $table->string('modalidad_pago', 20)->default('por_evento')->after('tipo');
            }
            if (!Schema::hasColumn('musicos_personal', 'tarifa_evento')) {
                $table->decimal('tarifa_evento', 10, 2)->nullable()->after('modalidad_pago');
            }
            if (!Schema::hasColumn('musicos_personal', 'sueldo_mensual')) {
                $table->decimal('sueldo_mensual', 10, 2)->nullable()->after('tarifa_evento');
            }
            if (!Schema::hasColumn('musicos_personal', 'va_en_todos_eventos')) {
                $table->boolean('va_en_todos_eventos')->default(false)->after('sueldo_mensual');
            }
            if (!Schema::hasColumn('musicos_personal', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('va_en_todos_eventos')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('musicos_personal', function (Blueprint $table) {
            if (Schema::hasColumn('musicos_personal', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
            $table->dropColumn(['modalidad_pago', 'tarifa_evento', 'sueldo_mensual', 'va_en_todos_eventos']);
        });
    }
};
