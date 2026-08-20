<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReturn extends Model
{
    protected $table = 'asset_returns';

    protected $fillable = [
        'asset_assignment_id',
        'asset_id',
        'responsable_id',
        'user_id',
        'fecha_devolucion',
        'cantidad',
        'condicion_recepcion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_devolucion' => 'datetime',
        'cantidad' => 'decimal:2',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(MusicoPersonal::class, 'responsable_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
