<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cotizacion extends Model
{
    use SoftDeletes;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'numero_cotizacion',
        'cliente_id',
        'evento_id',
        'servicio_id',
        'fecha_cotizacion',
        'precio_base',
        'descuento',
        'monto_total',
        'estado',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha_cotizacion' => 'date',
        'precio_base' => 'decimal:2',
        'descuento' => 'decimal:2',
        'monto_total' => 'decimal:2',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contrato(): HasOne
    {
        return $this->hasOne(Contrato::class, 'cotizacion_id');
    }
}
