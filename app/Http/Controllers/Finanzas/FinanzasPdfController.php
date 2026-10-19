<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\Evento;
use App\Models\Servicio;
use App\Models\MovimientoFinanciero;
use App\Models\FondoComunMovimiento;
use App\Services\Finanzas\FinanzasService;
use App\Traits\Auditable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class FinanzasPdfController extends Controller
{
    use Auditable;

    public function exportarReporteFinanciero(Request $request)
    {
        $fechaInicio = $request->get('inicio') ?: null;
        $fechaFin = $request->get('fin') ?: null;
        $clienteId = $request->get('cliente_id') ?: null;
        $eventoId = $request->get('evento_id') ?: null;
        $servicioId = $request->get('servicio_id') ?: null;
        $tipoBalance = $request->get('tipo', 'consolidado');

        $filtros = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cliente_id' => $clienteId,
            'evento_id' => $eventoId,
            'servicio_id' => $servicioId,
        ];

        $kpis = FinanzasService::calcularKPIs($filtros);

        // Balances por evento
        $qEventos = Evento::with(['cliente', 'servicio', 'contrato.pagos', 'participantes.pagos', 'gastosValidos']);
        if ($fechaInicio) $qEventos->whereDate('fecha_evento', '>=', $fechaInicio);
        if ($fechaFin) $qEventos->whereDate('fecha_evento', '<=', $fechaFin);
        if ($clienteId) $qEventos->where('cliente_id', $clienteId);
        if ($eventoId) $qEventos->where('id', $eventoId);
        if ($servicioId) $qEventos->where('servicio_id', $servicioId);
        $eventos = $qEventos->orderBy('fecha_evento', 'desc')->get();

        $balancesEventos = collect();
        foreach ($eventos as $ev) {
            $montoContratado = $ev->contrato ? (float)$ev->contrato->monto_total : 0.0;
            $totalCobrado = $ev->contrato ? (float)$ev->contrato->pagos->where('estado', 'Valido')->sum('monto') : 0.0;
            $totalAsignado = (float)$ev->participantes->sum('monto_asignado');
            $gastosEvento = (float)$ev->gastosValidos->sum('monto');
            $balanceNeto = $totalCobrado - ($totalAsignado + $gastosEvento);

            $balancesEventos->push([
                'codigo_evento' => $ev->codigo_evento,
                'fecha_evento' => $ev->fecha_evento,
                'cliente' => $ev->cliente->nombre_completo ?? 'Cliente S/N',
                'monto_contratado' => $montoContratado,
                'total_cobrado' => $totalCobrado,
                'total_asignado' => $totalAsignado,
                'gastos_evento' => $gastosEvento,
                'balance_neto' => $balanceNeto,
            ]);
        }

        // Gastos operativos por categoría
        $qGastos = MovimientoFinanciero::valido()->operativo()->egresos();
        if ($fechaInicio) $qGastos->where('fecha', '>=', $fechaInicio);
        if ($fechaFin) $qGastos->where('fecha', '<=', $fechaFin);

        $gastosPorCategoria = (clone $qGastos)
            ->selectRaw('categoria, count(*) as cantidad, SUM(monto) as total')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioGenerador = Auth::check() ? Auth::user()->name : 'Sistema';

        $clienteObj = $clienteId ? Cliente::find($clienteId) : null;
        $eventoObj = $eventoId ? Evento::find($eventoId) : null;

        // Bitácora
        $this->registrarAuditoria(
            'Informes y Reportes',
            'Generar PDF Reporte Financiero',
            "Se exportó el reporte financiero en PDF para el período " . ($fechaInicio ?: 'inicio') . " al " . ($fechaFin ?: 'fin') . ". Ingresos: Bs. " . number_format($kpis['total_ingresos_consolidados'], 2) . ", Resultado: Bs. " . number_format($kpis['resultado_operativo_estimado'], 2) . "."
        );

        $pdf = Pdf::loadView('pdf.reporte-financiero-completo', [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'kpis' => $kpis,
            'balancesEventos' => $balancesEventos,
            'gastosPorCategoria' => $gastosPorCategoria,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'cliente' => $clienteObj,
            'evento' => $eventoObj,
            'fechaEmision' => $fechaEmision,
            'usuarioGenerador' => $usuarioGenerador,
        ])
        ->setPaper('letter', 'portrait')
        ->setOption('isRemoteEnabled', false)
        ->setOption('isPhpEnabled', true)
        ->setOption('isFontSubsettingEnabled', true)
        ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('informe_financiero_' . date('Ymd_His') . '.pdf');
    }

    public function exportarGastos(Request $request)
    {
        $fechaDesde = $request->get('desde') ?: null;
        $fechaHasta = $request->get('hasta') ?: null;
        $tipo = $request->get('tipo') ?: null;
        $clasificacion = $request->get('clasificacion') ?: null;
        $estado = $request->get('estado', 'Valido');

        $query = MovimientoFinanciero::with(['evento.cliente', 'user']);

        if ($fechaDesde) $query->where('fecha', '>=', $fechaDesde);
        if ($fechaHasta) $query->where('fecha', '<=', $fechaHasta);
        if ($tipo) $query->where('tipo', $tipo);
        if ($clasificacion) $query->where('clasificacion', $clasificacion);
        if ($estado && $estado !== 'todos') $query->where('estado', $estado);

        $movimientos = $query->orderBy('fecha', 'desc')->get();

        $totales = [
            'egresos' => (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'EGRESO')->sum('monto'),
            'ingresos' => (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'INGRESO')->sum('monto'),
            'operativos' => (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'EGRESO')->where('clasificacion', 'OPERATIVO')->sum('monto'),
            'eventos' => (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'EGRESO')->where('clasificacion', 'EVENTO')->sum('monto'),
        ];

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioGenerador = Auth::check() ? Auth::user()->name : 'Sistema';

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Generar PDF Movimientos y Gastos',
            "Se exportó el reporte de gastos y movimientos en PDF ({$movimientos->count()} registros). Total egresos: Bs. " . number_format($totales['egresos'], 2) . "."
        );

        $pdf = Pdf::loadView('pdf.reporte-gastos-movimientos', [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'movimientos' => $movimientos,
            'totales' => $totales,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'tipoFiltro' => $tipo,
            'clasificacionFiltro' => $clasificacion,
            'estadoFiltro' => $estado,
            'fechaEmision' => $fechaEmision,
            'usuarioGenerador' => $usuarioGenerador,
        ])
        ->setPaper('letter', 'portrait')
        ->setOption('isRemoteEnabled', false)
        ->setOption('isPhpEnabled', true)
        ->setOption('isFontSubsettingEnabled', true)
        ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('reporte_gastos_' . date('Ymd_His') . '.pdf');
    }

    public function exportarFondoComun(Request $request)
    {
        $fechaDesde = $request->get('desde') ?: null;
        $fechaHasta = $request->get('hasta') ?: null;
        $tipo = $request->get('tipo') ?: null;

        $query = FondoComunMovimiento::with('user');
        if ($fechaDesde) $query->where('fecha', '>=', $fechaDesde);
        if ($fechaHasta) $query->where('fecha', '<=', $fechaHasta);
        if ($tipo) $query->where('tipo', $tipo);

        $movimientos = $query->orderBy('fecha', 'desc')->get();

        $saldoActual = FondoComunMovimiento::obtenerSaldoActual();
        $totalAportes = (float) FondoComunMovimiento::where('estado', 'Valido')->where('tipo', 'INGRESO')->sum('monto');
        $totalRetiros = (float) FondoComunMovimiento::where('estado', 'Valido')->where('tipo', 'EGRESO')->sum('monto');
        $aportesPeriodo = (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'INGRESO')->sum('monto');
        $retirosPeriodo = (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'EGRESO')->sum('monto');

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioGenerador = Auth::check() ? Auth::user()->name : 'Sistema';

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Generar PDF Fondo Común',
            "Se exportó el reporte de fondo común en PDF. Saldo actual: Bs. " . number_format($saldoActual, 2) . "."
        );

        $pdf = Pdf::loadView('pdf.reporte-fondo-comun', [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'movimientos' => $movimientos,
            'saldoActual' => $saldoActual,
            'totalAportes' => $totalAportes,
            'totalRetiros' => $totalRetiros,
            'aportesPeriodo' => $aportesPeriodo,
            'retirosPeriodo' => $retirosPeriodo,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'fechaEmision' => $fechaEmision,
            'usuarioGenerador' => $usuarioGenerador,
        ])
        ->setPaper('letter', 'portrait')
        ->setOption('isRemoteEnabled', false)
        ->setOption('isPhpEnabled', true)
        ->setOption('isFontSubsettingEnabled', true)
        ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('reporte_fondo_comun_' . date('Ymd_His') . '.pdf');
    }
}
