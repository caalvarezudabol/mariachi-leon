<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pagos al personal - Mariachi León Guanajuato</title>
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

        .firmas {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .firmas td {
            width: 45%;
            text-align: center;
            font-size: 7.5pt;
        }

        .linea {
            border-top: 0.75px solid #111;
            width: 170px;
            margin: 0 auto;
            padding-top: 3px;
        }

        .pie {
            margin-top: 14px;
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

        if ($modalidad === 'consolidado') {
            $subtitulo = 'Resumen por persona';
        } elseif ($modalidad === 'por_evento') {
            $subtitulo = 'Evento ' . $evento->codigo_evento;
            if ($evento->fecha_evento) {
                $subtitulo .= ' · ' . \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y');
            }
        } else {
            $subtitulo = $persona->nombre_completo;
        }
        $periodo =
            $modalidad === 'por_evento' ? null : 'Del ' . ($fechaDesde ?: 'inicio') . ' al ' . ($fechaHasta ?: 'hoy');
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

    <div class="titulo">Pagos al personal</div>
    <div class="ref">
        {{ $subtitulo }}
        @if ($periodo)
            · {{ $periodo }}
        @endif
    </div>

    <table class="resumen">
        <tr>
            <td style="width: 33.33%;">
                <div class="k">Asignado</div>
                <div class="v">Bs {{ number_format($resumen['total_asignado'], 2) }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="k">Pagado</div>
                <div class="v">Bs {{ number_format($resumen['total_pagado'], 2) }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="k">Saldo pendiente</div>
                <div class="v">Bs {{ number_format($resumen['saldo_pendiente'], 2) }}</div>
            </td>
        </tr>
    </table>

    @if ($modalidad === 'consolidado')
        <table class="t">
            <thead>
                <tr>
                    <th>Persona</th>
                    <th>Tipo</th>
                    <th class="c">Eventos</th>
                    <th class="r">Asignado</th>
                    <th class="r">Pagado</th>
                    <th class="r">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($filas as $f)
                    <tr>
                        <td>{{ $f['nombre_completo'] }}</td>
                        <td>{{ $f['tipo'] }}</td>
                        <td class="c">{{ $f['total_eventos'] }}</td>
                        <td class="r">{{ number_format($f['total_asignado'], 2) }}</td>
                        <td class="r">{{ number_format($f['total_pagado'], 2) }}</td>
                        <td class="r">{{ number_format($f['saldo_pendiente'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="c" style="padding: 8px; color: #555;">Sin registros en el
                            periodo.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="r">Total</td>
                    <td class="r">{{ number_format($resumen['total_asignado'], 2) }}</td>
                    <td class="r">{{ number_format($resumen['total_pagado'], 2) }}</td>
                    <td class="r">{{ number_format($resumen['saldo_pendiente'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @elseif ($modalidad === 'por_evento')
        <table class="t">
            <thead>
                <tr>
                    <th>Persona</th>
                    <th>Función</th>
                    <th class="r">Asignado</th>
                    <th class="r">Pagado</th>
                    <th class="r">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($participantes as $p)
                    <tr>
                        <td>{{ $p->persona->nombre_completo ?? '-' }}</td>
                        <td>{{ $p->funcion }}</td>
                        <td class="r">{{ number_format($p->monto_asignado, 2) }}</td>
                        <td class="r">{{ number_format($p->total_pagado, 2) }}</td>
                        <td class="r">{{ number_format($p->saldo_pendiente, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="c" style="padding: 8px; color: #555;">Sin personal asignado.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="r">Total</td>
                    <td class="r">{{ number_format($resumen['total_asignado'], 2) }}</td>
                    <td class="r">{{ number_format($resumen['total_pagado'], 2) }}</td>
                    <td class="r">{{ number_format($resumen['saldo_pendiente'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <table class="t">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Evento</th>
                    <th>Cliente</th>
                    <th>Función</th>
                    <th class="r">Asignado</th>
                    <th class="r">Pagado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($participaciones as $part)
                    <tr>
                        <td>{{ $part->evento ? \Carbon\Carbon::parse($part->evento->fecha_evento)->format('d/m/Y') : '-' }}
                        </td>
                        <td>{{ $part->evento->codigo_evento ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($part->evento->cliente->nombre_completo ?? '-', 24) }}
                        </td>
                        <td>{{ $part->funcion }}</td>
                        <td class="r">{{ number_format($part->monto_asignado, 2) }}</td>
                        <td class="r">{{ number_format($part->total_pagado, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="c" style="padding: 8px; color: #555;">Sin participaciones en el
                            periodo.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="r">Total</td>
                    <td class="r">{{ number_format($resumen['total_asignado'], 2) }}</td>
                    <td class="r">{{ number_format($resumen['total_pagado'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="firmas">
            <tr>
                <td>
                    <div class="linea">Responsable de pagos</div>
                </td>
                <td style="width: 10%;"></td>
                <td>
                    <div class="linea">{{ $persona->nombre_completo }}</div>
                </td>
            </tr>
        </table>
    @endif

    <div class="pie">
        Generado por {{ $usuarioGenerador }} el {{ $fechaEmision }}.
    </div>

</body>

</html>