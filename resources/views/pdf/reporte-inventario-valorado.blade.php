<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Consolidado de Inventario y Valuación de Activos</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.2cm 1.4cm 1.4cm 1.4cm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #0f172a;
            line-height: 1.35;
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

        /* Top Header Table */
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
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 2px;
        }

        .doc-type-title {
            font-size: 11.5pt;
            font-weight: 800;
            color: #d97706;
            text-transform: uppercase;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        /* Gold Divider Line */
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

        /* Section Banners */
        .section-banner {
            background-color: #0b1a30;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 3px;
            margin-top: 12px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            border-left: 4px solid #d97706;
            page-break-inside: avoid;
        }

        /* Summary KPI Cards Table */
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

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }

        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 4px;
            font-size: 6.8pt;
            text-align: center;
            border: 1px solid #0f172a;
        }

        .data-table td {
            padding: 4px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .align-left { text-align: left !important; padding-left: 5px !important; }
        .align-right { text-align: right !important; padding-right: 5px !important; }
        .fw-bold { font-weight: bold; }

        /* Signatures Grid */
        .signatures-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
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

        .total-row td {
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 8pt;
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
                    <div>Informe Consolidado de Inventario & Valoración General</div>
                    <div style="font-size: 6.5pt; color: #94a3b8;">Documento oficial generado desde el sistema</div>
                </td>
                <td style="width: 30%; text-align: center;">
                    <div style="font-weight: bold; color: #334155;">Fecha de Emisión:</div>
                    <div>{{ $fechaEmision }}</div>
                </td>
                <td style="width: 25%; text-align: right; font-weight: bold; color: #0f172a;">
                    <!-- Inyectado por DomPDF -->
                </td>
            </tr>
        </table>
    </div>

    <!-- Script PHP DomPDF para numeración de páginas -->
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
                @endif
            </td>
            <td class="title-col">
                <div class="main-brand-name">{{ $empresa->nombre_comercial }}</div>
                <div class="main-sub-title">CONTROL DE ACTIVOS FIJOS E INVENTARIOS</div>
                <div class="doc-type-title">INFORME CONSOLIDADO DE STOCK Y VALORACIÓN DE INVENTARIO</div>
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

    <!-- 1. Resumen Ejecutivo KPIs -->
    <div class="section-banner">1. RESUMEN EJECUTIVO CONSOLIDADO DE INVENTARIO</div>
    <table class="summary-cards-table">
        <tr>
            <td>
                <div class="kpi-label">TOTAL ARTÍCULOS</div>
                <div class="kpi-value-num">{{ count($articulos) }}</div>
                <div class="kpi-unit-sub">registrados</div>
            </td>
            <td>
                <div class="kpi-label">STOCK FÍSICO TOTAL</div>
                <div class="kpi-value-num">{{ number_format($stockTotalUnidades, 0, ',', '.') }}</div>
                <div class="kpi-unit-sub">unidades en almacén</div>
            </td>
            <td>
                <div class="kpi-label">CATEGORÍAS</div>
                <div class="kpi-value-num">{{ count($resumenCategorias) }}</div>
                <div class="kpi-unit-sub">activas</div>
            </td>
            <td>
                <div class="kpi-label">MÉTODO VALUACIÓN</div>
                <div class="kpi-value-num" style="font-size: 8.5pt;">P.P.P.</div>
                <div class="kpi-unit-sub">Promedio Ponderado</div>
            </td>
            <td>
                <div class="kpi-label kpi-label-dark">VALOR TOTAL INVENTARIO</div>
                <div class="kpi-value-num" style="font-size: 10.5pt; color: #0b1a30;">Bs {{ number_format($valorTotalGeneral, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- 2. Desglose por Categorías -->
    <div class="section-banner">2. VALORACIÓN DE INVENTARIO POR CATEGORÍA</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Categoría de Activo</th>
                <th style="width: 15%;">Cant. Ítems</th>
                <th style="width: 20%;">Stock Total (Unid.)</th>
                <th style="width: 20%;">Valorización (Bs)</th>
                <th style="width: 10%;">% del Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumenCategorias as $cat)
                <tr>
                    <td class="align-left fw-bold">{{ $cat['nombre'] }}</td>
                    <td>{{ $cat['cant_articulos'] }} ítem(s)</td>
                    <td>{{ number_format($cat['stock_total'], 0, ',', '.') }} Unid.</td>
                    <td class="align-right fw-bold">Bs {{ number_format($cat['valor_total'], 2, ',', '.') }}</td>
                    <td class="align-right">
                        {{ $valorTotalGeneral > 0 ? number_format(($cat['valor_total'] / $valorTotalGeneral) * 100, 1, ',', '.') : 0 }}%
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td class="align-left">TOTALES CONSOLIDADOS</td>
                <td>{{ count($articulos) }} ítem(s)</td>
                <td>{{ number_format($stockTotalUnidades, 0, ',', '.') }} Unid.</td>
                <td class="align-right">Bs {{ number_format($valorTotalGeneral, 2, ',', '.') }}</td>
                <td class="align-right">100.0%</td>
            </tr>
        </tbody>
    </table>

    <!-- 3. Detalle Individual por Artículo -->
    <div class="section-banner">3. DETALLE INDIVIDUAL DE STOCK Y COSTO PPP POR ARTÍCULO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Código</th>
                <th style="width: 25%;">Artículo / Producto</th>
                <th style="width: 16%;">Categoría</th>
                <th style="width: 12%;">Tipo Control</th>
                <th style="width: 10%;">Stock Actual</th>
                <th style="width: 13%;">Costo Unit. (PPP)</th>
                <th style="width: 16%;">Valor Total Stock (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($articulos as $art)
                <tr>
                    <td class="fw-bold" style="color: #0b1a30;">{{ $art->codigo }}</td>
                    <td class="align-left fw-bold">{{ $art->nombre }}</td>
                    <td class="align-left">{{ $art->category->nombre ?? 'Sin Categoría' }}</td>
                    <td>{{ $art->tipo_control === 'cantidad' ? 'Por Cantidad' : 'Individual' }}</td>
                    <td class="fw-bold">{{ number_format($art->stock_actual, 2, ',', '.') }} Unid.</td>
                    <td class="align-right">Bs {{ number_format($art->costo_ppp, 2, ',', '.') }}</td>
                    <td class="align-right fw-bold" style="color: #0b1a30;">
                        Bs {{ number_format($art->valor_total_inventario, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 10px;">
                        No existen artículos ni activos registrados en el sistema.
                    </td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" class="align-left">TOTAL DE VALORACIÓN FÍSICA Y MONETARIA</td>
                <td>{{ number_format($stockTotalUnidades, 0, ',', '.') }} Unid.</td>
                <td class="align-right">-</td>
                <td class="align-right">Bs {{ number_format($valorTotalGeneral, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Declaración de Conformidad -->
    <div style="font-size: 7.5pt; color: #334155; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; margin-top: 10px; page-break-inside: avoid;">
        <strong>Declaración de Auditoría de Almacén:</strong> El presente informe certíﬁca los saldos físicos y la valoración monetaria del inventario perteneciente a <strong>{{ $empresa->nombre_comercial }}</strong> a la fecha de emisión. La información reportada responde fielmente al catálogo de activos fijos del sistema.
    </div>

    <!-- Signatures Area -->
    <table class="signatures-grid">
        <tr>
            <td>
                <table class="sig-row-table">
                    <tr>
                        <td class="sig-label-td">Responsable de Almacén:</td>
                        <td class="sig-line-td"></td>
                    </tr>
                </table>
                <div class="sig-subtext">{{ $usuarioEmision }}</div>
            </td>
            <td>
                <table class="sig-row-table">
                    <tr>
                        <td class="sig-label-td">Director / Rep. Legal:</td>
                        <td class="sig-line-td"></td>
                    </tr>
                </table>
                <div class="sig-subtext">{{ $empresa->representante_legal }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
