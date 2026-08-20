<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->foreignId('asset_category_id')->constrained('asset_categories')->onDelete('restrict');
            $table->text('descripcion')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable()->unique();
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('costo_adquisicion', 12, 2)->default(0);
            $table->enum('tipo_control', ['individual', 'cantidad'])->default('individual');
            $table->decimal('existencia', 12, 2)->default(0);
            $table->decimal('costo_promedio_ppp', 12, 2)->default(0);
            $table->enum('estado', [
                'disponible',
                'asignado',
                'en_mantenimiento',
                'deteriorado',
                'perdido',
                'dado_de_baja'
            ])->default('disponible');
            $table->foreignId('responsable_id')->nullable()->constrained('musicos_personal')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
