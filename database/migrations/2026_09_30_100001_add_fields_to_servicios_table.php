<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            if (!Schema::hasColumn('servicios', 'tipo_servicio')) {
                $table->string('tipo_servicio')->default('Musical')->after('nombre');
            }
            if (!Schema::hasColumn('servicios', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('activo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn(['tipo_servicio', 'observaciones']);
        });
    }
};
