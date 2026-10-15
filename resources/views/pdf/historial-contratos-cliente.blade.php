<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Contratos - {{ $cliente->nombre_completo }}</title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7.5pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* Utilidades tipográficas */
        .font-mono { font-family: monospace; }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .uppercase { text-transform: uppercase; }

        /* Colores corporativos */
        .color-primary { color: #102846; }
        .color-gold { color: #C99A2E; }
        .color-paid { color: #26733A; }
        .color-pending { color: #A92323; }

        /* Encabezado */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #102846;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-logo {
            width: 65px;
            text-align: left;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 65px;
            max-height: 60px;
        }
        .header-company {
            text-align: center;
            vertical-align: middle;
            padding: 0 10px;
        }
        .header-title-main {
            font-size: 13pt;
            font-weight: bold;
            color: #102846;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .header-subtext {
            font-size: 6.5pt;
            color: #475569;
            line-height: 1.2;
        }
        .header-badge-box {
            width: 170px;
            text-align: right;
            vertical-align: middle;
        }
        .badge-doc {
            background-color: #102846;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            text-align: center;
            margin-bottom: 4px;
        }

        /* Ficha de Información del Cliente */
        .client-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #C99A2E;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 10px;
        }
        .client-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .client-box td {
            font-size: 7pt;
            padding: 1.5px 4px;
            vertical-align: top;
        }

        /* Tarjetas de Indicadores KPI */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-bottom: 10px;
        }
        .kpi-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        .kpi-title {
            font-size: 6pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 9.5pt;
            font-weight: bold;
            font-family: monospace;
        }

        /* Tabla de Contratos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #102846;
            color: #ffffff;
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4.5px 5px;
            border: 1px solid #0f172a;
        }
        .data-table td {
            font-size: 6.8pt;
            padding: 4px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tfoot tr {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #102846;
        }

        /* Badges de Estado */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-confirmed { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-pending { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-cancelled { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        /* Bloque de Validación y QR Inferior Derecho */
        .qr-section {
            width: 100%;
            margin-top: 15px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            page-break-inside: avoid;
        }
        .qr-box {
            display: inline-block;
            text-align: center;
            border: 1px solid #cbd5e1;
            padding: 4px;
            border-radius: 4px;
            background: #ffffff;
        }
    </style>
</head>
<body>

    <!-- Encabezado Institucional -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo Corporativo">
                @endif
            </td>
            <td class="header-company">
                <div class="header-title-main">{{ $empresa->nombre_comercial ?? 'MARIACHI LEÓN GUANAJUATO' }}</div>
                <div class="header-subtext">
                    <strong>Razón Social:</strong> {{ $empresa->razon_social ?? 'Mariachi León Guanajuato S.R.L.' }} &bull; 
                    <strong>NIT:</strong> {{ $empresa->nit ?? 'No registrado' }}<br>
                    <strong>Dirección:</strong> {{ $empresa->direccion ?? 'Santa Cruz de la Sierra' }} &bull; 
                    <strong>Teléfono:</strong> {{ $empresa->telefono ?? '69186332' }} &bull; 
                    Santa Cruz - Bolivia
                </div>
            </td>
            <td class="header-badge-box">
                <div class="badge-doc">HISTORIAL DE CONTRATOS</div>
                <div style="font-size: 6.5pt; color: #64748b; line-height: 1.2;">
                    <strong>Emisión:</strong> {{ $fechaEmision }}<br>
                    <strong>Usuario:</strong> {{ $usuarioEmision }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Ficha de Datos del Cliente -->
    <div class="client-box">
        <table>
            <tr>
                <td style="width: 50%;">
                    <strong style="color: #102846;">CLIENTE:</strong> {{ $cliente->nombre_completo }}
                </td>
                <td style="width: 25%;">
                    <strong style="color: #102846;">C.I. / NIT:</strong> {{ $cliente->ci_nit ?: 'S/D' }}
                </td>
                <td style="width: 25%;">
                    <strong style="color: #102846;">ESTADO:</strong> 
                    <span style="font-weight: bold; color: {{ $cliente->estado === 'Activo' ? '#15803d' : '#b91c1c' }};">
                        {{ $cliente->estado }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <strong style="color: #102846;">TELÉFONO / WHATSAPP:</strong> {{ $cliente->telefono ?: ($cliente->whatsapp ?: 'No registrado') }}
                </td>
                <td>
                    <strong style="color: #102846;">CORREO:</strong> {{ $cliente->email ?: 'No registrado' }}
                </td>
                <td>
                    <strong style="color: #102846;">TOTAL CONTRATOS:</strong> {{ $totalContratos }}
                </td>
            </tr>
            @if($cliente->direccion)
                <tr>
                    <td colspan="3">
                        <strong style="color: #102846;">DIRECCIÓN REGISTRADA:</strong> {{ $cliente->direccion }}
                    </td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Tarjetas de Resumen Financiero (KPIs) -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%; border-top: 3px solid #102846;">
                <div class="kpi-title">Total Contratos</div>
                <div class="kpi-value color-primary">{{ $totalContratos }}</div>
            </td>
            <td class="kpi-card" style="width: 25%; border-top: 3px solid #C99A2E;">
                <div class="kpi-title">Monto Total Contratado</div>
                <div class="kpi-value color-gold">Bs {{ number_format($totalMontoContratado, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%; border-top: 3px solid #26733A;">
                <div class="kpi-title">Total A Cuenta (Cobrado)</div>
                <div class="kpi-value color-paid">Bs {{ number_format($totalAbonado, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%; border-top: 3px solid #A92323;">
                <div class="kpi-title">Saldo Total Pendiente</div>
                <div class="kpi-value {{ $totalSaldo > 0 ? 'color-pending' : 'color-paid' }}">
                    Bs {{ number_format($totalSaldo, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabla Detallada de Contratos por Evento -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">N°</th>
                <th style="width: 13%;">Contrato</th>
                <th style="width: 14%;">Evento</th>
                <th style="width: 21%;">Servicio / Ubicación</th>
                <th style="width: 13%; text-align: right;">Monto Contratado</th>
                <th style="width: 12%; text-align: right;">A Cuenta</th>
                <th style="width: 12%; text-align: right;">Saldo</th>
                <th style="width: 11%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contratos as $index => $c)
                @php
                    $monto = (float)$c->monto_total;
                    $abono = (float)$c->pagos->where('estado', 'Valido')->sum('monto');
                    $saldo = max(0.0, $monto - $abono);
                    $evento = $c->evento;
                @endphp
                <tr>
                    <td class="text-center font-mono font-bold">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-mono font-bold color-primary">{{ $c->numero_contrato }}</div>
                        <div style="font-size: 6.2pt; color: #64748b;">
                            {{ $c->fecha_contrato ? $c->fecha_contrato->format('d/m/Y') : '-' }}
                        </div>
                    </td>
                    <td>
                        @if($evento)
                            <div class="font-mono font-bold color-gold">{{ $evento->codigo_evento }}</div>
                            <div style="font-size: 6.2pt; color: #475569;">
                                {{ $evento->fecha_evento ? $evento->fecha_evento->format('d/m/Y') : '-' }} &bull; {{ $evento->hora_evento }}
                            </div>
                        @else
                            <span style="font-size: 6.2pt; color: #94a3b8; font-style: italic;">Sin evento enlazado</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $c->servicio->nombre ?? ($evento->servicio->nombre ?? 'Servicio Musical') }}</strong>
                        @if($evento && $evento->direccion_evento)
                            <div style="font-size: 6.2pt; color: #64748b; margin-top: 1px;">
                                📍 {{ \Illuminate\Support\Str::limit($evento->direccion_evento, 45) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-right font-mono font-bold color-primary">
                        Bs {{ number_format($monto, 2) }}
                    </td>
                    <td class="text-right font-mono font-bold color-paid">
                        Bs {{ number_format($abono, 2) }}
                    </td>
                    <td class="text-right font-mono font-bold {{ $saldo > 0 ? 'color-pending' : 'color-paid' }}">
                        Bs {{ number_format($saldo, 2) }}
                    </td>
                    <td class="text-center">
                        @if(in_array($c->estado, ['Confirmado', 'Finalizado', 'Vigente']))
                            <span class="badge badge-confirmed">{{ $c->estado }}</span>
                        @elseif($c->estado === 'Cancelado')
                            <span class="badge badge-cancelled">{{ $c->estado }}</span>
                        @else
                            <span class="badge badge-pending">{{ $c->estado }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                        No se registran contratos formalizados para este cliente.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($contratos) > 0)
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right" style="padding-right: 8px;">
                        TOTALES CONSOLIDADOS ({{ $totalContratos }} CONTRATOS):
                    </td>
                    <td class="text-right font-mono color-primary">
                        Bs {{ number_format($totalMontoContratado, 2) }}
                    </td>
                    <td class="text-right font-mono color-paid">
                        Bs {{ number_format($totalAbonado, 2) }}
                    </td>
                    <td class="text-right font-mono {{ $totalSaldo > 0 ? 'color-pending' : 'color-paid' }}">
                        Bs {{ number_format($totalSaldo, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Eventos adicionales sin contrato formal (si existen) -->
    @if(isset($eventosSinContrato) && count($eventosSinContrato) > 0)
        <div style="font-size: 7.5pt; font-weight: bold; color: #102846; margin: 10px 0 4px 0; text-transform: uppercase;">
            Eventos Registrados en Espera de Contrato Formal ({{ count($eventosSinContrato) }})
        </div>
        <table class="data-table" style="margin-bottom: 10px;">
            <thead>
                <tr>
                    <th style="width: 5%;">N°</th>
                    <th style="width: 18%;">Código Evento</th>
                    <th style="width: 20%;">Fecha y Hora</th>
                    <th style="width: 35%;">Servicio Solicitado / Ubicación</th>
                    <th style="width: 22%; text-align: center;">Estado Operativo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eventosSinContrato as $idx => $esc)
                    <tr>
                        <td class="text-center font-mono">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold color-gold">{{ $esc->codigo_evento }}</td>
                        <td>{{ $esc->fecha_evento ? $esc->fecha_evento->format('d/m/Y') : '-' }} &bull; {{ $esc->hora_evento }}</td>
                        <td>
                            <strong>{{ $esc->servicio->nombre ?? 'Servicio Estándar' }}</strong>
                            @if($esc->direccion_evento)
                                <div style="font-size: 6.2pt; color: #64748b;">📍 {{ $esc->direccion_evento }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-pending">{{ $esc->estado }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Bloque de Seguridad y Validación QR en la Parte Inferior Derecha -->
    <table class="qr-section">
        <tr>
            <td style="width: 75%; vertical-align: middle; padding-right: 15px;">
                <div style="font-size: 8pt; font-weight: bold; color: #102846; text-transform: uppercase; margin-bottom: 3px;">
                    🛡️ Verificación de Autenticidad y Validez Oficial
                </div>
                <div style="font-size: 6.8pt; color: #475569; line-height: 1.35;">
                    Este documento es un reporte financiero oficial emitido por el sistema informático de <strong>{{ $empresa->nombre_comercial ?? 'Mariachi León Guanajuato' }}</strong>. 
                    Escanee el código QR adjunto para verificar la validez, integridad y movimientos del historial de contratos del cliente en línea.
                </div>
                <table style="width: 100%; margin-top: 5px; font-size: 6.5pt; color: #334155; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 1.5px 0;"><strong>Fecha y Hora de Emisión:</strong> {{ $fechaEmision }}</td>
                        <td style="padding: 1.5px 0;"><strong>Generado por:</strong> {{ $usuarioEmision }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 1.5px 0; font-family: monospace; color: #64748b; font-size: 6pt;">
                            TOKEN DE SEGURIDAD: {{ $token }}
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: middle;">
                <div class="qr-box">
                    @if(isset($qrBase64))
                        <img src="{{ $qrBase64 }}" style="width: 75px; height: 75px;" alt="Código QR de Verificación">
                    @endif
                    <div style="font-size: 5.5pt; font-weight: bold; color: #102846; margin-top: 2px; text-transform: uppercase;">
                        Escanear para verificar
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Script de Paginación en DomPDF -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT} | Mariachi León Guanajuato S.R.L. | Historial de Contratos";
            $size = 7;
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.4, 0.45, 0.5));
        }
    </script>
</body>
</html>
