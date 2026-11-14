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
            'name' => $nombre . ' - Sistema',
            'short_name' => mb_strimwidth($nombre, 0, 12, ''),
            'description' => 'Sistema de gestión de ' . $nombre,
            'start_url' => route('admin.dashboard', [], false),
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#0f172a',
            'theme_color' => '#f59e0b',
            'lang' => 'es',
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

        if (!$origen || !is_file($origen) || !function_exists('imagecreatefromstring')) {
            $fallback = public_path('favicon.png');
            abort_unless(is_file($fallback), 404);
            return response()->file($fallback);
        }

        $cache = storage_path('app/pwa-icon-' . $size . ($maskable ? 'm' : '') . '-' . filemtime($origen) . '.png');
        if (!is_file($cache)) {
            $img = @imagecreatefromstring(file_get_contents($origen));
            abort_unless($img, 404);

            $lienzo = imagecreatetruecolor($size, $size);
            imagealphablending($lienzo, true);
            imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255));

            // El logo ocupa ~80% (70% si es maskable, para que no lo recorte el círculo)
            $caja = (int) round($size * ($maskable ? 0.70 : 0.82));
            $w = imagesx($img);
            $h = imagesy($img);
            $escala = min($caja / $w, $caja / $h);
            $nw = max(1, (int) round($w * $escala));
            $nh = max(1, (int) round($h * $escala));
            imagecopyresampled($lienzo, $img, (int) (($size - $nw) / 2), (int) (($size - $nh) / 2), 0, 0, $nw, $nh, $w, $h);

            imagepng($lienzo, $cache);
            imagedestroy($img);
            imagedestroy($lienzo);
        }

        return response()->file($cache, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=604800']);
    }
}