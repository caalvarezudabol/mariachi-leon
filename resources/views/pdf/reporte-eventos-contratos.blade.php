<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Eventos y Contratos - Mariachi León Guanajuato</title>
    <style>
        @page {
            size: letter landscape;
            margin: 1.0cm 1.0cm 1.2cm 1.0cm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 7pt;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        .page-footer {
            position: fixed;
            bottom: -0.9cm;
            left: 0;
            right: 0;
            height: 0.7cm;
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
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .logo-col {
            width: 65px;
            vertical-align: middle;
            text-align: left;
        }

        .logo-col img {
            max-width: 60px;
            max-height: 50px;
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
            font-size: 6.5pt;
            color: #475569;
            margin-top: 1px;
        }

        .badge-col {
            width: 170px;
            text-align: right;
            vertical-align: middle;
        }

        .doc-badge {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 3px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        /* Tarjetas de Resumen KPI */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .kpi-box {
            width: 25%;
            padding: 5px 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #0f172a;
            vertical-align: top;
        }

        .kpi-box.emerald { border-top-color: #10b981; }
        .kpi-box.rose { border-top-color: #ef4444; }
        .kpi-box.gold { border-top-color: #d97706; }

        .kpi-label {
            font-size: 5.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }

        .kpi-value {
            font-size: 10.5pt;
            font-weight: bold;
            font-family: monospace;
            color: #0f172a;
            margin-top: 2px;
        }

        .period-info {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3px solid #d97706;
            padding: 4px 8px;
            margin-bottom: 8px;
            font-size: 6.5pt;
        }

        /* Tabla de Datos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5pt;
        }

        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 3.5px 4px;
            border: 1px solid #0f172a;
        }

        .data-table td {
            padding: 3px 4px;
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
                    NIT: {{ $empresa->nit ?? 'S/N' }} &bull; {{ $empresa->direccion ?? 'Cochabamba, Bolivia' }} &bull; Cel: {{ $empresa->telefono ?? '63854129' }}
                </div>
            </td>
            <td class="badge-col">
                <div class="doc-badge">EVENTOS Y CONTRATOS</div>
                <div style="font-size: 6pt; color: #64748b; margin-top: 2px;">
                    Emisión: {{ $fechaEmision }}<br>
                    Usuario: {{ $usuarioGenerador }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Parámetros del Filtro -->
    <div class="period-info">
        <strong>Filtros aplicados:</strong> 
        Período ({{ $campoFecha === 'fecha_evento' ? 'Fecha Evento' : 'Fecha Contrato' }}): 
        {{ $fechaDesde ?: 'Inicio' }} al {{ $fechaHasta ?: 'Fin' }}
        @if($cliente) &bull; Cliente: {{ $cliente->nombre_completo }} @endif
        @if($servicio) &bull; Servicio: {{ $servicio->nombre }} @endif
        @if($estadoContrato) &bull; Est. Contrato: {{ $estadoContrato }} @endif
        @if($estadoEvento) &bull; Est. Evento: {{ $estadoEvento }} @endif
    </div>

    <!-- 4 Tarjetas de Resumen KPI -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <div class="kpi-label">Total Presentaciones</div>
                <div class="kpi-value font-mono">{{ number_format($resumen['total_registros']) }}</div>
            </td>
            <td class="kpi-box gold">
                <div class="kpi-label">Total Contratado</div>
                <div class="kpi-value">Bs. {{ number_format($resumen['total_contratado'], 2) }}</div>
            </td>
            <td class="kpi-box emerald">
                <div class="kpi-label">Total Cobrado</div>
                <div class="kpi-value text-emerald">Bs. {{ number_format($resumen['total_cobrado'], 2) }}</div>
            </td>
            <td class="kpi-box rose">
                <div class="kpi-label">Saldo por Cobrar</div>
                <div class="kpi-value text-rose">Bs. {{ number_format($resumen['total_saldo_pendiente'], 2) }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabla Detallada -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 10%;">Contrato</th>
                <th class="text-left" style="width: 9%;">Evento</th>
                <th class="text-left" style="width: 11%;">Fecha & Hora</th>
                <th class="text-left" style="width: 16%;">Cliente</th>
                <th class="text-left" style="width: 12%;">Servicio</th>
                <th class="text-left" style="width: 15%;">Ubicación</th>
                <th class="text-center" style="width: 8%;">Est. Contrato</th>
                <th class="text-center" style="width: 7%;">Est. Evento</th>
                <th class="text-right" style="width: 8%;">Contratado</th>
                <th class="text-right" style="width: 8%;">Cobrado</th>
                <th class="text-right" style="width: 8%;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($eventos as $ev)
                @php
                    $montoContratado = $ev->monto_contratado;
                    $totalCobrado = $ev->total_abonado;
                    $saldoPendiente = $ev->saldo_pendiente;
                @endphp
                <tr>
                    <td class="font-mono font-bold">{{ $ev->contrato ? $ev->contrato->numero_contrato : 'Sin Contrato' }}</td>
                    <td class="font-mono font-bold">{{ $ev->codigo_evento }}</td>
                    <td class="font-mono">
                        {{ \Carbon\Carbon::parse($ev->fecha_evento)->format('d/m/Y') }} {{ substr($ev->hora_evento, 0, 5) }}
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($ev->cliente->nombre_completo ?? 'S/N', 22) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($ev->servicio->nombre ?? 'Estándar', 18) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($ev->direccion_evento ?: 'No especificada', 22) }}</td>
                    <td class="text-center">{{ $ev->contrato->estado ?? '—' }}</td>
                    <td class="text-center">{{ $ev->estado }}</td>
                    <td class="text-right font-mono font-bold">Bs. {{ number_format($montoContratado, 2) }}</td>
                    <td class="text-right font-mono font-bold text-emerald">Bs. {{ number_format($totalCobrado, 2) }}</td>
                    <td class="text-right font-mono font-bold {{ $saldoPendiente > 0 ? 'text-rose' : '' }}">
                        Bs. {{ number_format($saldoPendiente, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px; color: #64748b;">
                        No se encontraron registros de eventos o contratos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($eventos->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="8" class="text-right uppercase">Totales Consolidados (Bs.):</td>
                    <td class="text-right font-mono">Bs. {{ number_format($resumen['total_contratado'], 2) }}</td>
                    <td class="text-right font-mono text-emerald">Bs. {{ number_format($resumen['total_cobrado'], 2) }}</td>
                    <td class="text-right font-mono text-rose">Bs. {{ number_format($resumen['total_saldo_pendiente'], 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td class="text-left">Mariachi León Guanajuato &bull; Sistema Comercial &bull; Reporte Oficial de Eventos y Contratos</td>
                <td class="text-right">Generado el {{ $fechaEmision }} por {{ $usuarioGenerador }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
