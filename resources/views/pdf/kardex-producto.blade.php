<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Técnico Kardex {{ $asset->codigo }} - {{ $asset->nombre }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.2cm 1.5cm 1.5cm 1.5cm;
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
            height: 0.7cm;
            font-size: 7pt;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
        }

        .footer-left {
            float: left;
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

        /* Top Header Report Card */
        .report-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
        }

        .logo-cell {
            width: 70px;
            vertical-align: middle;
        }

        .logo-cell img {
            max-width: 65px;
            max-height: 65px;
        }

        .company-cell {
            vertical-align: middle;
            padding-left: 8px;
        }

        .comp-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .comp-sub {
            font-size: 7.5pt;
            color: #d97706;
            text-transform: uppercase;
            font-weight: bold;
        }

        .comp-meta {
            font-size: 7pt;
            color: #475569;
            margin-top: 2px;
        }

        .doc-meta-cell {
            text-align: right;
            vertical-align: middle;
        }

        .report-badge {
            display: inline-block;
            background-color: #0f172a;
            color: #fbbf24;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .doc-sub {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* Section Banners */
        .section-banner {
            font-size: 8.5pt;
            font-weight: bold;
            color: #ffffff;
            background-color: #0f172a;
            padding: 4px 8px;
            margin-top: 10px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            border-left: 4px solid #d97706;
            page-break-inside: avoid;
        }

        /* Product Profile Grid */
        .grid-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .grid-info td {
            padding: 3.5px 5px;
            font-size: 7.5pt;
            border: 1px solid #e2e8f0;
        }

        .g-lbl {
            font-weight: bold;
            color: #334155;
            background-color: #f1f5f9;
            width: 17%;
            text-transform: uppercase;
            font-size: 7pt;
        }

        .g-val {
            color: #0f172a;
            width: 33%;
        }

        /* Executive Summary Cards Block */
        .summary-block {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .summary-block th {
            background-color: #1e293b;
            color: #f8fafc;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px;
            text-align: center;
            border: 1px solid #0f172a;
        }

        .summary-block td {
            padding: 5px 3px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-weight: bold;
            font-size: 8.5pt;
            background-color: #ffffff;
        }

        .sum-highlight {
            background-color: #fef3c7 !important;
            color: #78350f !important;
            font-size: 9.5pt !important;
            border: 1.5px solid #d97706 !important;
        }

        /* Movements Table */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 10px;
            font-size: 7.5pt;
        }

        .table-data th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 3px;
            font-size: 6.8pt;
            text-align: center;
            border: 1px solid #0f172a;
        }

        .table-data td {
            padding: 3.5px 3px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
        }

        .table-data tr {
            page-break-inside: avoid;
        }

        .table-data tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .align-l { text-align: left !important; }
        .align-r { text-align: right !important; }
        .fw-bold { font-weight: bold; }

        .txt-entrada { color: #047857; font-weight: bold; }
        .txt-salida { color: #b91c1c; font-weight: bold; }

        /* Notes Box */
        .declaration-box {
            font-size: 7pt;
            color: #475569;
            background-color: #f8fafc;
            padding: 5px 8px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            margin-top: 8px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        /* Protected Signatures Area */
        .signatures-wrapper {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signatures-wrapper td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .s-line {
            width: 60%;
            border-top: 1px solid #0f172a;
            margin: 0 auto 3px auto;
        }

        .s-name {
            font-weight: bold;
            font-size: 8pt;
            color: #0f172a;
        }

        .s-title {
            font-size: 7pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Dynamic Fixed Footer -->
    <div class="page-footer">
        <div class="footer-left">
            <strong>{{ $empresa->nombre_comercial }}</strong> • Informe de Kardex & Valuación de Inventarios
        </div>
        <div class="footer-right">
            Emisión: {{ $fechaEmision }} • Página <span class="pagenum"></span> de <span class="pagecount"></span>
        </div>
    </div>

    <!-- Executive Header -->
    <table class="report-header">
        <tr>
            @if($logoBase64)
                <td class="logo-cell">
                    <img src="{{ $logoBase64 }}" alt="Logo">
                </td>
            @endif
            <td class="company-cell">
                <div class="comp-title">{{ $empresa->nombre_comercial }}</div>
                <div class="comp-sub">Sistema de Auditoría & Control de Activos Fijos</div>
                <div class="comp-meta">
                    {{ $empresa->direccion_fisica }} • Tel: {{ $empresa->telefono_principal }} @if($empresa->whatsapp_comercial)/ Cel: {{ $empresa->whatsapp_comercial }}@endif<br>
                    Email: {{ $empresa->email_contacto }} @if($empresa->nit_ruc)• NIT/RUC: {{ $empresa->nit_ruc }}@endif
                </div>
            </td>
            <td class="doc-meta-cell">
                <div class="report-badge">INFORME DE AUDITORÍA</div>
                <div class="doc-title">KARDEX DE PRODUCTO</div>
                <div class="doc-sub">Código Ref: INF-KDX-{{ $asset->codigo }}</div>
                <div class="doc-sub">Fecha Emisión: {{ $fechaEmision }}</div>
            </td>
        </tr>
    </table>

    <!-- 1. Product Ficha -->
    <div class="section-banner">1. FICHA TÉCNICA E IDENTIFICACIÓN DEL PRODUCTO</div>
    <table class="grid-info">
        <tr>
            <td class="g-lbl">Código Activo:</td>
            <td class="g-val"><strong>{{ $asset->codigo }}</strong></td>
            <td class="g-lbl">Categoría:</td>
            <td class="g-val">{{ $asset->category->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="g-lbl">Nombre Artículo:</td>
            <td class="g-val"><strong>{{ $asset->nombre }}</strong></td>
            <td class="g-lbl">Tipo Control:</td>
            <td class="g-val">
                <span class="fw-bold">{{ $asset->tipo_control === 'cantidad' ? 'Por Cantidad / Lote' : 'Individual / N° Serie' }}</span>
            </td>
        </tr>
        <tr>
            <td class="g-lbl">Unidad Medida:</td>
            <td class="g-val">Unidad (Pza.)</td>
            <td class="g-lbl">Método Valuación:</td>
            <td class="g-val">
                {{ $asset->tipo_control === 'cantidad' ? 'Precio Promedio Ponderado (PPP)' : 'Costo Directo de Adquisición' }}
            </td>
        </tr>
        @if($asset->marca || $asset->modelo || $asset->numero_serie)
        <tr>
            <td class="g-lbl">Marca / Modelo:</td>
            <td class="g-val">{{ $asset->marca }} {{ $asset->modelo }}</td>
            <td class="g-lbl">Número Serie:</td>
            <td class="g-val"><code>{{ $asset->numero_serie ?? 'N/A' }}</code></td>
        </tr>
        @endif
    </table>

    <!-- 2. Executive Summary KPIs -->
    <div class="section-banner">2. RESUMEN EJECUTIVO DE VALUACIÓN E INVENTARIO</div>
    <table class="summary-block">
        <thead>
            <tr>
                <th style="width: 18%;">TOTAL ENTRADAS</th>
                <th style="width: 18%;">TOTAL SALIDAS</th>
                <th style="width: 18%;">STOCK ACTUAL</th>
                <th style="width: 20%;">COSTO PPP ACTUAL</th>
                <th style="width: 26%;">VALOR TOTAL INVENTARIO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="color: #047857;">{{ number_format($totalEntradas, 0, ',', '.') }} Unid.</td>
                <td style="color: #b91c1c;">{{ number_format($totalSalidas, 0, ',', '.') }} Unid.</td>
                <td style="color: #0f172a;">{{ number_format($saldoActual, 0, ',', '.') }} Unid.</td>
                <td style="color: #d97706;">Bs {{ number_format($pppActual, 2, ',', '.') }}</td>
                <td class="sum-highlight">
                    Bs {{ number_format($valorInventario, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 3. Transactions Table -->
    <div class="section-banner">3. DETALLE CRONOLÓGICO DE MOVIMIENTOS REGISTRADOS</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 8%;">Fecha</th>
                <th style="width: 6%;">Hora</th>
                <th style="width: 18%;">Movimiento / Motivo</th>
                <th style="width: 14%;">Usuario Operador</th>
                <th style="width: 7%;">Entrada</th>
                <th style="width: 9%;">Costo U.</th>
                <th style="width: 7%;">Salida</th>
                <th style="width: 9%;">Costo U.</th>
                <th style="width: 7%;">Saldo</th>
                <th style="width: 8%;">PPP</th>
                <th style="width: 9%;">Total (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                <tr>
                    <td>{{ $mov->fecha_movimiento->format('d/m/Y') }}</td>
                    <td>{{ $mov->fecha_movimiento->format('H:i') }}</td>
                    <td class="align-l">
                        @if($mov->tipo_movimiento === 'entrada')
                            <span class="txt-entrada">(+) {{ ucfirst(str_replace('_', ' ', $mov->motivo)) }}</span>
                        @else
                            <span class="txt-salida">(-) {{ ucfirst(str_replace('_', ' ', $mov->motivo)) }}</span>
                        @endif
                    </td>
                    <td class="align-l">{{ $mov->user->name ?? 'Sistema' }}</td>

                    <!-- Entrada -->
                    <td class="txt-entrada">
                        {{ $mov->tipo_movimiento === 'entrada' ? number_format($mov->cantidad, 0, ',', '.') : '-' }}
                    </td>
                    <td class="align-r">
                        {{ $mov->tipo_movimiento === 'entrada' ? 'Bs ' . number_format($mov->costo_unitario, 2, ',', '.') : '-' }}
                    </td>

                    <!-- Salida -->
                    <td class="txt-salida">
                        {{ $mov->tipo_movimiento === 'salida' ? number_format($mov->cantidad, 0, ',', '.') : '-' }}
                    </td>
                    <td class="align-r">
                        {{ $mov->tipo_movimiento === 'salida' ? 'Bs ' . number_format($mov->costo_unitario, 2, ',', '.') : '-' }}
                    </td>

                    <!-- Saldo -->
                    <td class="fw-bold">
                        {{ number_format($mov->cantidad_saldo, 0, ',', '.') }}
                    </td>
                    <td class="align-r">
                        Bs {{ number_format($mov->costo_ppp_saldo, 2, ',', '.') }}
                    </td>
                    <td class="align-r fw-bold" style="color: #0f172a;">
                        Bs {{ number_format($mov->valor_total_saldo, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: #64748b; padding: 10px;">
                        No se registran movimientos en el Kardex para este producto.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Declaration Box -->
    <div class="declaration-box">
        <strong>Declaración de Autenticidad:</strong> El presente informe de Kardex constituye un reporte oficial emitido por el sistema informático de {{ $empresa->nombre_comercial }}. La información contenida refleja de forma fiel y auditable la totalidad de los registros de almacén e inventarios a la fecha de emisión.
    </div>

    <!-- Protected Signatures Area -->
    <table class="signatures-wrapper">
        <tr>
            <td>
                <div class="s-line"></div>
                <div class="s-name">{{ $usuarioEmision }}</div>
                <div class="s-title">Responsable de Emisión / Operador</div>
                <div class="s-title">{{ $empresa->nombre_comercial }}</div>
            </td>
            <td>
                <div class="s-line"></div>
                <div class="s-name">{{ $empresa->representante_legal }}</div>
                <div class="s-title">Director / Representante Legal</div>
                <div class="s-title">{{ $empresa->nombre_comercial }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
