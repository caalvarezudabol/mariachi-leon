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

    /**
     * Exportar estado de cuenta directamente desde el módulo interactivo de reportes
     */
    public function exportarDesdeReporte(Request $request)
    {
        $clienteId = $request->get('cliente_id');
        $contratoId = $request->get('contrato_id');
        $modalidad = $request->get('modalidad', 'contrato');

        if (!$clienteId) {
            $primerCliente = Cliente::has('contratos')->first() ?? Cliente::first();
            $clienteId = $primerCliente ? $primerCliente->id : null;
        }

        if (!$clienteId) {
            abort(404, 'No hay clientes registrados en el sistema.');
        }

        $tokenCliente = DocumentToken::encodeId($clienteId, 'cliente');
        $tokenContrato = $contratoId ? DocumentToken::encodeId($contratoId, 'contrato') : null;

        $request->merge([
            'tipo' => $modalidad === 'cliente' ? 'general' : 'contrato',
            'contrato' => $tokenContrato ?: $contratoId,
        ]);

        return $this->exportarPdf($request, $tokenCliente);
    }

    /**
     * Exportar estado de cuenta en PDF (por contrato o consolidado del cliente)
     */
    public function exportarPdf(Request $request, $token)
    {
        // 1. Resolver el cliente por token seguro o por ID si está autenticado
        $clienteId = DocumentToken::decodeId($token, 'cliente');
        if (!$clienteId) {
            if (Auth::check() && is_numeric($token)) {
                $clienteId = (int)$token;
            } else {
                $contratoPorCodigo = Contrato::where('codigo_verificacion', $token)->first();
                if ($contratoPorCodigo) {
                    $clienteId = $contratoPorCodigo->cliente_id;
                }
            }
        }

        if (!$clienteId) {
            abort(404, 'El estado de cuenta solicitado no es válido o no existe.');
        }

        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            abort(404, 'Cliente no encontrado.');
        }

        // 2. Resolver parámetro contrato si fue enviado
        $contratoParam = $request->get('contrato') ?? $request->get('contrato_id');
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
                } elseif (is_numeric($contratoParam)) {
                    $contratoObj = Contrato::where('cliente_id', $cliente->id)
                        ->where('id', (int)$contratoParam)
                        ->first();
                    if ($contratoObj) {
                        $contrato_id = $contratoObj->id;
                    }
                }
            }
        }

        $tipo = $request->get('tipo', 'contrato');
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

        // 3. Modalidad por Contrato
        if ($tipo === 'contrato') {
            $contrato = null;
            if ($contrato_id) {
                $contrato = Contrato::with(['evento', 'servicio', 'cliente', 'pagos.user'])
                    ->where('cliente_id', $cliente->id)
                    ->where('id', $contrato_id)
                    ->first();
            }

            // Fallback: Si no se especificó contrato o no se encontró, tomar el último contrato del cliente
            if (!$contrato) {
                $contrato = Contrato::with(['evento', 'servicio', 'cliente', 'pagos.user'])
                    ->where('cliente_id', $cliente->id)
                    ->orderBy('fecha_contrato', 'desc')
                    ->first();
            }

            // Si el cliente no tiene contratos registrados, conmutar a reporte general
            if (!$contrato) {
                $tipo = 'general';
            } else {
                $montoContratado = (float)$contrato->monto_total;
                $pagosValidosTotal = (float)$contrato->pagos->where('estado', 'Valido')->sum('monto');
                $saldoPendiente = max(0.0, $montoContratado - $pagosValidosTotal);
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

                // Calcular saldo acumulado (running balance) idéntico a la tabla en pantalla
                $saldoAcumulado = $montoContratado;
                foreach ($pagos as $p) {
                    if ($p->estado === 'Valido') {
                        $saldoAcumulado = max(0.0, $saldoAcumulado - (float)$p->monto);
                    }
                    $p->saldo_posterior = $saldoAcumulado;
                }

                // Generación de Código QR para validación oficial
                $tokenCliente = DocumentToken::encodeId($cliente->id, 'cliente');
                $urlVerificacion = route('admin.clientes.estado-cuenta', [
                    'token' => $tokenCliente,
                    'contrato_id' => $contrato->codigo_verificacion ?? $contrato->id,
                ]);
                $qrSvg = QrCode::format('svg')->size(70)->errorCorrection('M')->generate($urlVerificacion);
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
                    'totalAbonado' => $pagosValidosTotal,
                    'saldoPendiente' => $saldoPendiente,
                    'cantidadPagosValidos' => $cantidadPagosValidos,
                    'ultimoPago' => $ultimoPago,
                    'pagos' => $pagos,
                    'token' => $tokenCliente,
                    'qrBase64' => $qrBase64,
                    'fechaEmision' => $fechaEmision,
                    'usuarioGenerador' => $usuarioGenerador,
                ])
                ->setPaper('letter', 'portrait')
                ->setOption('isRemoteEnabled', false)
                ->setOption('isPhpEnabled', true)
                ->setOption('isFontSubsettingEnabled', true)
                ->setOption('defaultFont', 'DejaVu Serif');

                $filename = 'estado_cuenta_' . strtolower(str_replace(['-', ' '], '_', $contrato->numero_contrato)) . '.pdf';

                return $request->has('download') ? $pdf->download($filename) : $pdf->stream($filename);
            }
        }

        // 4. Modalidad Consolidada / General del Cliente
        $contratos = Contrato::with(['evento', 'servicio', 'pagos'])
            ->where('cliente_id', $cliente->id)
            ->orderBy('fecha_contrato', 'desc')
            ->get();

        $totalContratos = $contratos->count();
        $montoContratadoTotal = (float)$contratos->sum('monto_total');
        $totalAbonadoNeto = (float)Pago::where('cliente_id', $cliente->id)->where('estado', 'Valido')->sum('monto');
        $saldoPendienteTotal = max(0.0, $montoContratadoTotal - $totalAbonadoNeto);
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

        // Calcular running balance para el consolidado
        $saldoAcumulado = $montoContratadoTotal;
        foreach ($pagos as $p) {
            if ($p->estado === 'Valido') {
                $saldoAcumulado = max(0.0, $saldoAcumulado - (float)$p->monto);
            }
            $p->saldo_posterior = $saldoAcumulado;
        }

        // Generación de Código QR para validación oficial
        $tokenCliente = DocumentToken::encodeId($cliente->id, 'cliente');
        $urlVerificacion = route('admin.clientes.estado-cuenta', $tokenCliente);
        $qrSvg = QrCode::format('svg')->size(70)->errorCorrection('M')->generate($urlVerificacion);
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
            'token' => $tokenCliente,
            'qrBase64' => $qrBase64,
            'fechaEmision' => $fechaEmision,
            'usuarioGenerador' => $usuarioGenerador,
        ])
        ->setPaper('letter', 'portrait')
        ->setOption('isRemoteEnabled', false)
        ->setOption('isPhpEnabled', true)
        ->setOption('isFontSubsettingEnabled', true)
        ->setOption('defaultFont', 'DejaVu Serif');

        $filename = 'estado_cuenta_general_' . strtolower(str_replace([' ', '.'], '_', $cliente->nombre_completo)) . '.pdf';

        return $request->has('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
