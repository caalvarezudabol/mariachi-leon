<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Paquete extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_paquete',
        'destacado',
        'activo',
    ];

    protected $casts = [
        'precio_paquete' => 'decimal:2',
        'destacado' => 'boolean',
        'activo' => 'boolean',
    ];

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'paquete_servicio');
    }
}
