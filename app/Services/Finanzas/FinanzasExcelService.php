<?php

namespace App\Services\Finanzas;

use App\Models\Empresa;
use App\Models\Evento;
use App\Models\Contrato;
use App\Models\MovimientoFinanciero;
use App\Models\MusicoPersonal;
use App\Models\EventoParticipante;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FinanzasExcelService
{
    protected string $colorNavyDark = '0F172A';
    protected string $colorSlateHeader = '1E293B';
    protected string $colorGold = 'D97706';
    protected string $colorEmerald = '059669';
    protected string $colorRose = 'E11D48';
    protected string $colorZebra = 'F8FAFC';

    /**
     * Genera Excel de Movimientos Financieros y Gastos
     */
    public function generarMovimientos(array $filtros): Spreadsheet
    {
        $empresa = Empresa::obtener();
        $user = Auth::user();

        $query = MovimientoFinanciero::with(['evento.cliente', 'user']);
        if (!empty($filtros['desde'])) $query->where('fecha', '>=', $filtros['desde']);
        if (!empty($filtros['hasta'])) $query->where('fecha', '<=', $filtros['hasta']);
        if (!empty($filtros['tipo'])) $query->where('tipo', $filtros['tipo']);
        if (!empty($filtros['clasificacion'])) $query->where('clasificacion', $filtros['clasificacion']);
        if (!empty($filtros['estado']) && $filtros['estado'] !== 'todos') $query->where('estado', $filtros['estado']);

        $movimientos = $query->orderBy('fecha', 'desc')->get();

        $totalEgresos = (float) $movimientos->where('estado', 'Valido')->where('tipo', 'EGRESO')->sum('monto');
        $totalIngresos = (float) $movimientos->where('estado', 'Valido')->where('tipo', 'INGRESO')->sum('monto');
        $totalOperativos = (float) $movimientos->where('estado', 'Valido')->where('tipo', 'EGRESO')->where('clasificacion', 'OPERATIVO')->sum('monto');
        $totalEventos = (float) $movimientos->where('estado', 'Valido')->where('tipo', 'EGRESO')->where('clasificacion', 'EVENTO')->sum('monto');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Movimientos Financieros');
        $sheet->setShowGridLines(true);

        // Encabezado
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', strtoupper($empresa->nombre_comercial ?? 'MARIACHI LEÓN GUANAJUATO'));
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB($this->colorGold);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'REPORTE OFICIAL DE MOVIMIENTOS FINANCIEROS Y GASTOS OPERATIVOS');
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB($this->colorNavyDark);

        $sheet->mergeCells('A3:K3');
        $sheet->setCellValue('A3', 'Generado: ' . now()->format('d/m/Y H:i') . ' | Usuario: ' . ($user->name ?? 'Sistema') . ' | Período: ' . ($filtros['desde'] ?? 'Inicio') . ' al ' . ($filtros['hasta'] ?? 'Fin'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        // Tarjetas de Resumen
        $sheet->setCellValue('B5', 'Total Egresos: Bs. ' . number_format($totalEgresos, 2));
        $sheet->setCellValue('D5', 'Gastos Operativos: Bs. ' . number_format($totalOperativos, 2));
        $sheet->setCellValue('F5', 'Gastos Eventos: Bs. ' . number_format($totalEventos, 2));
        $sheet->setCellValue('H5', 'Ingresos Extra: Bs. ' . number_format($totalIngresos, 2));
        $sheet->getStyle('B5:I5')->getFont()->setBold(true)->setSize(9);

        // Encabezados de Tabla
        $row = 7;
        $headers = ['ID', 'Fecha', 'Tipo', 'Clasificación', 'Categoría', 'Concepto', 'Evento', 'Proveedor', 'N° Comprobante', 'Método', 'Monto (Bs.)', 'Estado'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . $row, $h);
        }

        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorSlateHeader);
        $sheet->getStyle("A{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Datos
        $row++;
        foreach ($movimientos as $m) {
            $sheet->setCellValue("A{$row}", $m->id);
            $sheet->setCellValue("B{$row}", Carbon::parse($m->fecha)->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", $m->tipo);
            $sheet->setCellValue("D{$row}", $m->clasificacion);
            $sheet->setCellValue("E{$row}", $m->categoria);
            $sheet->setCellValue("F{$row}", $m->concepto);
            $sheet->setCellValue("G{$row}", $m->evento ? $m->evento->codigo_evento : 'General');
            $sheet->setCellValue("H{$row}", $m->proveedor ?: '—');
            $sheet->setCellValue("I{$row}", $m->numero_comprobante ?: '—');
            $sheet->setCellValue("J{$row}", $m->metodo_pago);
            $sheet->setCellValue("K{$row}", (float)$m->monto);
            $sheet->setCellValue("L{$row}", $m->estado);

            // Formato de Monto
            $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorZebra);
            }
            $row++;
        }

        // Autoajustar ancho de columnas
        foreach ($cols as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Genera Excel de Eventos y Contratos
     */
    public function generarEventosContratos(array $filtros): Spreadsheet
    {
        $empresa = Empresa::obtener();
        $user = Auth::user();

        $query = Evento::with(['cliente', 'servicio', 'contrato.pagos']);
        if (!empty($filtros['cliente_id'])) $query->where('cliente_id', $filtros['cliente_id']);
        if (!empty($filtros['servicio_id'])) $query->where('servicio_id', $filtros['servicio_id']);
        if (!empty($filtros['estado_evento'])) $query->where('estado', $filtros['estado_evento']);
        if (!empty($filtros['search_lugar'])) $query->where('direccion_evento', 'like', '%' . $filtros['search_lugar'] . '%');

        if (!empty($filtros['estado_contrato'])) {
            if ($filtros['estado_contrato'] === 'sin_contrato') {
                $query->doesntHave('contrato');
            } else {
                $query->whereHas('contrato', fn($ct) => $ct->where('estado', $filtros['estado_contrato']));
            }
        }

        $campoFecha = $filtros['campo_fecha'] ?? 'fecha_evento';
        if ($campoFecha === 'fecha_evento') {
            if (!empty($filtros['desde'])) $query->whereDate('fecha_evento', '>=', $filtros['desde']);
            if (!empty($filtros['hasta'])) $query->whereDate('fecha_evento', '<=', $filtros['hasta']);
        } else {
            if (!empty($filtros['desde'])) $query->whereHas('contrato', fn($ct) => $ct->whereDate('fecha_contrato', '>=', $filtros['desde']));
            if (!empty($filtros['hasta'])) $query->whereHas('contrato', fn($ct) => $ct->whereDate('fecha_contrato', '<=', $filtros['hasta']));
        }

        $eventos = $query->orderBy('fecha_evento', 'desc')->get();

        $totalContratado = (float)$eventos->sum('monto_contratado');
        $totalCobrado = (float)$eventos->sum('total_abonado');
        $totalSaldo = (float)$eventos->sum('saldo_pendiente');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Eventos y Contratos');
        $sheet->setShowGridLines(true);

        // Header Institucional
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', strtoupper($empresa->nombre_comercial ?? 'MARIACHI LEÓN GUANAJUATO'));
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB($this->colorGold);

        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'REPORTE GENERAL DE EVENTOS Y CONTRATOS COMERCIALES');
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB($this->colorNavyDark);

        $sheet->mergeCells('A3:K3');
        $sheet->setCellValue('A3', 'Generado: ' . now()->format('d/m/Y H:i') . ' | Registros: ' . $eventos->count() . ' | Total Contratado: Bs. ' . number_format($totalContratado, 2) . ' | Cobrado: Bs. ' . number_format($totalCobrado, 2) . ' | Saldo: Bs. ' . number_format($totalSaldo, 2));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        // Encabezados
        $row = 5;
        $headers = ['N° Contrato', 'Cód. Evento', 'Fecha Evento', 'Hora', 'Cliente', 'Servicio', 'Ubicación', 'Est. Contrato', 'Est. Evento', 'Contratado (Bs.)', 'Cobrado (Bs.)', 'Saldo (Bs.)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . $row, $h);
        }
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorSlateHeader);
        $sheet->getStyle("A{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        foreach ($eventos as $ev) {
            $sheet->setCellValue("A{$row}", $ev->contrato ? $ev->contrato->numero_contrato : 'Sin Contrato');
            $sheet->setCellValue("B{$row}", $ev->codigo_evento);
            $sheet->setCellValue("C{$row}", Carbon::parse($ev->fecha_evento)->format('d/m/Y'));
            $sheet->setCellValue("D{$row}", substr($ev->hora_evento, 0, 5));
            $sheet->setCellValue("E{$row}", $ev->cliente->nombre_completo ?? 'S/N');
            $sheet->setCellValue("F{$row}", $ev->servicio->nombre ?? 'Estándar');
            $sheet->setCellValue("G{$row}", $ev->direccion_evento ?: 'No especificada');
            $sheet->setCellValue("H{$row}", $ev->contrato->estado ?? '—');
            $sheet->setCellValue("I{$row}", $ev->estado);
            $sheet->setCellValue("J{$row}", (float)$ev->monto_contratado);
            $sheet->setCellValue("K{$row}", (float)$ev->total_abonado);
            $sheet->setCellValue("L{$row}", (float)$ev->saldo_pendiente);

            $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorZebra);
            }
            $row++;
        }

        // Fila de Totales
        $sheet->setCellValue("I{$row}", 'TOTALES:');
        $sheet->setCellValue("J{$row}", $totalContratado);
        $sheet->setCellValue("K{$row}", $totalCobrado);
        $sheet->setCellValue("L{$row}", $totalSaldo);
        $sheet->getStyle("I{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

        foreach ($cols as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Genera Excel de Distribución Económica a Músicos
     */
    public function generarDistribucion(array $filtros): Spreadsheet
    {
        $empresa = Empresa::obtener();
        $modalidad = $filtros['modalidad'] ?? 'consolidado';
        $fechaDesde = $filtros['desde'] ?? null;
        $fechaHasta = $filtros['hasta'] ?? null;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Distribución Económica');
        $sheet->setShowGridLines(true);

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', strtoupper($empresa->nombre_comercial ?? 'MARIACHI LEÓN GUANAJUATO'));
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB($this->colorGold);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'DISTRIBUCIÓN ECONÓMICA Y COMISIONES DE INTEGRANTES');
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB($this->colorNavyDark);

        $row = 4;

        if ($modalidad === 'por_evento' && !empty($filtros['evento_id'])) {
            $evento = Evento::with(['cliente', 'servicio', 'participantes.persona', 'participantes.pagos'])->find($filtros['evento_id']);
            $sheet->setCellValue("A{$row}", "Evento: {$evento->codigo_evento} | Fecha: " . Carbon::parse($evento->fecha_evento)->format('d/m/Y') . " | Cliente: " . ($evento->cliente->nombre_completo ?? 'S/N'));
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row += 2;

            $headers = ['Integrante', 'Función / Instrumento', 'Concepto', 'Monto Asignado (Bs.)', 'Monto Pagado (Bs.)', 'Saldo Pendiente (Bs.)', 'Estado'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue($cols[$i] . $row, $h);
            }
            $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorSlateHeader);

            $row++;
            foreach ($evento->participantes as $p) {
                $sheet->setCellValue("A{$row}", $p->persona->nombre_completo ?? 'No asignado');
                $sheet->setCellValue("B{$row}", $p->funcion);
                $sheet->setCellValue("C{$row}", $p->concepto_pago);
                $sheet->setCellValue("D{$row}", (float)$p->monto_asignado);
                $sheet->setCellValue("E{$row}", (float)$p->total_pagado);
                $sheet->setCellValue("F{$row}", (float)$p->saldo_pendiente);
                $sheet->setCellValue("G{$row}", $p->estado_calculado);

                $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $row++;
            }
        } elseif ($modalidad === 'por_participante' && !empty($filtros['persona_id'])) {
            $persona = MusicoPersonal::find($filtros['persona_id']);
            $sheet->setCellValue("A{$row}", "Integrante: {$persona->nombre_completo} | Tipo: {$persona->tipo} | CI: {$persona->ci_nit}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row += 2;

            $query = EventoParticipante::with(['evento.cliente', 'pagos'])->where('persona_id', $persona->id);
            if ($fechaDesde) $query->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '>=', $fechaDesde));
            if ($fechaHasta) $query->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '<=', $fechaHasta));
            $parts = $query->get();

            $headers = ['Fecha Evento', 'Cód. Evento', 'Cliente', 'Función', 'Monto Asignado (Bs.)', 'Monto Pagado (Bs.)', 'Saldo Pendiente (Bs.)'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue($cols[$i] . $row, $h);
            }
            $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorSlateHeader);

            $row++;
            foreach ($parts as $p) {
                $sheet->setCellValue("A{$row}", $p->evento ? Carbon::parse($p->evento->fecha_evento)->format('d/m/Y') : '—');
                $sheet->setCellValue("B{$row}", $p->evento->codigo_evento ?? 'S/E');
                $sheet->setCellValue("C{$row}", $p->evento->cliente->nombre_completo ?? 'S/N');
                $sheet->setCellValue("D{$row}", $p->funcion);
                $sheet->setCellValue("E{$row}", (float)$p->monto_asignado);
                $sheet->setCellValue("F{$row}", (float)$p->total_pagado);
                $sheet->setCellValue("G{$row}", (float)$p->saldo_pendiente);

                $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $row++;
            }
        } else {
            // Consolidado
            $musicos = MusicoPersonal::with(['participaciones' => function ($q) use ($fechaDesde, $fechaHasta) {
                if ($fechaDesde) $q->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '>=', $fechaDesde));
                if ($fechaHasta) $q->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '<=', $fechaHasta));
                $q->with('pagos');
            }])->orderBy('nombre_completo')->get();

            $headers = ['Integrante', 'Tipo', 'Teléfono', 'Eventos', 'Total Asignado (Bs.)', 'Total Pagado (Bs.)', 'Saldo Pendiente (Bs.)', 'Estado'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue($cols[$i] . $row, $h);
            }
            $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorSlateHeader);

            $row++;
            foreach ($musicos as $m) {
                $asig = (float)$m->participaciones->sum('monto_asignado');
                $pag = (float)$m->participaciones->sum('total_pagado');
                $saldo = max(0.0, $asig - $pag);

                $sheet->setCellValue("A{$row}", $m->nombre_completo);
                $sheet->setCellValue("B{$row}", $m->tipo);
                $sheet->setCellValue("C{$row}", $m->telefono ?: '—');
                $sheet->setCellValue("D{$row}", $m->participaciones->count());
                $sheet->setCellValue("E{$row}", $asig);
                $sheet->setCellValue("F{$row}", $pag);
                $sheet->setCellValue("G{$row}", $saldo);
                $sheet->setCellValue("H{$row}", $saldo <= 0.01 ? 'Al Día' : 'Pendiente');

                $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $row++;
            }
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Genera Excel de Reporte Financiero Ejecutivo
     */
    public function generarFinanciero(array $filtros): Spreadsheet
    {
        $empresa = Empresa::obtener();
        $kpis = FinanzasService::calcularKPIs($filtros);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Informe Financiero');
        $sheet->setShowGridLines(true);

        // Header
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', strtoupper($empresa->nombre_comercial ?? 'MARIACHI LEÓN GUANAJUATO'));
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB($this->colorGold);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'ESTADO FINANCIERO Y BALANCES OPERATIVOS CONSOLIDADOS');
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB($this->colorNavyDark);

        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A3', 'Período: ' . ($filtros['fecha_inicio'] ?? 'Histórico') . ' al ' . ($filtros['fecha_fin'] ?? 'Actual') . ' | Moneda: Bolivianos (Bs.)');
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        // Tarjetas
        $sheet->setCellValue('B5', 'INGRESOS CONSOLIDADOS');
        $sheet->setCellValue('B6', (float)$kpis['total_ingresos_consolidados']);
        $sheet->getStyle('B6')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('B5:B6')->getFont()->setBold(true);

        $sheet->setCellValue('D5', 'COSTOS & GASTOS TOTALES');
        $sheet->setCellValue('D6', (float)$kpis['costos_totales_reconocidos']);
        $sheet->getStyle('D6')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('D5:D6')->getFont()->setBold(true);

        $sheet->setCellValue('F5', 'RESULTADO OPERATIVO ESTIMADO');
        $sheet->setCellValue('F6', (float)$kpis['resultado_operativo_estimado']);
        $sheet->getStyle('F6')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('F5:F6')->getFont()->setBold(true);

        // Tabla de Desglose
        $row = 8;
        $sheet->setCellValue("A{$row}", "RUBRO FINANCIERO");
        $sheet->setCellValue("C{$row}", "CLASIFICACIÓN");
        $sheet->setCellValue("E{$row}", "MONTO VIGENTE (Bs.)");
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorSlateHeader);

        $rubros = [
            ['Total Contratado con Clientes', 'Comercial', $kpis['total_contratado']],
            ['Total Cobrado a Clientes', 'Ingreso Efectivo', $kpis['total_cobrado']],
            ['Saldo Pendiente de Clientes', 'Por Cobrar', $kpis['saldo_pendiente_clientes']],
            ['Ingresos Extraordinarios', 'Ingreso Operativo', $kpis['total_ingresos_adicionales']],
            ['TOTAL INGRESOS CONSOLIDADOS', 'Ingresos Totales', $kpis['total_ingresos_consolidados']],
            ['Total Asignado a Participantes (Músicos)', 'Costo Directo', $kpis['total_asignado_participantes']],
            ['Total Pagado a Participantes', 'Desembolsado', $kpis['total_pagado_participantes']],
            ['Saldo Pendiente a Participantes', 'Por Desembolsar', $kpis['saldo_pendiente_participantes']],
            ['Gastos Directos de Eventos', 'Gasto Directo', $kpis['total_gastos_eventos']],
            ['Gastos Operativos Institucionales', 'Gasto Operativo', $kpis['total_gastos_operativos']],
            ['TOTAL COSTOS & GASTOS RECONOCIDOS', 'Egresos Totales', $kpis['costos_totales_reconocidos']],
            ['BALANCE BRUTO DE EVENTOS', 'Cobrado - Asignado', $kpis['balance_bruto_eventos']],
            ['RESULTADO OPERATIVO ESTIMADO', 'Margen Neto', $kpis['resultado_operativo_estimado']],
            ['SALDO ACTUAL FONDO COMÚN', 'Caja de Reserva', $kpis['saldo_fondo_comun']],
        ];

        $row++;
        foreach ($rubros as $r) {
            $sheet->setCellValue("A{$row}", $r[0]);
            $sheet->setCellValue("C{$row}", $r[1]);
            $sheet->setCellValue("E{$row}", (float)$r[2]);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            if (in_array($r[0], ['TOTAL INGRESOS CONSOLIDADOS', 'TOTAL COSTOS & GASTOS RECONOCIDOS', 'RESULTADO OPERATIVO ESTIMADO'])) {
                $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
            }
            $row++;
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
