<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resumen del mes - {{ $mes }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 16mm 18mm 16mm 18mm;
        }

        body {
            font-family: 'DejaVu Serif', 'Times New Roman', serif;
            font-size: 9pt;
            color: #111;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .head {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #111;
            margin-bottom: 10px;
        }

        .head td {
            vertical-align: middle;
            padding-bottom: 6px;
        }

        .empresa {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sub {
            font-size: 7.5pt;
            color: #444;
        }

        .titulo {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 8px 0 2px 0;
        }

        .ref {
            text-align: center;
            font-size: 8pt;
            color: #444;
            margin-bottom: 10px;
        }

        .resumen {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .resumen td {
            width: 33.33%;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #111;
        }

        .resumen .k {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #444;
        }

        .resumen .v {
            font-size: 11pt;
            font-weight: bold;
        }

        .sec {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 0.75px solid #111;
            padding-bottom: 2px;
            margin: 12px 0 4px 0;
        }

        table.t {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        table.t th {
            text-align: left;
            font-weight: bold;
            border-bottom: 0.75px solid #111;
            padding: 3px 4px;
            font-size: 7.5pt;
            text-transform: uppercase;
        }

        table.t td {
            padding: 3px 4px;
            border-bottom: 0.5px solid #ccc;
        }

        table.t tr.tot td {
            border-top: 0.75px solid #111;
            border-bottom: none;
            font-weight: bold;
        }

        .r {
            text-align: right;
        }

        .pie {
            margin-top: 16px;
            border-top: 0.75px solid #111;
            padding-top: 5px;
            font-size: 7.5pt;
            color: #444;
        }
    </style>
</head>

<body>
    @php
        $nombreEmpresa = $empresa->razon_social ?? ($empresa->nombre_comercial ?? 'Mariachi León Guanajuato');
        $lineaEmpresa = collect([
            !empty($empresa->nit_ruc) ? 'NIT: ' . $empresa->nit_ruc : null,
            $empresa->ciudad_pais ?? null,
        ])
            ->filter()
            ->implode(' · ');
        $ingresos = $cobros + $ingresosExtra;
        $egresos = $gastos + $personal;
        $resultado = $ingresos - $egresos;
    @endphp

    <table class="head">
        <tr>
            <td style="width: 22%;">
                @if (!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 80px; max-height: 48px;">
                @endif
            </td>
            <td style="width: 78%; text-align: right;">
                <div class="empresa">{{ $nombreEmpresa }}</div>
                @if ($lineaEmpresa !== '')
                    <div class="sub">{{ $lineaEmpresa }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="titulo">Resumen del mes</div>
    <div class="ref">{{ $mes }}</div>

    <table class="resumen">
        <tr>
            <td>
                <div class="k">Ingresos</div>
                <div class="v">Bs {{ number_format($ingresos, 2) }}</div>
            </td>
            <td>
                <div class="k">Egresos</div>
                <div class="v">Bs {{ number_format($egresos, 2) }}</div>
            </td>
            <td>
                <div class="k">Resultado</div>
                <div class="v">{{ $resultado < 0 ? '-' : '' }}Bs {{ number_format(abs($resultado), 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="sec">Detalle del mes</div>
    <table class="t">
        <tbody>
            <tr>
                <td>Cobrado a clientes</td>
                <td class="r">{{ number_format($cobros, 2) }}</td>
            </tr>
            <tr>
                <td>Ingresos extra</td>
                <td class="r">{{ number_format($ingresosExtra, 2) }}</td>
            </tr>
            <tr>
                <td>Pagos al personal</td>
                <td class="r">{{ number_format($personal, 2) }}</td>
            </tr>
            <tr>
                <td>Gastos</td>
                <td class="r">{{ number_format($gastos, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($gastosPorCategoria->isNotEmpty())
        <div class="sec">Gastos por categoría</div>
        <table class="t">
            <tbody>
                @foreach ($gastosPorCategoria as $g)
                    <tr>
                        <td>{{ $g->categoria }}</td>
                        <td class="r">{{ number_format($g->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="sec">Caja y pendientes (a hoy)</div>
    <table class="t">
        <tbody>
            <tr>
                <td>Caja en efectivo</td>
                <td class="r">{{ number_format($caja['Efectivo'], 2) }}</td>
            </tr>
            <tr>
                <td>Caja QR</td>
                <td class="r">{{ number_format($caja['QR'], 2) }}</td>
            </tr>
            <tr>
                <td>Fondo común reservado</td>
                <td class="r">{{ number_format($fondo, 2) }}</td>
            </tr>
            <tr>
                <td>Por cobrar a clientes</td>
                <td class="r">{{ number_format($porCobrar, 2) }}</td>
            </tr>
            <tr>
                <td>Por pagar al personal</td>
                <td class="r">{{ number_format($deudaPersonal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($proximos->isNotEmpty())
        <div class="sec">Próximos eventos</div>
        <table class="t">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Evento</th>
                    <th>Cliente</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($proximos as $e)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($e->fecha_evento)->format('d/m/Y') }}</td>
                        <td>{{ $e->codigo_evento }}</td>
                        <td>{{ $e->cliente->nombre_completo ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="pie">Generado por {{ $usuarioGenerador }} el {{ $fechaEmision }}.</div>
</body>

</html>