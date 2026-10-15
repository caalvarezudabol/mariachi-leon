<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Empresa;
use App\Models\Pago;
use App\Services\NumeroALetras;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class ReciboPdfController extends Controller
{
    use Auditable;

    /**
     * Obtiene los datos consolidados del recibo para PDF y Vista Previa
     */
    public function obtenerDatosRecibo($id)
    {
        $pago = Pago::with([
            'cliente',
            'contrato.servicio',
            'evento',
            'user',
            'anuladoPor'
        ])->findOrFail($id);

        if (empty($pago->codigo_verificacion)) {
            $pago->codigo_verificacion = Str::random(40);
            $pago->save();
        }

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();

        $montoLiteral = NumeroALetras::convertir((float) $pago->monto);
        $urlVerificacion = url('/verificar/recibo/' . $pago->codigo_verificacion);

        // QR Code SVG base64 (resolución nítida sin dependencias externas)
        $qrSvg = QrCode::format('svg')->size(75)->errorCorrection('M')->generate($urlVerificacion);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $usuarioImpresion = Auth::check() ? Auth::user()->name : 'Público / Sistema';
        $usuarioCreacion = $pago->user ? $pago->user->name : 'Sistema';
        $fechaImpresion = now()->format('d/m/Y H:i');
        $fechaCreacion = $pago->created_at ? $pago->created_at->format('d/m/Y H:i') : ($pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : now()->format('d/m/Y H:i'));

        return [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'pago' => $pago,
            'montoLiteral' => $montoLiteral,
            'qrBase64' => $qrBase64,
            'urlVerificacion' => $urlVerificacion,
            'usuarioImpresion' => $usuarioImpresion,
            'usuarioCreacion' => $usuarioCreacion,
            'fechaImpresion' => $fechaImpresion,
            'fechaCreacion' => $fechaCreacion,
        ];
    }

    /**
     * Vista Previa en Pantalla con opción de Impresión Directa vía Navegador
     */
    public function verRecibo($id)
    {
        $datos = $this->obtenerDatosRecibo($id);

        $this->registrarAuditoria(
            'Gestión Comercial',
            'Visualizar Recibo',
            'Se visualizó en pantalla el recibo N° ' . $datos['pago']->numero_recibo . ' por Bs ' . number_format($datos['pago']->monto, 2) . ' para ' . ($datos['pago']->cliente ? $datos['pago']->cliente->nombre_completo : 'N/A')
        );

        return view('comercial.recibo-vista-previa', $datos);
    }

    /**
     * Generación y Descarga de Recibo en PDF en formato Cuarto Oficio Horizontal (Landscape)
     */
    public function exportarPdf($id)
    {
        $datos = $this->obtenerDatosRecibo($id);

        // Auditoría
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Imprimir Recibo PDF',
            'Se generó en PDF (cuarto oficio horizontal) el recibo N° ' . $datos['pago']->numero_recibo . ' por Bs ' . number_format($datos['pago']->monto, 2) . ' para ' . ($datos['pago']->cliente ? $datos['pago']->cliente->nombre_completo : 'N/A')
        );

        // Cuarto Oficio / Media Carta Horizontal: 216mm x 140mm (612.28pt x 396.85pt)
        $pdf = Pdf::loadView('pdf.recibo-pago', $datos)
            ->setPaper([0, 0, 396.85, 612.28], 'landscape')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'recibo_' . strtolower(str_replace('-', '_', $datos['pago']->numero_recibo)) . '.pdf';

        return $pdf->stream($filename);
    }
}
