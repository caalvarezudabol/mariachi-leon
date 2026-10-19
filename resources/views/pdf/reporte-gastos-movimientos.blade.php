<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos Financieros & Gastos - Mariachi León Guanajuato</title>
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

        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .kpi-box {
            padding: 6px 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #0f172a;
            vertical-align: top;
            width: 33.3%;
        }

        .kpi-box.emerald { border-top-color: #10b981; }
        .kpi-box.rose { border-top-color: #ef4444; }
        .kpi-box.gold { border-top-color: #d97706; }

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
            padding: 4px 5px;
            border: 1px solid #0f172a;
        }

        .data-table td {
            padding: 3.5px 5px;
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
                <div class="doc-badge">GASTOS & MOVIMIENTOS</div>
                <div style="font-size: 6.5pt; color: #64748b; margin-top: 2px;">
                    Emisión: {{ $fechaEmision }}<br>
                    Usuario: {{ $usuarioGenerador }}
                </div>
            </td>
        </tr>
    </table>

    <div class="period-info">
        <strong>Filtros aplicados:</strong> 
        Período: {{ $fechaDesde ?: 'Inicio' }} al {{ $fechaHasta ?: 'Fin' }} &bull;
        Tipo: {{ $tipoFiltro ?: 'Todos' }} &bull;
        Clasificación: {{ $clasificacionFiltro ?: 'Todas' }} &bull;
        Estado: {{ $estadoFiltro ?: 'Validos' }}
    </div>

    <!-- KPIs -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box rose">
                <div class="kpi-label">Total Egresos (Gastos)</div>
                <div class="kpi-value text-rose">Bs. {{ number_format($totales['egresos'], 2) }}</div>
                <div style="font-size: 5.5pt; color: #64748b;">Operativos: Bs. {{ number_format($totales['operativos'], 2) }} | Eventos: Bs. {{ number_format($totales['eventos'], 2) }}</div>
            </td>
            <td class="kpi-box emerald">
                <div class="kpi-label">Ingresos Extraordinarios</div>
                <div class="kpi-value text-emerald">Bs. {{ number_format($totales['ingresos'], 2) }}</div>
                <div style="font-size: 5.5pt; color: #64748b;">Entradas extraordinarias fuera de contratos</div>
            </td>
            <td class="kpi-box gold">
                <div class="kpi-label">Flujo Neto de Caja Extra</div>
                <div class="kpi-value">Bs. {{ number_format($totales['ingresos'] - $totales['egresos'], 2) }}</div>
                <div style="font-size: 5.5pt; color: #64748b;">Diferencial de estos movimientos</div>
            </td>
        </tr>
    </table>

    <!-- Tabla de Movimientos -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 11%;">Fecha</th>
                <th class="text-left" style="width: 14%;">Tipo / Clasif.</th>
                <th class="text-left" style="width: 18%;">Categoría</th>
                <th class="text-left" style="width: 25%;">Concepto / Evento</th>
                <th class="text-left" style="width: 14%;">Proveedor / Doc</th>
                <th class="text-right" style="width: 18%;">Monto (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                <tr>
                    <td class="font-mono">{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                    <td>
                        <span class="font-bold {{ $mov->tipo === 'INGRESO' ? 'text-emerald' : 'text-rose' }}">{{ $mov->tipo }}</span>
                        <span style="color: #64748b;">({{ $mov->clasificacion }})</span>
                    </td>
                    <td>{{ $mov->categoria }}</td>
                    <td>
                        <div class="font-bold">{{ $mov->concepto }}</div>
                        @if($mov->evento)
                            <div style="font-size: 6pt; color: #d97706;">Evento: {{ $mov->evento->codigo_evento }}</div>
                        @endif
                    </td>
                    <td>
                        <div>{{ $mov->proveedor ?: '—' }}</div>
                        @if($mov->numero_comprobante)
                            <div style="font-size: 6pt; color: #64748b;">{{ $mov->numero_comprobante }}</div>
                        @endif
                    </td>
                    <td class="text-right font-mono font-bold {{ $mov->tipo === 'INGRESO' ? 'text-emerald' : 'text-rose' }}">
                        {{ $mov->tipo === 'INGRESO' ? '+' : '-' }} Bs. {{ number_format($mov->monto, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #64748b;">
                        No se registraron movimientos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($movimientos->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="5" class="text-right uppercase">Total Egresos Válidos:</td>
                    <td class="text-right font-mono text-rose">Bs. {{ number_format($totales['egresos'], 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td class="text-left">Mariachi León Guanajuato &bull; Sistema Financiero &bull; Reporte de Movimientos y Gastos</td>
                <td class="text-right">Generado el {{ $fechaEmision }} por {{ $usuarioGenerador }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
