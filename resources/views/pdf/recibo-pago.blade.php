<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Cobranza - {{ $pago->numero_recibo }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.2cm 1.4cm 1.4cm 1.4cm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #0f172a;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .watermark-anulado {
            position: fixed;
            top: 30%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-size: 55pt;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.16);
            transform: rotate(-30deg);
            z-index: -1000;
            text-transform: uppercase;
            letter-spacing: 6px;
        }

        .page-footer {
            position: fixed;
            bottom: -1.0cm;
            left: 0;
            right: 0;
            height: 0.8cm;
            font-size: 7pt;
            color: #475569;
            border-top: 1.5px solid #d97706;
            padding-top: 5px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .logo-col {
            width: 22%;
            vertical-align: middle;
            text-align: left;
        }

        .logo-col img {
            max-width: 110px;
            max-height: 80px;
            object-fit: contain;
        }

        .title-col {
            width: 53%;
            vertical-align: middle;
            text-align: center;
        }

        .receipt-badge-col {
            width: 25%;
            vertical-align: middle;
            text-align: right;
        }

        .receipt-number-card {
            border: 2px solid #0b1a30;
            border-radius: 6px;
            background-color: #f8fafc;
            padding: 6px 8px;
            text-align: center;
        }

        .receipt-number-card .label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }

        .receipt-number-card .value {
            font-size: 11pt;
            font-weight: 800;
            color: #b45309;
            margin-top: 2px;
        }

        .badge-status {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-valido {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .badge-anulado {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .main-brand-name {
            font-size: 14pt;
            font-weight: 800;
            color: #0b1329;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .main-sub-title {
            font-size: 8pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 1px;
        }

        .doc-type-title {
            font-size: 11.5pt;
            font-weight: 800;
            color: #d97706;
            text-transform: uppercase;
            margin-top: 3px;
            letter-spacing: 0.5px;
        }

        .gold-line-container {
            text-align: center;
            margin: 4px auto 8px auto;
            width: 90%;
        }

        .gold-line-table {
            width: 100%;
            border-collapse: collapse;
        }

        .gold-line-table td {
            vertical-align: middle;
        }

        .gold-line-hr {
            border-top: 1px solid #d97706;
            width: 100%;
        }

        .gold-line-diamond {
            color: #d97706;
            font-size: 8pt;
            padding: 0 6px;
        }

        .section-banner {
            background-color: #0b1a30;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 3px;
            margin-top: 8px;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
            border-left: 4px solid #d97706;
            page-break-inside: avoid;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 7.8pt;
        }

        .info-table td {
            padding: 3.5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #0b1a30;
            width: 25%;
            background-color: #f8fafc;
        }

        .amount-highlight-box {
            background: #fffbeb;
            border: 1.5px solid #d97706;
            border-radius: 5px;
            padding: 8px 12px;
            margin: 8px 0;
        }

        .amount-number-row {
            font-size: 16pt;
            font-weight: 900;
            color: #0b1a30;
        }

        .amount-literal-row {
            font-size: 8pt;
            font-weight: bold;
            color: #92400e;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .econ-box-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
        }

        .econ-box-table td {
            width: 33.33%;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }

        .econ-label {
            font-size: 7pt;
            font-weight: bold;
            color: #0b1a30;
            text-transform: uppercase;
        }

        .econ-val {
            font-size: 10pt;
            font-weight: 800;
            margin-top: 2px;
        }

        .alert-anulado-box {
            background-color: #fef2f2;
            border: 1.5px solid #ef4444;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 8px;
            color: #991b1b;
            font-size: 7.8pt;
        }

        .signatures-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signatures-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0 25px;
        }

        .sig-line-td {
            border-bottom: 1px solid #0f172a;
            height: 35px;
        }

        .sig-subtext {
            text-align: center;
            font-size: 7.5pt;
            color: #0f172a;
            margin-top: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    @if($pago->estado === 'Anulado')
        <div class="watermark-anulado">ANULADO</div>
    @endif

    <!-- Fixed Footer -->
    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td style="width: 50%; text-align: left;">
                    <div style="font-weight: bold; color: #0f172a;">{{ $empresa->nombre_comercial }}</div>
                    <div>Comprobante Oficial de Ingreso / Cobranza Electrónica</div>
                </td>
                <td style="width: 25%; text-align: center;">
                    <div style="font-weight: bold; color: #334155;">Recibo: {{ $pago->numero_recibo }}</div>
                    <div>Fecha Impresión: {{ $fechaImpresion }}</div>
                </td>
                <td style="width: 25%; text-align: right; font-weight: bold; color: #0f172a;">
                    <!-- DomPDF Inyectado -->
                </td>
            </tr>
        </table>
    </div>

    <!-- DomPDF Script -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("DejaVu Sans", "bold");
            $size = 7;
            $color = array(0.06, 0.09, 0.16);
            $pdf->page_text(495, 756, $text, $font, $size, $color);
        }
    </script>

    <!-- Top Header Table -->
    <table class="top-header-table">
        <tr>
            <td class="logo-col">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <div style="width: 65px; height: 65px; border-radius: 50%; border: 2px solid #d97706; background-color: #0b1329; text-align: center; line-height: 1.2; padding-top: 12px; color: #f59e0b; font-weight: 900; font-size: 8pt;">
                        LEÓN<br><span style="color: #ffffff; font-size: 4pt; letter-spacing: 1px;">GUANAJUATO</span>
                    </div>
                @endif
            </td>
            <td class="title-col">
                <div class="main-brand-name">{{ $empresa->nombre_comercial }}</div>
                <div class="main-sub-title">EXCELENCIA Y VIRTUISISMO MUSICAL EN CADA PRESENTACIÓN</div>
                <div class="doc-type-title">RECIBO DE COBRANZA Y PAGO</div>
            </td>
            <td class="receipt-badge-col">
                <div class="receipt-number-card">
                    <div class="label">N° RECIBO</div>
                    <div class="value">{{ $pago->numero_recibo }}</div>
                    @if($pago->estado === 'Anulado')
                        <div class="badge-status badge-anulado">ANULADO</div>
                    @else
                        <div class="badge-status badge-valido">VÁLIDO</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Gold Line Divider -->
    <div class="gold-line-container">
        <table class="gold-line-table">
            <tr>
                <td><div class="gold-line-hr"></div></td>
                <td class="gold-line-diamond">◆</td>
                <td><div class="gold-line-hr"></div></td>
            </tr>
        </table>
    </div>

    @if($pago->estado === 'Anulado')
        <div class="alert-anulado-box">
            <strong>⚠️ ESTE PAGO HA SIDO ANULADO</strong><br>
            <strong>Fecha de Anulación:</strong> {{ $pago->fecha_anulacion ? $pago->fecha_anulacion->format('d/m/Y H:i:s') : 'N/A' }} | 
            <strong>Anulado por:</strong> {{ $pago->anuladoPor ? $pago->anuladoPor->name : 'Administrador' }}<br>
            <strong>Motivo registrado:</strong> {{ $pago->motivo_anulacion ?: 'Sin motivo especificado' }}
        </div>
    @endif

    <!-- Monto Destacado -->
    <div class="amount-highlight-box">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: middle;">
                    <div style="font-size: 8pt; font-weight: bold; color: #475569; text-transform: uppercase;">MONTO PERCIBIDO:</div>
                    <div class="amount-number-row">Bs {{ number_format($pago->monto, 2, ',', '.') }}</div>
                </td>
                <td style="width: 50%; vertical-align: middle; text-align: right;">
                    <div style="font-size: 7.5pt; color: #475569; font-weight: bold; text-transform: uppercase;">MÉTODO DE PAGO:</div>
                    <div style="font-size: 11pt; font-weight: 800; color: #0b1a30; text-transform: uppercase;">{{ $pago->metodo_pago }}</div>
                    @if($pago->comprobante_referencia)
                        <div style="font-size: 7.5pt; color: #64748b;">Ref: {{ $pago->comprobante_referencia }}</div>
                    @endif
                </td>
            </tr>
        </table>
        <div class="amount-literal-row">
            SON: {{ $montoLiteral }}
        </div>
    </div>

    <!-- 1. Datos de la Empresa Receptora -->
    <div class="section-banner">1. DATOS DE LA EMPRESA RECEPTORA</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Razón Social:</td>
            <td><strong>{{ $empresa->razon_social ?: $empresa->nombre_comercial }}</strong> (NIT: {{ $empresa->nit_ruc }})</td>
            <td class="info-label">Teléfonos / WhatsApp:</td>
            <td>{{ $empresa->telefono_principal }} / {{ $empresa->whatsapp_comercial }}</td>
        </tr>
        <tr>
            <td class="info-label">Dirección Fiscal:</td>
            <td colspan="3">{{ $empresa->direccion_fisica }} &bull; {{ $empresa->ciudad_pais }}</td>
        </tr>
    </table>

    <!-- 2. Datos del Cliente / Quien Paga -->
    <div class="section-banner">2. DATOS DEL CLIENTE / PAGADOR</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Cliente / Pagador:</td>
            <td><strong>{{ $pago->cliente ? $pago->cliente->nombre_completo : 'Cliente Particular' }}</strong></td>
            <td class="info-label">C.I. / NIT:</td>
            <td>{{ $pago->cliente ? ($pago->cliente->ci_nit ?: 'Sin documento') : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Teléfono / WhatsApp:</td>
            <td>{{ $pago->cliente ? ($pago->cliente->telefono . ' / ' . $pago->cliente->whatsapp) : '-' }}</td>
            <td class="info-label">Correo Electrónico:</td>
            <td>{{ $pago->cliente ? ($pago->cliente->email ?: 'Sin correo') : '-' }}</td>
        </tr>
    </table>

    <!-- 3. Concepto y Detalles del Servicio Vinculado -->
    <div class="section-banner">3. CONCEPTO DE PAGO Y SERVICIO VINCULADO</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Concepto del Pago:</td>
            <td><strong>{{ $pago->observaciones ?: 'Pago a cuenta de servicio musical contratado' }}</strong></td>
            <td class="info-label">Fecha y Hora de Pago:</td>
            <td><strong>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : $pago->created_at->format('d/m/Y H:i') }}</strong></td>
        </tr>
        @if($pago->contrato)
            <tr>
                <td class="info-label">Contrato Asociado:</td>
                <td><strong>N° {{ $pago->contrato->numero_contrato }}</strong></td>
                <td class="info-label">Evento Asociado:</td>
                <td>
                    @if($pago->evento)
                        <strong>{{ $pago->evento->codigo_evento }}</strong> ({{ $pago->evento->fecha_evento->format('d/m/Y') }} {{ $pago->evento->hora_evento }})
                    @else
                        -
                    @endif
                </td>
            </tr>
            @if($pago->contrato->servicio)
                <tr>
                    <td class="info-label">Servicio Contratado:</td>
                    <td colspan="3">{{ $pago->contrato->servicio->nombre }}</td>
                </tr>
            @endif
        @endif
    </table>

    <!-- 4. Estado de Cuenta del Contrato -->
    @if($pago->contrato)
        <div class="section-banner">4. BALANCE Y ESTADO DE CUENTA ACTUAL DEL CONTRATO</div>
        <table class="econ-box-table">
            <tr>
                <td>
                    <div class="econ-label">MONTO TOTAL CONTRATADO</div>
                    <div class="econ-val" style="color: #0b1a30;">Bs {{ number_format($pago->contrato->monto_total, 2, ',', '.') }}</div>
                </td>
                <td>
                    <div class="econ-label">TOTAL ABONADO A LA FECHA</div>
                    <div class="econ-val" style="color: #16a34a;">Bs {{ number_format($pago->contrato->total_pagado, 2, ',', '.') }}</div>
                </td>
                <td>
                    <div class="econ-label">SALDO PENDIENTE ACTUAL</div>
                    <div class="econ-val" style="color: #dc2626;">Bs {{ number_format($pago->contrato->saldo_pendiente, 2, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    @endif

    <!-- Bloque de Firmas -->
    <table class="signatures-grid">
        <tr>
            <td>
                <div class="sig-line-td"></div>
                <div class="sig-subtext">POR LA EMPRESA / CAJERO</div>
                <div style="text-align: center; font-size: 7.5pt; color: #475569;">
                    {{ $usuarioCreacion }}<br>Cobranza Autorizada
                </div>
            </td>
            <td>
                <div class="sig-line-td"></div>
                <div class="sig-subtext">POR EL CLIENTE / PAGADOR</div>
                <div style="text-align: center; font-size: 7.5pt; color: #475569;">
                    {{ $pago->cliente ? $pago->cliente->nombre_completo : 'Firma Pagador' }}<br>
                    C.I.: {{ $pago->cliente ? ($pago->cliente->ci_nit ?: '-') : '-' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Bloque de Seguridad y Validación QR Anti-Fraude (Inferior Derecho) -->
    <table style="width: 100%; margin-top: 25px; border-top: 1px dashed #cbd5e1; padding-top: 8px; page-break-inside: avoid;">
        <tr>
            <td style="width: 72%; vertical-align: middle; padding-right: 15px;">
                <div style="font-size: 8pt; font-weight: bold; color: #0b1a30; text-transform: uppercase; margin-bottom: 3px;">
                    🛡️ Verificación de Autenticidad y Validez Tributaria/Contable
                </div>
                <div style="font-size: 7pt; color: #475569; line-height: 1.35;">
                    Este recibo es emitido electrónicamente por el sistema contable de <strong>{{ $empresa->nombre_comercial }}</strong>. 
                    Cualquier alteración física, enmienda o borrón invalida el documento. Para comprobar la autenticidad y estado de este cobro, escanee el código QR adjunto.
                </div>
                <table style="width: 100%; margin-top: 5px; font-size: 6.8pt; color: #334155; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 2px 0;"><strong>Creación Original:</strong> {{ $fechaCreacion }}</td>
                        <td style="padding: 2px 0;"><strong>Cobrado/Registrado por:</strong> {{ $usuarioCreacion }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;"><strong>Fecha Impresión:</strong> {{ $fechaImpresion }}</td>
                        <td style="padding: 2px 0;"><strong>Generado por:</strong> {{ $usuarioImpresion }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 2px 0; font-family: monospace; color: #64748b; font-size: 6.3pt;">
                            HASH DE VERIFICACIÓN: {{ $pago->codigo_verificacion }}
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 28%; text-align: right; vertical-align: middle;">
                <div style="display: inline-block; text-align: center; border: 1px solid #cbd5e1; padding: 4px; border-radius: 4px; background: #ffffff;">
                    @if(isset($qrBase64))
                        <img src="{{ $qrBase64 }}" style="width: 82px; height: 82px;" alt="QR Verificación Recibo">
                    @endif
                    <div style="font-size: 5.5pt; font-weight: bold; color: #0b1a30; margin-top: 2px; text-transform: uppercase;">
                        Escanear para verificar
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
