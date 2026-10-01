<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoParticipante extends Model
{
    protected $table = 'evento_participantes';

    protected $fillable = [
        'evento_id',
        'persona_id',
        'funcion',
        'concepto_pago',
        'monto_asignado',
        'estado_distribucion',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
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
}
