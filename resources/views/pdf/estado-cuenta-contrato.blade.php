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
            font-size: 8.5pt;
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
            margin-bottom: 12px;
        }

        /* Resumen de KPIs */
        .resumen {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .resumen td {
            width: 33.33%;
            text-align: center;
            padding: 7px 4px;
            border: 1px solid #111;
        }

        .resumen .k {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #444;
            margin-bottom: 2px;
        }

        .resumen .v {
            font-size: 11pt;
            font-weight: bold;
        }

        /* Tabla de Información de 2 Columnas */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
        }

        .info-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .info-box {
            border: 0.75px solid #bbb;
            padding: 6px 8px;
        }

        .info-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.2pt;
            border-bottom: 0.5px solid #ddd;
            padding-bottom: 3px;
            margin-bottom: 5px;
            color: #222;
            letter-spacing: 0.4px;
        }

        .info-row {
            margin-bottom: 2px;
            line-height: 1.35;
        }

        .info-lbl {
            font-weight: bold;
            color: #444;
            display: inline-block;
            width: 90px;
        }

        /* Secciones y Tablas */
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
            padding: 4px 5px;
            font-size: 7.5pt;
            text-transform: uppercase;
        }

        table.t td {
            padding: 4px 5px;
            border-bottom: 0.5px solid #ccc;
        }

        table.t tr.tot td,
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

        /* Bloque de Verificación y Seguridad */
        .verif-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            border-top: 0.75px dashed #aaa;
            padding-top: 8px;
            page-break-inside: avoid;
        }

        .verif-box td {
            vertical-align: middle;
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

        $liquidado = $saldoPendiente <= 0.01;
    @endphp

    <!-- Encabezado Institucional -->
    <table class="head">
        <tr>
            <td style="width: 22%;">
                @if (!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 80px; max-height: 48px;">
                @else
                    <div style="font-weight: bold; font-size: 11pt;">MARIACHI LEÓN</div>
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

    <div class="titulo">Estado de cuenta por contrato</div>
    <div class="ref">
        Contrato: {{ $contrato->numero_contrato }} · Cliente: {{ $cliente->nombre_completo }}
    </div>

    <!-- Resumen Financiero de KPIs -->
    <table class="resumen">
        <tr>
            <td>
                <div class="k">Contratado</div>
                <div class="v">Bs {{ number_format($montoContratado, 2) }}</div>
            </td>
            <td>
                <div class="k">Abonado</div>
                <div class="v" style="color: #1a6b32;">Bs {{ number_format($totalAbonado, 2) }}</div>
            </td>
            <td>
                <div class="k">{{ $liquidado ? 'Estado' : 'Saldo pendiente' }}</div>
                <div class="v" style="color: {{ $liquidado ? '#1a6b32' : '#a01515' }};">
                    {{ $liquidado ? 'Al día (Liquidado)' : 'Bs ' . number_format($saldoPendiente, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Ficha de Información: Cliente y Contrato -->
    <table class="info-grid">
        <tr>
            <td style="padding-right: 5px;">
                <div class="info-box">
                    <div class="info-title">Datos del Cliente</div>
                    <div class="info-row"><span class="info-lbl">Cliente:</span> <strong>{{ $cliente->nombre_completo }}</strong></div>
                    <div class="info-row"><span class="info-lbl">C.I. / NIT:</span> {{ $cliente->ci_nit ?: 'S/N' }}</div>
                    <div class="info-row"><span class="info-lbl">Teléfono:</span> {{ $cliente->telefono ?: ($cliente->whatsapp ?: 'S/N') }}</div>
                    <div class="info-row"><span class="info-lbl">Correo:</span> {{ $cliente->email ?: 'S/N' }}</div>
                </div>
            </td>
            <td style="padding-left: 5px;">
                <div class="info-box">
                    <div class="info-title">Datos del Contrato</div>
                    <div class="info-row"><span class="info-lbl">N° Contrato:</span> <strong>{{ $contrato->numero_contrato }}</strong></div>
                    <div class="info-row"><span class="info-lbl">Fecha:</span> {{ $contrato->fecha_contrato ? \Carbon\Carbon::parse($contrato->fecha_contrato)->format('d/m/Y') : '-' }}</div>
                    <div class="info-row"><span class="info-lbl">Servicio:</span> {{ $contrato->servicio->nombre ?? 'Servicio Musical' }}</div>
                    <div class="info-row"><span class="info-lbl">Evento:</span> {{ $contrato->evento ? $contrato->evento->codigo_evento . ' (' . \Carbon\Carbon::parse($contrato->evento->fecha_evento)->format('d/m/Y H:i') . ')' : 'Sin evento formal' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Detalle de Cobros y Movimientos -->
    <div class="sec">Detalle de cobros y movimientos</div>

    <table class="t">
        <thead>
            <tr>
                <th style="width: 14%;">Fecha</th>
                <th style="width: 16%;">Recibo</th>
                <th style="width: 30%;">Detalle</th>
                <th style="width: 14%;">Método</th>
                <th class="r" style="width: 13%;">Importe</th>
                <th class="r" style="width: 13%;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagos as $p)
                @php $anulado = $p->estado === 'Anulado'; @endphp
                <tr @if($anulado) style="color: #888;" @endif>
                    <td>{{ $p->fecha_pago ? \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') : '-' }}</td>
                    <td>
                        <strong>{{ $p->numero_recibo }}</strong>
                        @if($anulado)
                            <span style="font-size: 7pt; color: #a01515;">(Anulado)</span>
                        @endif
                    </td>
                    <td>{{ $p->observaciones ?: 'Abono a cuenta por servicio musical' }}</td>
                    <td>{{ $p->metodo_pago }}</td>
                    <td class="r" @if($anulado) style="text-decoration: line-through;" @endif>
                        Bs {{ number_format($p->monto, 2) }}
                    </td>
                    <td class="r" style="font-weight: bold; color: {{ ($p->saldo_posterior ?? 0) <= 0.01 ? '#1a6b32' : '#111' }};">
                        Bs {{ number_format($p->saldo_posterior ?? 0, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="c" style="padding: 12px; color: #666;">
                        No se registran cobros ni movimientos para este contrato.
                    </td>
                </tr>
            @endforelse
            @if(count($pagos) > 0)
                <tr class="tot">
                    <td colspan="4">Total abonado válido ({{ $cantidadPagosValidos }} cobro(s))</td>
                    <td class="r" style="color: #1a6b32;">Bs {{ number_format($totalAbonado, 2) }}</td>
                    <td class="r" style="color: {{ $liquidado ? '#1a6b32' : '#a01515' }};">
                        Bs {{ number_format($saldoPendiente, 2) }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Bloque de Seguridad y Validación QR -->
    <table class="verif-box">
        <tr>
            <td style="width: 78%; padding-right: 15px;">
                <div style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">
                    Verificación de Autenticidad y Validez Oficial
                </div>
                <div style="font-size: 7pt; color: #444; line-height: 1.35;">
                    Documento oficial emitido por el Sistema de Gestión de <strong>{{ $nombreEmpresa }}</strong>.
                    Escanee el código QR adjunto para constatar la autenticidad e integridad de este estado de cuenta en tiempo real.
                </div>
                <div style="font-size: 6.8pt; color: #666; margin-top: 3px;">
                    Código de verificación: {{ $contrato->codigo_verificacion ?? $token }} · Emisión: {{ $fechaEmision }}
                </div>
            </td>
            <td style="width: 22%; text-align: right;">
                @if(!empty($qrBase64))
                    <div style="display: inline-block; text-align: center; border: 0.75px solid #bbb; padding: 4px; background: #fff;">
                        <img src="{{ $qrBase64 }}" style="width: 65px; height: 65px;" alt="Código QR de Verificación">
                        <div style="font-size: 5.5pt; font-weight: bold; text-transform: uppercase; margin-top: 2px; color: #333;">
                            Validación
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="pie">
        Generado por {{ $usuarioGenerador }} el {{ $fechaEmision }}.
    </div>

</body>

</html>
