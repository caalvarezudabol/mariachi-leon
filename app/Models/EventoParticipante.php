<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventoParticipante extends Model
{
    protected $table = 'evento_participantes';

    protected $fillable = [
        'evento_id',
        'persona_id',
        'funcion',
        'concepto_pago',
        'tipo_calculo',
        'valor_sugerido',
        'monto_asignado',
        'estado_distribucion',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'valor_sugerido' => 'decimal:2',
        'monto_asignado' => 'decimal:2',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(MusicoPersonal::class, 'persona_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoParticipante::class, 'evento_participante_id');
    }

    public function pagosValidos(): HasMany
    {
        return $this->hasMany(PagoParticipante::class, 'evento_participante_id')->where('estado', 'Valido');
    }

    /**
     * Total efectivamente pagado y vigente (excluyendo reversiones)
     */
    public function getTotalPagadoAttribute(): float
    {
        if ($this->relationLoaded('pagos')) {
            return (float)$this->pagos->where('estado', 'Valido')->sum('monto');
        }
        return (float)$this->pagos()->where('estado', 'Valido')->sum('monto');
    }

    /**
     * Saldo pendiente de pago al participante
     */
    public function getSaldoPendienteAttribute(): float
    {
        return max(0.0, (float)$this->monto_asignado - $this->total_pagado);
    }

    /**
     * Estado automático según reglas de negocio:
     * - PENDIENTE: no existen pagos válidos.
     * - PAGO PARCIAL: se ha pagado una parte y todavía existe saldo.
     * - PAGADO: el saldo pendiente es cero.
     */
    public function getEstadoCalculadoAttribute(): string
    {
        $pagado = $this->total_pagado;
        $asignado = (float)$this->monto_asignado;

        if ($pagado <= 0) {
            return 'PENDIENTE';
        }

        if ($pagado < $asignado) {
            return 'PAGO PARCIAL';
        }

        return 'PAGADO';
    }
}
