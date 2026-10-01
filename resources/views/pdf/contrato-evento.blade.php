<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Servicio Musical - {{ $contrato->numero_contrato }}</title>
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
            width: 78%;
            vertical-align: middle;
            text-align: center;
            padding-right: 8%;
        }

        .main-brand-name {
            font-size: 15pt;
            font-weight: 800;
            color: #0b1329;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .main-sub-title {
            font-size: 9pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 2px;
        }

        .doc-type-title {
            font-size: 12pt;
            font-weight: 800;
            color: #d97706;
            text-transform: uppercase;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .gold-line-container {
            text-align: center;
            margin: 4px auto 10px auto;
            width: 85%;
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
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 3px;
            margin-top: 10px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            border-left: 4px solid #d97706;
            page-break-inside: avoid;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8pt;
        }

        .info-table td {
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #0b1a30;
            width: 28%;
            background-color: #f8fafc;
        }

        .econ-box-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 10px;
        }

        .econ-box-table td {
            width: 25%;
            text-align: center;
            padding: 8px 4px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }

        .econ-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #0b1a30;
            text-transform: uppercase;
        }

        .econ-val {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }

        .clauses-box {
            font-size: 7.5pt;
            color: #334155;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
            margin-top: 10px;
            page-break-inside: avoid;
            text-align: justify;
        }

        .signatures-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
            page-break-inside: avoid;
        }

        .signatures-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0 20px;
        }

        .sig-line-td {
            border-bottom: 1px solid #0f172a;
            height: 40px;
        }

        .sig-subtext {
            text-align: center;
            font-size: 8pt;
            color: #0f172a;
            margin-top: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Fixed Footer -->
    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td style="width: 45%; text-align: left;">
                    <div style="font-weight: bold; color: #0f172a;">{{ $empresa->nombre_comercial }}</div>
                    <div>Contrato Oficial de Presentación / Servicio Musical</div>
                </td>
                <td style="width: 30%; text-align: center;">
                    <div style="font-weight: bold; color: #334155;">Contrato N°: {{ $contrato->numero_contrato }}</div>
                    <div>Fecha: {{ $fechaEmision }}</div>
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

    <!-- Header Section -->
    <table class="top-header-table">
        <tr>
            <td class="logo-col">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <div style="width: 70px; height: 70px; border-radius: 50%; border: 2px solid #d97706; background-color: #0b1329; text-align: center; line-height: 1.2; padding-top: 14px; color: #f59e0b; font-weight: 900; font-size: 9pt;">
                        LEÓN<br><span style="color: #ffffff; font-size: 4.5pt; letter-spacing: 1px;">GUANAJUATO</span>
                    </div>
                @endif
            </td>
            <td class="title-col">
                <div class="main-brand-name">{{ $empresa->nombre_comercial }}</div>
                <div class="main-sub-title">EXCELENCIA Y VIRTUISISMO MUSICAL EN CADA PRESENTACIÓN</div>
                <div class="doc-type-title">CONTRATO DE PRESTACIÓN DE SERVICIOS</div>
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

    <!-- 1. Datos de la Empresa Prestadora -->
    <div class="section-banner">1. DATOS DE LA EMPRESA PRESTADORA DE SERVICIO</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Empresa / Razón Social:</td>
            <td><strong>{{ $empresa->razon_social ?: $empresa->nombre_comercial }}</strong> (NIT/RUC: {{ $empresa->nit_ruc }})</td>
        </tr>
        <tr>
            <td class="info-label">Representante Legal:</td>
            <td><strong>{{ $empresa->representante_legal }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">Teléfonos / WhatsApp:</td>
            <td>{{ $empresa->telefono_principal }} / {{ $empresa->whatsapp_comercial }}</td>
        </tr>
        <tr>
            <td class="info-label">Dirección Fiscal:</td>
            <td>{{ $empresa->direccion_fisica }} &bull; {{ $empresa->ciudad_pais }}</td>
        </tr>
    </table>

    <!-- 2. Datos del Cliente Contratante -->
    <div class="section-banner">2. DATOS DEL CLIENTE CONTRATANTE</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Nombre del Cliente:</td>
            <td><strong>{{ $contrato->cliente->nombre_completo }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">C.I. / NIT:</td>
            <td>{{ $contrato->cliente->ci_nit ?: 'Sin Especificar' }}</td>
        </tr>
        <tr>
            <td class="info-label">Teléfono / WhatsApp:</td>
            <td>{{ $contrato->cliente->telefono }} / {{ $contrato->cliente->whatsapp }}</td>
        </tr>
        <tr>
            <td class="info-label">Correo Electrónico:</td>
            <td>{{ $contrato->cliente->email ?: 'Sin Correo' }}</td>
        </tr>
    </table>

    <!-- 3. Detalles del Evento y Servicio -->
    <div class="section-banner">3. DETALLES DEL EVENTO Y UBICACIÓN</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Código del Evento:</td>
            <td><strong>{{ $contrato->evento->codigo_evento }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">Servicio Contratado:</td>
            <td><strong>{{ $contrato->servicio->nombre }}</strong> (Duración: {{ $contrato->evento->duracion_horas }} horas)</td>
        </tr>
        <tr>
            <td class="info-label">Fecha y Hora de Inicio:</td>
            <td><strong>{{ $contrato->evento->fecha_evento->format('d/m/Y') }}</strong> a las <strong>{{ $contrato->evento->hora_evento }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">Persona de Contacto:</td>
            <td>{{ $contrato->evento->contacto_evento }} (Tel: {{ $contrato->evento->telefono_contacto }})</td>
        </tr>
        <tr>
            <td class="info-label">Dirección Exacta del Evento:</td>
            <td>{{ $contrato->evento->direccion_evento }}</td>
        </tr>
        @if($contrato->evento->latitud && $contrato->evento->longitud)
            <tr>
                <td class="info-label">Coordenadas GPS:</td>
                <td>Lat: {{ $contrato->evento->latitud }}, Lng: {{ $contrato->evento->longitud }} (Verificable en Google Maps)</td>
            </tr>
        @endif
    </table>

    <!-- 4. Resumen Económico del Contrato -->
    <div class="section-banner">4. CONDICIONES ECONÓMICAS Y PLAN DE PAGO</div>
    <table class="econ-box-table">
        <tr>
            <td>
                <div class="econ-label">MONTO TOTAL CONTRATO</div>
                <div class="econ-val" style="color: #0b1a30;">Bs {{ number_format($contrato->monto_total, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="econ-label">PAGO INICIAL / SEÑA</div>
                <div class="econ-val" style="color: #0284c7;">Bs {{ number_format($contrato->pago_inicial, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="econ-label">TOTAL ABONADO</div>
                <div class="econ-val" style="color: #16a34a;">Bs {{ number_format($contrato->total_pagado, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="econ-label">SALDO PENDIENTE</div>
                <div class="econ-val" style="color: #dc2626;">Bs {{ number_format($contrato->saldo_pendiente, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Cláusulas y Términos -->
    <div class="clauses-box">
        <strong>TÉRMINOS Y CLÁUSULAS DEL CONTRATO:</strong><br>
        1. <strong>Compromiso de Puntualidad:</strong> <em>{{ $empresa->nombre_comercial }}</em> se compromete a presentarse puntualmente en la fecha y hora indicadas.<br>
        2. <strong>Condiciones de Pago:</strong> El cliente debe haber cancelado la totalidad del saldo pendiente como máximo al momento del inicio de la presentación en la fecha del evento.<br>
        3. <strong>Garantía de Calidad:</strong> La agrupación musical se compromete a asistir con su vestimenta oficial e instrumentos profesionales en perfecto estado.<br>
        4. {{ $empresa->terminos_contrato ?: 'Cualquier modificación de fecha u horario deberá coordinarse con 48 horas de anticipación sujeto a disponibilidad.' }}
    </div>

    <!-- Bloque de Firmas -->
    <table class="signatures-grid">
        <tr>
            <td>
                <div class="sig-line-td"></div>
                <div class="sig-subtext">POR EL CLIENTE CONTRATANTE</div>
                <div style="text-align: center; font-size: 7.5pt; color: #475569;">{{ $contrato->cliente->nombre_completo }}<br>C.I.: {{ $contrato->cliente->ci_nit ?: '-' }}</div>
            </td>
            <td>
                <div class="sig-line-td"></div>
                <div class="sig-subtext">POR MARIACHI LEÓN GUANAJUATO</div>
                <div style="text-align: center; font-size: 7.5pt; color: #475569;">{{ $empresa->representante_legal }}<br>Director / Representante Legal</div>
            </td>
        </tr>
    </table>

</body>
</html>
