<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Empresa;
use App\Traits\Auditable;
use App\Services\Security\DocumentToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventoComisionesPdfController extends Controller
{
    use Auditable;

    public function exportarPdf($token)
    {
        // 1. Rechazar accesos con IDs numéricos simples (anti-enumeración)
        if (ctype_digit((string)$token) || DocumentToken::isPlainNumeric($token)) {
            return redirect()->route('web.home');
        }

        // 2. Decodificar el token del evento
        $eventoId = DocumentToken::decodeId($token, 'evento');
        if (!$eventoId) {
            return redirect()->route('web.home');
        }

        $evento = Evento::with(['cliente', 'servicio', 'contrato.pagos', 'participantes.persona', 'distribuciones.persona', 'user'])
            ->find($eventoId);

        if (!$evento) {
            return redirect()->route('web.home');
        }

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioEmision = Auth::check() ? Auth::user()->name : 'Sistema';

        // URL y código QR de verificación oficial
        $urlVerificacion = url('/verificar/comisiones/' . $token);
        $qrSvg = QrCode::format('svg')->size(100)->errorCorrection('M')->generate($urlVerificacion);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        // Cálculos financieros de comisiones y margen
        $montoEvento = $evento->contrato ? (float)$evento->contrato->monto_total : (float)($evento->monto_total ?? 0);
        $participantes = $evento->participantes;
        $totalComisiones = (float)$participantes->sum('monto_asignado');
        $totalPagado = (float)$participantes->where('estado_distribucion', 'Distribuido')->sum('monto_asignado');
        $totalPendiente = max(0.0, $totalComisiones - $totalPagado);
        $margenGrupo = $montoEvento - $totalComisiones;

        $resumen = [
            'monto_evento' => $montoEvento,
            'total_comisiones' => $totalComisiones,
            'total_pagado' => $totalPagado,
            'total_pendiente' => $totalPendiente,
            'margen_grupo' => $margenGrupo,
        ];

        // Auditoría
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Exportar PDF Comisiones Evento',
            "Se exportó la planilla de comisiones y liquidación a músicos del evento {$evento->codigo_evento} (Total Comisiones: Bs " . number_format($totalComisiones, 2) . ")"
        );

        $pdf = Pdf::loadView('pdf.planilla-comisiones-evento', [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'evento' => $evento,
            'participantes' => $participantes,
            'resumen' => $resumen,
            'fechaEmision' => $fechaEmision,
            'usuarioEmision' => $usuarioEmision,
            'token' => $token,
            'qrBase64' => $qrBase64,
            'urlVerificacion' => $urlVerificacion,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Planilla-Comisiones-' . $evento->codigo_evento . '.pdf');
    }
}
