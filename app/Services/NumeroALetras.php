<?php

namespace App\Services;

class NumeroALetras
{
    private static $unidades = [
        '', 'UN ', 'DOS ', 'TRES ', 'CUATRO ', 'CINCO ', 'SEIS ', 'SIETE ', 'OCHO ', 'NUEVE ',
        'DIEZ ', 'ONCE ', 'DOCE ', 'TRECE ', 'CATORCE ', 'QUINCE ', 'DIECISÉIS ', 'DIECISIETE ',
        'DIECIOCHO ', 'DIECINUEVE ', 'VEINTE '
    ];

    private static $decenas = [
        'VEINTI', 'TREINTA ', 'CUARENTA ', 'CINCUENTA ', 'SESENTA ', 'SETENTA ', 'OCHENTA ', 'NOVENTA ', 'CIEN '
    ];

    private static $centenas = [
        'CIENTO ', 'DOSCIENTOS ', 'TRESCIENTOS ', 'CUATROCIENTOS ', 'QUINIENTOS ',
        'SEISCIENTOS ', 'SETECIENTOS ', 'OCHOCIENTOS ', 'NOVECIENTOS '
    ];

    public static function convertir(float $cantidad, string $moneda = 'BOLIVIANOS'): string
    {
        $cantidad = round($cantidad, 2);
        $entero = (int)$cantidad;
        $centavos = (int)round(($cantidad - $entero) * 100);

        if ($entero === 0) {
            $letras = 'CERO ';
        } else {
            $letras = self::seccion($entero);
        }

        $centavosStr = str_pad((string)$centavos, 2, '0', STR_PAD_LEFT) . '/100';

        return trim($letras) . ' ' . $centavosStr . ' ' . trim($moneda);
    }

    private static function seccion(int $numero): string
    {
        if ($numero >= 1000000) {
            $millones = (int)($numero / 1000000);
            $resto = $numero % 1000000;
            $textoMillones = ($millones === 1) ? 'UN MILLÓN ' : self::seccion($millones) . 'MILLONES ';
            return $textoMillones . ($resto > 0 ? self::seccion($resto) : '');
        }

        if ($numero >= 1000) {
            $miles = (int)($numero / 1000);
            $resto = $numero % 1000;
            $textoMiles = ($miles === 1) ? 'MIL ' : self::centenas($miles) . 'MIL ';
            return $textoMiles . ($resto > 0 ? self::centenas($resto) : '');
        }

        return self::centenas($numero);
    }

    private static function centenas(int $numero): string
    {
        if ($numero === 100) {
            return 'CIEN ';
        }

        $c = (int)($numero / 100);
        $resto = $numero % 100;
        $textoC = ($c > 0) ? self::$centenas[$c - 1] : '';

        return $textoC . self::decenas($resto);
    }

    private static function decenas(int $numero): string
    {
        if ($numero === 0) {
            return '';
        }

        if ($numero <= 20) {
            return self::$unidades[$numero];
        }

        $d = (int)($numero / 10);
        $u = $numero % 10;

        if ($d === 2) {
            return ($u === 0) ? 'VEINTE ' : 'VEINTI' . self::$unidades[$u];
        }

        $textoD = self::$decenas[$d - 2];
        if ($u > 0) {
            return $textoD . 'Y ' . self::$unidades[$u];
        }

        return $textoD;
    }
}
