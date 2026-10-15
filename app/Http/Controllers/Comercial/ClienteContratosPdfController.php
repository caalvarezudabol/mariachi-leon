<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Evento;
use App\Models\Empresa;
use App\Traits\Auditable;
use App\Services\Security\DocumentToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ClienteContratosPdfController extends Controller
{
    use Auditable;

    public function exportarPdf($token)
    {
        // 1. Rechazar accesos que utilicen IDs numéricos simples directos (anti-enumeración)
        if (ctype_digit((string)$token) || DocumentToken::isPlainNumeric($token)) {
            return redirect()->route('web.home');
        }

        // 2. Decodificar el token del cliente
        $clienteId = DocumentToken::decodeId($token, 'cliente');
        if (!$clienteId) {
            return redirect()->route('web.home');
        }

        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            return redirect()->route('web.home');
        }

        // 3. Obtener contratos del cliente con sus eventos, servicios y pagos
        $contratos = Contrato::with(['evento.servicio', 'servicio', 'pagos', 'user'])
            ->where('cliente_id', $cliente->id)
            ->orderBy('fecha_contrato', 'desc')
            ->get();

        // 4. Obtener eventos que aún no cuentan con contrato formal asociado
        $eventosSinContrato = Evento::with(['servicio', 'user', 'pagos'])
            ->where('cliente_id', $cliente->id)
            ->whereDoesntHave('contrato')
            ->orderBy('fecha_evento', 'desc')
            ->get();

        // 5. Cálculos financieros consolidados
        $totalContratos = $contratos->count();
        $totalMontoContratado = (float)$contratos->sum('monto_total');
        $totalAbonado = 0.0;
        foreach ($contratos as $cnt) {
            $totalAbonado += (float)$cnt->pagos->where('estado', 'Valido')->sum('monto');
        }
        $totalSaldo = max(0.0, $totalMontoContratado - $totalAbonado);

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioEmision = Auth::check() ? Auth::user()->name : 'Sistema';

        // 6. Generación del código QR en base64 (SVG de alta nitidez)
        $urlVerificacion = route('admin.clientes.historial-contratos.pdf', $token);
        $qrSvg = QrCode::format('svg')->size(75)->errorCorrection('M')->generate($urlVerificacion);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        // 7. Registro en bitácora de auditoría
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Generar Historial Contratos PDF',
            "Se generó en PDF el Historial de Contratos del cliente {$cliente->nombre_completo} ({$totalContratos} contratos). Monto: Bs " . number_format($totalMontoContratado, 2) . ", Cobrado: Bs " . number_format($totalAbonado, 2) . ", Saldo: Bs " . number_format($totalSaldo, 2)
        );

        // 8. Carga y renderizado del PDF con DomPDF
        $pdf = Pdf::loadView('pdf.historial-contratos-cliente', [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'cliente' => $cliente,
            'contratos' => $contratos,
            'eventosSinContrato' => $eventosSinContrato,
            'totalContratos' => $totalContratos,
            'totalMontoContratado' => $totalMontoContratado,
            'totalAbonado' => $totalAbonado,
            'totalSaldo' => $totalSaldo,
            'token' => $token,
            'qrBase64' => $qrBase64,
            'fechaEmision' => $fechaEmision,
            'usuarioEmision' => $usuarioEmision,
        ])
        ->setPaper('letter', 'portrait')
        ->setOption('isRemoteEnabled', false)
        ->setOption('isPhpEnabled', true)
        ->setOption('isFontSubsettingEnabled', true)
        ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'historial_contratos_' . strtolower(str_replace(' ', '_', $cliente->nombre_completo)) . '.pdf';

        return $pdf->stream($filename);
    }
}
