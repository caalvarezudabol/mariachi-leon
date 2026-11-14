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
        $origen = $empresa->logo_url ? public_path($empresa->logo_url) : null;
        $cabeceras = ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400'];

        // Sin logo: usar el favicon del sitio
        if (!$origen || !is_file($origen)) {
            $fallback = public_path('favicon.png');
            abort_unless(is_file($fallback), 404);
            return response()->file($fallback, $cabeceras);
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
}