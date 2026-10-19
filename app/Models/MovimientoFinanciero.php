<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MovimientoFinanciero extends Model
{
    protected $table = 'movimientos_financieros';

    protected $fillable = [
        'tipo',
        'clasificacion',
        'categoria',
        'evento_id',
        'contrato_id',
        'proveedor',
        'concepto',
        'descripcion',
        'monto',
        'metodo_pago',
        'numero_comprobante',
        'fecha',
        'glosa',
        'observaciones',
        'estado',
        'motivo_anulacion',
        'anulado_por',
        'fecha_anulacion',
        'user_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
        'fecha_anulacion' => 'datetime',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeValido(Builder $query): Builder
    {
        return $query->where('estado', 'Valido');
    }

    public function scopeEgresos(Builder $query): Builder
    {
        return $query->where('tipo', 'EGRESO');
    }

    public function scopeEgreso(Builder $query): Builder
    {
        return $query->where('tipo', 'EGRESO');
    }

    public function scopeIngresos(Builder $query): Builder
    {
        return $query->where('tipo', 'INGRESO');
    }

    public function scopeIngreso(Builder $query): Builder
    {
        return $query->where('tipo', 'INGRESO');
    }

    public function scopeOperativos(Builder $query): Builder
    {
        return $query->where('clasificacion', 'OPERATIVO');
    }

    public function scopeOperativo(Builder $query): Builder
    {
        return $query->where('clasificacion', 'OPERATIVO');
    }

    public function scopeEventos(Builder $query): Builder
    {
        return $query->where('clasificacion', 'EVENTO');
    }
}
