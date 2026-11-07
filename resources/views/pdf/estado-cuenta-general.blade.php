<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Estado de cuenta - {{ $contrato->numero_contrato }}</title>
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

        .datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }

        .datos td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .lbl {
            color: #444;
            width: 18%;
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
            margin: 10px 0 4px 0;
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

        table.t tfoot td {
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
            width: 100%;
            margin-top: 16px;
            border-top: 0.75px solid #111;
            padding-top: 6px;
            page-break-inside: avoid;
        }

        .pie td {
            vertical-align: middle;
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
        $docCliente = $cliente->ci_nit ?? null;
        $contacto = $cliente->telefono ?: $cliente->whatsapp ?? null;
        $pagosValidos = collect($pagos)->where('estado', 'Valido')->values();
        $servicioNombre = $contrato->servicio ? $contrato->servicio->nombre : null;
        $textoEvento = null;
        if ($contrato->evento) {
            $textoEvento = $contrato->evento->codigo_evento;
            if ($contrato->evento->fecha_evento) {
                $textoEvento .= ' · ' . $contrato->evento->fecha_evento->format('d/m/Y');
            }
        }
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

    <div class="titulo">Estado de cuenta</div>
    <div class="ref">Contrato N° {{ $contrato->numero_contrato }} · Emitido el {{ $fechaEmision }}</div>

    <table class="datos">
        <tr>
            <td class="lbl">Cliente:</td>
            <td><strong>{{ $cliente->nombre_completo }}</strong></td>
        </tr>
        @if (!empty($docCliente))
            <tr>
                <td class="lbl">C.I. / NIT:</td>
                <td>{{ $docCliente }}</td>
            </tr>
        @endif
        @if (!empty($contacto))
            <tr>
                <td class="lbl">Teléfono:</td>
                <td>{{ $contacto }}</td>
            </tr>
        @endif
        @if (!empty($servicioNombre))
            <tr>
                <td class="lbl">Servicio:</td>
                <td>{{ $servicioNombre }}</td>
            </tr>
        @endif
        @if (!empty($textoEvento))
            <tr>
                <td class="lbl">Evento:</td>
                <td>{{ $textoEvento }}</td>
            </tr>
        @endif
    </table>

    <table class="resumen">
        <tr>
            <td>
                <div class="k">Monto del contrato</div>
                <div class="v">Bs {{ number_format($montoContratado, 2) }}</div>
            </td>
            <td>
                <div class="k">Total abonado</div>
                <div class="v">Bs {{ number_format($totalAbonado, 2) }}</div>
            </td>
            <td>
                <div class="k">Saldo pendiente</div>
                <div class="v">Bs {{ number_format($saldoPendiente, 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="sec">Pagos realizados</div>
    <table class="t">
        <thead>
            <tr>
                <th style="width: 22%;">Fecha</th>
                <th style="width: 26%;">Recibo</th>
                <th>Método</th>
                <th class="r">Monto (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagosValidos as $p)
                <tr>
                    <td>{{ $p->fecha_pago ? $p->fecha_pago->format('d/m/Y') : '-' }}</td>
                    <td>{{ $p->numero_recibo }}</td>
                    <td>{{ $p->metodo_pago }}</td>
                    <td class="r">{{ number_format($p->monto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="c" style="padding: 8px; color: #555;">Sin pagos registrados.</td>
                </tr>
            @endforelse
        </tbody>
        @if (count($pagosValidos) > 0)
            <tfoot>
                <tr>
                    <td colspan="3" class="r">Total abonado</td>
                    <td class="r">{{ number_format($totalAbonado, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <table class="pie">
        <tr>
            <td style="width: 78%;">
                Documento emitido por {{ $empresa->nombre_comercial ?? $nombreEmpresa }}.<br>
                Generado por {{ $usuarioGenerador }} el {{ $fechaEmision }}.
                @if (isset($token))
                    <br><span style="font-size: 6.5pt;">Código: {{ $token }}</span>
                @endif
            </td>
            <td style="width: 22%; text-align: right;">
                @if (!empty($qrBase64))
                    <img src="{{ $qrBase64 }}" style="width: 62px; height: 62px;" alt="QR">
                @endif
            </td>
        </tr>
    </table>

</body>

</html>