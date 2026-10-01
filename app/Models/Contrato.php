<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrato extends Model
{
    use SoftDeletes;

    protected $table = 'contratos';

    protected $fillable = [
        'numero_contrato',
        'cliente_id',
        'evento_id',
        'servicio_id',
        'cotizacion_id',
        'fecha_contrato',
        'hora_contrato',
        'monto_total',
        'pago_inicial',
        'total_pagado',
        'saldo_pendiente',
        'estado',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha_contrato' => 'date',
        'monto_total' => 'decimal:2',
        'pago_inicial' => 'decimal:2',
        'total_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'contrato_id');
    }

    public function distribuciones(): HasMany
    {
        return $this->hasMany(DistribucionEconomica::class, 'contrato_id');
    }
}
