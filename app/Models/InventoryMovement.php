<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';

    protected $fillable = [
        'asset_id',
        'user_id',
        'fecha_movimiento',
        'tipo_movimiento',
        'motivo',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'cantidad_saldo',
        'costo_ppp_saldo',
        'valor_total_saldo',
        'responsable_id',
        'documento_referencia',
        'observaciones',
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
        'cantidad' => 'decimal:2',
        'costo_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'cantidad_saldo' => 'decimal:2',
        'costo_ppp_saldo' => 'decimal:2',
        'valor_total_saldo' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(MusicoPersonal::class, 'responsable_id');
    }
}
