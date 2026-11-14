<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('musicos_personal', function (Blueprint $table) {
            $table->decimal('porcentaje_evento', 5, 2)->nullable()->after('tarifa_evento');
        });
    }

    public function down(): void
    {
        Schema::table('musicos_personal', function (Blueprint $table) {
            $table->dropColumn('porcentaje_evento');
        });
    }
};
