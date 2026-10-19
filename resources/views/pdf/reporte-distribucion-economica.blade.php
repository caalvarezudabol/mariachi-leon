<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Distribución Económica - Mariachi León Guanajuato</title>
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
            padding: 6px 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #0f172a;
            vertical-align: top;
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

        /* Tablas */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7pt;
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
                <div class="doc-badge">COMISIONES & MÚSICOS</div>
                <div style="font-size: 6.5pt; color: #64748b; margin-top: 2px;">
                    Emisión: {{ $fechaEmision }}<br>
                    Usuario: {{ $usuarioGenerador }}
                </div>
            </td>
        </tr>
    </table>

    <!-- ============================================================= -->
    <!-- 1. MODALIDAD CONSOLIDADO -->
    <!-- ============================================================= -->
    @if($modalidad === 'consolidado')
        <div class="period-info">
            <strong>Modalidad:</strong> Consolidado General de Integrantes &bull;
            <strong>Período:</strong> {{ $fechaDesde ?: 'Inicio' }} al {{ $fechaHasta ?: 'Fin' }} &bull;
            <strong>Moneda:</strong> Bolivianos (Bs.)
        </div>

        <table class="kpi-table">
            <tr>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-label">Integrantes</div>
                    <div class="kpi-value font-mono">{{ number_format($resumen['total_musicos']) }}</div>
                </td>
                <td class="kpi-box gold" style="width: 25%;">
                    <div class="kpi-label">Total Asignado</div>
                    <div class="kpi-value">Bs. {{ number_format($resumen['total_asignado'], 2) }}</div>
                </td>
                <td class="kpi-box emerald" style="width: 25%;">
                    <div class="kpi-label">Total Pagado</div>
                    <div class="kpi-value text-emerald">Bs. {{ number_format($resumen['total_pagado'], 2) }}</div>
                </td>
                <td class="kpi-box rose" style="width: 25%;">
                    <div class="kpi-label">Saldo Pendiente</div>
                    <div class="kpi-value text-rose">Bs. {{ number_format($resumen['saldo_pendiente'], 2) }}</div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 32%;">Integrante</th>
                    <th class="text-left" style="width: 14%;">Tipo</th>
                    <th class="text-center" style="width: 10%;">Eventos</th>
                    <th class="text-right" style="width: 14%;">Total Asignado</th>
                    <th class="text-right" style="width: 14%;">Total Pagado</th>
                    <th class="text-right" style="width: 16%;">Saldo Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filas as $f)
                    <tr>
                        <td class="font-bold">{{ $f['nombre_completo'] }}</td>
                        <td>{{ $f['tipo'] }}</td>
                        <td class="text-center font-mono font-bold">{{ $f['total_eventos'] }}</td>
                        <td class="text-right font-mono font-bold">Bs. {{ number_format($f['total_asignado'], 2) }}</td>
                        <td class="text-right font-mono font-bold text-emerald">Bs. {{ number_format($f['total_pagado'], 2) }}</td>
                        <td class="text-right font-mono font-bold {{ $f['saldo_pendiente'] > 0 ? 'text-rose' : '' }}">
                            Bs. {{ number_format($f['saldo_pendiente'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 12px; color: #64748b;">
                            No se encontraron integrantes para este período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="3" class="text-right uppercase">Totales Generales (Bs.):</td>
                    <td class="text-right font-mono">Bs. {{ number_format($resumen['total_asignado'], 2) }}</td>
                    <td class="text-right font-mono text-emerald">Bs. {{ number_format($resumen['total_pagado'], 2) }}</td>
                    <td class="text-right font-mono text-rose">Bs. {{ number_format($resumen['saldo_pendiente'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

    <!-- ============================================================= -->
    <!-- 2. MODALIDAD POR EVENTO -->
    <!-- ============================================================= -->
    @elseif($modalidad === 'por_evento')
        <div class="period-info">
            <strong>Evento:</strong> {{ $evento->codigo_evento }} &bull;
            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} &bull;
            <strong>Cliente:</strong> {{ $evento->cliente->nombre_completo ?? 'Cliente S/N' }} &bull;
            <strong>Servicio:</strong> {{ $evento->servicio->nombre ?? 'Estándar' }}
        </div>

        <table class="kpi-table">
            <tr>
                <td class="kpi-box gold" style="width: 33.3%;">
                    <div class="kpi-label">Total Asignado al Grupo</div>
                    <div class="kpi-value">Bs. {{ number_format($resumen['total_asignado'], 2) }}</div>
                </td>
                <td class="kpi-box emerald" style="width: 33.3%;">
                    <div class="kpi-label">Total Pagado a Músicos</div>
                    <div class="kpi-value text-emerald">Bs. {{ number_format($resumen['total_pagado'], 2) }}</div>
                </td>
                <td class="kpi-box rose" style="width: 33.3%;">
                    <div class="kpi-label">Saldo Pendiente a Músicos</div>
                    <div class="kpi-value text-rose">Bs. {{ number_format($resumen['saldo_pendiente'], 2) }}</div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 30%;">Integrante</th>
                    <th class="text-left" style="width: 20%;">Función / Instrumento</th>
                    <th class="text-right" style="width: 16%;">Asignado (Bs.)</th>
                    <th class="text-right" style="width: 16%;">Pagado (Bs.)</th>
                    <th class="text-right" style="width: 18%;">Saldo (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participantes as $p)
                    <tr>
                        <td class="font-bold">{{ $p->persona->nombre_completo ?? 'No asignado' }}</td>
                        <td>{{ $p->funcion }}</td>
                        <td class="text-right font-mono font-bold">Bs. {{ number_format($p->monto_asignado, 2) }}</td>
                        <td class="text-right font-mono font-bold text-emerald">Bs. {{ number_format($p->total_pagado, 2) }}</td>
                        <td class="text-right font-mono font-bold {{ $p->saldo_pendiente > 0 ? 'text-rose' : '' }}">
                            Bs. {{ number_format($p->saldo_pendiente, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 12px; color: #64748b;">
                            No hay participantes asignados a este evento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="2" class="text-right uppercase">Totales del Evento (Bs.):</td>
                    <td class="text-right font-mono">Bs. {{ number_format($resumen['total_asignado'], 2) }}</td>
                    <td class="text-right font-mono text-emerald">Bs. {{ number_format($resumen['total_pagado'], 2) }}</td>
                    <td class="text-right font-mono text-rose">Bs. {{ number_format($resumen['saldo_pendiente'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

    <!-- ============================================================= -->
    <!-- 3. MODALIDAD POR PARTICIPANTE -->
    <!-- ============================================================= -->
    @else
        <div class="period-info">
            <strong>Integrante:</strong> {{ $persona->nombre_completo }} &bull;
            <strong>Tipo:</strong> {{ $persona->tipo }} &bull;
            <strong>CI:</strong> {{ $persona->ci_nit ?: 'S/D' }} &bull;
            <strong>Período:</strong> {{ $fechaDesde ?: 'Inicio' }} al {{ $fechaHasta ?: 'Fin' }}
        </div>

        <table class="kpi-table">
            <tr>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-label">Eventos</div>
                    <div class="kpi-value font-mono">{{ $resumen['total_eventos'] }}</div>
                </td>
                <td class="kpi-box gold" style="width: 25%;">
                    <div class="kpi-label">Total Asignado</div>
                    <div class="kpi-value">Bs. {{ number_format($resumen['total_asignado'], 2) }}</div>
                </td>
                <td class="kpi-box emerald" style="width: 25%;">
                    <div class="kpi-label">Total Cobrado</div>
                    <div class="kpi-value text-emerald">Bs. {{ number_format($resumen['total_pagado'], 2) }}</div>
                </td>
                <td class="kpi-box rose" style="width: 25%;">
                    <div class="kpi-label">Saldo por Cobrar</div>
                    <div class="kpi-value text-rose">Bs. {{ number_format($resumen['saldo_pendiente'], 2) }}</div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 14%;">Fecha</th>
                    <th class="text-left" style="width: 14%;">Cód. Evento</th>
                    <th class="text-left" style="width: 26%;">Cliente</th>
                    <th class="text-left" style="width: 16%;">Función</th>
                    <th class="text-right" style="width: 15%;">Asignado</th>
                    <th class="text-right" style="width: 15%;">Pagado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participaciones as $part)
                    <tr>
                        <td class="font-mono">{{ $part->evento ? \Carbon\Carbon::parse($part->evento->fecha_evento)->format('d/m/Y') : '—' }}</td>
                        <td class="font-mono font-bold">{{ $part->evento->codigo_evento ?? 'S/E' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($part->evento->cliente->nombre_completo ?? 'S/N', 24) }}</td>
                        <td>{{ $part->funcion }}</td>
                        <td class="text-right font-mono font-bold">Bs. {{ number_format($part->monto_asignado, 2) }}</td>
                        <td class="text-right font-mono font-bold text-emerald">Bs. {{ number_format($part->total_pagado, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 12px; color: #64748b;">
                            No se registran participaciones en el período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="4" class="text-right uppercase">Totales Acumulados (Bs.):</td>
                    <td class="text-right font-mono">Bs. {{ number_format($resumen['total_asignado'], 2) }}</td>
                    <td class="text-right font-mono text-emerald">Bs. {{ number_format($resumen['total_pagado'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Firmas -->
    <table style="width: 100%; margin-top: 30px; border-collapse: collapse;">
        <tr>
            <td style="width: 45%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px dashed #475569; width: 170px; margin: 0 auto; padding-top: 4px; font-size: 7pt; font-weight: bold;">
                    RESPONSABLE DE PAGOS<br>
                    <span style="font-size: 6pt; font-weight: normal; color: #64748b;">Mariachi León Guanajuato</span>
                </div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px dashed #475569; width: 170px; margin: 0 auto; padding-top: 4px; font-size: 7pt; font-weight: bold;">
                    CONFORMIDAD DEL INTEGRANTE<br>
                    <span style="font-size: 6pt; font-weight: normal; color: #64748b;">Firma / Recepción</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td class="text-left">Mariachi León Guanajuato &bull; Sistema de Personal &bull; Reporte de Distribución Económica</td>
                <td class="text-right">Generado el {{ $fechaEmision }} por {{ $usuarioGenerador }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
