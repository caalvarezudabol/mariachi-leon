<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'nit_ruc',
        'slogan',
        'representante_legal',
        'telefono_principal',
        'whatsapp_comercial',
        'email_contacto',
        'direccion_fisica',
        'ciudad_pais',
        'logo_url',
        'moneda_nombre',
        'moneda_simbolo',
        'redes_linktree',
        'banco_nombre',
        'banco_numero_cuenta',
        'banco_titular',
        'banco_qr_url',
        'terminos_contrato',
        'observaciones',
    ];

    public static function obtener(): self
    {
        return self::firstOrCreate([], [
            'nombre_comercial' => 'Mariachi León Guanajuato',
            'razon_social' => 'Mariachi León Guanajuato S.R.L.',
            'nit_ruc' => '1029384756',
            'slogan' => 'Puntualidad, elegancia y virtuosismo musical en cada presentación',
            'representante_legal' => 'Enrrique Escalera',
            'telefono_principal' => '+591 700 00000',
            'whatsapp_comercial' => '+591 700 00000',
            'email_contacto' => 'contacto@mariachileonguanajuato.com',
            'direccion_fisica' => 'León, Guanajuato, México / Santa Cruz, Bolivia',
            'ciudad_pais' => 'Santa Cruz - Bolivia',
            'moneda_nombre' => 'Bolivianos',
            'moneda_simbolo' => 'Bs.',
            'redes_linktree' => 'https://linktr.ee/mariachileonguanajuato',
            'banco_nombre' => 'Banco Nacional de Bolivia',
            'banco_numero_cuenta' => '1000-2938-4756',
            'banco_titular' => 'Enrrique Escalera',
            'terminos_contrato' => 'El Mariachi León Guanajuato se compromete a brindar puntualidad, profesionalismo y presentación impecable.',
        ]);
    }
}
