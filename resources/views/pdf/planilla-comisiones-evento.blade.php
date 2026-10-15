<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Comisiones - Evento {{ $evento->codigo_evento }}</title>
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
            width: 180px;
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

        /* Ficha de Información del Evento */
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #C99A2E;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 10px;
        }
        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-box td {
            font-size: 7pt;
            padding: 1.5px 4px;
            vertical-align: top;
        }

        /* Tarjetas de Resumen Financiero */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-bottom: 10px;
        }
        .kpi-cell {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
        }
        .kpi-label {
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

        /* Tablas de Datos */
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
            border: 1px solid #0b1d33;
            text-align: left;
        }
        .data-table td {
            font-size: 7pt;
            padding: 4px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tfoot td {
            background-color: #e2e8f0;
            font-weight: bold;
            border-top: 2px solid #102846;
        }

        /* Badges de Estado */
        .badge {
            display: inline-block;
            padding: 1.5px 4px;
            border-radius: 3px;
            font-size: 6.2pt;
            font-weight: bold;
            text-align: center;
        }
        .badge-paid {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-pending {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .badge-role {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        /* Bloque de Firmas */
        .firmas-table {
            width: 100%;
            margin-top: 18px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .firmas-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 30px;
        }
        .linea-firma {
            border-top: 1px solid #475569;
            padding-top: 4px;
            font-size: 6.8pt;
            font-weight: bold;
            color: #1e293b;
        }

        /* Sección QR y Seguridad */
        .qr-section {
            width: 100%;
            border-top: 1.5px dashed #cbd5e1;
            padding-top: 8px;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .qr-box {
            text-align: center;
            display: inline-block;
            border: 1px solid #cbd5e1;
            padding: 4px;
            background: #ffffff;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <!-- Encabezado Oficial -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo Empresa">
                @endif
            </td>
            <td class="header-company">
                <div class="header-title-main">{{ $empresa->nombre_comercial ?? 'MARIACHI LEÓN GUANAJUATO' }}</div>
                <div class="header-subtext">
                    {{ $empresa->razon_social ?? 'Mariachi León Guanajuato S.R.L.' }} &bull; NIT: {{ $empresa->nit ?? 'S/N' }}<br>
                    {{ $empresa->direccion ?? 'Dirección Central' }} &bull; Cel: {{ $empresa->telefono ?? '70000000' }} &bull; {{ $empresa->ciudad_pais ?? 'Bolivia' }}
                </div>
            </td>
            <td class="header-badge-box">
                <div class="badge-doc">PLANILLA DE LIQUIDACIÓN</div>
                <div style="font-size: 8.5pt; font-weight: bold; color: #C99A2E; font-family: monospace;">
                    {{ $evento->codigo_evento }}
                </div>
                <div style="font-size: 6.5pt; color: #64748b;">
                    @if($evento->contrato)
                        Contrato: <strong>{{ $evento->contrato->numero_contrato }}</strong>
                    @else
                        <em>Sin contrato formal</em>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Ficha del Evento y Servicio -->
    <div class="info-box">
        <table>
            <tr>
                <td style="width: 50%;">
                    <strong style="color: #102846;">CLIENTE / CONTRATANTE:</strong><br>
                    <span style="font-size: 8pt; font-weight: bold; color: #102846;">
                        {{ $evento->cliente->nombre_completo ?? 'Cliente General' }}
                    </span><br>
                    <span style="color: #475569;">
                        CI/NIT: {{ $evento->cliente->ci_nit ?? 'S/N' }} | Celular: {{ $evento->cliente->telefono ?? ($evento->telefono_contacto ?? 'S/N') }}
                    </span>
                </td>
                <td style="width: 50%;">
                    <strong style="color: #102846;">SERVICIO Y DETALLES DEL EVENTO:</strong><br>
                    <strong>Servicio:</strong> {{ $evento->servicio->nombre ?? 'Presentación Musical' }}<br>
                    <strong>Fecha y Hora:</strong> {{ $evento->fecha_evento ? $evento->fecha_evento->format('d/m/Y') : '-' }} &bull; {{ $evento->hora_evento ?: 'Hora a convenir' }}<br>
                    <strong>Lugar / Ubicación:</strong> {{ $evento->direccion_evento ?? ($evento->lugar ?? 'No especificado') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Tarjetas de Resumen Financiero y Margen -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-cell" style="width: 25%;">
                <div class="kpi-label">Monto Contratado</div>
                <div class="kpi-value color-primary">
                    Bs {{ number_format($resumen['monto_evento'], 2) }}
                </div>
            </td>
            <td class="kpi-cell" style="width: 25%;">
                <div class="kpi-label">Total Comisiones</div>
                <div class="kpi-value" style="color: #0284c7;">
                    Bs {{ number_format($resumen['total_comisiones'], 2) }}
                </div>
            </td>
            <td class="kpi-cell" style="width: 25%;">
                <div class="kpi-label">Comisiones Pagadas</div>
                <div class="kpi-value color-paid">
                    Bs {{ number_format($resumen['total_pagado'], 2) }}
                </div>
            </td>
            <td class="kpi-cell" style="width: 25%; background-color: {{ $resumen['margen_grupo'] >= 0 ? '#f0fdf4' : '#fff1f2' }}; border-color: {{ $resumen['margen_grupo'] >= 0 ? '#bbf7d0' : '#fecaca' }};">
                <div class="kpi-label">Margen Mariachi León</div>
                <div class="kpi-value {{ $resumen['margen_grupo'] >= 0 ? 'color-gold' : 'color-pending' }}">
                    Bs {{ number_format($resumen['margen_grupo'], 2) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabla Detallada de Músicos y Personal -->
    <div style="font-size: 7.5pt; font-weight: bold; color: #102846; margin: 4px 0 4px 0; text-transform: uppercase;">
        Detalle de Comisiones y Liquidación a Integrantes ({{ count($participantes) }})
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">N°</th>
                <th style="width: 26%;">Músico / Personal</th>
                <th style="width: 12%;">CI / Documento</th>
                <th style="width: 14%;">Función / Rol</th>
                <th style="width: 16%;">Concepto</th>
                <th style="width: 12%; text-align: right;">Comisión (Bs)</th>
                <th style="width: 10%; text-align: center;">Estado</th>
                <th style="width: 16%; text-align: center;">Firma de Recepción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($participantes as $idx => $part)
                <tr>
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td>
                        <strong class="color-primary">{{ $part->persona->nombre_completo ?? ($part->persona->nombre ?? 'N/A') }}</strong>
                        @if(!empty($part->persona->telefono))
                            <div style="font-size: 6pt; color: #64748b;">Tel: {{ $part->persona->telefono }}</div>
                        @endif
                    </td>
                    <td class="font-mono">{{ $part->persona->ci_nit ?? '-' }}</td>
                    <td>
                        <span class="badge badge-role">{{ $part->funcion }}</span>
                    </td>
                    <td>{{ $part->concepto_pago ?: 'Comisión por Evento' }}</td>
                    <td class="text-right font-mono font-bold color-primary">
                        Bs {{ number_format($part->monto_asignado, 2) }}
                    </td>
                    <td class="text-center">
                        @if($part->estado_distribucion === 'Distribuido')
                            <span class="badge badge-paid">PAGADO</span>
                        @else
                            <span class="badge badge-pending">PENDIENTE</span>
                        @endif
                    </td>
                    <td class="text-center" style="height: 22px;">
                        <!-- Espacio para firma física -->
                        <div style="border-bottom: 1px dotted #94a3b8; width: 85%; margin: 14px auto 0 auto;"></div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                        No se han registrado músicos ni personal para este evento.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($participantes) > 0)
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right uppercase font-bold" style="padding-right: 8px;">
                        Total Liquidación Integrantes:
                    </td>
                    <td class="text-right font-mono font-bold color-primary">
                        Bs {{ number_format($resumen['total_comisiones'], 2) }}
                    </td>
                    <td colspan="2" class="text-center" style="font-size: 6.2pt; color: #475569;">
                        Pagado: Bs {{ number_format($resumen['total_pagado'], 2) }} | Pendiente: Bs {{ number_format($resumen['total_pendiente'], 2) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Bloque de Firmas -->
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea-firma">
                    RESPONSABLE DE ADMINISTRACIÓN<br>
                    <span style="font-size: 5.5pt; font-weight: normal; color: #64748b;">Mariachi León Guanajuato</span>
                </div>
            </td>
            <td>
                <div class="linea-firma">
                    DIRECTOR MUSICAL / COORDINADOR<br>
                    <span style="font-size: 5.5pt; font-weight: normal; color: #64748b;">Conformidad de Liquidación</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bloque de Seguridad y Validación QR en la Parte Inferior Derecha -->
    <table class="qr-section">
        <tr>
            <td style="width: 75%; vertical-align: middle; padding-right: 15px;">
                <div style="font-size: 8pt; font-weight: bold; color: #102846; text-transform: uppercase; margin-bottom: 3px;">
                    🛡️ Verificación Oficial de Liquidación de Comisiones
                </div>
                <div style="font-size: 6.8pt; color: #475569; line-height: 1.35;">
                    Documento oficial de control financiero interno emitido por <strong>{{ $empresa->nombre_comercial ?? 'Mariachi León Guanajuato' }}</strong>. 
                    Escanee el código QR adjunto para constatar la autenticidad de la asignación y pagos de comisiones del evento <strong>{{ $evento->codigo_evento }}</strong>.
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
            $text = "Página {PAGE_NUM} de {PAGE_COUNT} | Mariachi León Guanajuato | Planilla de Comisiones Evento {{ $evento->codigo_evento }}";
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
