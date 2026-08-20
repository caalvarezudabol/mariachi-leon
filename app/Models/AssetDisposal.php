<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDisposal extends Model
{
    protected $table = 'asset_disposals';

    protected $fillable = [
        'asset_id',
        'user_id',
        'responsable_id',
        'fecha_baja',
        'cantidad',
        'motivo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_baja' => 'date',
        'cantidad' => 'decimal:2',
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
