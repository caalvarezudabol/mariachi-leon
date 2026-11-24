<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Cobros por QR - {{ $empresa->nombre_comercial ?? 'Mariachi León' }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 14mm 16mm 14mm 16mm;
        }

        body {
            font-family: 'DejaVu Serif', 'Times New Roman', serif;
            font-size: 8.5pt;
            color: #111;
            line-height: 1.35;
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

        .subtitulo {
            text-align: center;
            font-size: 8pt;
            color: #333;
            margin-bottom: 4px;
        }

        .ref {
            text-align: center;
            font-size: 7.5pt;
            color: #555;
            margin-bottom: 10px;
            padding: 3px 6px;
            background-color: #f7f7f7;
            border: 0.5px solid #ddd;
        }

        /* Resumen de KPIs */
        .resumen {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .resumen td {
            width: 25%;
            text-align: center;
            padding: 6px 3px;
            border: 1px solid #222;
        }

        .resumen .k {
            font-size: 6.8pt;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #444;
            margin-bottom: 2px;
        }

        .resumen .v {
            font-size: 10.5pt;
            font-weight: bold;
            color: #111;
        }

        .resumen .s {
            font-size: 6.8pt;
            color: #666;
            margin-top: 1px;
        }

        /* Secciones y Tablas */
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
            font-size: 7.5pt;
        }

        table.t th {
            text-align: left;
            font-weight: bold;
            border-bottom: 0.75px solid #111;
            border-top: 0.75px solid #111;
            padding: 4px 3px;
            font-size: 7pt;
            text-transform: uppercase;
            background-color: #f2f2f2;
        }

        table.t td {
            padding: 4px 3px;
            border-bottom: 0.5px solid #ddd;
            vertical-align: top;
        }

        table.t tr.even {
            background-color: #fafafa;
        }

        table.t tr.tot td {
            border-top: 1px solid #111;
            border-bottom: 1.5px solid #111;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .r {
            text-align: right;
        }

        .c {
            text-align: center;
        }

        .badge-pagado {
            font-size: 6.5pt;
            font-weight: bold;
            color: #065f46;
            background-color: #d1fae5;
            padding: 1px 4px;
            border: 0.5px solid #a7f3d0;
            border-radius: 2px;
        }

        .badge-pendiente {
            font-size: 6.5pt;
            font-weight: bold;
            color: #92400e;
            background-color: #fef3c7;
            padding: 1px 4px;
            border: 0.5px solid #fde68a;
            border-radius: 2px;
        }

        .badge-otro {
            font-size: 6.5pt;
            font-weight: bold;
            color: #4b5563;
            background-color: #f3f4f6;
            padding: 1px 4px;
            border: 0.5px solid #e5e7eb;
            border-radius: 2px;
        }

        .badge-web {
            font-size: 6.5pt;
            font-weight: bold;
            color: #0369a1;
            background-color: #e0f2fe;
            padding: 1px 3px;
            border-radius: 2px;
        }

        .badge-sistema {
            font-size: 6.5pt;
            font-weight: bold;
            color: #b45309;
            background-color: #fef3c7;
            padding: 1px 3px;
            border-radius: 2px;
        }

        .pie {
            margin-top: 12px;
            border-top: 0.75px solid #111;
            padding-top: 4px;
            font-size: 7pt;
            color: #444;
        }
    </style>
</head>

<body>
    @php
        $nombreEmpresa = $empresa->razon_social ?? ($empresa->nombre_comercial ?? 'Mariachi León Guanajuato');
        $nitEmpresa = $empresa->nit_ruc ?? ($empresa->nit ?? null);
        $lineaEmpresa = collect([
            !empty($nitEmpresa) ? 'NIT: ' . $nitEmpresa : null,
            $empresa->telefono ? 'Tel.: ' . $empresa->telefono : null,
            $empresa->ciudad_pais ?? 'Santa Cruz - Bolivia'
        ])->filter()->implode(' · ');

        // Texto de filtros para cabecera
        $filtroPeriodo = 'Historial Completo';
        if ($fechaDesde && $fechaHasta) {
            $filtroPeriodo = \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y');
        } elseif ($fechaDesde) {
            $filtroPeriodo = 'Desde ' . \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y');
        } elseif ($fechaHasta) {
            $filtroPeriodo = 'Hasta ' . \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y');
        }

        $filtroOrigen = 'Todos los orígenes';
        if ($origen === 'web') {
            $filtroOrigen = 'Web Pública (Clientes)';
        } elseif ($origen === 'sistema') {
            $filtroOrigen = 'Sistema Interno' . ($usuarioFiltro ? ' (Usuario: ' . $usuarioFiltro->name . ')' : '');
        }

        $filtroEstado = ($estado !== null && $estado !== '') ? 'Estado: ' . $estado : 'Todos los estados';
    @endphp

    <!-- Encabezado Institucional -->
    <table class="head">
        <tr>
            <td style="width: 20%;">
                @if (!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 75px; max-height: 45px;">
                @else
                    <div style="font-weight: bold; font-size: 10pt;">MARIACHI LEÓN</div>
                @endif
            </td>
            <td style="width: 55%; text-align: left;">
                <div class="empresa">{{ $nombreEmpresa }}</div>
                <div class="sub">{{ $lineaEmpresa }}</div>
                <div class="sub">{{ $empresa->direccion ?? 'Av. Principal - Santa Cruz, Bolivia' }}</div>
            </td>
            <td style="width: 25%; text-align: right;" class="sub">
                <div><strong>Emisión:</strong> {{ $fechaEmision }}</div>
                <div><strong>Usuario:</strong> {{ $usuarioGenerador }}</div>
                <div><strong>Moneda:</strong> Bolivianos (Bs)</div>
            </td>
        </tr>
    </table>

    <!-- Título del Reporte -->
    <div class="titulo">Reporte de Cobros por Código QR</div>
    <div class="subtitulo">Banco Económico S.A. · API REST Gateway Simple QR</div>

    <!-- Referencia de Filtros -->
    <div class="ref">
        <strong>Período:</strong> {{ $filtroPeriodo }} &nbsp;|&nbsp;
        <strong>Canal:</strong> {{ $filtroOrigen }} &nbsp;|&nbsp;
        <strong>Filtro:</strong> {{ $filtroEstado }}
        @if ($search)
            &nbsp;|&nbsp; <strong>Búsqueda:</strong> "{{ $search }}"
        @endif
    </div>

    <!-- Resumen Ejecutivo de KPIs -->
    <table class="resumen">
        <tr>
            <td>
                <div class="k">Total Cobrado (QR)</div>
                <div class="v">Bs {{ number_format($resumen['total_cobrado'], 2) }}</div>
                <div class="s">{{ $resumen['cantidad_cobrados'] }} {{ $resumen['cantidad_cobrados'] === 1 ? 'cobro exitoso' : 'cobros exitosos' }}</div>
            </td>
            <td>
                <div class="k">Web Pública (Clientes)</div>
                <div class="v">Bs {{ number_format($resumen['total_web'], 2) }}</div>
                <div class="s">{{ $resumen['cantidad_web'] }} anticipos web</div>
            </td>
            <td>
                <div class="k">Sistema Administrativo</div>
                <div class="v">Bs {{ number_format($resumen['total_sistema'], 2) }}</div>
                <div class="s">{{ $resumen['cantidad_sistema'] }} pagos contratos</div>
            </td>
            <td>
                <div class="k">Transacciones Totales</div>
                <div class="v">{{ $resumen['total_registros'] }}</div>
                <div class="s">
                    @if ($resumen['cantidad_pendientes'] > 0)
                        {{ $resumen['cantidad_pendientes'] }} pendientes (Bs {{ number_format($resumen['total_pendientes'], 2) }})
                    @else
                        100% efectivas
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabla Detallada -->
    <div class="sec">Detalle de Cobros y Transacciones QR</div>

    <table class="t">
        <thead>
            <tr>
                <th style="width: 10%;">Fecha/Hora</th>
                <th style="width: 14%;">Transacción / QR ID</th>
                <th style="width: 13%;">Origen / Canal</th>
                <th style="width: 22%;">Cliente / Pagador</th>
                <th style="width: 22%;">Concepto / Contrato</th>
                <th class="c" style="width: 8%;">Estado</th>
                <th class="r" style="width: 11%;">Monto (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transacciones as $idx => $tx)
                <tr class="{{ $idx % 2 === 1 ? 'even' : '' }}">
                    <td>
                        <div>{{ $tx->created_at->format('d/m/Y') }}</div>
                        <div style="color: #666; font-size: 6.8pt;">{{ $tx->created_at->format('H:i:s') }}</div>
                    </td>
                    <td>
                        <div style="font-family: monospace; font-size: 7.2pt; font-weight: bold;">{{ $tx->transaction_id }}</div>
                        @if ($tx->qr_id)
                            <div style="font-family: monospace; color: #777; font-size: 6.5pt;">ID: {{ Str::limit($tx->qr_id, 16) }}</div>
                        @endif
                    </td>
                    <td>
                        @if ($tx->origen === 'contacto_web')
                            <span class="badge-web">Web Pública</span>
                        @else
                            <span class="badge-sistema">Sistema</span>
                            @if ($tx->creador)
                                <div style="font-size: 6.8pt; color: #555;">{{ Str::limit($tx->creador->name, 15) }}</div>
                            @endif
                        @endif
                    </td>
                    <td>
                        <strong>{{ $tx->nombre_cliente }}</strong>
                        @if ($tx->telefono_cliente)
                            <div style="color: #555; font-size: 6.8pt;">Tel: {{ $tx->telefono_cliente }}</div>
                        @endif
                        @if (!empty($tx->datos_pago['senderName']) && $tx->datos_pago['senderName'] !== $tx->nombre_cliente)
                            <div style="color: #065f46; font-size: 6.5pt;">Banca: {{ Str::limit($tx->datos_pago['senderName'], 22) }}</div>
                        @endif
                    </td>
                    <td>
                        <div>{{ Str::limit($tx->descripcion ?: 'Cobro mediante QR', 38) }}</div>
                        @if ($tx->contrato)
                            <div style="color: #555; font-size: 6.8pt;">Contrato: {{ $tx->contrato->numero_contrato }}</div>
                        @elseif ($tx->pago)
                            <div style="color: #555; font-size: 6.8pt;">Recibo N° {{ $tx->pago->numero_recibo }}</div>
                        @endif
                    </td>
                    <td class="c">
                        @if ($tx->estado === 'PAGADO')
                            <span class="badge-pagado">PAGADO</span>
                        @elseif ($tx->estado === 'PENDIENTE')
                            <span class="badge-pendiente">PENDIENTE</span>
                        @else
                            <span class="badge-otro">{{ $tx->estado }}</span>
                        @endif
                    </td>
                    <td class="r">
                        <strong>{{ number_format($tx->monto, 2) }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="c" style="padding: 18px; color: #777;">
                        No se registraron transacciones de cobro por QR para el período y filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($transacciones->count() > 0)
            <tfoot>
                <tr class="tot">
                    <td colspan="5" style="text-align: right; padding-right: 8px;">
                        TOTAL COBRADO (PAGADOS):
                    </td>
                    <td class="c">
                        <span class="badge-pagado">{{ $resumen['cantidad_cobrados'] }} Pagos</span>
                    </td>
                    <td class="r" style="font-size: 8.5pt;">
                        Bs {{ number_format($resumen['total_cobrado'], 2) }}
                    </td>
                </tr>
                @if ($resumen['total_pendientes'] > 0)
                    <tr style="background-color: #fdfdfd; font-size: 7.2pt; color: #555;">
                        <td colspan="5" style="text-align: right; padding-right: 8px;">
                            Pendientes / No Concretados:
                        </td>
                        <td class="c">
                            <span class="badge-pendiente">{{ $resumen['cantidad_pendientes'] }} QRs</span>
                        </td>
                        <td class="r">
                            Bs {{ number_format($resumen['total_pendientes'], 2) }}
                        </td>
                    </tr>
                @endif
            </tfoot>
        @endif
    </table>

    <!-- Pie de Página Institucional y Auditoría -->
    <div class="pie">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <strong>Mariachi León Guanajuato</strong> · Módulo de Informes y Reportes Financieros.<br>
                    Documento generado electrónicamente con validación de auditoría interna y conciliación Banco Económico S.A.
                </td>
                <td style="width: 30%; text-align: right; vertical-align: top;">
                    Página 1 de 1<br>
                    <span style="font-size: 6.5pt; color: #777;">ID Auditoría: {{ strtoupper(substr(md5($fechaEmision . $usuarioGenerador), 0, 12)) }}</span>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
