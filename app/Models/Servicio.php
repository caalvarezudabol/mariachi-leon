<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    use SoftDeletes;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'tipo_servicio',
        'descripcion',
        'precio_base',
        'duracion_minutos',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'duracion_minutos' => 'integer',
        'activo' => 'boolean',
    ];

    public function paquetes(): BelongsToMany
    {
        return $this->belongsToMany(Paquete::class, 'paquete_servicio');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'servicio_id');
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'servicio_id');
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'servicio_id');
    }
}
