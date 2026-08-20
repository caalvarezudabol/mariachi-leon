<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial')->default('Mariachi León Guanajuato');
            $table->string('razon_social')->default('Mariachi León Guanajuato S.R.L.');
            $table->string('nit_ruc')->default('1029384756');
            $table->string('slogan')->nullable()->default('Puntualidad, elegancia y virtuosismo musical en cada presentación');
            $table->string('representante_legal')->default('Enrrique Escalera');
            $table->string('telefono_principal')->default('+591 700 00000');
            $table->string('whatsapp_comercial')->default('+591 700 00000');
            $table->string('email_contacto')->default('contacto@mariachileonguanajuato.com');
            $table->string('direccion_fisica')->default('León, Guanajuato, México / Santa Cruz, Bolivia');
            $table->string('ciudad_pais')->default('Santa Cruz - Bolivia');
            $table->string('logo_url')->nullable()->default('/assets/images/logo_official.png');
            $table->string('moneda_nombre')->default('Bolivianos');
            $table->string('moneda_simbolo')->default('Bs.');
            $table->string('redes_linktree')->nullable()->default('https://linktr.ee/mariachileonguanajuato');
            $table->string('banco_nombre')->nullable()->default('Banco Nacional de Bolivia');
            $table->string('banco_numero_cuenta')->nullable()->default('1000-2938-4756');
            $table->string('banco_titular')->nullable()->default('Enrrique Escalera');
            $table->string('banco_qr_url')->nullable();
            $table->text('terminos_contrato')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
