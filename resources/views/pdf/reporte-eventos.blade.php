<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Gestión de Eventos - Mariachi León Guanajuato</title>
    <style>
        @page {
            margin: 1.5cm 1.2cm 1.5cm 1.2cm;
            size: letter landscape;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            font-size: 8pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Encabezado */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #C99A2E;
            padding-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 2px;
        }

        .company-title {
            font-size: 13pt;
            font-weight: 900;
            color: #102846;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .company-subtitle {
            font-size: 7.5pt;
            font-weight: bold;
            color: #C99A2E;
            margin: 1px 0 0 0;
            letter-spacing: 0.5px;
        }

        .report-title {
            font-size: 11pt;
            font-weight: 800;
            color: #102846;
            text-transform: uppercase;
            margin: 0;
            text-align: center;
        }

        .meta-box {
            text-align: right;
            font-size: 7pt;
            color: #475569;
            line-height: 1.3;
        }

        /* Filtros y Resumen */
        .info-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        .info-bar td {
            padding: 5px 8px;
            font-size: 7.5pt;
            color: #334155;
            border: none;
        }

        /* Tarjetas de Resumen KPI */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }

        .kpi-card {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 6px 10px;
            text-align: center;
            border-radius: 4px;
        }

        .kpi-card-title {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }

        .kpi-card-value {
            font-size: 10pt;
            font-weight: 900;
            font-family: 'DejaVu Sans Mono', monospace;
        }

        /* Tabla de Eventos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table th {
            background-color: #102846;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6.8pt;
            padding: 6px 5px;
            border: 1px solid #0f172a;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 5px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .row-even {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'DejaVu Sans Mono', monospace; }
        .font-bold { font-weight: bold; }

        /* Colores Financieros */
        .color-contract { color: #102846; }
        .color-paid { color: #26733A; }
        .color-pending { color: #A92323; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-align: center;
        }
        .badge-confirmed { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-pending { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-cancelled { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Totales Footer Table */
        .total-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            border-top: 2px solid #102846;
            border-bottom: 2px solid #102846;
            font-size: 7.5pt;
        }
    </style>
</head>
<body>

    <!-- Encabezado Corporativo Oficial -->
    <table class="header-table">
        <tr>
            <td style="width: 25%;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="height: 44px; max-width: 140px; object-fit: contain;">
                @else
                    <div class="company-title">MARIACHI LEÓN</div>
                    <div class="company-subtitle">GUANAJUATO S.R.L.</div>
                @endif
            </td>
            <td style="width: 50%; text-align: center;">
                <h1 class="report-title">Programación de Eventos & Estado Financiero</h1>
                <div style="font-size: 7.5pt; color: #475569; margin-top: 2px;">
                    NIT: {{ $empresa->nit_ruc }} &bull; {{ $empresa->ciudad_pais }} &bull; Cel: {{ $empresa->telefono }}
                </div>
            </td>
            <td style="width: 25%;" class="meta-box">
                <div><strong>Fecha Emisión:</strong> {{ $fechaEmision }}</div>
                <div><strong>Generado Por:</strong> {{ $usuarioEmision }}</div>
                <div><strong>Total Registros:</strong> {{ $totalEventos }} evento(s)</div>
            </td>
        </tr>
    </table>

    <!-- Barra de Filtros Aplicados -->
    <table class="info-bar">
        <tr>
            <td>
                <strong>Filtros aplicados:</strong>
                @if($clienteSeleccionado)
                    Cliente: <strong>{{ $clienteSeleccionado->nombre_completo }}</strong> (CI/NIT: {{ $clienteSeleccionado->ci_nit ?: 'S/N' }}) &bull;
                @else
                    Cliente: <em>Todos los clientes</em> &bull;
                @endif

                @if($estadoFiltro)
                    Estado: <strong>{{ $estadoFiltro }}</strong> &bull;
                @else
                    Estado: <em>Todos</em> &bull;
                @endif

                @if($search)
                    Búsqueda: <strong>"{{ $search }}"</strong>
                @else
                    Búsqueda: <em>Ninguna</em>
                @endif
            </td>
        </tr>
    </table>

    <!-- Tarjetas de Resumen KPI Financiero -->
    <table class="kpi-table">
        <tr>
            <td style="width: 25%;" class="kpi-card">
                <span class="kpi-card-title">Eventos Programados</span>
                <span class="kpi-card-value color-contract">{{ $totalEventos }}</span>
            </td>
            <td style="width: 25%;" class="kpi-card">
                <span class="kpi-card-title">Monto Total Contratado</span>
                <span class="kpi-card-value color-contract">Bs {{ number_format($totalContratado, 2) }}</span>
            </td>
            <td style="width: 25%;" class="kpi-card">
                <span class="kpi-card-title">Total A Cuenta / Cobrado</span>
                <span class="kpi-card-value color-paid">Bs {{ number_format($totalAbonado, 2) }}</span>
            </td>
            <td style="width: 25%;" class="kpi-card">
                <span class="kpi-card-title">Saldo Pendiente Global</span>
                <span class="kpi-card-value {{ $totalSaldo > 0 ? 'color-pending' : 'color-paid' }}">Bs {{ number_format($totalSaldo, 2) }}</span>
            </td>
        </tr>
    </table>

    <!-- Tabla Detallada de Eventos -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">N°</th>
                <th style="width: 9%;">Código</th>
                <th style="width: 18%;">Cliente / Contacto</th>
                <th style="width: 11%;">Fecha & Hora</th>
                <th style="width: 12%;">Servicio</th>
                <th style="width: 16%;">Ubicación / Dirección</th>
                <th style="width: 10%;" class="text-right">Monto Contratado</th>
                <th style="width: 10%;" class="text-right">A Cuenta</th>
                <th style="width: 10%;" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($eventos as $idx => $ev)
                @php
                    $monto = $ev->monto_contratado;
                    $abono = $ev->total_abonado;
                    $saldo = $ev->saldo_pendiente;
                @endphp
                <tr class="{{ $idx % 2 == 1 ? 'row-even' : '' }}">
                    <td class="text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                    <td class="font-mono font-bold" style="color: #102846;">
                        {{ $ev->codigo_evento }}
                        <div style="font-size: 6.5pt; color: #64748b; font-weight: normal;">
                            {{ $ev->contrato ? $ev->contrato->numero_contrato : 'Sin contrato' }}
                        </div>
                    </td>
                    <td>
                        <strong style="color: #0f172a;">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</strong>
                        @if($ev->contacto_evento && $ev->contacto_evento !== ($ev->cliente->nombre_completo ?? ''))
                            <div style="font-size: 6.5pt; color: #64748b;">Contacto: {{ $ev->contacto_evento }}</div>
                        @endif
                        @if($ev->telefono_contacto)
                            <div style="font-size: 6.5pt; color: #15803d; font-family: monospace;">Tel: {{ $ev->telefono_contacto }}</div>
                        @endif
                    </td>
                    <td class="font-mono">
                        <div><strong>{{ $ev->fecha_evento ? $ev->fecha_evento->format('d/m/Y') : '-' }}</strong></div>
                        <div style="font-size: 6.8pt; color: #C99A2E;">{{ $ev->hora_evento }} ({{ $ev->duracion_horas }}h)</div>
                    </td>
                    <td>
                        <strong>{{ $ev->servicio->nombre ?? 'N/A' }}</strong>
                        <div style="margin-top: 2px;">
                            @if(in_array($ev->estado, ['Confirmado', 'Realizado', 'Finalizado']))
                                <span class="badge badge-confirmed">{{ $ev->estado }}</span>
                            @elseif($ev->estado === 'Cancelado')
                                <span class="badge badge-cancelled">{{ $ev->estado }}</span>
                            @else
                                <span class="badge badge-pending">{{ $ev->estado }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-size: 6.8pt; color: #334155;">
                        {{ $ev->direccion_evento ?: 'No especificada' }}
                    </td>
                    <td class="text-right font-mono font-bold color-contract">
                        @if($ev->contrato)
                            Bs {{ number_format($monto, 2) }}
                        @else
                            <span style="font-size: 6.5pt; color: #94a3b8; font-style: italic;">Sin contrato</span>
                        @endif
                    </td>
                    <td class="text-right font-mono font-bold color-paid">
                        Bs {{ number_format($abono, 2) }}
                    </td>
                    <td class="text-right font-mono font-bold {{ $saldo > 0 ? 'color-pending' : 'color-paid' }}">
                        Bs {{ number_format($saldo, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                        No se encontraron eventos registrados con los criterios seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right" style="padding-right: 10px;">
                    TOTALES GENERALES ({{ $totalEventos }} EVENTOS):
                </td>
                <td class="text-right font-mono color-contract">
                    Bs {{ number_format($totalContratado, 2) }}
                </td>
                <td class="text-right font-mono color-paid">
                    Bs {{ number_format($totalAbonado, 2) }}
                </td>
                <td class="text-right font-mono {{ $totalSaldo > 0 ? 'color-pending' : 'color-paid' }}">
                    Bs {{ number_format($totalSaldo, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Script de Paginación en DomPDF -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT} | Mariachi León Guanajuato S.R.L. | Sistema de Gestión";
            $size = 7;
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 25;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.4, 0.45, 0.5));
        }
    </script>
</body>
</html>
