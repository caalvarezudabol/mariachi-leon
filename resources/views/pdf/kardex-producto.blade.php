<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex {{ $asset->codigo }} - {{ $asset->nombre }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 2.0cm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Header Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }

        .header-logo {
            width: 70px;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 65px;
            max-height: 65px;
        }

        .header-company {
            vertical-align: middle;
            padding-left: 10px;
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-subtitle {
            font-size: 7.5pt;
            color: #475569;
            text-transform: uppercase;
            font-weight: bold;
        }

        .company-contact {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 3px;
        }

        .header-document {
            text-align: right;
            vertical-align: middle;
        }

        .doc-title-main {
            font-size: 11pt;
            font-weight: bold;
            color: #b45309;
            text-transform: uppercase;
        }

        .doc-title-sub {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .doc-date {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 4px;
        }

        /* Section Titles */
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            background-color: #f1f5f9;
            padding: 5px 8px;
            border-left: 4px solid #d97706;
            margin-top: 12px;
            margin-bottom: 8px;
        }

        /* Product Info Grid Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table td {
            padding: 4px 6px;
            font-size: 8.5pt;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-label {
            font-weight: bold;
            color: #475569;
            width: 18%;
            background-color: #f8fafc;
        }

        .info-val {
            color: #0f172a;
            width: 32%;
        }

        /* Movements Table */
        .kardex-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
            font-size: 8pt;
        }

        .kardex-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 4px;
            font-size: 7pt;
            text-align: center;
            border: 1px solid #0f172a;
        }

        .kardex-table td {
            padding: 4px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .kardex-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .col-left {
            text-align: left !important;
        }

        .col-right {
            text-align: right !important;
        }

        .tag-entrada {
            color: #15803d;
            font-weight: bold;
        }

        .tag-salida {
            color: #b91c1c;
            font-weight: bold;
        }

        /* Summary Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .summary-table th {
            background-color: #1e293b;
            color: #f8fafc;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px;
            text-align: center;
            border: 1px solid #1e293b;
        }

        .summary-table td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            background-color: #f1f5f9;
        }

        /* Footer & Signatures */
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

        .signature-line {
            width: 70%;
            border-top: 1px solid #0f172a;
            margin: 0 auto 5px auto;
        }

        .signature-name {
            font-weight: bold;
            font-size: 8.5pt;
            color: #0f172a;
        }

        .signature-title {
            font-size: 7.5pt;
            color: #64748b;
        }

        .page-footer {
            position: fixed;
            bottom: -1cm;
            left: 0;
            right: 0;
            height: 0.8cm;
            font-size: 7pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        .pagenum:before {
            content: counter(page);
        }

        .pagecount:before {
            content: counter(pages);
        }
    </style>
</head>
<body>

    <!-- Fixed Footer -->
    <div class="page-footer">
        <div class="footer-left">
            {{ $empresa->nombre_comercial }} • Sistema de Gestión de Activos Fijos e Inventario • Documento generado automáticamente
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
                <div class="company-name">{{ $empresa->nombre_comercial }}</div>
                <div class="company-subtitle">Control de Activos Fijos & Inventario</div>
                <div class="company-contact">
                    {{ $empresa->direccion_fisica }} • Tel: {{ $empresa->telefono_principal }} / {{ $empresa->whatsapp_comercial }}<br>
                    Email: {{ $empresa->email_contacto }}
                </div>
            </td>
            <td class="header-document">
                <div class="doc-title-main">KARDEX VALORIZADO</div>
                <div class="doc-title-sub">KARDEX DE PRODUCTO</div>
                <div class="doc-date">Fecha de Emisión: {{ $fechaEmision }}</div>
            </td>
        </tr>
    </table>

    <!-- Product Details Section -->
    <div class="section-title">INFORMACIÓN DEL PRODUCTO</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Código:</td>
            <td class="info-val"><strong>{{ $asset->codigo }}</strong></td>
            <td class="info-label">Categoría:</td>
            <td class="info-val">{{ $asset->category->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="info-label">Producto:</td>
            <td class="info-val"><strong>{{ $asset->nombre }}</strong></td>
            <td class="info-label">Tipo de Control:</td>
            <td class="info-val">{{ $asset->tipo_control === 'cantidad' ? 'Por cantidad' : 'Individual' }}</td>
        </tr>
        <tr>
            <td class="info-label">Unidad de Medida:</td>
            <td class="info-val">Unidad</td>
            <td class="info-label">Método Costeo:</td>
            <td class="info-val">
                {{ $asset->tipo_control === 'cantidad' ? 'Precio Promedio Ponderado (PPP)' : 'Costo de Adquisición Individual' }}
            </td>
        </tr>
        @if($asset->marca || $asset->modelo || $asset->numero_serie)
        <tr>
            <td class="info-label">Marca / Modelo:</td>
            <td class="info-val">{{ $asset->marca }} {{ $asset->modelo }}</td>
            <td class="info-label">N° de Serie:</td>
            <td class="info-val">{{ $asset->numero_serie ?? 'N/A' }}</td>
        </tr>
        @endif
    </table>

    <!-- Kardex Table Section -->
    <div class="section-title">MOVIMIENTOS DEL KARDEX</div>
    <table class="kardex-table">
        <thead>
            <tr>
                <th style="width: 10%;">Fecha</th>
                <th style="width: 7%;">Hora</th>
                <th style="width: 15%;">Movimiento / Motivo</th>
                <th style="width: 14%;">Usuario Operador</th>
                <th style="width: 8%;">Entrada</th>
                <th style="width: 9%;">Costo Unit.</th>
                <th style="width: 8%;">Salida</th>
                <th style="width: 9%;">Costo Unit.</th>
                <th style="width: 7%;">Saldo</th>
                <th style="width: 8%;">PPP Saldo</th>
                <th style="width: 9%;">Valor Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                <tr>
                    <td>{{ $mov->fecha_movimiento->format('d/m/Y') }}</td>
                    <td>{{ $mov->fecha_movimiento->format('H:i') }}</td>
                    <td class="col-left">
                        @if($mov->tipo_movimiento === 'entrada')
                            <span class="tag-entrada">(+) {{ ucfirst(str_replace('_', ' ', $mov->motivo)) }}</span>
                        @else
                            <span class="tag-salida">(-) {{ ucfirst(str_replace('_', ' ', $mov->motivo)) }}</span>
                        @endif
                    </td>
                    <td class="col-left">{{ $mov->user->name ?? 'Sistema' }}</td>

                    <!-- Entrada -->
                    <td class="tag-entrada">
                        {{ $mov->tipo_movimiento === 'entrada' ? number_format($mov->cantidad, 0, ',', '.') : '-' }}
                    </td>
                    <td class="col-right">
                        {{ $mov->tipo_movimiento === 'entrada' ? 'Bs ' . number_format($mov->costo_unitario, 2, ',', '.') : '-' }}
                    </td>

                    <!-- Salida -->
                    <td class="tag-salida">
                        {{ $mov->tipo_movimiento === 'salida' ? number_format($mov->cantidad, 0, ',', '.') : '-' }}
                    </td>
                    <td class="col-right">
                        {{ $mov->tipo_movimiento === 'salida' ? 'Bs ' . number_format($mov->costo_unitario, 2, ',', '.') : '-' }}
                    </td>

                    <!-- Saldo -->
                    <td style="font-weight: bold;">
                        {{ number_format($mov->cantidad_saldo, 0, ',', '.') }}
                    </td>
                    <td class="col-right">
                        Bs {{ number_format($mov->costo_ppp_saldo, 2, ',', '.') }}
                    </td>
                    <td class="col-right" style="font-weight: bold; color: #0f172a;">
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

    <!-- Summary Section -->
    <div class="section-title">RESUMEN EJECUTIVO</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>TOTAL ENTRADAS</th>
                <th>TOTAL SALIDAS</th>
                <th>SALDO ACTUAL</th>
                <th>PPP ACTUAL</th>
                <th>VALOR ACTUAL INVENTARIO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="color: #15803d;">{{ number_format($totalEntradas, 0, ',', '.') }}</td>
                <td style="color: #b91c1c;">{{ number_format($totalSalidas, 0, ',', '.') }}</td>
                <td style="color: #0f172a;">{{ number_format($saldoActual, 0, ',', '.') }}</td>
                <td style="color: #b45309;">Bs {{ number_format($pppActual, 2, ',', '.') }}</td>
                <td style="color: #0f172a; font-size: 10pt; background-color: #e2e8f0;">
                    Bs {{ number_format($valorInventario, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Observaciones -->
    <div style="font-size: 8pt; color: #475569; margin-top: 15px; background-color: #f8fafc; padding: 6px 10px; border-radius: 4px; border: 1px solid #e2e8f0;">
        <strong>Observaciones:</strong> Documento generado desde el Sistema de Gestión de Mariachi León Guanajuato. La información refleja la totalidad del historial transaccional registrado a la fecha.
    </div>

    <!-- Signatures Section -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $usuarioEmision }}</div>
                <div class="signature-title">Responsable de Emisión / Operador</div>
                <div class="signature-title">{{ $empresa->nombre_comercial }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $empresa->representante_legal }}</div>
                <div class="signature-title">Director / Representante Legal</div>
                <div class="signature-title">{{ $empresa->nombre_comercial }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
