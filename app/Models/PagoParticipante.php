<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoParticipante extends Model
{
    protected $table = 'pagos_participantes';

    protected $fillable = [
        'evento_participante_id',
        'distribucion_economica_id',
        'evento_id',
        'persona_id',
        'monto',
        'metodo_pago',
        'glosa',
        'observaciones',
        'fecha_pago',
        'estado',
        'motivo_reversion',
        'fecha_reversion',
        'revertido_por',
        'user_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'fecha_reversion' => 'datetime',
    ];

    public function participante(): BelongsTo
    {
        return $this->belongsTo(EventoParticipante::class, 'evento_participante_id');
    }

    public function distribucion(): BelongsTo
    {
        return $this->belongsTo(DistribucionEconomica::class, 'distribucion_economica_id');
    }

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

    public function revertidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revertido_por');
    }
}
