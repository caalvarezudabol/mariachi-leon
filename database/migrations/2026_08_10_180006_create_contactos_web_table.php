<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos_web', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('telefono');
            $table->string('email')->nullable();
            $table->foreignId('tipo_evento_id')->nullable()->constrained('tipos_evento')->onDelete('set null');
            $table->date('fecha_estimada')->nullable();
            $table->text('mensaje');
            $table->enum('estado', ['nuevo', 'atendido', 'cotizado', 'descartado'])->default('nuevo');
            $table->timestamps();
        });

        Schema::create('galeria_items', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['foto', 'video', 'facebook'])->default('foto');
            $table->string('imagen_url')->nullable();
            $table->text('video_url')->nullable();
            $table->text('facebook_url')->nullable();
            $table->string('categoria')->default('general');
            $table->date('fecha_evento')->nullable()->index();
            $table->boolean('destacado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeria_items');
        Schema::dropIfExists('contactos_web');
    }
};
