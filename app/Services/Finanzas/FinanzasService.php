<?php

namespace App\Services\Finanzas;

use App\Models\Contrato;
use App\Models\Pago;
use App\Models\Evento;
use App\Models\EventoParticipante;
use App\Models\PagoParticipante;
use App\Models\MovimientoFinanciero;
use App\Models\FondoComunMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanzasService
{
    /**
     * Calcula todos los indicadores principales para el Dashboard Financiero aplicando filtros
     */
    public static function calcularKPIs(array $filtros = []): array
    {
        $fechaInicio = !empty($filtros['fecha_inicio']) ? Carbon::parse($filtros['fecha_inicio'])->startOfDay() : null;
        $fechaFin = !empty($filtros['fecha_fin']) ? Carbon::parse($filtros['fecha_fin'])->endOfDay() : null;
        $clienteId = $filtros['cliente_id'] ?? null;
        $eventoId = $filtros['evento_id'] ?? null;
        $servicioId = $filtros['servicio_id'] ?? null;
        $metodoPago = $filtros['metodo_pago'] ?? null;

        // 1. CONTRATOS (Total Contratado)
        $qContratos = Contrato::query()->where('estado', '!=', 'Cancelado');
        if ($fechaInicio && $fechaFin) {
            $qContratos->whereBetween('fecha_contrato', [$fechaInicio, $fechaFin]);
        }
        if ($clienteId) {
            $qContratos->where('cliente_id', $clienteId);
        }
        if ($eventoId) {
            $qContratos->where('evento_id', $eventoId);
        }
        if ($servicioId) {
            $qContratos->where('servicio_id', $servicioId);
        }
        $totalContratado = (float) $qContratos->sum('monto_total');
        $countContratos = $qContratos->count();

        // 2. PAGOS DE CLIENTES (Total Cobrado y Saldo Pendiente)
        $qPagos = Pago::query()->where('estado', 'Valido');
        if ($fechaInicio && $fechaFin) {
            $qPagos->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        }
        if ($clienteId) {
            $qPagos->where('cliente_id', $clienteId);
        }
        if ($eventoId) {
            $qPagos->where('evento_id', $eventoId);
        }
        if ($servicioId) {
            $qPagos->whereHas('contrato', fn($c) => $c->where('servicio_id', $servicioId));
        }
        if ($metodoPago) {
            $qPagos->where('metodo_pago', $metodoPago);
        }
        $totalCobrado = (float) $qPagos->sum('monto');
        $saldoPendienteClientes = max(0.0, $totalContratado - $totalCobrado);

        // 3. PARTICIPANTES & COMISIONES (Asignado, Pagado y Saldo Pendiente)
        $qPart = EventoParticipante::query();
        if ($fechaInicio && $fechaFin) {
            $qPart->whereHas('evento', fn($e) => $e->whereBetween('fecha_evento', [$fechaInicio, $fechaFin]));
        }
        if ($clienteId) {
            $qPart->whereHas('evento', fn($e) => $e->where('cliente_id', $clienteId));
        }
        if ($eventoId) {
            $qPart->where('evento_id', $eventoId);
        }
        if ($servicioId) {
            $qPart->whereHas('evento', fn($e) => $e->where('servicio_id', $servicioId));
        }
        $totalAsignadoParticipantes = (float) $qPart->sum('monto_asignado');

        // Pagos válidos a participantes
        $qPagosPart = PagoParticipante::query()->where('estado', 'Valido');
        if ($fechaInicio && $fechaFin) {
            $qPagosPart->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        }
        if ($eventoId) {
            $qPagosPart->where('evento_id', $eventoId);
        }
        if ($clienteId) {
            $qPagosPart->whereHas('evento', fn($e) => $e->where('cliente_id', $clienteId));
        }
        if ($servicioId) {
            $qPagosPart->whereHas('evento', fn($e) => $e->where('servicio_id', $servicioId));
        }
        if ($metodoPago) {
            $qPagosPart->where('metodo_pago', $metodoPago);
        }
        $totalPagadoParticipantes = (float) $qPagosPart->sum('monto');
        $saldoPendienteParticipantes = max(0.0, $totalAsignadoParticipantes - $totalPagadoParticipantes);

        // 4. GASTOS OPERATIVOS Y GASTOS POR EVENTO (Movimientos Financieros)
        $qGastosOp = MovimientoFinanciero::query()->valido()->egresos()->operativos();
        if ($fechaInicio && $fechaFin) {
            $qGastosOp->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')]);
        }
        if ($metodoPago) {
            $qGastosOp->where('metodo_pago', $metodoPago);
        }
        $totalGastosOperativos = (float) $qGastosOp->sum('monto');

        $qGastosEv = MovimientoFinanciero::query()->valido()->egresos()->eventos();
        if ($fechaInicio && $fechaFin) {
            $qGastosEv->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')]);
        }
        if ($eventoId) {
            $qGastosEv->where('evento_id', $eventoId);
        }
        if ($clienteId) {
            $qGastosEv->whereHas('evento', fn($e) => $e->where('cliente_id', $clienteId));
        }
        if ($metodoPago) {
            $qGastosEv->where('metodo_pago', $metodoPago);
        }
        $totalGastosEventos = (float) $qGastosEv->sum('monto');

        // Total egresos (operativos + eventos)
        $totalEgresos = $totalGastosOperativos + $totalGastosEventos;

        // 5. INGRESOS ADICIONALES (no contractuales)
        $qIngresosExtra = MovimientoFinanciero::query()->valido()->ingresos();
        if ($fechaInicio && $fechaFin) {
            $qIngresosExtra->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')]);
        }
        if ($metodoPago) {
            $qIngresosExtra->where('metodo_pago', $metodoPago);
        }
        $totalIngresosAdicionales = (float) $qIngresosExtra->sum('monto');

        // Total Ingresos Consolidados
        $totalIngresosConsolidados = $totalCobrado + $totalIngresosAdicionales;

        // 6. BALANCES SEGÚN FÓRMULAS DEL SPRINT:
        // Balance Bruto de Eventos = Total cobrado a clientes - Total asignado a participantes
        $balanceBrutoEventos = $totalCobrado - $totalAsignadoParticipantes;

        // Resultado Operativo Global Estimado = Total ingresos - (Total asignado a participantes + Total gastos eventos + Total gastos operativos)
        $costosTotalesReconocidos = $totalAsignadoParticipantes + $totalGastosEventos + $totalGastosOperativos;
        $resultadoOperativoEstimado = $totalIngresosConsolidados - $costosTotalesReconocidos;

        // 7. FONDO COMÚN
        $saldoFondoComun = FondoComunMovimiento::obtenerSaldoActual();

        return [
            'total_contratado' => $totalContratado,
            'count_contratos' => $countContratos,
            'total_cobrado' => $totalCobrado,
            'saldo_pendiente_clientes' => $saldoPendienteClientes,
            'total_asignado_participantes' => $totalAsignadoParticipantes,
            'total_pagado_participantes' => $totalPagadoParticipantes,
            'saldo_pendiente_participantes' => $saldoPendienteParticipantes,
            'total_gastos_operativos' => $totalGastosOperativos,
            'total_gastos_eventos' => $totalGastosEventos,
            'total_egresos' => $totalEgresos,
            'total_ingresos_adicionales' => $totalIngresosAdicionales,
            'total_ingresos_consolidados' => $totalIngresosConsolidados,
            'balance_bruto_eventos' => $balanceBrutoEventos,
            'costos_totales_reconocidos' => $costosTotalesReconocidos,
            'resultado_operativo_estimado' => $resultadoOperativoEstimado,
            'saldo_fondo_comun' => $saldoFondoComun,
        ];
    }

    /**
     * Obtiene datos para los gráficos del dashboard por mes (últimos 6 meses o período)
     */
    public static function obtenerDatosGraficos(array $filtros = []): array
    {
        $meses = [];
        $ingresosPorMes = [];
        $egresosPorMes = [];
        $balancePorMes = [];

        // Generar los últimos 6 meses cronológicos
        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $yearMonth = $dt->format('Y-m');
            $label = $dt->locale('es')->translatedFormat('M Y');
            $meses[$yearMonth] = [
                'label' => ucfirst($label),
                'ingresos' => 0.0,
                'egresos' => 0.0,
            ];
        }

        // Ingresos de clientes por mes
        $pagosMes = Pago::where('estado', 'Valido')
            ->where('fecha_pago', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(fecha_pago, '%Y-%m') as mes, SUM(monto) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        // Ingresos adicionales por mes
        $ingresosExtraMes = MovimientoFinanciero::valido()->ingresos()
            ->where('fecha', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, SUM(monto) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        // Egresos por mes (Gastos operativos + Gastos de eventos)
        $gastosMes = MovimientoFinanciero::valido()->egresos()
            ->where('fecha', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, SUM(monto) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        // Pagos a participantes por mes
        $pagosPartMes = PagoParticipante::where('estado', 'Valido')
            ->where('fecha_pago', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(fecha_pago, '%Y-%m') as mes, SUM(monto) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        foreach ($meses as $ym => $data) {
            $ingreso = ($pagosMes[$ym] ?? 0) + ($ingresosExtraMes[$ym] ?? 0);
            $egreso = ($gastosMes[$ym] ?? 0) + ($pagosPartMes[$ym] ?? 0);
            $meses[$ym]['ingresos'] = (float)$ingreso;
            $meses[$ym]['egresos'] = (float)$egreso;
            $meses[$ym]['balance'] = (float)($ingreso - $egreso);
        }

        // Distribución de Ingresos por Servicio
        $ingresosPorServicio = DB::table('contratos')
            ->join('servicios', 'contratos.servicio_id', '=', 'servicios.id')
            ->join('pagos', 'contratos.id', '=', 'pagos.contrato_id')
            ->where('pagos.estado', 'Valido')
            ->select('servicios.nombre as servicio', DB::raw('SUM(pagos.monto) as total'))
            ->groupBy('servicios.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Distribución de Gastos por Categoría
        $gastosPorCategoria = MovimientoFinanciero::valido()->egresos()
            ->select('categoria', DB::raw('SUM(monto) as total'))
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'meses' => array_values($meses),
            'ingresos_por_servicio' => $ingresosPorServicio,
            'gastos_por_categoria' => $gastosPorCategoria,
        ];
    }

    /**
     * Genera el desglose financiero de un Evento para el Reporte de Balance por Evento
     */
    public static function obtenerBalanceEvento(int $eventoId): ?array
    {
        $evento = Evento::with(['cliente', 'servicio', 'contrato.pagos', 'participantes.pagos', 'gastosValidos'])->find($eventoId);
        if (!$evento) return null;

        $montoContratado = $evento->contrato ? (float)$evento->contrato->monto_total : 0.0;
        $totalCobrado = $evento->contrato ? (float)$evento->contrato->pagos->where('estado', 'Valido')->sum('monto') : 0.0;
        $saldoPendienteCliente = max(0.0, $montoContratado - $totalCobrado);

        $totalAsignado = (float)$evento->participantes->sum('monto_asignado');
        $totalPagadoPart = 0.0;
        foreach ($evento->participantes as $p) {
            $totalPagadoPart += (float)$p->pagos->where('estado', 'Valido')->sum('monto');
        }
        $saldoPendientePart = max(0.0, $totalAsignado - $totalPagadoPart);

        $gastosEvento = (float)$evento->gastosValidos->sum('monto');

        // Fórmulas requeridas por el Sprint
        $balanceBruto = $totalCobrado - $totalAsignado;
        $resultadoOperativoEstimado = $totalCobrado - ($totalAsignado + $gastosEvento);

        return [
            'evento' => $evento,
            'monto_contratado' => $montoContratado,
            'total_cobrado' => $totalCobrado,
            'saldo_pendiente_cliente' => $saldoPendienteCliente,
            'total_asignado_participantes' => $totalAsignado,
            'total_pagado_participantes' => $totalPagadoPart,
            'saldo_pendiente_participantes' => $saldoPendientePart,
            'gastos_evento' => $gastosEvento,
            'balance_bruto' => $balanceBruto,
            'resultado_operativo_estimado' => $resultadoOperativoEstimado,
        ];
    }
}
