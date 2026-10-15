<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Cobranza - {{ $pago->numero_recibo }}</title>
    <style>
        @page {
            size: 216mm 140mm landscape;
            margin: 5mm 8mm 4mm 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 7.2pt;
            color: #102846;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background-color: #FFFFFF;
        }

        .watermark-anulado {
            position: fixed;
            top: 25%;
            left: 15%;
            width: 70%;
            text-align: center;
            font-size: 42pt;
            font-weight: 900;
            color: rgba(169, 35, 35, 0.16);
            transform: rotate(-22deg);
            z-index: -1000;
            text-transform: uppercase;
            letter-spacing: 5px;
        }

        /* Header Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .header-logo-col {
            width: 18%;
            vertical-align: middle;
            text-align: left;
        }

        .header-logo-col img {
            max-width: 95px;
            max-height: 48px;
            object-fit: contain;
        }

        .header-title-col {
            width: 58%;
            vertical-align: middle;
            text-align: center;
            padding: 0 4px;
        }

        .header-brand {
            font-size: 11pt;
            font-weight: 900;
            color: #102846;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-slogan {
            font-size: 6.2pt;
            font-weight: bold;
            color: #C99A2E;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-top: 1px;
            font-style: italic;
        }

        .header-doc-title {
            font-size: 9.5pt;
            font-weight: 800;
            color: #102846;
            text-transform: uppercase;
            margin-top: 2px;
            letter-spacing: 0.5px;
            border-bottom: 1.5px solid #C99A2E;
            display: inline-block;
            padding-bottom: 1px;
        }

        .header-badge-col {
            width: 24%;
            vertical-align: middle;
            text-align: right;
        }

        .receipt-card {
            border: 1.5px solid #C99A2E;
            border-radius: 4px;
            background-color: #E7ECF2;
            padding: 3px 6px;
            text-align: center;
        }

        .receipt-card-label {
            font-size: 6.2pt;
            font-weight: bold;
            color: #102846;
            text-transform: uppercase;
        }

        .receipt-card-number {
            font-size: 9.5pt;
            font-weight: 900;
            color: #102846;
            letter-spacing: 0.5px;
        }

        .badge-status {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .badge-valido {
            background-color: #dcfce7;
            color: #26733A;
            border: 1px solid #26733A;
        }

        .badge-anulado {
            background-color: #fee2e2;
            color: #A92323;
            border: 1px solid #A92323;
        }

        /* Resumen Monto Destacado */
        .amount-card {
            background-color: #F8FAFC;
            border: 1.5px solid #C99A2E;
            border-left: 5px solid #102846;
            border-radius: 4px;
            padding: 4px 8px;
            margin-bottom: 4px;
        }

        .amount-table {
            width: 100%;
            border-collapse: collapse;
        }

        .amount-label {
            font-size: 6.2pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }

        .amount-value {
            font-size: 13.5pt;
            font-weight: 900;
            color: #102846;
        }

        .amount-literal {
            font-size: 6.8pt;
            font-weight: bold;
            color: #C99A2E;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .meta-pill {
            display: inline-block;
            background-color: #E7ECF2;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            color: #102846;
        }

        /* 2-Column Info Grid */
        .info-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .col-half {
            width: 50%;
            vertical-align: top;
        }

        .col-half-left {
            padding-right: 3px;
        }

        .col-half-right {
            padding-left: 3px;
        }

        .section-header {
            background-color: #102846;
            color: #FFFFFF;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 2px;
            letter-spacing: 0.4px;
            border-left: 3px solid #C99A2E;
            margin-bottom: 2px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            font-size: 6.8pt;
        }

        .detail-table td {
            padding: 2px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .label-cell {
            background-color: #E7ECF2;
            font-weight: bold;
            color: #102846;
            width: 32%;
        }

        .value-cell {
            color: #0f172a;
            width: 68%;
        }

        /* Balance del Contrato (3 Bloques Horizontales) */
        .balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .balance-table td {
            width: 33.33%;
            text-align: center;
            padding: 3px 2px;
            border: 1px solid #cbd5e1;
            background-color: #F8FAFC;
        }

        .balance-label {
            font-size: 6pt;
            font-weight: bold;
            color: #102846;
            text-transform: uppercase;
        }

        .balance-val {
            font-size: 9pt;
            font-weight: 900;
            margin-top: 1px;
        }

        .val-total { color: #102846; }
        .val-pagado { color: #26733A; }
        .val-saldo { color: #A92323; }

        /* Firmas & QR Table */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .signatures-col {
            width: 68%;
            vertical-align: bottom;
            padding-right: 8px;
        }

        .qr-col {
            width: 32%;
            vertical-align: middle;
            text-align: right;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-table td {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 8px;
        }

        .sig-line {
            border-bottom: 1px solid #102846;
            height: 22px;
            margin-bottom: 2px;
        }

        .sig-title {
            font-size: 6.2pt;
            font-weight: bold;
            color: #102846;
            text-transform: uppercase;
        }

        .sig-name {
            font-size: 5.8pt;
            color: #475569;
        }

        /* QR & Anti-fraude Box */
        .security-box {
            display: inline-block;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            background-color: #FFFFFF;
            padding: 2px 4px;
            text-align: left;
            width: 100%;
        }

        .sec-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sec-text-td {
            vertical-align: middle;
            font-size: 5.4pt;
            color: #475569;
            line-height: 1.2;
            padding-right: 4px;
        }

        .sec-qr-td {
            width: 58px;
            text-align: center;
            vertical-align: middle;
        }

        .sec-qr-td img {
            width: 54px;
            height: 54px;
        }

        /* Footer */
        .footer-bar {
            border-top: 1px solid #C99A2E;
            padding-top: 2px;
            margin-top: 3px;
            font-size: 5.5pt;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    @if($pago->estado === 'Anulado')
        <div class="watermark-anulado">ANULADO</div>
    @endif

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="header-logo-col">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo Mariachi León">
                @else
                    <div style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #C99A2E; background-color: #102846; text-align: center; line-height: 1.1; padding-top: 10px; color: #C99A2E; font-weight: 900; font-size: 7pt;">
                        LEÓN<br><span style="color: #ffffff; font-size: 3.5pt; letter-spacing: 0.5px;">GUANAJUATO</span>
                    </div>
                @endif
            </td>
            <td class="header-title-col">
                <div class="header-brand">{{ $empresa->razon_social ?: 'MARIACHI LEÓN GUANAJUATO S.R.L.' }}</div>
                <div class="header-slogan">"Excelencia y virtuosismo musical en cada presentación"</div>
                <div class="header-doc-title">RECIBO DE COBRANZA Y PAGO</div>
            </td>
            <td class="header-badge-col">
                <div class="receipt-card">
                    <div class="receipt-card-label">N° RECIBO</div>
                    <div class="receipt-card-number">{{ $pago->numero_recibo }}</div>
                    @if($pago->estado === 'Anulado')
                        <div class="badge-status badge-anulado">ANULADO</div>
                    @else
                        <div class="badge-status badge-valido">VÁLIDO</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Bloque A: Resumen Destacado del Pago -->
    <div class="amount-card">
        <table class="amount-table">
            <tr>
                <td style="width: 52%; vertical-align: middle;">
                    <div class="amount-label">MONTO PERCIBIDO (ACTUAL):</div>
                    <div class="amount-value">Bs {{ number_format($pago->monto, 2, ',', '.') }}</div>
                    <div class="amount-literal">SON: {{ $montoLiteral }}</div>
                </td>
                <td style="width: 48%; vertical-align: middle; text-align: right;">
                    <div style="margin-bottom: 2px;">
                        <span class="amount-label">MÉTODO DE PAGO:</span>
                        <span class="meta-pill">{{ $pago->metodo_pago }}</span>
                    </div>
                    @if($pago->comprobante_referencia)
                        <div style="font-size: 6.2pt; color: #475569; margin-bottom: 1px;">
                            <strong>Ref:</strong> {{ $pago->comprobante_referencia }}
                        </div>
                    @endif
                    <div style="font-size: 6.2pt; color: #475569;">
                        <strong>Fecha Cobro:</strong> {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : $pago->created_at->format('d/m/Y H:i') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 2 Columnas de Información: B, C y D -->
    <table class="info-grid-table">
        <tr>
            <!-- Columna Izquierda: Empresa y Cliente -->
            <td class="col-half col-half-left">
                <!-- B. Empresa Receptora -->
                <div class="section-header">DATOS DE LA EMPRESA RECEPTORA</div>
                <table class="detail-table">
                    <tr>
                        <td class="label-cell">Razón Social:</td>
                        <td class="value-cell"><strong>{{ $empresa->razon_social ?: $empresa->nombre_comercial }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label-cell">NIT:</td>
                        <td class="value-cell">{{ $empresa->nit_ruc }} &bull; Tel: {{ $empresa->telefono_principal }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Dirección:</td>
                        <td class="value-cell">{{ $empresa->direccion_fisica }} &bull; {{ $empresa->ciudad_pais }}</td>
                    </tr>
                </table>

                <!-- C. Datos del Cliente -->
                <div class="section-header">DATOS DEL CLIENTE / PAGADOR</div>
                <table class="detail-table">
                    <tr>
                        <td class="label-cell">Cliente:</td>
                        <td class="value-cell"><strong>{{ $pago->cliente ? $pago->cliente->nombre_completo : 'Cliente Particular' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label-cell">C.I. / NIT:</td>
                        <td class="value-cell">{{ $pago->cliente ? ($pago->cliente->ci_nit ?: 'Sin documento') : '-' }} &bull; Cel: {{ $pago->cliente ? ($pago->cliente->whatsapp ?: $pago->cliente->telefono) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Correo:</td>
                        <td class="value-cell">{{ $pago->cliente ? ($pago->cliente->email ?: 'Sin correo') : '-' }}</td>
                    </tr>
                </table>
            </td>

            <!-- Columna Derecha: Concepto y Servicio Vinculado -->
            <td class="col-half col-half-right">
                <div class="section-header">CONCEPTO DEL PAGO Y SERVICIO VINCULADO</div>
                <table class="detail-table">
                    <tr>
                        <td class="label-cell">Concepto:</td>
                        <td class="value-cell"><strong>{{ $pago->observaciones ?: 'Abono a cuenta por servicio musical' }}</strong></td>
                    </tr>
                    @if($pago->contrato)
                        <tr>
                            <td class="label-cell">Contrato N°:</td>
                            <td class="value-cell">
                                <strong style="color: #102846;">{{ $pago->contrato->numero_contrato }}</strong>
                                @if($pago->evento)
                                    &bull; Evento: <strong>{{ $pago->evento->codigo_evento }}</strong>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell">Servicio:</td>
                            <td class="value-cell">{{ $pago->contrato->servicio ? $pago->contrato->servicio->nombre : 'Servicio Musical' }}</td>
                        </tr>
                        @if($pago->evento)
                            <tr>
                                <td class="label-cell">Fecha Evento:</td>
                                <td class="value-cell">
                                    {{ $pago->evento->fecha_evento->format('d/m/Y') }} a las {{ $pago->evento->hora_evento }}
                                </td>
                            </tr>
                        @endif
                    @endif
                    <tr>
                        <td class="label-cell">Cajero / Operador:</td>
                        <td class="value-cell">{{ $usuarioCreacion }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- E. Balance del Contrato (3 Bloques Horizontales) -->
    @if($pago->contrato)
        <table class="balance-table">
            <tr>
                <td>
                    <div class="balance-label">1. MONTO TOTAL CONTRATADO</div>
                    <div class="balance-val val-total">Bs {{ number_format($pago->contrato->monto_total, 2, ',', '.') }}</div>
                </td>
                <td>
                    <div class="balance-label">2. TOTAL ABONADO ACUMULADO</div>
                    <div class="balance-val val-pagado">Bs {{ number_format($pago->contrato->total_pagado, 2, ',', '.') }}</div>
                </td>
                <td>
                    <div class="balance-label">3. SALDO PENDIENTE ACTUAL</div>
                    <div class="balance-val val-saldo">Bs {{ number_format($pago->contrato->saldo_pendiente, 2, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    @endif

    <!-- F & G. Firmas y Verificación QR Antifraude -->
    <table class="bottom-table">
        <tr>
            <!-- F. Firmas -->
            <td class="signatures-col">
                <table class="sig-table">
                    <tr>
                        <td>
                            <div class="sig-line"></div>
                            <div class="sig-title">POR LA EMPRESA / CAJERO</div>
                            <div class="sig-name">{{ $usuarioCreacion }} &bull; Cobranza Autorizada</div>
                        </td>
                        <td>
                            <div class="sig-line"></div>
                            <div class="sig-title">POR EL CLIENTE / PAGADOR</div>
                            <div class="sig-name">
                                {{ $pago->cliente ? $pago->cliente->nombre_completo : 'Firma Cliente' }}
                                @if($pago->cliente && $pago->cliente->ci_nit)
                                    &bull; C.I.: {{ $pago->cliente->ci_nit }}
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            <!-- G. Bloque QR y Autenticidad Antifraude -->
            <td class="qr-col">
                <div class="security-box">
                    <table class="sec-table">
                        <tr>
                            <td class="sec-text-td">
                                <strong style="color: #102846; font-size: 5.6pt;">VERIFICACIÓN ELECTRÓNICA</strong><br>
                                <span>Escanee el QR para validar autenticidad oficial en línea.</span><br>
                                <strong>Creación:</strong> {{ $fechaCreacion }} ({{ $usuarioCreacion }})<br>
                                <strong>Impresión:</strong> {{ $fechaImpresion }} ({{ $usuarioImpresion }})<br>
                                <span style="font-family: monospace; font-size: 4.8pt; color: #64748b;">
                                    HASH: {{ substr($pago->codigo_verificacion, 0, 14) }}...
                                </span>
                            </td>
                            <td class="sec-qr-td">
                                @if($qrBase64)
                                    <img src="{{ $qrBase64 }}" alt="QR Verificación">
                                @else
                                    <div style="font-size: 5pt; color: #94a3b8;">Verificación electrónica no habilitada</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- H. Pie de Página -->
    <div class="footer-bar">
        {{ $empresa->nombre_comercial }} &bull; Comprobante de ingreso / cobranza &bull; Recibo: {{ $pago->numero_recibo }} &bull; Fecha impresión: {{ $fechaImpresion }} &bull; Página 1 de 1
    </div>

</body>
</html>
