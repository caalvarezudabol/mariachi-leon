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
        $empresa = Empresa::obtener();

        // Conversión del logo a Base64
        $logoBase64 = null;
        if ($empresa->logo_url && Storage::disk('public')->exists(str_replace('storage/', '', $empresa->logo_url))) {
            $path = storage_path('app/public/' . str_replace('storage/', '', $empresa->logo_url));
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $datos = [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'contrato' => $contrato,
            'fechaEmision' => now()->format('d/m/Y H:i:s'),
        ];

        // Auditoría
        $this->registrarAuditoria('Gestión Comercial', 'Imprimir Contrato PDF', 'Se imprimió/generó en PDF el contrato N° ' . $contrato->numero_contrato . ' para el cliente ' . $contrato->cliente->nombre_completo);

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
