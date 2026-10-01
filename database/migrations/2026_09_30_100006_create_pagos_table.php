<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_recibo')->unique();
            $table->foreignId('contrato_id')->constrained('contratos')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->dateTime('fecha_pago');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['EFECTIVO', 'QR', 'TRANSFERENCIA'])->default('EFECTIVO');
            $table->string('comprobante_referencia')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
