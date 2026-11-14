<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            if (!Schema::hasColumn('servicios', 'por_hora')) {
                $table->boolean('por_hora')->default(false);
            }
            if (!Schema::hasColumn('servicios', 'horas_minimas')) {
                $table->decimal('horas_minimas', 4, 1)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn(['por_hora', 'horas_minimas']);
        });
    }
};
