<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_transacciones', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 30)->unique();
            $table->string('qr_id', 60)->nullable()->index();
            $table->string('origen', 30)->default('contacto_web'); // contacto_web, pago_contrato, cobro_directo
            $table->foreignId('contacto_web_id')->nullable()->constrained('contactos_web')->onDelete('set null');
            $table->foreignId('contrato_id')->nullable()->constrained('contratos')->onDelete('set null');
            $table->foreignId('pago_id')->nullable()->constrained('pagos')->onDelete('set null');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 3)->default('BOB');
            $table->string('descripcion', 100)->nullable();
            $table->date('due_date');
            $table->longText('qr_image_base64')->nullable();
            $table->enum('estado', ['PENDIENTE', 'PAGADO', 'ANULADO', 'EXPIRADO'])->default('PENDIENTE');
            $table->dateTime('expira_en');
            $table->dateTime('pagado_en')->nullable();
            $table->json('datos_pago')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::table('contactos_web', function (Blueprint $table) {
            if (!Schema::hasColumn('contactos_web', 'monto_anticipo')) {
                $table->decimal('monto_anticipo', 10, 2)->default(0)->after('estado');
            }
            if (!Schema::hasColumn('contactos_web', 'anticipo_pagado')) {
                $table->boolean('anticipo_pagado')->default(false)->after('monto_anticipo');
            }
            if (!Schema::hasColumn('contactos_web', 'qr_transaccion_id')) {
                $table->foreignId('qr_transaccion_id')->nullable()->after('anticipo_pagado')->constrained('qr_transacciones')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contactos_web', function (Blueprint $table) {
            if (Schema::hasColumn('contactos_web', 'qr_transaccion_id')) {
                $table->dropForeign(['qr_transaccion_id']);
                $table->dropColumn(['qr_transaccion_id']);
            }
            if (Schema::hasColumn('contactos_web', 'anticipo_pagado')) {
                $table->dropColumn(['anticipo_pagado']);
            }
            if (Schema::hasColumn('contactos_web', 'monto_anticipo')) {
                $table->dropColumn(['monto_anticipo']);
            }
        });

        Schema::dropIfExists('qr_transacciones');
    }
};
