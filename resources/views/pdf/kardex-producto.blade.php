<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex Valorizado - {{ $asset->codigo }} - {{ $asset->nombre }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.8cm 1.8cm 1.8cm 1.8cm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #0f172a;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* Fixed Footer */
        .page-footer {
            position: fixed;
            bottom: -1.2cm;
            left: 0;
            right: 0;
            height: 0.8cm;
            font-size: 7.5pt;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
        }

        .footer-left {
            float: left;
            font-weight: 500;
        }

        .footer-right {
            float: right;
            font-weight: bold;
        }

        .pagenum:before {
            content: counter(page);
        }

        .pagecount:before {
            content: counter(pages);
        }

        /* Header Document Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }

        .header-logo {
            width: 75px;
            vertical-align: middle;
            text-align: left;
        }

        .header-logo img {
            max-width: 70px;
            max-height: 70px;
        }

        .header-company {
            vertical-align: middle;
            padding-left: 8px;
        }

        .company-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-subtitle {
            font-size: 8pt;
            color: #d97706;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.8px;
        }

        .company-contact {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }

        .header-document {
            text-align: right;
            vertical-align: middle;
        }

        .doc-badge {
            display: inline-block;
            background-color: #0f172a;
            color: #fbbf24;
            font-size: 8pt;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .doc-title-main {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .doc-date {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 3px;
        }

        /* Section Banners */
        .section-header {
            font-size: 9pt;
            font-weight: bold;
            color: #ffffff;
            background-color: #0f172a;
            padding: 5px 8px;
            margin-top: 14px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            border-left: 4px solid #f59e0b;
        }

        /* Information Grid */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-grid td {
            padding: 4px 6px;
            font-size: 8pt;
            border-bottom: 1px solid #e2e8f0;
        }

        .lbl {
            font-weight: bold;
            color: #475569;
            width: 18%;
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 7.5pt;
        }

        .val {
            color: #0f172a;
            width: 32%;
        }

        /* Movements Table */
        .kardex-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
            font-size: 7.5pt;
        }

        .kardex-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 4px;
            font-size: 7pt;
            text-align: center;
            border: 1px solid #0f172a;
        }

        .kardex-table td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
        }

        .kardex-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .txt-left {
            text-align: left !important;
        }

        .txt-right {
            text-align: right !important;
        }

        .txt-bold {
            font-weight: bold;
        }

        .tag-entrada {
            color: #047857;
            font-weight: bold;
        }

        .tag-salida {
            color: #b91c1c;
            font-weight: bold;
        }

        /* Executive Summary Box */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        .summary-table th {
            background-color: #0f172a;
            color: #fbbf24;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px;
            text-align: center;
            border: 1px solid #0f172a;
        }

        .summary-table td {
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            background-color: #f8fafc;
        }

        .summary-total {
            background-color: #fef3c7 !important;
            color: #92400e !important;
            font-size: 10pt !important;
            border: 1.5px solid #d97706 !important;
        }

        /* Notes Box */
        .notes-box {
            font-size: 7.5pt;
            color: #475569;
            background-color: #f8fafc;
            padding: 6px 8px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        /* Signatures Area */
        .signatures-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }

        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .sig-line {
            width: 65%;
            border-top: 1px solid #0f172a;
            margin: 0 auto 4px auto;
        }

        .sig-name {
            font-weight: bold;
            font-size: 8.5pt;
            color: #0f172a;
        }

        .sig-title {
            font-size: 7.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Fixed Page Footer -->
    <div class="page-footer">
        <div class="footer-left">
            <strong>{{ $empresa->nombre_comercial }}</strong> • Sistema de Control de Activos Fijos e Inventario
        </div>
        <div class="footer-right">
            Impreso: {{ $fechaEmision }} • Página <span class="pagenum"></span> de <span class="pagecount"></span>
        </div>
    </div>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            @if($logoBase64)
                <td class="header-logo">
                    <img src="{{ $logoBase64 }}" alt="Logo">
                </td>
            @endif
            <td class="header-company">
                <div class="company-title">{{ $empresa->nombre_comercial }}</div>
                <div class="company-subtitle">Control de Activos Fijos & Inventario</div>
                <div class="company-contact">
                    {{ $empresa->direccion_fisica }} • Tel: {{ $empresa->telefono_principal }} @if($empresa->whatsapp_comercial)/ Cel: {{ $empresa->whatsapp_comercial }}@endif<br>
                    Email: {{ $empresa->email_contacto }} @if($empresa->nit_ruc)• NIT/RUC: {{ $empresa->nit_ruc }}@endif
                </div>
            </td>
            <td class="header-document">
                <div class="doc-badge">DOCUMENTO OFICIAL</div>
                <div class="doc-title-main">KARDEX VALORIZADO</div>
                <div class="doc-date">Emisión: {{ $fechaEmision }}</div>
            </td>
        </tr>
    </table>

    <!-- Product Details Section -->
    <div class="section-header">1. IDENTIFICACIÓN Y FICHA TÉCNICA DEL PRODUCTO</div>
    <table class="info-grid">
        <tr>
            <td class="lbl">Código Activo:</td>
            <td class="val"><strong>{{ $asset->codigo }}</strong></td>
            <td class="lbl">Categoría:</td>
            <td class="val">{{ $asset->category->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="lbl">Producto / Artículo:</td>
            <td class="val"><strong>{{ $asset->nombre }}</strong></td>
            <td class="lbl">Tipo de Control:</td>
            <td class="val">
                <span class="txt-bold">{{ $asset->tipo_control === 'cantidad' ? 'Por Cantidad / Lote' : 'Individual / Serie' }}</span>
            </td>
        </tr>
        <tr>
            <td class="lbl">Unidad de Medida:</td>
            <td class="val">Unidad (Pza.)</td>
            <td class="lbl">Método Valuación:</td>
            <td class="val">
                {{ $asset->tipo_control === 'cantidad' ? 'Precio Promedio Ponderado (PPP)' : 'Costo Directo Individual' }}
            </td>
        </tr>
        @if($asset->marca || $asset->modelo || $asset->numero_serie)
        <tr>
            <td class="lbl">Marca / Modelo:</td>
            <td class="val">{{ $asset->marca }} {{ $asset->modelo }}</td>
            <td class="lbl">Número de Serie:</td>
            <td class="val"><code>{{ $asset->numero_serie ?? 'N/A' }}</code></td>
        </tr>
        @endif
    </table>

    <!-- Kardex Movements Section -->
    <div class="section-header">2. HISTORIAL COMPLETO DE MOVIMIENTOS REGISTRADOS EN EL KARDEX</div>
    <table class="kardex-table">
        <thead>
            <tr>
                <th style="width: 9%;">Fecha</th>
                <th style="width: 6%;">Hora</th>
                <th style="width: 17%;">Movimiento / Motivo</th>
                <th style="width: 15%;">Usuario Operador</th>
                <th style="width: 8%;">Entrada</th>
                <th style="width: 10%;">Costo Unit.</th>
                <th style="width: 8%;">Salida</th>
                <th style="width: 10%;">Costo Unit.</th>
                <th style="width: 7%;">Saldo</th>
                <th style="width: 10%;">PPP Saldo</th>
                <th style="width: 10%;">Valor Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                <tr>
                    <td>{{ $mov->fecha_movimiento->format('d/m/Y') }}</td>
                    <td>{{ $mov->fecha_movimiento->format('H:i') }}</td>
                    <td class="txt-left">
                        @if($mov->tipo_movimiento === 'entrada')
                            <span class="tag-entrada">(+) {{ ucfirst(str_replace('_', ' ', $mov->motivo)) }}</span>
                        @else
                            <span class="tag-salida">(-) {{ ucfirst(str_replace('_', ' ', $mov->motivo)) }}</span>
                        @endif
                    </td>
                    <td class="txt-left">{{ $mov->user->name ?? 'Sistema' }}</td>

                    <!-- Entrada -->
                    <td class="tag-entrada">
                        {{ $mov->tipo_movimiento === 'entrada' ? number_format($mov->cantidad, 0, ',', '.') : '-' }}
                    </td>
                    <td class="txt-right">
                        {{ $mov->tipo_movimiento === 'entrada' ? 'Bs ' . number_format($mov->costo_unitario, 2, ',', '.') : '-' }}
                    </td>

                    <!-- Salida -->
                    <td class="tag-salida">
                        {{ $mov->tipo_movimiento === 'salida' ? number_format($mov->cantidad, 0, ',', '.') : '-' }}
                    </td>
                    <td class="txt-right">
                        {{ $mov->tipo_movimiento === 'salida' ? 'Bs ' . number_format($mov->costo_unitario, 2, ',', '.') : '-' }}
                    </td>

                    <!-- Saldo -->
                    <td class="txt-bold">
                        {{ number_format($mov->cantidad_saldo, 0, ',', '.') }}
                    </td>
                    <td class="txt-right">
                        Bs {{ number_format($mov->costo_ppp_saldo, 2, ',', '.') }}
                    </td>
                    <td class="txt-right txt-bold" style="color: #0f172a;">
                        Bs {{ number_format($mov->valor_total_saldo, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: #64748b; padding: 12px;">
                        No existen movimientos registrados para este producto en el Kardex.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Executive Summary Section -->
    <div class="section-header">3. RESUMEN EJECUTIVO Y CONSOLIDADO DE INVENTARIO</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>TOTAL ENTRADAS</th>
                <th>TOTAL SALIDAS</th>
                <th>SALDO ACTUAL STOCK</th>
                <th>COSTO PPP ACTUAL</th>
                <th>VALOR TOTAL INVENTARIO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="color: #047857;">{{ number_format($totalEntradas, 0, ',', '.') }} Unid.</td>
                <td style="color: #b91c1c;">{{ number_format($totalSalidas, 0, ',', '.') }} Unid.</td>
                <td style="color: #0f172a;">{{ number_format($saldoActual, 0, ',', '.') }} Unid.</td>
                <td style="color: #d97706;">Bs {{ number_format($pppActual, 2, ',', '.') }}</td>
                <td class="summary-total">
                    Bs {{ number_format($valorInventario, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Observations -->
    <div class="notes-box">
        <strong>Nota Institucional:</strong> Documento oficial emitido por el Sistema de Gestión de Mariachi León Guanajuato. La información refleja con fidelidad la totalidad del historial transaccional registrado hasta la fecha de emisión.
    </div>

    <!-- Signatures Section -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $usuarioEmision }}</div>
                <div class="sig-title">Responsable de Emisión / Operador</div>
                <div class="sig-title">{{ $empresa->nombre_comercial }}</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $empresa->representante_legal }}</div>
                <div class="sig-title">Director / Representante Legal</div>
                <div class="sig-title">{{ $empresa->nombre_comercial }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
