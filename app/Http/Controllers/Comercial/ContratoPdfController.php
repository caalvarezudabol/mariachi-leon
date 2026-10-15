<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Empresa;
use App\Models\Contrato;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ContratoPdfController extends Controller
{
    use Auditable;

    public function exportarPdf($id)
    {
        $contrato = Contrato::with(['cliente', 'evento', 'servicio', 'cotizacion', 'user'])->findOrFail($id);

        if (empty($contrato->codigo_verificacion)) {
            $contrato->codigo_verificacion = \Illuminate\Support\Str::random(40);
            $contrato->save();
        }

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();

        $urlVerificacion = url('/verificar/contrato/' . $contrato->codigo_verificacion);
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->errorCorrection('M')->generate($urlVerificacion);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $usuarioImpresion = Auth::check() ? Auth::user()->name : 'Público / Sistema';
        $usuarioCreacion = $contrato->user ? $contrato->user->name : 'Sistema';
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $fechaCreacion = $contrato->created_at ? $contrato->created_at->format('d/m/Y H:i:s') : $contrato->fecha_contrato->format('d/m/Y H:i:s');

        $datos = [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'contrato' => $contrato,
            'qrBase64' => $qrBase64,
            'urlVerificacion' => $urlVerificacion,
            'usuarioImpresion' => $usuarioImpresion,
            'usuarioCreacion' => $usuarioCreacion,
            'fechaEmision' => $fechaEmision,
            'fechaCreacion' => $fechaCreacion,
        ];

        // Auditoría
        $this->registrarAuditoria('Gestión Comercial', 'Imprimir Contrato PDF', 'Se imprimió/generó en PDF el contrato N° ' . $contrato->numero_contrato . ' para el cliente ' . ($contrato->cliente ? $contrato->cliente->nombre_completo : 'N/A'));

        $pdf = Pdf::loadView('pdf.contrato-evento', $datos)
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'contrato_' . strtolower(str_replace('-', '_', $contrato->numero_contrato)) . '.pdf';

        return $pdf->stream($filename);
    }
}
