<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resumen financiero - Mariachi León Guanajuato</title>
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

        .c {
            text-align: center;
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
        $nitEmpresa = $empresa->nit_ruc ?? ($empresa->nit ?? null);
        $lineaEmpresa = collect([!empty($nitEmpresa) ? 'NIT: ' . $nitEmpresa : null, $empresa->ciudad_pais ?? null])
            ->filter()
            ->implode(' · ');

        $periodo =
            'Del ' .
            ($fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') : 'inicio') .
            ' al ' .
            ($fechaFin ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') : 'hoy');
        if ($cliente) {
            $periodo .= ' · Cliente: ' . $cliente->nombre_completo;
        }
        if ($evento) {
            $periodo .= ' · Evento: ' . $evento->codigo_evento;
        }
        $resultado = $kpis['resultado_operativo_estimado'];
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

    <div class="titulo">Resumen financiero</div>
    <div class="ref">{{ $periodo }}</div>

    <table class="resumen">
        <tr>
            <td>
                <div class="k">Ingresos</div>
                <div class="v">Bs {{ number_format($kpis['total_ingresos_consolidados'], 2) }}</div>
            </td>
            <td>
                <div class="k">Egresos</div>
                <div class="v">Bs {{ number_format($kpis['costos_totales_reconocidos'], 2) }}</div>
            </td>
            <td>
                <div class="k">Resultado</div>
                <div class="v">{{ $resultado < 0 ? '-' : '' }}Bs {{ number_format(abs($resultado), 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="sec">Ingresos</div>
    <table class="t">
        <tbody>
            <tr>
                <td>Cobrado a clientes</td>
                <td class="r">{{ number_format($kpis['total_cobrado'], 2) }}</td>
            </tr>
            <tr>
                <td>Ingresos extra</td>
                <td class="r">{{ number_format($kpis['total_ingresos_adicionales'], 2) }}</td>
            </tr>
            <tr class="tot">
                <td>Total ingresos</td>
                <td class="r">{{ number_format($kpis['total_ingresos_consolidados'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="sec">Egresos</div>
    <table class="t">
        <tbody>
            <tr>
                <td>Personal (músicos, transporte)</td>
                <td class="r">{{ number_format($kpis['total_asignado_participantes'], 2) }}</td>
            </tr>
            <tr>
                <td>Gastos de eventos</td>
                <td class="r">{{ number_format($kpis['total_gastos_eventos'], 2) }}</td>
            </tr>
            <tr>
                <td>Gastos operativos</td>
                <td class="r">{{ number_format($kpis['total_gastos_operativos'], 2) }}</td>
            </tr>
            <tr class="tot">
                <td>Total egresos</td>
                <td class="r">{{ number_format($kpis['costos_totales_reconocidos'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="sec">Pendientes y reserva</div>
    <table class="t">
        <tbody>
            <tr>
                <td>Por cobrar a clientes</td>
                <td class="r">{{ number_format($kpis['saldo_pendiente_clientes'], 2) }}</td>
            </tr>
            <tr>
                <td>Por pagar al personal</td>
                <td class="r">{{ number_format($kpis['saldo_pendiente_participantes'], 2) }}</td>
            </tr>
            <tr>
                <td>Fondo común disponible</td>
                <td class="r">{{ number_format($kpis['saldo_fondo_comun'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if (isset($balancesEventos) && $balancesEventos->isNotEmpty())
        <div class="sec">Resultado por evento</div>
        <table class="t">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th class="r">Cobrado</th>
                    <th class="r">Personal</th>
                    <th class="r">Gastos</th>
                    <th class="r">Margen</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($balancesEventos as $be)
                    <tr>
                        <td>{{ $be['codigo_evento'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($be['fecha_evento'])->format('d/m/Y') }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($be['cliente'], 22) }}</td>
                        <td class="r">{{ number_format($be['total_cobrado'], 2) }}</td>
                        <td class="r">{{ number_format($be['total_asignado'], 2) }}</td>
                        <td class="r">{{ number_format($be['gastos_evento'], 2) }}</td>
                        <td class="r">{{ number_format($be['balance_neto'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (isset($gastosPorCategoria) && $gastosPorCategoria->isNotEmpty())
        <div class="sec">Gastos operativos por categoría</div>
        <table class="t">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="c" style="width: 20%;">Registros</th>
                    <th class="r" style="width: 25%;">Monto (Bs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($gastosPorCategoria as $gpc)
                    <tr>
                        <td>{{ $gpc->categoria }}</td>
                        <td class="c">{{ $gpc->cantidad }}</td>
                        <td class="r">{{ number_format($gpc->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="pie">
        Generado por {{ $usuarioGenerador }} el {{ $fechaEmision }}.
    </div>

</body>

</html>