<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta - {{ $contrato->numero_contrato }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 12mm 14mm 14mm 14mm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #102846;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 2px solid #C99A2E;
            padding-bottom: 6px;
        }

        .logo-col {
            width: 20%;
            vertical-align: middle;
            text-align: left;
        }

        .logo-col img {
            max-width: 95px;
            max-height: 55px;
        }

        .title-col {
            width: 55%;
            vertical-align: middle;
            text-align: center;
        }

        .company-name {
            font-size: 11pt;
            font-weight: bold;
            color: #102846;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title {
            font-size: 10pt;
            font-weight: bold;
            color: #C99A2E;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .meta-col {
            width: 25%;
            vertical-align: middle;
            text-align: right;
            font-size: 6.8pt;
            color: #475569;
        }

        /* 2 Column Details Box */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .section-box {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            border-radius: 4px;
            padding: 5px 8px;
            vertical-align: top;
        }

        .section-title {
            font-size: 7.2pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #102846;
            border-bottom: 1px solid #E7ECF2;
            padding-bottom: 2px;
            margin-bottom: 4px;
            letter-spacing: 0.4px;
        }

        .data-row {
            margin-bottom: 2px;
            font-size: 7.3pt;
        }

        .data-label {
            font-weight: bold;
            color: #475569;
            display: inline-block;
            width: 85px;
        }

        .data-value {
            color: #0f172a;
        }

        /* Financial Cards Table */
        .cards-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .cards-table td {
            width: 20%;
            padding: 5px;
            text-align: center;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }

        .card-contract { background-color: #E7ECF2; }
        .card-paid { background-color: #eaf5ec; border-color: #a3d9ad !important; }
        .card-pending { background-color: #fcedeb; border-color: #f1aeb5 !important; }
        .card-stat { background-color: #f8fafc; }

        .card-label {
            font-size: 6.2pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 2px;
        }

        .card-amount {
            font-size: 9.5pt;
            font-weight: bold;
            font-family: 'DejaVu Sans Mono', monospace;
        }

        .color-contract { color: #102846; }
        .color-paid { color: #26733A; }
        .color-pending { color: #A92323; }
        .color-stat { color: #0f172a; }

        /* Payments Table */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7.2pt;
        }

        .payments-table th {
            background-color: #102846;
            color: #FFFFFF;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 5px;
            border: 1px solid #102846;
            font-size: 6.7pt;
            letter-spacing: 0.3px;
        }

        .payments-table td {
            padding: 3.5px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .payments-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'DejaVu Sans Mono', monospace; }

        .badge-valido {
            background-color: #eaf5ec;
            color: #26733A;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            border: 1px solid #a3d9ad;
            font-size: 6.3pt;
            text-transform: uppercase;
        }

        .badge-anulado {
            background-color: #fcedeb;
            color: #A92323;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            border: 1px solid #f1aeb5;
            font-size: 6.3pt;
            text-transform: uppercase;
            text-decoration: line-through;
        }

        /* Footer */
        .doc-footer {
            margin-top: 15px;
            padding-top: 5px;
            border-top: 1px solid #cbd5e1;
            font-size: 6.5pt;
            color: #64748b;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Encabezado Institucional -->
    <table class="header-table">
        <tr>
            <td class="logo-col">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo Mariachi León">
                @else
                    <strong style="color:#102846; font-size: 11pt;">MARIACHI LEÓN</strong>
                @endif
            </td>
            <td class="title-col">
                <div class="company-name">{{ $empresa->razon_social ?? $empresa->nombre_comercial }}</div>
                <div class="doc-title">ESTADO DE CUENTA POR CONTRATO</div>
                <div style="font-size: 7.2pt; color: #64748b; margin-top: 1px;">NIT: {{ $empresa->nit_ruc }} &bull; {{ $empresa->ciudad_pais }}</div>
            </td>
            <td class="meta-col">
                <div><strong>N° Reporte:</strong> EDC-{{ $contrato->numero_contrato }}</div>
                <div><strong>Emisión:</strong> {{ $fechaEmision }}</div>
                <div><strong>Generado por:</strong> {{ $usuarioGenerador }}</div>
            </td>
        </tr>
    </table>

    <!-- Bloque de 2 Columnas: Cliente y Contrato -->
    <table class="details-table">
        <tr>
            <td style="width: 50%; padding-right: 4px;" class="section-box">
                <div class="section-title">Datos del Cliente</div>
                <div class="data-row"><span class="data-label">Cliente:</span> <strong class="data-value">{{ $cliente->nombre_completo }}</strong></div>
                <div class="data-row"><span class="data-label">CI / NIT:</span> <span class="data-value">{{ $cliente->ci_nit ?: 'S/N' }}</span></div>
                <div class="data-row"><span class="data-label">Teléfono:</span> <span class="data-value">{{ $cliente->telefono ?: ($cliente->whatsapp ?: 'N/A') }}</span></div>
                <div class="data-row"><span class="data-label">Correo:</span> <span class="data-value">{{ $cliente->email ?: 'N/A' }}</span></div>
            </td>
            <td style="width: 50%; padding-left: 4px;" class="section-box">
                <div class="section-title">Datos del Contrato Asociado</div>
                <div class="data-row"><span class="data-label">N° Contrato:</span> <strong class="data-value font-mono">{{ $contrato->numero_contrato }}</strong></div>
                <div class="data-row"><span class="data-label">Fecha Contrato:</span> <span class="data-value">{{ $contrato->fecha_contrato ? $contrato->fecha_contrato->format('d/m/Y') : 'N/A' }}</span></div>
                <div class="data-row"><span class="data-label">Servicio:</span> <span class="data-value">{{ $contrato->servicio ? $contrato->servicio->nombre : 'Servicio Musical' }}</span></div>
                <div class="data-row"><span class="data-label">Evento / Fecha:</span> <span class="data-value">{{ $contrato->evento ? $contrato->evento->codigo_evento . ' (' . $contrato->evento->fecha_evento->format('d/m/Y H:i') . ')' : 'N/A' }}</span></div>
                <div class="data-row"><span class="data-label">Estado:</span> <span class="data-value"><strong>{{ $contrato->estado }}</strong></span></div>
            </td>
        </tr>
    </table>

    <!-- Tarjetas de Resumen Financiero -->
    <table class="cards-table">
        <tr>
            <td class="card-contract">
                <div class="card-label">Monto Contratado</div>
                <div class="card-amount color-contract">Bs {{ number_format($montoContratado, 2) }}</div>
            </td>
            <td class="card-paid">
                <div class="card-label">Total Abonado</div>
                <div class="card-amount color-paid">Bs {{ number_format($totalAbonado, 2) }}</div>
            </td>
            <td class="card-pending">
                <div class="card-label">Saldo Pendiente</div>
                <div class="card-amount color-pending">Bs {{ number_format($saldoPendiente, 2) }}</div>
            </td>
            <td class="card-stat">
                <div class="card-label">Pagos Válidos</div>
                <div class="card-amount color-stat">{{ $cantidadPagosValidos }}</div>
            </td>
            <td class="card-stat">
                <div class="card-label">Último Pago</div>
                <div class="card-amount color-stat" style="font-size: 7.5pt;">
                    {{ $ultimoPago ? $ultimoPago->fecha_pago->format('d/m/Y') : 'Sin pagos' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Historial Detallado de Pagos -->
    <div style="font-size: 7.8pt; font-weight: bold; color: #102846; margin-bottom: 3px; text-transform: uppercase;">
        Historial de Movimientos y Pagos del Contrato
    </div>

    <table class="payments-table">
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 13%;">Recibo</th>
                <th style="width: 14%;">Fecha y Hora</th>
                <th style="width: 25%;">Detalle o Concepto</th>
                <th style="width: 12%; text-align: right;">Importe (Bs)</th>
                <th style="width: 11%;">Método</th>
                <th style="width: 12%;">Cobrado Por</th>
                <th style="width: 8%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagos as $index => $p)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="text-center font-mono font-bold">{{ $p->numero_recibo }}</td>
                    <td class="text-center">{{ $p->fecha_pago ? $p->fecha_pago->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $p->observaciones ?: 'Abono a cuenta por servicio musical' }}</td>
                    <td class="text-right font-mono font-bold {{ $p->estado === 'Anulado' ? 'badge-anulado' : 'color-paid' }}">
                        {{ number_format($p->monto, 2) }}
                    </td>
                    <td class="text-center">{{ $p->metodo_pago }}</td>
                    <td class="text-center" style="font-size: 6.8pt;">{{ $p->user ? $p->user->name : 'Sistema' }}</td>
                    <td class="text-center">
                        @if($p->estado === 'Valido')
                            <span class="badge-valido">Válido</span>
                        @else
                            <span class="badge-anulado">Anulado</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #64748b;">
                        No se registran movimientos ni pagos para este contrato.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($pagos) > 0)
            <tfoot>
                <tr style="background-color: #E7ECF2; font-weight: bold;">
                    <td colspan="4" class="text-right" style="padding: 5px;">TOTAL ABONADO NETO (PAGOS VÁLIDOS):</td>
                    <td class="text-right font-mono color-paid" style="padding: 5px; font-size: 8pt;">
                        Bs {{ number_format($totalAbonado, 2) }}
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Bloque de Seguridad y Validación QR en la Parte Inferior Derecha -->
    <table style="width: 100%; margin-top: 15px; border-top: 1px dashed #cbd5e1; padding-top: 8px; page-break-inside: avoid;">
        <tr>
            <td style="width: 75%; vertical-align: middle; padding-right: 15px;">
                <div style="font-size: 8pt; font-weight: bold; color: #102846; text-transform: uppercase; margin-bottom: 3px;">
                    🛡️ Verificación de Autenticidad y Validez Oficial
                </div>
                <div style="font-size: 6.8pt; color: #475569; line-height: 1.35;">
                    Documento financiero oficial de <strong>{{ $empresa->nombre_comercial ?? 'Mariachi León Guanajuato' }}</strong>. 
                    Escanee el código QR adjunto para verificar la validez e integridad del estado de cuenta de este contrato en tiempo real.
                </div>
                <table style="width: 100%; margin-top: 5px; font-size: 6.5pt; color: #334155; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 1.5px 0;"><strong>Fecha y Hora de Emisión:</strong> {{ $fechaEmision }}</td>
                        <td style="padding: 1.5px 0;"><strong>Generado por:</strong> {{ $usuarioGenerador }}</td>
                    </tr>
                    @if(isset($token))
                    <tr>
                        <td colspan="2" style="padding: 1.5px 0; font-family: monospace; color: #64748b; font-size: 6pt;">
                            TOKEN DE SEGURIDAD: {{ $token }}
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: middle;">
                <div style="display: inline-block; text-align: center; border: 1px solid #cbd5e1; padding: 4px; border-radius: 4px; background: #ffffff;">
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

    <!-- Pie del Documento -->
    <table class="doc-footer">
        <tr>
            <td style="width: 60%;">
                Documento de consulta oficial emitido por el Sistema de Gestión de <strong>{{ $empresa->nombre_comercial }}</strong>.<br>
                Cualquier discrepancia debe ser informada al área de administración.
            </td>
            <td style="width: 40%; text-align: right;">
                Página 1 de 1 &bull; Impreso: {{ $fechaEmision }}
            </td>
        </tr>
    </table>

</body>
</html>
