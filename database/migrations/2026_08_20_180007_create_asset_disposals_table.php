<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('responsable_id')->nullable()->constrained('musicos_personal')->onDelete('set null');
            $table->date('fecha_baja');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->enum('motivo', [
                'deterioro',
                'perdida',
                'obsolescencia',
                'dano_irreparable',
                'desuso',
                'otro'
            ]);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};
