<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'numero_recibo',
        'contrato_id',
        'cliente_id',
        'evento_id',
        'fecha_pago',
        'monto',
        'metodo_pago',
        'comprobante_referencia',
        'observaciones',
        'codigo_verificacion',
        'estado',
        'motivo_anulacion',
        'fecha_anulacion',
        'anulado_por',
        'user_id',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'monto' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($pago) {
            if (empty($pago->codigo_verificacion)) {
                $pago->codigo_verificacion = \Illuminate\Support\Str::random(40);
            }
            if (empty($pago->estado)) {
                $pago->estado = 'Valido';
            }
        });
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }
}
