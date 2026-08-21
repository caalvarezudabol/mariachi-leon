<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex {{ $asset->codigo }} - {{ $asset->nombre }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.2cm 1.4cm 1.4cm 1.4cm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #0f172a;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Fixed Footer */
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

        .footer-table td {
            vertical-align: middle;
            font-size: 7pt;
        }

        .pagenum:before {
            content: counter(page);
        }

        .pagecount:before {
            content: counter(pages);
        }

        /* Header Document Table */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .logo-col {
            width: 25%;
            vertical-align: middle;
            text-align: left;
        }

        .logo-col img {
            max-width: 110px;
            max-height: 85px;
            object-fit: contain;
        }

        .title-col {
            width: 75%;
            vertical-align: middle;
            text-align: center;
            padding-right: 10%;
        }

        .main-brand-name {
            font-size: 15pt;
            font-weight: 800;
            color: #0b1329;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .main-sub-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 2px;
        }

        .doc-type-title {
            font-size: 13pt;
            font-weight: 800;
            color: #0b1329;
            text-transform: uppercase;
            margin-top: 6px;
            letter-spacing: 0.5px;
        }

        /* Decorative Gold Divider */
        .gold-line-container {
            text-align: center;
            margin: 4px auto 10px auto;
            width: 80%;
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

        /* Product & Info Box (2 Columns) */
        .info-box {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 12px;
            border-collapse: collapse;
            background-color: #ffffff;
        }

        .info-box-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box-table td {
            padding: 4px 8px;
            font-size: 8pt;
            vertical-align: top;
        }

        .info-left-col {
            width: 50%;
            border-right: 1px solid #cbd5e1;
        }

        .info-right-col {
            width: 50%;
        }

        .info-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .info-row td {
            padding: 2.5px 2px;
        }

        .lbl-title {
            font-weight: bold;
            color: #0f172a;
            width: 38%;
        }

        .lbl-val {
            color: #1e293b;
            width: 62%;
        }

        /* Section Banners Dark Navy */
        .section-navy-banner {
            background-color: #0b1a30;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 5px 0;
            border-radius: 3px;
            margin-top: 10px;
            margin-bottom: 6px;
            letter-spacing: 0.8px;
            page-break-inside: avoid;
        }

        /* Movements Table */
        .kardex-main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }

        .kardex-main-table th {
            background-color: #ffffff;
            color: #0f172a;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 3px;
            font-size: 7pt;
            text-align: center;
            border: 1px solid #cbd5e1;
        }

        .kardex-main-table td {
            padding: 4px 3px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
            background-color: #ffffff;
        }

        .kardex-main-table tr {
            page-break-inside: avoid;
        }

        .align-left { text-align: left !important; padding-left: 4px !important; }
        .align-right { text-align: right !important; padding-right: 4px !important; }
        .fw-bld { font-weight: bold; }

        /* Summary KPI 5 Cards Box */
        .summary-cards-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
            page-break-inside: avoid;
        }

        .summary-cards-table td {
            width: 20%;
            text-align: center;
            vertical-align: middle;
            padding: 8px 3px;
            border-right: 1px solid #cbd5e1;
        }

        .summary-cards-table td:last-child {
            border-right: none;
        }

        .kpi-label {
            font-size: 7pt;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        .kpi-label-red { color: #dc2626; }
        .kpi-label-dark { color: #0f172a; }

        .kpi-value-num {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
        }

        .kpi-unit-sub {
            font-size: 6.5pt;
            color: #64748b;
        }

        /* Observations Box */
        .obs-container {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 7.5pt;
            color: #334155;
            margin-bottom: 25px;
            background-color: #ffffff;
            page-break-inside: avoid;
        }

        .obs-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        /* Signatures Area */
        .signatures-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .signatures-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0 15px;
        }

        .sig-row-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-label-td {
            width: 35%;
            font-weight: bold;
            font-size: 7.5pt;
            color: #0f172a;
            vertical-align: bottom;
            padding-bottom: 2px;
        }

        .sig-line-td {
            width: 65%;
            border-bottom: 1px solid #0f172a;
            vertical-align: bottom;
            height: 25px;
        }

        .sig-subtext {
            text-align: center;
            font-size: 7pt;
            color: #64748b;
            margin-top: 3px;
        }
    </style>
</head>
<body>

    <!-- Fixed Footer -->
    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td style="width: 45%; text-align: left;">
                    <div style="font-weight: bold; color: #0f172a;">Mariachi León Guanajuato</div>
                    <div>Sistema de Gestión de Activos Fijos e Inventario</div>
                    <div style="font-size: 6.5pt; color: #94a3b8;">Documento generado automáticamente</div>
                </td>
                <td style="width: 30%; text-align: center;">
                    <div style="font-weight: bold; color: #334155;">Fecha y hora de impresión:</div>
                    <div>{{ $fechaEmision }}</div>
                </td>
                <td style="width: 25%; text-align: right; font-weight: bold; color: #0f172a;">
                    <!-- Se inyecta dinámicamente el texto mediante el script PHP de DomPDF -->
                </td>
            </tr>
        </table>
    </div>

    <!-- Script PHP de DomPDF para numeración exacta de páginas (Página 1 de 1, Página 1 de 2, etc.) -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("DejaVu Sans", "bold");
            $size = 7;
            $color = array(0.06, 0.09, 0.16); // #0f172a
            $pdf->page_text(495, 756, $text, $font, $size, $color);
        }
    </script>

    <!-- Header Section -->
    <table class="top-header-table">
        <tr>
            <td class="logo-col">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </td>
            <td class="title-col">
                <div class="main-brand-name">{{ $empresa->nombre_comercial }}</div>
                <div class="main-sub-title">CONTROL DE ACTIVOS FIJOS E INVENTARIO</div>
                <div class="doc-type-title">KARDEX DE PRODUCTO</div>
            </td>
        </tr>
    </table>

    <!-- Decorative Gold Divider Line -->
    <div class="gold-line-container">
        <table class="gold-line-table">
            <tr>
                <td><div class="gold-line-hr"></div></td>
                <td class="gold-line-diamond">◆</td>
                <td><div class="gold-line-hr"></div></td>
            </tr>
        </table>
    </div>

    <!-- Info Box 2 Columns -->
    <div class="info-box">
        <table class="info-box-table">
            <tr>
                <!-- Left Column -->
                <td class="info-left-col">
                    <table class="info-row">
                        <tr>
                            <td class="lbl-title">Código:</td>
                            <td class="lbl-val"><strong>{{ $asset->codigo }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Producto:</td>
                            <td class="lbl-val"><strong>{{ $asset->nombre }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Categoría:</td>
                            <td class="lbl-val">{{ $asset->category->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Tipo de control:</td>
                            <td class="lbl-val">{{ $asset->tipo_control === 'cantidad' ? 'Por cantidad' : 'Individual' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Unidad:</td>
                            <td class="lbl-val">Unidad</td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Método de costeo:</td>
                            <td class="lbl-val">
                                {{ $asset->tipo_control === 'cantidad' ? 'Precio Promedio Ponderado (PPP)' : 'Costo de Adquisición Individual' }}
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- Right Column -->
                <td class="info-right-col">
                    <table class="info-row">
                        <tr>
                            <td class="lbl-title">Fecha de generación:</td>
                            <td class="lbl-val">{{ now()->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Usuario:</td>
                            <td class="lbl-val">{{ $usuarioEmision }}</td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Empresa:</td>
                            <td class="lbl-val">{{ $empresa->nombre_comercial }}</td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Teléfono:</td>
                            <td class="lbl-val">{{ $empresa->telefono_principal }} @if($empresa->whatsapp_comercial)/ {{ $empresa->whatsapp_comercial }}@endif</td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Dirección:</td>
                            <td class="lbl-val">{{ $empresa->direccion_fisica }} @if($empresa->ciudad_pais), {{ $empresa->ciudad_pais }}@endif</td>
                        </tr>
                        <tr>
                            <td class="lbl-title">Correo:</td>
                            <td class="lbl-val">{{ $empresa->email_contacto }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Section 1 Banner: Movements -->
    <div class="section-navy-banner">MOVIMIENTOS DEL KARDEX</div>

    <!-- Movements Main Table -->
    <table class="kardex-main-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 9%;">Fecha</th>
                <th rowspan="2" style="width: 6%;">Hora</th>
                <th rowspan="2" style="width: 10%;">Movimiento</th>
                <th rowspan="2" style="width: 12%;">Motivo</th>
                <th rowspan="2" style="width: 14%;">Usuario</th>
                <th colspan="2" style="width: 18%;">Entrada</th>
                <th colspan="2" style="width: 18%;">Salida</th>
                <th rowspan="2" style="width: 5%;">Saldo</th>
                <th rowspan="2" style="width: 8%;">PPP</th>
                <th rowspan="2" style="width: 10%;">Valor Saldo</th>
            </tr>
            <tr>
                <th style="width: 8%;">Cantidad</th>
                <th style="width: 10%;">Costo Unit.</th>
                <th style="width: 8%;">Cantidad</th>
                <th style="width: 10%;">Costo Unit.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                <tr>
                    <td>{{ $mov->fecha_movimiento->format('d/m/Y') }}</td>
                    <td>{{ $mov->fecha_movimiento->format('H:i') }}</td>
                    <td>{{ ucfirst($mov->tipo_movimiento) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $mov->motivo)) }}</td>
                    <td class="align-left">{{ $mov->user->name ?? 'Sistema' }}</td>

                    <!-- Entrada -->
                    <td>
                        {{ $mov->tipo_movimiento === 'entrada' ? number_format($mov->cantidad, 0, ',', '.') : '-' }}
                    </td>
                    <td class="align-right">
                        {{ $mov->tipo_movimiento === 'entrada' ? 'Bs ' . number_format($mov->costo_unitario, 2, ',', '.') : '-' }}
                    </td>

                    <!-- Salida -->
                    <td>
                        {{ $mov->tipo_movimiento === 'salida' ? number_format($mov->cantidad, 0, ',', '.') : '-' }}
                    </td>
                    <td class="align-right">
                        {{ $mov->tipo_movimiento === 'salida' ? 'Bs ' . number_format($mov->costo_unitario, 2, ',', '.') : '-' }}
                    </td>

                    <!-- Saldo & Valuación -->
                    <td class="fw-bld">
                        {{ number_format($mov->cantidad_saldo, 0, ',', '.') }}
                    </td>
                    <td class="align-right">
                        Bs {{ number_format($mov->costo_ppp_saldo, 2, ',', '.') }}
                    </td>
                    <td class="align-right fw-bld">
                        Bs {{ number_format($mov->valor_total_saldo, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; color: #64748b; padding: 10px;">
                        No existen movimientos registrados para este producto.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Section 2 Banner: Summary -->
    <div class="section-navy-banner">RESUMEN</div>

    <!-- Summary KPI 5 Cards Box -->
    <table class="summary-cards-table">
        <tr>
            <!-- KPI 1: TOTAL ENTRADAS -->
            <td>
                <div class="kpi-label">TOTAL ENTRADAS</div>
                <div class="kpi-value-num">{{ number_format($totalEntradas, 0, ',', '.') }}</div>
                <div class="kpi-unit-sub">unidades</div>
            </td>

            <!-- KPI 2: TOTAL SALIDAS -->
            <td>
                <div class="kpi-label kpi-label-red">TOTAL SALIDAS</div>
                <div class="kpi-value-num">{{ number_format($totalSalidas, 0, ',', '.') }}</div>
                <div class="kpi-unit-sub">unidades</div>
            </td>

            <!-- KPI 3: SALDO ACTUAL -->
            <td>
                <div class="kpi-label">SALDO ACTUAL</div>
                <div class="kpi-value-num">{{ number_format($saldoActual, 0, ',', '.') }}</div>
                <div class="kpi-unit-sub">unidades</div>
            </td>

            <!-- KPI 4: PPP ACTUAL -->
            <td>
                <div class="kpi-label">PPP ACTUAL</div>
                <div class="kpi-value-num" style="font-size: 10pt;">Bs {{ number_format($pppActual, 2, ',', '.') }}</div>
            </td>

            <!-- KPI 5: VALOR ACTUAL DEL INVENTARIO -->
            <td>
                <div class="kpi-label kpi-label-dark">VALOR ACTUAL DEL INVENTARIO</div>
                <div class="kpi-value-num" style="font-size: 10.5pt; color: #0b1a30;">Bs {{ number_format($valorInventario, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Observations -->
    <div class="obs-container">
        <div class="obs-title">OBSERVACIONES:</div>
        <div>Documento generado desde el Sistema de Gestión de Mariachi León Guanajuato.</div>
    </div>

    <!-- Signatures Grid -->
    <table class="signatures-grid">
        <tr>
            <td>
                <table class="sig-row-table">
                    <tr>
                        <td class="sig-label-td">Responsable de emisión:</td>
                        <td class="sig-line-td"></td>
                    </tr>
                </table>
                <div class="sig-subtext">Nombre y Apellido</div>
            </td>
            <td>
                <table class="sig-row-table">
                    <tr>
                        <td class="sig-label-td">Firma:</td>
                        <td class="sig-line-td"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
