<?php

namespace App\Services\Security;

class DocumentToken
{
    /**
     * Codifica un ID numérico en un token alfanumérico seguro para URLs
     */
    public static function encodeId(int $id, string $context = 'doc'): string
    {
        $appKey = config('app.key', 'mariachi-leon-secure-key');
        $key = substr(hash('sha256', $appKey . $context), 0, 16);
        $iv = substr(hash('sha256', $context . $appKey . 'iv'), 0, 16);
        
        $payload = (string)$id;
        $encrypted = openssl_encrypt($payload, 'AES-128-CBC', $key, 0, $iv);
        
        return rtrim(strtr($encrypted, '+/', '-_'), '=');
    }

    /**
     * Decodifica un token alfanumérico a su ID entero original.
     * Retorna null si el token es inválido o no coincide.
     */
    public static function decodeId(string $token, string $context = 'doc'): ?int
    {
        if (empty($token) || self::isPlainNumeric($token)) {
            return null;
        }

        $appKey = config('app.key', 'mariachi-leon-secure-key');
        $key = substr(hash('sha256', $appKey . $context), 0, 16);
        $iv = substr(hash('sha256', $context . $appKey . 'iv'), 0, 16);
        
        $base64 = strtr($token, '-_', '+/');
        // Restaurar padding '=' si es necesario
        $mod4 = strlen($base64) % 4;
        if ($mod4 > 0) {
            $base64 .= str_repeat('=', 4 - $mod4);
        }

        $decrypted = openssl_decrypt($base64, 'AES-128-CBC', $key, 0, $iv);
        
        if ($decrypted === false || !ctype_digit($decrypted)) {
            return null;
        }

        return (int)$decrypted;
    }

    /**
     * Verifica si una cadena es un número entero simple no cifrado
     */
    public static function isPlainNumeric($value): bool
    {
        if (is_int($value)) {
            return true;
        }
        if (!is_string($value)) {
            return false;
        }
        return ctype_digit(trim($value));
    }
}
