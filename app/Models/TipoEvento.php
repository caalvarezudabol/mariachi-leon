<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoEvento extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_evento';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_sugerido',
        'activo',
    ];

    protected $casts = [
        'precio_sugerido' => 'decimal:2',
        'activo' => 'boolean',
    ];
}
