<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero & Balances - Mariachi León Guanajuato</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.2cm 1.2cm 1.4cm 1.2cm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 7.5pt;
            color: #0f172a;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .page-footer {
            position: fixed;
            bottom: -1.0cm;
            left: 0;
            right: 0;
            height: 0.8cm;
            font-size: 6.5pt;
            color: #64748b;
            border-top: 1.5px solid #d97706;
            padding-top: 4px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .logo-col {
            width: 70px;
            vertical-align: middle;
            text-align: left;
        }

        .logo-col img {
            max-width: 65px;
            max-height: 55px;
        }

        .company-col {
            text-align: center;
            vertical-align: middle;
            padding: 0 10px;
        }

        .company-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .company-sub {
            font-size: 7pt;
            color: #475569;
            margin-top: 1px;
        }

        .badge-col {
            width: 160px;
            text-align: right;
            vertical-align: middle;
        }

        .doc-badge {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .period-info {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3px solid #d97706;
            padding: 4px 8px;
            margin-bottom: 8px;
            font-size: 7pt;
        }

        /* Tarjetas de Resumen KPI */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .kpi-box {
            width: 25%;
            padding: 6px 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #0f172a;
            vertical-align: top;
        }

        .kpi-box.emerald { border-top-color: #10b981; }
        .kpi-box.rose { border-top-color: #ef4444; }
        .kpi-box.gold { border-top-color: #d97706; }
        .kpi-box.blue { border-top-color: #3b82f6; }

        .kpi-label {
            font-size: 6pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }

        .kpi-value {
            font-size: 11pt;
            font-weight: bold;
            font-family: monospace;
            color: #0f172a;
            margin-top: 2px;
        }

        .section-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        /* Tablas */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 6.8pt;
        }

        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 3.5px 5px;
            border: 1px solid #0f172a;
        }

        .data-table td {
            padding: 3px 5px;
            border: 1px solid #e2e8f0;
        }

        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        .text-emerald { color: #047857; }
        .text-rose { color: #b91c1c; }
    </style>
</head>
<body>
    <!-- Encabezado Institucional -->
    <table class="header-table">
        <tr>
            <td class="logo-col">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </td>
            <td class="company-col">
                <div class="company-title">{{ $empresa->nombre_comercial ?? 'Mariachi León Guanajuato' }}</div>
                <div class="company-sub">
                    NIT/RUC: {{ $empresa->nit ?? 'S/N' }} &bull; {{ $empresa->direccion ?? 'Cochabamba, Bolivia' }} &bull; Tel: {{ $empresa->telefono ?? '63854129' }}
                </div>
            </td>
            <td class="badge-col">
                <div class="doc-badge">INFORME FINANCIERO</div>
                <div style="font-size: 6.5pt; color: #64748b; margin-top: 2px;">
                    Emisión: {{ $fechaEmision }}<br>
                    Usuario: {{ $usuarioGenerador }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Parámetros del Período -->
    <div class="period-info">
        <strong>Período evaluado:</strong> 
        {{ $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') : 'Histórico completo' }} al 
        {{ $fechaFin ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') : 'Fecha actual' }}
        &bull; <strong>Moneda:</strong> Bolivianos (Bs.)
        @if($cliente) &bull; <strong>Cliente:</strong> {{ $cliente->nombre_completo }} @endif
        @if($evento) &bull; <strong>Evento:</strong> {{ $evento->codigo_evento }} @endif
    </div>

    <!-- 4 Indicadores Principales Oficiales -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box emerald">
                <div class="kpi-label">Ingresos Consolidados</div>
                <div class="kpi-value">Bs. {{ number_format($kpis['total_ingresos_consolidados'], 2) }}</div>
                <div style="font-size: 5.5pt; color: #64748b;">Cobranzas + extras</div>
            </td>
            <td class="kpi-box rose">
                <div class="kpi-label">Costos & Gastos Totales</div>
                <div class="kpi-value text-rose">Bs. {{ number_format($kpis['costos_totales_reconocidos'], 2) }}</div>
                <div style="font-size: 5.5pt; color: #64748b;">Músicos + eventos + operación</div>
            </td>
            <td class="kpi-box gold">
                <div class="kpi-label">Balance Bruto Eventos</div>
                <div class="kpi-value">Bs. {{ number_format($kpis['balance_bruto_eventos'], 2) }}</div>
                <div style="font-size: 5.5pt; color: #64748b;">Cobrado - Asignado músicos</div>
            </td>
            <td class="kpi-box {{ $kpis['resultado_operativo_estimado'] >= 0 ? 'emerald' : 'rose' }}">
                <div class="kpi-label">Resultado Operativo</div>
                <div class="kpi-value {{ $kpis['resultado_operativo_estimado'] >= 0 ? 'text-emerald' : 'text-rose' }}">
                    {{ $kpis['resultado_operativo_estimado'] >= 0 ? '+' : '' }}Bs. {{ number_format($kpis['resultado_operativo_estimado'], 2) }}
                </div>
                <div style="font-size: 5.5pt; color: #64748b;">Margen operativo neto</div>
            </td>
        </tr>
    </table>

    <!-- Matriz de Resumen Consolidado de Rubros -->
    <div class="section-title">1. Resumen Consolidado de Flujos Financieros</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 50%;">Rubro Financiero</th>
                <th class="text-left" style="width: 25%;">Clasificación</th>
                <th class="text-right" style="width: 25%;">Importe Vigente (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Contratado con Clientes</td>
                <td>Compromiso Comercial</td>
                <td class="text-right font-mono font-bold">Bs. {{ number_format($kpis['total_contratado'], 2) }}</td>
            </tr>
            <tr>
                <td>Total Cobrado a Clientes (Anticipos & Pagos Confirmados)</td>
                <td class="text-emerald font-bold">Ingreso Efectivo</td>
                <td class="text-right font-mono font-bold text-emerald">Bs. {{ number_format($kpis['total_cobrado'], 2) }}</td>
            </tr>
            <tr>
                <td>Saldo Pendiente de Cobro a Clientes</td>
                <td class="text-rose">Por Cobrar</td>
                <td class="text-right font-mono font-bold text-rose">Bs. {{ number_format($kpis['saldo_pendiente_clientes'], 2) }}</td>
            </tr>
            <tr>
                <td>Ingresos Adicionales Extraordinarios</td>
                <td class="text-emerald">Ingreso Operativo</td>
                <td class="text-right font-mono font-bold text-emerald">Bs. {{ number_format($kpis['total_ingresos_adicionales'], 2) }}</td>
            </tr>
            <tr style="background-color: #ecfdf5;">
                <td class="font-bold">TOTAL INGRESOS CONSOLIDADOS</td>
                <td class="font-bold text-emerald">Ingresos Totales</td>
                <td class="text-right font-mono font-bold text-emerald">Bs. {{ number_format($kpis['total_ingresos_consolidados'], 2) }}</td>
            </tr>
            <tr>
                <td>Total Comisiones Asignadas a Participantes (Músicos)</td>
                <td>Costo Directo</td>
                <td class="text-right font-mono font-bold">Bs. {{ number_format($kpis['total_asignado_participantes'], 2) }}</td>
            </tr>
            <tr>
                <td>Total Comisiones Pagadas a Participantes</td>
                <td>Desembolsado</td>
                <td class="text-right font-mono">Bs. {{ number_format($kpis['total_pagado_participantes'], 2) }}</td>
            </tr>
            <tr>
                <td>Saldo Pendiente por Pagar a Participantes</td>
                <td class="text-rose">Por Desembolsar</td>
                <td class="text-right font-mono font-bold text-rose">Bs. {{ number_format($kpis['saldo_pendiente_participantes'], 2) }}</td>
            </tr>
            <tr>
                <td>Gastos Directos de Eventos (Viáticos, combustible, logística)</td>
                <td>Gasto Directo</td>
                <td class="text-right font-mono font-bold text-rose">Bs. {{ number_format($kpis['total_gastos_eventos'], 2) }}</td>
            </tr>
            <tr>
                <td>Gastos Operativos Generales (Alquiler, servicios, oficina)</td>
                <td>Gasto Operativo</td>
                <td class="text-right font-mono font-bold text-rose">Bs. {{ number_format($kpis['total_gastos_operativos'], 2) }}</td>
            </tr>
            <tr style="background-color: #fff1f2;">
                <td class="font-bold">TOTAL COSTOS & GASTOS RECONOCIDOS</td>
                <td class="font-bold text-rose">Egresos Totales</td>
                <td class="text-right font-mono font-bold text-rose">Bs. {{ number_format($kpis['costos_totales_reconocidos'], 2) }}</td>
            </tr>
            <tr style="background-color: #f1f5f9; font-size: 7.5pt;">
                <td class="font-bold">RESULTADO OPERATIVO ESTIMADO DEL PERÍODO</td>
                <td class="font-bold">Utilidad Operativa</td>
                <td class="text-right font-mono font-bold {{ $kpis['resultado_operativo_estimado'] >= 0 ? 'text-emerald' : 'text-rose' }}">
                    {{ $kpis['resultado_operativo_estimado'] >= 0 ? '+' : '' }}Bs. {{ number_format($kpis['resultado_operativo_estimado'], 2) }}
                </td>
            </tr>
            <tr>
                <td>Saldo Disponible Actual en Fondo Común</td>
                <td>Caja de Reserva</td>
                <td class="text-right font-mono font-bold">Bs. {{ number_format($kpis['saldo_fondo_comun'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Desglose de Rentabilidad por Evento (si existen eventos) -->
    @if(isset($balancesEventos) && $balancesEventos->isNotEmpty())
        <div class="section-title">2. Rentabilidad y Balance Detallado por Evento</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 14%;">Evento</th>
                    <th class="text-left" style="width: 12%;">Fecha</th>
                    <th class="text-left" style="width: 22%;">Cliente</th>
                    <th class="text-right" style="width: 12%;">Cobrado</th>
                    <th class="text-right" style="width: 14%;">Asig. Músicos</th>
                    <th class="text-right" style="width: 12%;">Gastos Ev.</th>
                    <th class="text-right" style="width: 14%;">Margen Neto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($balancesEventos as $be)
                    <tr>
                        <td class="font-mono font-bold">{{ $be['codigo_evento'] }}</td>
                        <td class="font-mono">{{ \Carbon\Carbon::parse($be['fecha_evento'])->format('d/m/Y') }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($be['cliente'], 25) }}</td>
                        <td class="text-right font-mono text-emerald">Bs. {{ number_format($be['total_cobrado'], 2) }}</td>
                        <td class="text-right font-mono">Bs. {{ number_format($be['total_asignado'], 2) }}</td>
                        <td class="text-right font-mono text-rose">Bs. {{ number_format($be['gastos_evento'], 2) }}</td>
                        <td class="text-right font-mono font-bold {{ $be['balance_neto'] >= 0 ? 'text-emerald' : 'text-rose' }}">
                            {{ $be['balance_neto'] >= 0 ? '+' : '' }}Bs. {{ number_format($be['balance_neto'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Desglose de Gastos Operativos por Categoría -->
    @if(isset($gastosPorCategoria) && $gastosPorCategoria->isNotEmpty())
        <div class="section-title">3. Gastos Operativos Institucionales por Categoría</div>
        <table class="data-table" style="width: 70%;">
            <thead>
                <tr>
                    <th class="text-left">Categoría de Gasto Operativo</th>
                    <th class="text-center" style="width: 25%;">Registros</th>
                    <th class="text-right" style="width: 35%;">Monto Total (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gastosPorCategoria as $gpc)
                    <tr>
                        <td>{{ $gpc->categoria }}</td>
                        <td class="text-center font-mono">{{ $gpc->cantidad }}</td>
                        <td class="text-right font-mono font-bold text-rose">Bs. {{ number_format($gpc->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Firmas de Responsabilidad -->
    <table style="width: 100%; margin-top: 35px; border-collapse: collapse;">
        <tr>
            <td style="width: 45%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px dashed #475569; width: 180px; margin: 0 auto; padding-top: 4px; font-size: 7pt; font-weight: bold;">
                    ADMINISTRACIÓN<br>
                    <span style="font-size: 6pt; font-weight: normal; color: #64748b;">Mariachi León Guanajuato</span>
                </div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px dashed #475569; width: 180px; margin: 0 auto; padding-top: 4px; font-size: 7pt; font-weight: bold;">
                    DIRECCIÓN MUSICAL<br>
                    <span style="font-size: 6pt; font-weight: normal; color: #64748b;">Mariachi León Guanajuato</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td class="text-left">Mariachi León Guanajuato &bull; Sistema de Gestión Administrativa &bull; Reporte Oficial</td>
                <td class="text-right">Generado el {{ $fechaEmision }} por {{ $usuarioGenerador }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
