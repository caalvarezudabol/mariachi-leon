<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la columna ya existe (de una versión anterior), no se vuelve a crear
        if (!Schema::hasColumn('musicos_personal', 'user_id')) {
            Schema::table('musicos_personal', function (Blueprint $table) {
                // Usuario con el que el músico entra a su panel (opcional)
                $table->unsignedBigInteger('user_id')->nullable()->after('es_dueno');
            });
        }
    }

    public function down(): void
    {
        // No se elimina: puede existir desde antes
    }
};
