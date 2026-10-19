<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FondoComunMovimiento extends Model
{
    protected $table = 'fondo_comun_movimientos';

    protected $fillable = [
        'tipo',
        'concepto',
        'monto',
        'saldo_anterior',
        'saldo_resultante',
        'fecha',
        'metodo',
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
        'saldo_anterior' => 'decimal:2',
        'saldo_resultante' => 'decimal:2',
        'fecha' => 'date',
        'fecha_anulacion' => 'datetime',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

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

    // =========================================================================
    // MÉTODOS DE NEGOCIO
    // =========================================================================

    /**
     * Obtiene el saldo actual del fondo común (último movimiento válido o cálculo de ingresos - egresos válidos)
     */
    public static function obtenerSaldoActual(): float
    {
        $totalIngresos = (float) static::where('estado', 'Valido')->where('tipo', 'INGRESO')->sum('monto');
        $totalEgresos = (float) static::where('estado', 'Valido')->where('tipo', 'EGRESO')->sum('monto');
        return max(0.0, $totalIngresos - $totalEgresos);
    }
}
