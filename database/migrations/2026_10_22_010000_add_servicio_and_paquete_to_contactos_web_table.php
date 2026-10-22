<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contactos_web', function (Blueprint $table) {
            $table->foreignId('servicio_id')->nullable()->after('tipo_evento_id')->constrained('servicios')->onDelete('set null');
            $table->foreignId('paquete_id')->nullable()->after('servicio_id')->constrained('paquetes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('contactos_web', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropForeign(['paquete_id']);
            $table->dropColumn(['servicio_id', 'paquete_id']);
        });
    }
};
