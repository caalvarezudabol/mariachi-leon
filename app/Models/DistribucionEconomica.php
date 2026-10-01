<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribucionEconomica extends Model
{
    protected $table = 'distribuciones_economicas';

    protected $fillable = [
        'evento_id',
        'contrato_id',
        'persona_id',
        'evento_participante_id',
        'funcion',
        'concepto',
        'monto',
        'fecha_distribucion',
        'estado',
        'user_id',
    ];

    protected $casts = [
        'fecha_distribucion' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(MusicoPersonal::class, 'persona_id');
    }

    public function participante(): BelongsTo
    {
        return $this->belongsTo(EventoParticipante::class, 'evento_participante_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
