<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MusicoPersonal extends Model
{
    protected $table = 'musicos_personal';

    protected $fillable = [
        'nombre',
        'apellido',
        'nombre_completo',
        'ci_nit',
        'tipo',
        'telefono',
        'email',
        'direccion',
        'estado',
        'observaciones',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'responsable_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'responsable_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(AssetReturn::class, 'responsable_id');
    }

    public function disposals(): HasMany
    {
        return $this->hasMany(AssetDisposal::class, 'responsable_id');
    }

    public function participaciones(): HasMany
    {
        return $this->hasMany(EventoParticipante::class, 'persona_id');
    }

    public function distribuciones(): HasMany
    {
        return $this->hasMany(DistribucionEconomica::class, 'persona_id');
    }
}
