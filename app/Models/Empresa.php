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
        'redes_facebook',
        'redes_instagram',
        'redes_tiktok',
        'redes_youtube',
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
            'redes_facebook' => 'https://www.facebook.com/profile.php?id=100084898613114&mibextid=wwXIfr&rdid=kBYYK5El1w37sd4I&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F18V9VWnVxP%2F%3Fmibextid%3DwwXIfr#',
            'redes_instagram' => 'https://www.instagram.com/mariachileonguanajuato',
            'redes_tiktok' => 'https://www.tiktok.com/@mariachileonguanajuato',
            'redes_youtube' => 'http://youtube.com/@mariachileonguanajuato',
            'banco_nombre' => 'Banco Nacional de Bolivia',
            'banco_numero_cuenta' => '1000-2938-4756',
            'banco_titular' => 'Enrrique Escalera',
            'terminos_contrato' => 'El Mariachi León Guanajuato se compromete a brindar puntualidad, profesionalismo y presentación impecable.',
        ]);
    }

    public function obtenerLogoBase64(): ?string
    {
        if (!$this->logo_url) {
            return null;
        }

        $relativePath = parse_url($this->logo_url, PHP_URL_PATH) ?? $this->logo_url;
        $relativePath = ltrim(str_replace(['/storage/', 'storage/'], '', $relativePath), '/');

        $fullPath = storage_path('app/public/' . $relativePath);

        if (!file_exists($fullPath)) {
            $fullPath = public_path('storage/' . $relativePath);
        }

        if (!file_exists($fullPath)) {
            $fullPath = public_path(ltrim($this->logo_url, '/'));
        }

        if (file_exists($fullPath) && is_file($fullPath)) {
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($ext === 'jpeg') $ext = 'jpg';
            $mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
            $data = @file_get_contents($fullPath);
            if ($data) {
                return 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        }

        return null;
    }

    public function getLogoPublicUrlAttribute(): ?string
    {
        if (!$this->logo_url) {
            return null;
        }
        $url = ltrim($this->logo_url, '/');
        return asset($url);
    }

    public function getWhatsappNumeroLimpioAttribute(): string
    {
        $tel = $this->whatsapp_comercial ?: $this->telefono_principal;
        return preg_replace('/[^0-9]/', '', (string)$tel);
    }

    public function getWhatsappUrlAttribute(): string
    {
        $numero = $this->whatsapp_numero_limpio;
        if (empty($numero)) {
            return '#';
        }
        $nombre = $this->nombre_comercial ?? 'Mariachi León Guanajuato';
        $mensaje = rawurlencode("Hola {$nombre}, me gustaría solicitar información y cotización para un evento musical.");
        return "https://api.whatsapp.com/send?phone={$numero}&text={$mensaje}";
    }

    public function getTelefonoUrlAttribute(): string
    {
        $clean = preg_replace('/[^0-9+]/', '', (string)$this->telefono_principal);
        return $clean ? "tel:{$clean}" : '#';
    }

    public function getRedesSocialesAttribute(): array
    {
        $redes = [];

        if ($this->whatsapp_numero_limpio) {
            $redes[] = [
                'clave' => 'whatsapp',
                'nombre' => 'WhatsApp',
                'url' => $this->whatsapp_url,
                'icono' => 'fa-brands fa-whatsapp',
                'color' => 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-400/40 shadow-emerald-600/30',
                'texto' => 'WhatsApp Directo',
            ];
        }

        if ($this->redes_facebook) {
            $redes[] = [
                'clave' => 'facebook',
                'nombre' => 'Facebook',
                'url' => $this->redes_facebook,
                'icono' => 'fa-brands fa-facebook-f',
                'color' => 'bg-[#1877F2] hover:bg-[#166fe5] text-white border-blue-400/40 shadow-blue-600/30',
                'texto' => 'Facebook',
            ];
        }

        if ($this->redes_instagram) {
            $redes[] = [
                'clave' => 'instagram',
                'nombre' => 'Instagram',
                'url' => $this->redes_instagram,
                'icono' => 'fa-brands fa-instagram',
                'color' => 'bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] hover:opacity-90 text-white border-pink-400/40 shadow-pink-600/30',
                'texto' => 'Instagram',
            ];
        }

        if ($this->redes_tiktok) {
            $redes[] = [
                'clave' => 'tiktok',
                'nombre' => 'TikTok',
                'url' => $this->redes_tiktok,
                'icono' => 'fa-brands fa-tiktok',
                'color' => 'bg-slate-900 hover:bg-black text-white border-cyan-400/50 hover:border-pink-500/50 shadow-cyan-600/20',
                'texto' => 'TikTok',
            ];
        }

        if ($this->redes_youtube) {
            $redes[] = [
                'clave' => 'youtube',
                'nombre' => 'YouTube',
                'url' => $this->redes_youtube,
                'icono' => 'fa-brands fa-youtube',
                'color' => 'bg-[#FF0000] hover:bg-[#e60000] text-white border-red-400/40 shadow-red-600/30',
                'texto' => 'YouTube',
            ];
        }

        return $redes;
    }
}
