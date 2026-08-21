<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $table = 'assets';

    protected $fillable = [
        'codigo',
        'nombre',
        'asset_category_id',
        'descripcion',
        'marca',
        'modelo',
        'numero_serie',
        'fecha_adquisicion',
        'costo_adquisicion',
        'tipo_control',
        'existencia',
        'costo_promedio_ppp',
        'estado',
        'responsable_id',
        'user_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'costo_adquisicion' => 'decimal:2',
        'existencia' => 'decimal:2',
        'costo_promedio_ppp' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(MusicoPersonal::class, 'responsable_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'asset_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(AssetReturn::class, 'asset_id');
    }

    public function disposals(): HasMany
    {
        return $this->hasMany(AssetDisposal::class, 'asset_id');
    }

    public function getStockActualAttribute(): float
    {
        $lastMov = $this->movements()->orderBy('fecha_movimiento', 'desc')->orderBy('id', 'desc')->first();
        if ($lastMov && isset($lastMov->cantidad_saldo)) {
            return (float) $lastMov->cantidad_saldo;
        }

        return (float) ($this->existencia ?? 0);
    }

    public function getCostoPppAttribute(): float
    {
        $lastMov = $this->movements()->orderBy('fecha_movimiento', 'desc')->orderBy('id', 'desc')->first();
        if ($lastMov && (float) $lastMov->costo_ppp_saldo > 0) {
            return (float) $lastMov->costo_ppp_saldo;
        }

        if ((float) $this->costo_promedio_ppp > 0) {
            return (float) $this->costo_promedio_ppp;
        }

        return (float) ($this->costo_adquisicion ?? 0);
    }

    public function getValorTotalInventarioAttribute(): float
    {
        $lastMov = $this->movements()->orderBy('fecha_movimiento', 'desc')->orderBy('id', 'desc')->first();
        if ($lastMov && isset($lastMov->valor_total_saldo)) {
            return (float) $lastMov->valor_total_saldo;
        }

        return (float) ($this->stock_actual * $this->costo_ppp);
    }
}
