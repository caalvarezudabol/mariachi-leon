<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Response;

/**
 * PWA: manifest, service worker e iconos generados desde el logo de la empresa.
 */
class PwaController extends Controller
{
    public function manifest()
    {
        $empresa = Empresa::obtener();
        $nombre = $empresa->nombre_comercial ?: 'Mariachi León Guanajuato';
        $v = $empresa->updated_at ? $empresa->updated_at->timestamp : 1;

        $manifest = [
            'id' => '/',
            'name' => $nombre . ' - Sistema',
            'short_name' => 'Mariachi León',
            'description' => 'Sistema de gestión de ' . $nombre,
            'start_url' => route('admin.dashboard', [], false),
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#0f172a',
            'theme_color' => '#f59e0b',
            'lang' => 'es',
            'categories' => ['business', 'productivity'],
            'icons' => [
                ['src' => route('pwa.icono', ['size' => 192], false) . '?v=' . $v, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => route('pwa.icono', ['size' => 512], false) . '?v=' . $v, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => route('pwa.icono', ['size' => 512], false) . '?v=' . $v . '&m=1', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ];

        return response()->json($manifest)->header('Content-Type', 'application/manifest+json');
    }

    public function serviceWorker()
    {
        // Service worker mínimo: hace la app instalable sin guardar páginas en caché
        // (el sistema es dinámico y siempre debe mostrar datos al día).
        $js = <<<'JS'
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {});
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'no-cache',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    public function icono(int $size)
    {
        $size = $size >= 512 ? 512 : 192;
        $maskable = request()->boolean('m');
        $empresa = Empresa::obtener();
        $origen = $this->buscarArchivo($empresa->logo_url) ?: $this->ultimoLogoSubido();
        $cabeceras = ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400'];

        // No se encontró el archivo en disco: redirigir a la misma URL que usa el sitio para mostrar el logo
        if (!$origen) {
            return redirect($empresa->logo_url ? asset($empresa->logo_url) : asset('favicon.png'));
        }

        $cache = storage_path('app/pwa-icon-' . $size . ($maskable ? 'm' : '') . '-' . filemtime($origen) . '.png');
        if (is_file($cache)) {
            return response()->file($cache, $cabeceras);
        }

        try {
            $img = function_exists('imagecreatefromstring') ? @imagecreatefromstring(file_get_contents($origen)) : false;
            if (!$img) {
                throw new \RuntimeException('GD no pudo leer el logo');
            }

            $lienzo = imagecreatetruecolor($size, $size);
            imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255));

            // El logo ocupa ~82% (70% si es maskable, para que no lo recorte el círculo)
            $caja = (int) round($size * ($maskable ? 0.70 : 0.82));
            $w = imagesx($img);
            $h = imagesy($img);
            $escala = min($caja / $w, $caja / $h);
            $nw = max(1, (int) round($w * $escala));
            $nh = max(1, (int) round($h * $escala));
            imagecopyresampled($lienzo, $img, (int) (($size - $nw) / 2), (int) (($size - $nh) / 2), 0, 0, $nw, $nh, $w, $h);

            ob_start();
            imagepng($lienzo);
            $png = ob_get_clean();
            imagedestroy($img);
            imagedestroy($lienzo);

            @file_put_contents($cache, $png); // si no se puede guardar, igual se responde
            return response($png, 200, $cabeceras);
        } catch (\Throwable $e) {
            // Último recurso: entregar el logo tal cual con su tipo real
            $mime = mime_content_type($origen) ?: 'image/png';
            return response()->file($origen, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=3600']);
        }
    }

    /** Busca el archivo del logo en las carpetas posibles (Hostinger puede usar public_html). */
    private function buscarArchivo(?string $ruta): ?string
    {
        if (!$ruta) {
            return null;
        }
        $ruta = ltrim($ruta, '/');
        $candidatos = [
            public_path($ruta),
            base_path('public/' . $ruta),
            base_path('../public_html/' . $ruta),
            rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/' . $ruta,
            storage_path('app/public/' . preg_replace('#^storage/#', '', $ruta)),
        ];
        foreach ($candidatos as $c) {
            if (is_file($c)) {
                return $c;
            }
        }
        return null;
    }

    /** Si Empresa no tiene logo asignado, usa el más reciente de storage/logos. */
    private function ultimoLogoSubido(): ?string
    {
        $carpetas = [
            public_path('storage/logos'),
            storage_path('app/public/logos'),
            rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/storage/logos',
        ];
        foreach ($carpetas as $dir) {
            $archivos = glob($dir . '/*.{png,jpg,jpeg,webp}', GLOB_BRACE) ?: [];
            if ($archivos) {
                usort($archivos, fn($a, $b) => filemtime($b) <=> filemtime($a));
                return $archivos[0];
            }
        }
        return null;
    }
}