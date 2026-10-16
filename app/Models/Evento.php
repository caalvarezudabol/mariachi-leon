<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Evento extends Model
{
    use SoftDeletes;

    protected $table = 'eventos';

    protected $fillable = [
        'codigo_evento',
        'cliente_id',
        'servicio_id',
        'contacto_evento',
        'telefono_contacto',
        'fecha_evento',
        'hora_evento',
        'duracion_horas',
        'direccion_evento',
        'latitud',
        'longitud',
        'estado',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha_evento' => 'date',
        'duracion_horas' => 'decimal:2',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'evento_id');
    }

    public function contrato(): HasOne
    {
        return $this->hasOne(Contrato::class, 'evento_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'evento_id');
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(EventoParticipante::class, 'evento_id');
    }

    public function distribuciones(): HasMany
    {
        return $this->hasMany(DistribucionEconomica::class, 'evento_id');
    }

    public function pagosParticipantes(): HasMany
    {
        return $this->hasMany(PagoParticipante::class, 'evento_id');
    }

    public function getMontoContratadoAttribute(): float
    {
        if ($this->contrato) {
            return (float)$this->contrato->monto_total;
        }
        return 0.0;
    }

    public function getTotalAbonadoAttribute(): float
    {
        if ($this->relationLoaded('contrato') && $this->contrato && $this->contrato->relationLoaded('pagos')) {
            return (float)$this->contrato->pagos->where('estado', 'Valido')->sum('monto');
        }
        if ($this->contrato) {
            return (float)$this->contrato->pagos()->where('estado', 'Valido')->sum('monto');
        }
        if ($this->relationLoaded('pagos')) {
            return (float)$this->pagos->where('estado', 'Valido')->sum('monto');
        }
        return (float)$this->pagos()->where('estado', 'Valido')->sum('monto');
    }

    public function getSaldoPendienteAttribute(): float
    {
        return max(0.0, $this->monto_contratado - $this->total_abonado);
    }
}
