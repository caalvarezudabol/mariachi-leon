<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReporteEjecutivoController extends Controller
{
    public function exportarPdf()
    {
        $empresa = Empresa::obtener();

        // Conversión del logo a Base64
        $logoBase64 = null;
        if ($empresa->logo_url && Storage::disk('public')->exists(str_replace('storage/', '', $empresa->logo_url))) {
            $path = storage_path('app/public/' . str_replace('storage/', '', $empresa->logo_url));
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Historial completo de prompts y soluciones implementadas
        $prompts = [
            [
                'prompt' => 'si',
                'solucion' => 'Aprobación e inicio de requerimientos técnicos del proyecto Mariachi León Guanajuato.'
            ],
            [
                'prompt' => 'no olvidar que estamos trabajando sobre php 8.1.10',
                'solucion' => 'Definición de regla técnica permanente inflexible. Bloqueo de Composer Platform en PHP 8.1.10.'
            ],
            [
                'prompt' => 'me sale este error [500 Internal Server Error en Livewire upload]',
                'solucion' => 'Registro del alias \'throttle\' en Kernel.php ($middlewareAliases) y configuración del disk \'public\' en config/livewire.php.'
            ],
            [
                'prompt' => 'Continue',
                'solucion' => 'Continuación del desarrollo de los servicios y vistas del sistema.'
            ],
            [
                'prompt' => 'sigo con problemas',
                'solucion' => 'Verificación y creación física de carpetas temporales storage/app/public/livewire-tmp con permisos de escritura.'
            ],
            [
                'prompt' => 'me parece que es tema de version de php, estamos trabajando sobre php 8.1.10',
                'solucion' => 'Revisión y desarrollo del servicio de imágenes ImageOptimizerService usando la extensión nativa GD en PHP 8.1.10.'
            ],
            [
                'prompt' => 'analiza como cambiar este logo y éste sea para todos los documentos',
                'solucion' => 'Implementación de carga interactiva de Logotipo Oficial en el Módulo Empresa y su propagación dinámica a Sidebar Admin, Comprobantes, Kardex PDF y Sitio Web Público.'
            ],
            [
                'prompt' => 'tengo este error: cdn.tailwindcss.com / livewire upload 500',
                'solucion' => 'Configuración estricta de APP_URL=http://127.0.0.1:8000 en .env para la validación de URLs firmadas.'
            ],
            [
                'prompt' => 'perfecto, ahora vamos a arreglar el PDF del KARDEX, quiero que lo optimices y presentes algo profesional',
                'solucion' => 'Optimización del controlador KardexPdfController con miniatura Base64 mediante GD, reduciendo la descarga a milisegundos (~40KB).'
            ],
            [
                'prompt' => 'guarda en tu memoria que todo este proyecto es con php 8.1.10',
                'solucion' => 'Registro en memoria activa permanente de la compatibilidad estricta con PHP 8.1.10.'
            ],
            [
                'prompt' => 'modifica el pdf, no tiene formato de informe existen hojas en blanco, proponme uno',
                'solucion' => 'Reestructuración CSS con page-break-inside: avoid en todas las tablas y secciones para eliminar hojas en blanco.'
            ],
            [
                'prompt' => 'lo quiero en este formato el kardex',
                'solucion' => 'Recreación fiel 1:1 de la plantilla del Kardex PDF basada en la maqueta de diseño presentada por el usuario.'
            ],
            [
                'prompt' => 'ahora quiero que tambien se actualice el logo del index de inicio, el ejemplo de la portada del sitio web',
                'solucion' => 'Actualización de componentes/layouts/web.blade.php para que el Header y Footer del portal web público muestren el logotipo dinámico del Módulo Empresa.'
            ],
            [
                'prompt' => 'quiero que el articulo se pueda buscar y tambien seleccionar',
                'solucion' => 'Desarrollo de buscador con autocompletado en tiempo real por código/nombre y selector con botón [ ✕ ] para limpiar.'
            ],
            [
                'prompt' => 'faltan los iconos en el pdf, suprime para el informe porque se ve feo',
                'solucion' => 'Remoción total de emojis y caracteres no soportados en DomPDF, dejando una tipografía nítida y ejecutiva.'
            ],
            [
                'prompt' => 'actualiza el pie de pagina pagina 1 de 0, no deberia ser asi, deberia ser 1 de 1',
                'solucion' => 'Activación de isPhpEnabled = true y script de DomPDF page_text() para la numeración exacta "Página X de Y".'
            ],
            [
                'prompt' => 'quiero que muevas datos de empresa al primer item del menu',
                'solucion' => 'Reordenamiento del Sidebar Admin ubicar "Datos de la Empresa" como primer ítem bajo SPRINT 1: BASE.'
            ],
            [
                'prompt' => 'ahora quiero que me mandes un pdf de todo lo realizado hasta el momento, incluyendo prompts',
                'solucion' => 'Generación de este Informe Ejecutivo de Proyecto y Bitácora de Prompts en formato PDF descargable.'
            ]
        ];

        $datos = [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'prompts' => $prompts,
            'fechaEmision' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.reporte-ejecutivo', $datos)
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'reporte_ejecutivo_proyecto_mariachi_leon.pdf';

        return $pdf->download($filename);
    }
}
