<?php

namespace App\Support;

class Texto
{
    /**
     * Nombre propio: primera letra de cada palabra en mayúscula ("juan pérez" => "Juan Pérez").
     * Las partículas (de, del, la, y...) quedan en minúscula cuando no son la primera palabra.
     */
    public static function nombrePropio(?string $texto): string
    {
        $texto = trim(preg_replace('/\s+/u', ' ', (string) $texto));
        if ($texto === '') {
            return '';
        }

        $particulas = ['de', 'del', 'la', 'las', 'los', 'y', 'e', 'da', 'do', 'van', 'von'];
        $palabras = explode(' ', mb_strtolower($texto, 'UTF-8'));

        foreach ($palabras as $i => $p) {
            if ($i > 0 && in_array($p, $particulas, true)) {
                continue;
            }
            // También capitaliza tras guion o apóstrofe (Pérez-Gómez, D'Angelo)
            $palabras[$i] = preg_replace_callback(
                '/(^|[-\'’])(\p{L})/u',
                fn($m) => $m[1] . mb_strtoupper($m[2], 'UTF-8'),
                $p
            );
        }

        return implode(' ', $palabras);
    }
}