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

    public function exportarPdf($id)
    {
        $pago = Pago::with(['cliente', 'contrato', 'evento', 'user', 'anuladoPor'])->findOrFail($id);

        if (empty($pago->codigo_verificacion)) {
            $pago->codigo_verificacion = Str::random(40);
            $pago->save();
        }

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();

        $montoLiteral = NumeroALetras::convertir((float) $pago->monto);
        $urlVerificacion = url('/verificar/recibo/' . $pago->codigo_verificacion);

        $qrSvg = QrCode::format('svg')->size(100)->errorCorrection('M')->generate($urlVerificacion);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $usuarioImpresion = Auth::check() ? Auth::user()->name : 'Público / Sistema';
        $usuarioCreacion = $pago->user ? $pago->user->name : 'Sistema';
        $fechaImpresion = now()->format('d/m/Y H:i:s');
        $fechaCreacion = $pago->created_at ? $pago->created_at->format('d/m/Y H:i:s') : $pago->fecha_pago->format('d/m/Y H:i:s');

        // Auditoría
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Imprimir Recibo PDF',
            'Se generó en PDF el recibo de pago N° ' . $pago->numero_recibo . ' por Bs ' . number_format($pago->monto, 2) . ' para el cliente ' . ($pago->cliente ? $pago->cliente->nombre_completo : 'N/A')
        );

        $datos = [
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

        $pdf = Pdf::loadView('pdf.recibo-pago', $datos)
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'recibo_' . strtolower(str_replace('-', '_', $pago->numero_recibo)) . '.pdf';

        return $pdf->stream($filename);
    }
}
