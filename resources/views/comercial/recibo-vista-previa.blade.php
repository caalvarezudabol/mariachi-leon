<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo {{ $pago->numero_recibo }} - {{ $empresa->nombre_comercial }}</title>
    <!-- Tailwind CSS CDN para el visor interactivo -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos generales */
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #0b1329;
            color: #102846;
        }

        /* Dimensiones Cuarto Oficio Horizontal en Pantalla */
        .receipt-sheet {
            width: 216mm;
            min-height: 140mm;
            max-height: 140mm;
            background-color: #FFFFFF;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
            margin: 0 auto;
            padding: 5mm 8mm 4mm 8mm;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }

        .watermark-anulado {
            position: absolute;
            top: 25%;
            left: 15%;
            width: 70%;
            text-align: center;
            font-size: 44pt;
            font-weight: 900;
            color: rgba(169, 35, 35, 0.16);
            transform: rotate(-22deg);
            z-index: 10;
            text-transform: uppercase;
            letter-spacing: 5px;
            pointer-events: none;
        }

        /* Reglas estrictas de Impresión @media print */
        @media print {
            @page {
                size: 216mm 140mm landscape;
                margin: 4mm 6mm 4mm 6mm;
            }

            html, body {
                background: #FFFFFF !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 216mm !important;
                height: 140mm !important;
            }

            .no-print {
                display: none !important;
            }

            .receipt-sheet {
                width: 100% !important;
                height: 100% !important;
                min-height: 132mm !important;
                max-height: 135mm !important;
                margin: 0 !important;
                padding: 2mm 3mm !important;
                box-shadow: none !important;
                border: none !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="min-h-screen py-6 px-2 sm:px-4">

    <!-- Barra Superior de Acciones (No se imprime) -->
    <div class="no-print max-w-[216mm] mx-auto mb-4 bg-slate-900/90 border border-slate-800 rounded-2xl p-3 flex flex-wrap items-center justify-between gap-3 shadow-xl backdrop-blur-md">
        <div class="flex items-center space-x-3">
            <span class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm border border-amber-500/30">
                🦁
            </span>
            <div>
                <h1 class="text-xs font-black uppercase text-amber-400 tracking-wider">
                    {{ $empresa->nombre_comercial }}
                </h1>
                <p class="text-[11px] text-slate-400">Recibo: <strong class="text-white">{{ $pago->numero_recibo }}</strong> &bull; Formato: Cuarto Oficio Horizontal</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs flex items-center gap-2 shadow-lg shadow-amber-500/20 transition-all cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>Imprimir Recibo</span>
            </button>

            <a href="{{ route('comercial.pagos.pdf', $pago->codigo_verificacion) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-rose-400 hover:text-white font-bold text-xs flex items-center gap-1.5 border border-slate-700 transition-all">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Descargar PDF</span>
            </a>

            @if(Route::has('admin.pagos'))
                <a href="{{ route('admin.pagos') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs transition-all">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Pagos
                </a>
            @endif

            @if(Route::has('admin.cotizaciones'))
                <a href="{{ route('admin.cotizaciones') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs transition-all">
                    Cotizaciones
                </a>
            @endif
        </div>
    </div>

    <!-- Hoja de Recibo Cuarto Oficio Horizontal -->
    <div class="receipt-sheet rounded-sm text-[#102846] text-[7.2pt] leading-[1.25]">
        
        @if($pago->estado === 'Anulado')
            <div class="watermark-anulado">ANULADO</div>
        @endif

        <!-- Encabezado -->
        <table class="w-full border-collapse mb-1">
            <tr>
                <!-- Logo -->
                <td class="w-[18%] align-middle text-left">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo Mariachi León" class="max-w-[95px] max-h-[46px] object-contain">
                    @else
                        <div class="w-12 h-12 rounded-full border-2 border-[#C99A2E] bg-[#102846] text-[#C99A2E] text-center font-black text-[7pt] leading-tight pt-2">
                            LEÓN<br><span class="text-white text-[3.5pt] tracking-widest">GUANAJUATO</span>
                        </div>
                    @endif
                </td>

                <!-- Título y Eslogan -->
                <td class="w-[58%] align-middle text-center px-1">
                    <div class="text-[11pt] font-black text-[#102846] uppercase tracking-wide leading-tight">
                        {{ $empresa->razon_social ?: 'MARIACHI LEÓN GUANAJUATO S.R.L.' }}
                    </div>
                    <div class="text-[6.2pt] font-bold text-[#C99A2E] uppercase italic tracking-wide">
                        "Excelencia y virtuosismo musical en cada presentación"
                    </div>
                    <div class="text-[9.5pt] font-black text-[#102846] uppercase border-b-2 border-[#C99A2E] inline-block px-1 mt-0.5 tracking-wide">
                        RECIBO DE COBRANZA Y PAGO
                    </div>
                </td>

                <!-- Tarjeta N° Recibo -->
                <td class="w-[24%] align-middle text-right">
                    <div class="border-[1.5px] border-[#C99A2E] rounded bg-[#E7ECF2] p-1 text-center">
                        <div class="text-[6.2pt] font-bold text-[#102846] uppercase">N° RECIBO</div>
                        <div class="text-[9.5pt] font-black text-[#102846] tracking-wider">{{ $pago->numero_recibo }}</div>
                        @if($pago->estado === 'Anulado')
                            <span class="inline-block px-1.5 py-0.2 rounded text-[6pt] font-bold uppercase bg-red-100 text-[#A92323] border border-[#A92323]">
                                ANULADO
                            </span>
                        @else
                            <span class="inline-block px-1.5 py-0.2 rounded text-[6pt] font-bold uppercase bg-green-100 text-[#26733A] border border-[#26733A]">
                                VÁLIDO
                            </span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- A. Resumen Destacado del Pago -->
        <div class="bg-slate-50 border-[1.5px] border-[#C99A2E] border-l-[5px] border-l-[#102846] rounded p-1 px-2 mb-1">
            <table class="w-full border-collapse">
                <tr>
                    <td class="w-[52%] align-middle">
                        <div class="text-[6.2pt] font-bold text-slate-500 uppercase">MONTO PERCIBIDO (ACTUAL):</div>
                        <div class="text-[13.5pt] font-black text-[#102846] leading-none">
                            Bs {{ number_format($pago->monto, 2, ',', '.') }}
                        </div>
                        <div class="text-[6.8pt] font-bold text-[#C99A2E] uppercase mt-0.5">
                            SON: {{ $montoLiteral }}
                        </div>
                    </td>
                    <td class="w-[48%] align-middle text-right text-[6.2pt]">
                        <div class="mb-0.5">
                            <span class="font-bold text-slate-500 uppercase">MÉTODO DE PAGO:</span>
                            <span class="inline-block bg-[#E7ECF2] text-[#102846] font-extrabold px-1.5 py-0.5 rounded text-[6.5pt] uppercase">
                                {{ $pago->metodo_pago }}
                            </span>
                        </div>
                        @if($pago->comprobante_referencia)
                            <div class="text-slate-600 mb-0.5">
                                <strong>Ref:</strong> {{ $pago->comprobante_referencia }}
                            </div>
                        @endif
                        <div class="text-slate-600">
                            <strong>Fecha Cobro:</strong> {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : $pago->created_at->format('d/m/Y H:i') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 2 Columnas de Información: B, C y D -->
        <table class="w-full border-collapse mb-1 text-[6.8pt]">
            <tr>
                <!-- Columna Izquierda: Empresa y Cliente -->
                <td class="w-1/2 align-top pr-1">
                    <!-- B. Datos de la Empresa Receptora -->
                    <div class="bg-[#102846] text-white text-[6.5pt] font-bold uppercase px-1.5 py-0.5 rounded-t-sm border-l-2 border-[#C99A2E] tracking-wider">
                        DATOS DE LA EMPRESA RECEPTORA
                    </div>
                    <table class="w-full border-collapse mb-1">
                        <tr>
                            <td class="bg-[#E7ECF2] font-bold text-[#102846] w-[32%] p-0.5 px-1 border border-slate-300">Razón Social:</td>
                            <td class="w-[68%] p-0.5 px-1 border border-slate-300"><strong>{{ $empresa->razon_social ?: $empresa->nombre_comercial }}</strong></td>
                        </tr>
                        <tr>
                            <td class="bg-[#E7ECF2] font-bold text-[#102846] p-0.5 px-1 border border-slate-300">NIT:</td>
                            <td class="p-0.5 px-1 border border-slate-300">{{ $empresa->nit_ruc }} &bull; Tel: {{ $empresa->telefono_principal }}</td>
                        </tr>
                        <tr>
                            <td class="bg-[#E7ECF2] font-bold text-[#102846] p-0.5 px-1 border border-slate-300">Dirección:</td>
                            <td class="p-0.5 px-1 border border-slate-300">{{ $empresa->direccion_fisica }} &bull; {{ $empresa->ciudad_pais }}</td>
                        </tr>
                    </table>

                    <!-- C. Datos del Cliente / Pagador -->
                    <div class="bg-[#102846] text-white text-[6.5pt] font-bold uppercase px-1.5 py-0.5 rounded-t-sm border-l-2 border-[#C99A2E] tracking-wider">
                        DATOS DEL CLIENTE / PAGADOR
                    </div>
                    <table class="w-full border-collapse mb-1">
                        <tr>
                            <td class="bg-[#E7ECF2] font-bold text-[#102846] w-[32%] p-0.5 px-1 border border-slate-300">Cliente:</td>
                            <td class="w-[68%] p-0.5 px-1 border border-slate-300"><strong>{{ $pago->cliente ? $pago->cliente->nombre_completo : 'Cliente Particular' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="bg-[#E7ECF2] font-bold text-[#102846] p-0.5 px-1 border border-slate-300">C.I. / NIT:</td>
                            <td class="p-0.5 px-1 border border-slate-300">{{ $pago->cliente ? ($pago->cliente->ci_nit ?: 'Sin documento') : '-' }} &bull; Cel: {{ $pago->cliente ? ($pago->cliente->whatsapp ?: $pago->cliente->telefono) : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="bg-[#E7ECF2] font-bold text-[#102846] p-0.5 px-1 border border-slate-300">Correo:</td>
                            <td class="p-0.5 px-1 border border-slate-300">{{ $pago->cliente ? ($pago->cliente->email ?: 'Sin correo') : '-' }}</td>
                        </tr>
                    </table>
                </td>

                <!-- Columna Derecha: Concepto y Servicio Vinculado -->
                <td class="w-1/2 align-top pl-1">
                    <div class="bg-[#102846] text-white text-[6.5pt] font-bold uppercase px-1.5 py-0.5 rounded-t-sm border-l-2 border-[#C99A2E] tracking-wider">
                        CONCEPTO DEL PAGO Y SERVICIO VINCULADO
                    </div>
                    <table class="w-full border-collapse mb-1">
                        <tr>
                            <td class="bg-[#E7ECF2] font-bold text-[#102846] w-[32%] p-0.5 px-1 border border-slate-300">Concepto:</td>
                            <td class="w-[68%] p-0.5 px-1 border border-slate-300"><strong>{{ $pago->observaciones ?: 'Abono a cuenta por servicio musical' }}</strong></td>
                        </tr>
                        @if($pago->contrato)
                            <tr>
                                <td class="bg-[#E7ECF2] font-bold text-[#102846] p-0.5 px-1 border border-slate-300">Contrato N°:</td>
                                <td class="p-0.5 px-1 border border-slate-300">
                                    <strong class="text-[#102846]">{{ $pago->contrato->numero_contrato }}</strong>
                                    @if($pago->evento)
                                        &bull; Evento: <strong>{{ $pago->evento->codigo_evento }}</strong>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bg-[#E7ECF2] font-bold text-[#102846] p-0.5 px-1 border border-slate-300">Servicio:</td>
                                <td class="p-0.5 px-1 border border-slate-300">{{ $pago->contrato->servicio ? $pago->contrato->servicio->nombre : 'Servicio Musical' }}</td>
                            </tr>
                            @if($pago->evento)
                                <tr>
                                    <td class="bg-[#E7ECF2] font-bold text-[#102846] p-0.5 px-1 border border-slate-300">Fecha Evento:</td>
                                    <td class="p-0.5 px-1 border border-slate-300">
                                        {{ $pago->evento->fecha_evento->format('d/m/Y') }} a las {{ $pago->evento->hora_evento }}
                                    </td>
                                </tr>
                            @endif
                        @endif
                        <tr>
                            <td class="bg-[#E7ECF2] font-bold text-[#102846] p-0.5 px-1 border border-slate-300">Cajero / Operador:</td>
                            <td class="p-0.5 px-1 border border-slate-300">{{ $usuarioCreacion }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- E. Balance del Contrato (3 Bloques Horizontales) -->
        @if($pago->contrato)
            <table class="w-full border-collapse mb-1 text-center">
                <tr>
                    <td class="w-1/3 p-1 border border-slate-300 bg-slate-50">
                        <div class="text-[6pt] font-bold text-[#102846] uppercase">1. MONTO TOTAL CONTRATADO</div>
                        <div class="text-[9pt] font-black text-[#102846] mt-0.5">Bs {{ number_format($pago->contrato->monto_total, 2, ',', '.') }}</div>
                    </td>
                    <td class="w-1/3 p-1 border border-slate-300 bg-slate-50">
                        <div class="text-[6pt] font-bold text-[#102846] uppercase">2. TOTAL ABONADO ACUMULADO</div>
                        <div class="text-[9pt] font-black text-[#26733A] mt-0.5">Bs {{ number_format($pago->contrato->total_pagado, 2, ',', '.') }}</div>
                    </td>
                    <td class="w-1/3 p-1 border border-slate-300 bg-slate-50">
                        <div class="text-[6pt] font-bold text-[#102846] uppercase">3. SALDO PENDIENTE ACTUAL</div>
                        <div class="text-[9pt] font-black text-[#A92323] mt-0.5">Bs {{ number_format($pago->contrato->saldo_pendiente, 2, ',', '.') }}</div>
                    </td>
                </tr>
            </table>
        @endif

        <!-- F & G. Firmas y Verificación QR Antifraude -->
        <table class="w-full border-collapse mt-0.5">
            <tr>
                <!-- F. Firmas -->
                <td class="w-[68%] align-bottom pr-2">
                    <table class="w-full border-collapse">
                        <tr>
                            <td class="w-1/2 align-bottom text-center px-2">
                                <div class="border-b border-[#102846] h-6 mb-0.5"></div>
                                <div class="text-[6.2pt] font-bold text-[#102846] uppercase">POR LA EMPRESA / CAJERO</div>
                                <div class="text-[5.8pt] text-slate-500">{{ $usuarioCreacion }} &bull; Cobranza Autorizada</div>
                            </td>
                            <td class="w-1/2 align-bottom text-center px-2">
                                <div class="border-b border-[#102846] h-6 mb-0.5"></div>
                                <div class="text-[6.2pt] font-bold text-[#102846] uppercase">POR EL CLIENTE / PAGADOR</div>
                                <div class="text-[5.8pt] text-slate-500">
                                    {{ $pago->cliente ? $pago->cliente->nombre_completo : 'Firma Cliente' }}
                                    @if($pago->cliente && $pago->cliente->ci_nit)
                                        &bull; C.I.: {{ $pago->cliente->ci_nit }}
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- G. Bloque QR y Autenticidad Antifraude -->
                <td class="w-[32%] align-middle text-right">
                    <div class="border border-slate-300 rounded p-1 bg-white text-left">
                        <table class="w-full border-collapse">
                            <tr>
                                <td class="align-middle text-[5.4pt] text-slate-500 leading-tight pr-1">
                                    <strong class="text-[#102846] text-[5.6pt]">VERIFICACIÓN ELECTRÓNICA</strong><br>
                                    <span>Escanee el QR para validar autenticidad en línea.</span><br>
                                    <strong>Creación:</strong> {{ $fechaCreacion }} ({{ $usuarioCreacion }})<br>
                                    <strong>Impresión:</strong> {{ $fechaImpresion }} ({{ $usuarioImpresion }})<br>
                                    <span class="font-mono text-[4.8pt] text-slate-400">
                                        HASH: {{ substr($pago->codigo_verificacion, 0, 14) }}...
                                    </span>
                                </td>
                                <td class="w-[58px] text-center align-middle">
                                    @if($qrBase64)
                                        <img src="{{ $qrBase64 }}" alt="QR Verificación" class="w-[54px] h-[54px] inline-block">
                                    @else
                                        <div class="text-[5pt] text-slate-400">Verificación electrónica no habilitada</div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- H. Pie de Página -->
        <div class="border-t border-[#C99A2E] pt-0.5 mt-1 text-[5.5pt] text-slate-400 text-center">
            {{ $empresa->nombre_comercial }} &bull; Comprobante de ingreso / cobranza &bull; Recibo: {{ $pago->numero_recibo }} &bull; Fecha impresión: {{ $fechaImpresion }} &bull; Página 1 de 1
        </div>

    </div>

</body>
</html>
