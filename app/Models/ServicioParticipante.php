<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioParticipante extends Model
{
    protected $table = 'servicio_participantes';

    protected $fillable = [
        'servicio_id',
        'persona_id',
        'funcion',
        'concepto_pago',
        'tipo_calculo',
        'valor',
        'activo',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(MusicoPersonal::class, 'persona_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Calcula el monto en Bs según el tipo de cálculo y el precio base del servicio.
     */
    public function calcularMontoSugerido(float $precioBase = 0.0): float
    {
        if ($this->tipo_calculo === 'porcentaje') {
            return round(($precioBase * (float)$this->valor) / 100, 2);
        }

        return (float)$this->valor;
    }
}
