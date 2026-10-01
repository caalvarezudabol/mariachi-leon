<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('musicos_personal', function (Blueprint $table) {
            if (!Schema::hasColumn('musicos_personal', 'ci_nit')) {
                $table->string('ci_nit')->nullable()->after('nombre_completo');
            }
            if (!Schema::hasColumn('musicos_personal', 'email')) {
                $table->string('email')->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('musicos_personal', 'direccion')) {
                $table->string('direccion')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('musicos_personal', function (Blueprint $table) {
            $table->dropColumn(['ci_nit', 'email', 'direccion']);
        });
    }
};
