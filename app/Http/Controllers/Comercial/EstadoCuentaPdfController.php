<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Pago;
use App\Models\Empresa;
use App\Traits\Auditable;
use App\Services\Security\DocumentToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EstadoCuentaPdfController extends Controller
{
    use Auditable;

    public function exportarPdf(Request $request, $token)
    {
        // 1. Rechazar accesos que utilicen IDs numéricos simples directos (anti-enumeración)
        if (ctype_digit((string)$token) || DocumentToken::isPlainNumeric($token)) {
            return redirect()->route('web.home');
        }

        // 2. Verificar parámetro contrato en URL si se envía numérico simple
        $contratoParam = $request->get('contrato_id') ?? $request->get('contrato');
        if ($contratoParam !== null && (ctype_digit((string)$contratoParam) || is_numeric($contratoParam))) {
            return redirect()->route('web.home');
        }

        // 3. Decodificar el token del cliente
        $clienteId = DocumentToken::decodeId($token, 'cliente');
        if (!$clienteId) {
            return redirect()->route('web.home');
        }

        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            return redirect()->route('web.home');
        }

        // 4. Decodificar parámetro contrato si se envía
        $contrato_id = null;
        if ($contratoParam) {
            $decodedContratoId = DocumentToken::decodeId($contratoParam, 'contrato');
            if ($decodedContratoId) {
                $contrato_id = $decodedContratoId;
            } else {
                $contratoObj = Contrato::where('codigo_verificacion', $contratoParam)
                    ->where('cliente_id', $cliente->id)
                    ->first();
                if ($contratoObj) {
                    $contrato_id = $contratoObj->id;
                }
            }
        }

        $tipo = $request->get('tipo', 'general');
        $fecha_inicio = $request->get('fecha_inicio');
        $fecha_fin = $request->get('fecha_fin');
        $metodo_pago = $request->get('metodo_pago');
        $usuario_id = $request->get('usuario_id');
        $estado_pago = $request->get('estado_pago', 'Valido');
        $search_recibo = $request->get('search_recibo');

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioGenerador = Auth::check() ? Auth::user()->name : 'Sistema';

        if ($tipo === 'contrato') {
            if (!$contrato_id) {
                return redirect()->route('web.home');
            }

            $contrato = Contrato::with(['evento', 'servicio', 'cliente', 'pagos.user'])
                ->where('cliente_id', $cliente->id)
                ->where('id', $contrato_id)
                ->first();

            if (!$contrato) {
                return redirect()->route('web.home');
            }

            $montoContratado = (float)$contrato->monto_total;
            $totalAbonado = (float)$contrato->pagos->where('estado', 'Valido')->sum('monto');
            $saldoPendiente = max(0, $montoContratado - $totalAbonado);
            $cantidadPagosValidos = $contrato->pagos->where('estado', 'Valido')->count();
            $ultimoPago = $contrato->pagos->where('estado', 'Valido')->sortByDesc('fecha_pago')->first();

            // Filtrado de movimientos
            $queryPagos = $contrato->pagos()->with('user');
            if ($fecha_inicio) $queryPagos->whereDate('fecha_pago', '>=', $fecha_inicio);
            if ($fecha_fin) $queryPagos->whereDate('fecha_pago', '<=', $fecha_fin);
            if ($metodo_pago) $queryPagos->where('metodo_pago', $metodo_pago);
            if ($usuario_id) $queryPagos->where('user_id', $usuario_id);
            if ($estado_pago && $estado_pago !== 'Todos') $queryPagos->where('estado', $estado_pago);
            if ($search_recibo) $queryPagos->where('numero_recibo', 'like', "%{$search_recibo}%");

            $pagos = $queryPagos->orderBy('fecha_pago', 'asc')->get();

            // Generación de Código QR para validación oficial
            $urlVerificacion = route('admin.clientes.estado-cuenta', ['token' => $token, 'contrato_id' => $contrato->codigo_verificacion]);
            $qrSvg = QrCode::format('svg')->size(75)->errorCorrection('M')->generate($urlVerificacion);
            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

            // Registro en bitácora de auditoría
            $this->registrarAuditoria(
                'Gestión Comercial',
                'Generar PDF Estado de Cuenta Contrato',
                "Se generó el PDF de Estado de Cuenta del Contrato N° {$contrato->numero_contrato} para el cliente {$cliente->nombre_completo}. Monto: Bs " . number_format($montoContratado, 2) . ", Saldo: Bs " . number_format($saldoPendiente, 2)
            );

            $pdf = Pdf::loadView('pdf.estado-cuenta-contrato', [
                'empresa' => $empresa,
                'logoBase64' => $logoBase64,
                'cliente' => $cliente,
                'contrato' => $contrato,
                'montoContratado' => $montoContratado,
                'totalAbonado' => $totalAbonado,
                'saldoPendiente' => $saldoPendiente,
                'cantidadPagosValidos' => $cantidadPagosValidos,
                'ultimoPago' => $ultimoPago,
                'pagos' => $pagos,
                'token' => $token,
                'qrBase64' => $qrBase64,
                'fechaEmision' => $fechaEmision,
                'usuarioGenerador' => $usuarioGenerador,
            ])
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

            $filename = 'estado_cuenta_' . strtolower(str_replace('-', '_', $contrato->numero_contrato)) . '.pdf';
            return $pdf->stream($filename);

        } else {
            // Reporte General
            $contratos = Contrato::with(['evento', 'servicio', 'pagos'])
                ->where('cliente_id', $cliente->id)
                ->orderBy('fecha_contrato', 'desc')
                ->get();

            $totalContratos = $contratos->count();
            $montoContratadoTotal = (float)$contratos->sum('monto_total');
            $totalAbonadoNeto = (float)Pago::where('cliente_id', $cliente->id)->where('estado', 'Valido')->sum('monto');
            $saldoPendienteTotal = max(0, $montoContratadoTotal - $totalAbonadoNeto);
            $cantidadPagosValidosTotal = Pago::where('cliente_id', $cliente->id)->where('estado', 'Valido')->count();
            $ultimoPagoTotal = Pago::where('cliente_id', $cliente->id)->where('estado', 'Valido')->orderBy('fecha_pago', 'desc')->first();

            // Filtrado de movimientos consolidados
            $queryPagos = Pago::with(['contrato', 'user'])->where('cliente_id', $cliente->id);
            if ($contrato_id) $queryPagos->where('contrato_id', $contrato_id);
            if ($fecha_inicio) $queryPagos->whereDate('fecha_pago', '>=', $fecha_inicio);
            if ($fecha_fin) $queryPagos->whereDate('fecha_pago', '<=', $fecha_fin);
            if ($metodo_pago) $queryPagos->where('metodo_pago', $metodo_pago);
            if ($usuario_id) $queryPagos->where('user_id', $usuario_id);
            if ($estado_pago && $estado_pago !== 'Todos') $queryPagos->where('estado', $estado_pago);
            if ($search_recibo) $queryPagos->where('numero_recibo', 'like', "%{$search_recibo}%");

            $pagos = $queryPagos->orderBy('fecha_pago', 'asc')->get();

            // Generación de Código QR para validación oficial
            $urlVerificacion = route('admin.clientes.estado-cuenta', $token);
            $qrSvg = QrCode::format('svg')->size(75)->errorCorrection('M')->generate($urlVerificacion);
            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

            // Registro en bitácora de auditoría
            $this->registrarAuditoria(
                'Gestión Comercial',
                'Generar PDF Estado de Cuenta General',
                "Se generó el PDF de Estado de Cuenta General para el cliente {$cliente->nombre_completo} ({$totalContratos} contratos). Monto Contratado: Bs " . number_format($montoContratadoTotal, 2) . ", Saldo Global: Bs " . number_format($saldoPendienteTotal, 2)
            );

            $pdf = Pdf::loadView('pdf.estado-cuenta-general', [
                'empresa' => $empresa,
                'logoBase64' => $logoBase64,
                'cliente' => $cliente,
                'contratos' => $contratos,
                'totalContratos' => $totalContratos,
                'montoContratadoTotal' => $montoContratadoTotal,
                'totalAbonadoNeto' => $totalAbonadoNeto,
                'saldoPendienteTotal' => $saldoPendienteTotal,
                'cantidadPagosValidosTotal' => $cantidadPagosValidosTotal,
                'ultimoPagoTotal' => $ultimoPagoTotal,
                'pagos' => $pagos,
                'token' => $token,
                'qrBase64' => $qrBase64,
                'fechaEmision' => $fechaEmision,
                'usuarioGenerador' => $usuarioGenerador,
            ])
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

            $filename = 'estado_cuenta_general_cliente_' . $cliente->id . '.pdf';
            return $pdf->stream($filename);
        }
    }
}
