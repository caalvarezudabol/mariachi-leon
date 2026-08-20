<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetAssignment extends Model
{
    protected $table = 'asset_assignments';

    protected $fillable = [
        'asset_id',
        'responsable_id',
        'user_id',
        'fecha_asignacion',
        'cantidad',
        'condicion_entrega',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'cantidad' => 'decimal:2',
    ];

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

    public function returnRecord(): HasOne
    {
        return $this->hasOne(AssetReturn::class, 'asset_assignment_id');
    }
}
