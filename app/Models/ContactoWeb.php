<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactoWeb extends Model
{
    protected $table = 'contactos_web';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'tipo_evento_id',
        'servicio_id',
        'paquete_id',
        'fecha_estimada',
        'mensaje',
        'estado',
        'monto_anticipo',
        'anticipo_pagado',
        'qr_transaccion_id',
    ];

    protected $casts = [
        'fecha_estimada' => 'date',
        'monto_anticipo' => 'decimal:2',
        'anticipo_pagado' => 'boolean',
    ];

    public function qrTransaccion(): BelongsTo
    {
        return $this->belongsTo(QrTransaccion::class, 'qr_transaccion_id');
    }

    public function tipoEvento(): BelongsTo
    {
        return $this->belongsTo(TipoEvento::class, 'tipo_evento_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(Paquete::class, 'paquete_id');
    }
}
