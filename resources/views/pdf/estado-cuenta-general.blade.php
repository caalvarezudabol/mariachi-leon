<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta General - {{ $cliente->nombre_completo }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 12mm 14mm 14mm 14mm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #102846;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 2px solid #C99A2E;
            padding-bottom: 6px;
        }

        .logo-col {
            width: 20%;
            vertical-align: middle;
            text-align: left;
        }

        .logo-col img {
            max-width: 95px;
            max-height: 55px;
        }

        .title-col {
            width: 55%;
            vertical-align: middle;
            text-align: center;
        }

        .company-name {
            font-size: 11pt;
            font-weight: bold;
            color: #102846;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title {
            font-size: 10pt;
            font-weight: bold;
            color: #C99A2E;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .meta-col {
            width: 25%;
            vertical-align: middle;
            text-align: right;
            font-size: 6.8pt;
            color: #475569;
        }

        .client-box {
            width: 100%;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        .section-title {
            font-size: 7.2pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #102846;
            border-bottom: 1px solid #E7ECF2;
            padding-bottom: 2px;
            margin-bottom: 4px;
            letter-spacing: 0.4px;
        }

        /* Financial Cards Table */
        .cards-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .cards-table td {
            width: 16.66%;
            padding: 5px 3px;
            text-align: center;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }

        .card-contract { background-color: #E7ECF2; }
        .card-paid { background-color: #eaf5ec; border-color: #a3d9ad !important; }
        .card-pending { background-color: #fcedeb; border-color: #f1aeb5 !important; }
        .card-stat { background-color: #f8fafc; }

        .card-label {
            font-size: 5.8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 2px;
        }

        .card-amount {
            font-size: 8.8pt;
            font-weight: bold;
            font-family: 'DejaVu Sans Mono', monospace;
        }

        .color-contract { color: #102846; }
        .color-paid { color: #26733A; }
        .color-pending { color: #A92323; }
        .color-stat { color: #0f172a; }

        /* Tables Styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7pt;
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table th {
            background-color: #102846;
            color: #FFFFFF;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 5px;
            border: 1px solid #102846;
            font-size: 6.5pt;
            letter-spacing: 0.3px;
        }

        .report-table td {
            padding: 3.5px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .report-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'DejaVu Sans Mono', monospace; }

        .badge-valido {
            background-color: #eaf5ec;
            color: #26733A;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            border: 1px solid #a3d9ad;
            font-size: 6.2pt;
            text-transform: uppercase;
        }

        .badge-anulado {
            background-color: #fcedeb;
            color: #A92323;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            border: 1px solid #f1aeb5;
            font-size: 6.2pt;
            text-transform: uppercase;
            text-decoration: line-through;
        }

        .badge-contract {
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6.2pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #cbd5e1;
        }

        /* Footer */
        .doc-footer {
            margin-top: 15px;
            padding-top: 5px;
            border-top: 1px solid #cbd5e1;
            font-size: 6.5pt;
            color: #64748b;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Encabezado Institucional -->
    <table class="header-table">
        <tr>
            <td class="logo-col">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo Mariachi León">
                @else
                    <strong style="color:#102846; font-size: 11pt;">MARIACHI LEÓN</strong>
                @endif
            </td>
            <td class="title-col">
                <div class="company-name">{{ $empresa->razon_social ?? $empresa->nombre_comercial }}</div>
                <div class="doc-title">ESTADO DE CUENTA GENERAL DEL CLIENTE</div>
                <div style="font-size: 7.2pt; color: #64748b; margin-top: 1px;">NIT: {{ $empresa->nit_ruc }} &bull; {{ $empresa->ciudad_pais }}</div>
            </td>
            <td class="meta-col">
                <div><strong>N° Reporte:</strong> EDC-GEN-{{ str_pad($cliente->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div><strong>Emisión:</strong> {{ $fechaEmision }}</div>
                <div><strong>Generado por:</strong> {{ $usuarioGenerador }}</div>
            </td>
        </tr>
    </table>

    <!-- Datos del Cliente -->
    <div class="client-box">
        <table style="width: 100%; border-collapse: collapse; font-size: 7.3pt;">
            <tr>
                <td style="width: 50%;">
                    <span style="font-weight: bold; color: #475569;">Cliente:</span> 
                    <strong style="color: #0f172a; font-size: 8pt;">{{ $cliente->nombre_completo }}</strong>
                </td>
                <td style="width: 25%;">
                    <span style="font-weight: bold; color: #475569;">CI / NIT:</span> 
                    <span style="color: #0f172a;">{{ $cliente->ci_nit ?: 'S/N' }}</span>
                </td>
                <td style="width: 25%; text-align: right;">
                    <span style="font-weight: bold; color: #475569;">Estado:</span> 
                    <strong style="color: {{ $cliente->estado === 'Activo' ? '#26733A' : '#A92323' }};">{{ $cliente->estado }}</strong>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 2px;">
                    <span style="font-weight: bold; color: #475569;">Teléfono / Celular:</span> 
                    <span style="color: #0f172a;">{{ $cliente->telefono ?: ($cliente->whatsapp ?: 'N/A') }}</span>
                </td>
                <td style="padding-top: 2px;" colspan="2">
                    <span style="font-weight: bold; color: #475569;">Correo Electrónico:</span> 
                    <span style="color: #0f172a;">{{ $cliente->email ?: 'N/A' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tarjetas de Resumen Financiero Consolidado -->
    <table class="cards-table">
        <tr>
            <td class="card-stat">
                <div class="card-label">Total Contratos</div>
                <div class="card-amount color-stat">{{ $totalContratos }}</div>
            </td>
            <td class="card-contract">
                <div class="card-label">Monto Contratado</div>
                <div class="card-amount color-contract">Bs {{ number_format($montoContratadoTotal, 2) }}</div>
            </td>
            <td class="card-paid">
                <div class="card-label">Total Abonado Neto</div>
                <div class="card-amount color-paid">Bs {{ number_format($totalAbonadoNeto, 2) }}</div>
            </td>
            <td class="card-pending">
                <div class="card-label">Saldo Pendiente Global</div>
                <div class="card-amount color-pending">Bs {{ number_format($saldoPendienteTotal, 2) }}</div>
            </td>
            <td class="card-stat">
                <div class="card-label">Pagos Válidos</div>
                <div class="card-amount color-stat">{{ $cantidadPagosValidosTotal }}</div>
            </td>
            <td class="card-stat">
                <div class="card-label">Último Pago</div>
                <div class="card-amount color-stat" style="font-size: 7.2pt;">
                    {{ $ultimoPagoTotal ? $ultimoPagoTotal->fecha_pago->format('d/m/Y') : 'Sin pagos' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- TABLA 1: Resumen de Contratos del Cliente -->
    <div style="font-size: 7.8pt; font-weight: bold; color: #102846; margin-bottom: 3px; text-transform: uppercase;">
        1. Resumen de Contratos Registrados
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 14%;">Contrato</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 25%;">Servicio / Evento</th>
                <th style="width: 15%; text-align: right;">Monto Contratado (Bs)</th>
                <th style="width: 15%; text-align: right;">Total Abonado (Bs)</th>
                <th style="width: 14%; text-align: right;">Saldo Pendiente (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contratos as $idx => $c)
                @php
                    $abonadoContrato = $c->pagos->where('estado', 'Valido')->sum('monto');
                    $saldoContrato = max(0, $c->monto_total - $abonadoContrato);
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td class="text-center font-mono font-bold">{{ $c->numero_contrato }}</td>
                    <td class="text-center">{{ $c->fecha_contrato ? $c->fecha_contrato->format('d/m/Y') : '-' }}</td>
                    <td>{{ $c->servicio ? $c->servicio->nombre : 'Servicio Musical' }}</td>
                    <td class="text-right font-mono">{{ number_format($c->monto_total, 2) }}</td>
                    <td class="text-right font-mono color-paid font-bold">{{ number_format($abonadoContrato, 2) }}</td>
                    <td class="text-right font-mono color-pending font-bold">{{ number_format($saldoContrato, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 10px; color: #64748b;">
                        El cliente no tiene contratos registrados en el sistema.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($contratos) > 0)
            <tfoot>
                <tr style="background-color: #E7ECF2; font-weight: bold;">
                    <td colspan="4" class="text-right" style="padding: 4px;">TOTALES CONSOLIDADOS:</td>
                    <td class="text-right font-mono color-contract" style="padding: 4px;">Bs {{ number_format($montoContratadoTotal, 2) }}</td>
                    <td class="text-right font-mono color-paid" style="padding: 4px;">Bs {{ number_format($totalAbonadoNeto, 2) }}</td>
                    <td class="text-right font-mono color-pending" style="padding: 4px;">Bs {{ number_format($saldoPendienteTotal, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- TABLA 2: Historial Consolidado de Pagos -->
    <div style="font-size: 7.8pt; font-weight: bold; color: #102846; margin-top: 10px; margin-bottom: 3px; text-transform: uppercase;">
        2. Historial Consolidado de Pagos y Abonos
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 4%;">N°</th>
                <th style="width: 12%;">Recibo</th>
                <th style="width: 12%;">Contrato</th>
                <th style="width: 13%;">Fecha y Hora</th>
                <th style="width: 23%;">Detalle o Concepto</th>
                <th style="width: 12%; text-align: right;">Importe (Bs)</th>
                <th style="width: 10%;">Método</th>
                <th style="width: 8%;">Estado</th>
                <th style="width: 6%;">Cobrador</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagos as $index => $p)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="text-center font-mono font-bold">{{ $p->numero_recibo }}</td>
                    <td class="text-center font-mono" style="font-size: 6.5pt;">{{ $p->contrato ? $p->contrato->numero_contrato : 'N/A' }}</td>
                    <td class="text-center">{{ $p->fecha_pago ? $p->fecha_pago->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $p->observaciones ?: 'Abono a cuenta por servicio musical' }}</td>
                    <td class="text-right font-mono font-bold {{ $p->estado === 'Anulado' ? 'badge-anulado' : 'color-paid' }}">
                        {{ number_format($p->monto, 2) }}
                    </td>
                    <td class="text-center">{{ $p->metodo_pago }}</td>
                    <td class="text-center">
                        @if($p->estado === 'Valido')
                            <span class="badge-valido">Válido</span>
                        @else
                            <span class="badge-anulado">Anulado</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 6.2pt;">{{ $p->user ? $p->user->name : 'Sistema' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 10px; color: #64748b;">
                        No se registran pagos en el rango seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($pagos) > 0)
            <tfoot>
                <tr style="background-color: #E7ECF2; font-weight: bold;">
                    <td colspan="5" class="text-right" style="padding: 4px;">TOTAL ABONADO NETO EN HISTORIAL:</td>
                    <td class="text-right font-mono color-paid" style="padding: 4px;">
                        Bs {{ number_format($totalAbonadoNeto, 2) }}
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Bloque de Seguridad y Validación QR en la Parte Inferior Derecha -->
    <table style="width: 100%; margin-top: 15px; border-top: 1px dashed #cbd5e1; padding-top: 8px; page-break-inside: avoid;">
        <tr>
            <td style="width: 75%; vertical-align: middle; padding-right: 15px;">
                <div style="font-size: 8pt; font-weight: bold; color: #102846; text-transform: uppercase; margin-bottom: 3px;">
                    🛡️ Verificación de Autenticidad y Validez Oficial
                </div>
                <div style="font-size: 6.8pt; color: #475569; line-height: 1.35;">
                    Documento financiero oficial de <strong>{{ $empresa->nombre_comercial ?? 'Mariachi León Guanajuato' }}</strong>. 
                    Escanee el código QR adjunto para verificar la validez e integridad del estado de cuenta general del cliente en tiempo real.
                </div>
                <table style="width: 100%; margin-top: 5px; font-size: 6.5pt; color: #334155; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 1.5px 0;"><strong>Fecha y Hora de Emisión:</strong> {{ $fechaEmision }}</td>
                        <td style="padding: 1.5px 0;"><strong>Generado por:</strong> {{ $usuarioGenerador }}</td>
                    </tr>
                    @if(isset($token))
                    <tr>
                        <td colspan="2" style="padding: 1.5px 0; font-family: monospace; color: #64748b; font-size: 6pt;">
                            TOKEN DE SEGURIDAD: {{ $token }}
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: middle;">
                <div style="display: inline-block; text-align: center; border: 1px solid #cbd5e1; padding: 4px; border-radius: 4px; background: #ffffff;">
                    @if(isset($qrBase64))
                        <img src="{{ $qrBase64 }}" style="width: 75px; height: 75px;" alt="Código QR de Verificación">
                    @endif
                    <div style="font-size: 5.5pt; font-weight: bold; color: #102846; margin-top: 2px; text-transform: uppercase;">
                        Escanear para verificar
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Pie del Documento -->
    <table class="doc-footer">
        <tr>
            <td style="width: 60%;">
                Documento de consulta oficial emitido por el Sistema de Gestión de <strong>{{ $empresa->nombre_comercial }}</strong>.<br>
                Cualquier discrepancia debe ser informada al área de administración.
            </td>
            <td style="width: 40%; text-align: right;">
                Impreso: {{ $fechaEmision }}
            </td>
        </tr>
    </table>

</body>
</html>
