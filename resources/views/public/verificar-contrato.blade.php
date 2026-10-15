<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Oficial de Contrato - {{ $empresa->nombre_comercial }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 antialiased pb-12">

    <!-- Top Navigation Bar -->
    <header class="bg-[#0b1329] text-white py-4 px-4 shadow-md sticky top-0 z-50 border-b-2 border-amber-500">
        <div class="max-w-2xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-amber-500 text-[#0b1329] flex items-center justify-center font-bold text-lg shadow">
                    🦁
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-wider uppercase text-amber-400 leading-tight">
                        {{ $empresa->nombre_comercial }}
                    </h1>
                    <p class="text-xs text-slate-300">Portal de Autenticidad Documental</p>
                </div>
            </div>
            <div class="text-right text-[11px] text-slate-400 hidden sm:block">
                <span>Certificación Digital</span><br>
                <span class="text-emerald-400 font-medium">SSL Seguro 256-bit</span>
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 mt-6">
        @if(!$contrato)
            <!-- Contrato no encontrado -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 overflow-hidden">
                <div class="bg-red-600 px-6 py-6 text-white text-center">
                    <i class="fa-solid fa-triangle-exclamation text-5xl mb-2 text-red-100"></i>
                    <h2 class="text-xl font-bold tracking-wide">CONTRATO NO VÁLIDO O INEXISTENTE</h2>
                    <p class="text-sm text-red-100 mt-1">El código escaneado no corresponde a ningún contrato oficial emitido por nuestro sistema.</p>
                </div>
                <div class="p-6 text-center">
                    <p class="text-sm text-slate-600 mb-4">
                        Este código QR podría ser fraudulento o haber sido alterado. Por favor, comuníquese directamente con la administración de <strong>{{ $empresa->nombre_comercial }}</strong>.
                    </p>
                    <div class="bg-slate-50 border border-slate-200 rounded p-3 text-xs font-mono text-slate-500 break-all mb-4">
                        Token consultado: {{ $token }}
                    </div>
                    <div class="text-xs text-slate-500">
                        Teléfono oficial: {{ $empresa->telefono_principal }} | WhatsApp: {{ $empresa->whatsapp_comercial }}
                    </div>
                </div>
            </div>
        @else
            <!-- Contrato Encontrado -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="bg-emerald-600 px-6 py-5 text-white text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-700 mb-2 shadow-inner">
                        <i class="fa-solid fa-shield-check text-3xl text-white"></i>
                    </div>
                    <h2 class="text-xl font-black uppercase tracking-wider">CONTRATO AUTÉNTICO Y REGISTRADO</h2>
                    <p class="text-xs text-emerald-100 mt-0.5">Emitido oficialmente por el sistema comercial de {{ $empresa->nombre_comercial }}</p>
                </div>

                <!-- Contenido del Contrato -->
                <div class="p-5 sm:p-7 space-y-6">

                    <!-- Cabecera de Resumen Contrato -->
                    <div class="bg-amber-50/60 border border-amber-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between text-center sm:text-left gap-3">
                        <div>
                            <span class="text-xs font-semibold text-amber-800 uppercase tracking-wider">Monto Total Contratado</span>
                            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-0.5">
                                Bs {{ number_format($contrato->monto_total, 2, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-600 mt-1">
                                Fecha de Firma: <strong>{{ $contrato->fecha_contrato ? $contrato->fecha_contrato->format('d/m/Y') : '-' }}</strong>
                            </div>
                        </div>
                        <div class="text-center sm:text-right">
                            <span class="text-[11px] text-slate-500 uppercase font-semibold">N° Contrato</span>
                            <div class="text-lg font-bold text-amber-700">{{ $contrato->numero_contrato }}</div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase mt-1 bg-emerald-100 text-emerald-800">
                                {{ $contrato->estado }}
                            </span>
                        </div>
                    </div>

                    <!-- Datos del Cliente Contratante -->
                    <div>
                        <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-2 flex items-center">
                            <i class="fa-solid fa-user-tie text-amber-500 mr-1.5"></i> Datos del Cliente Contratante
                        </h3>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm space-y-1.5">
                            <div class="flex justify-between flex-wrap">
                                <span class="text-slate-500 text-xs">Nombre Completo:</span>
                                <span class="font-bold text-slate-800">{{ $contrato->cliente ? $contrato->cliente->nombre_completo : '-' }}</span>
                            </div>
                            <div class="flex justify-between flex-wrap">
                                <span class="text-slate-500 text-xs">C.I. / NIT:</span>
                                <span class="font-semibold text-slate-700">{{ $contrato->cliente ? ($contrato->cliente->ci_nit ?: 'Sin documento') : '-' }}</span>
                            </div>
                            <div class="flex justify-between flex-wrap">
                                <span class="text-slate-500 text-xs">Teléfono / Celular:</span>
                                <span class="text-slate-700">{{ $contrato->cliente ? ($contrato->cliente->telefono . ' / ' . $contrato->cliente->whatsapp) : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Evento -->
                    @if($contrato->evento)
                        <div>
                            <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-2 flex items-center">
                                <i class="fa-solid fa-calendar-star text-amber-500 mr-1.5"></i> Detalles del Evento y Servicio
                            </h3>
                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm space-y-1.5">
                                <div class="flex justify-between flex-wrap">
                                    <span class="text-slate-500 text-xs">Código de Evento:</span>
                                    <span class="font-bold text-slate-800">{{ $contrato->evento->codigo_evento }}</span>
                                </div>
                                <div class="flex justify-between flex-wrap">
                                    <span class="text-slate-500 text-xs">Servicio Contratado:</span>
                                    <span class="font-semibold text-slate-800">{{ $contrato->servicio ? $contrato->servicio->nombre : 'Servicio Musical' }}</span>
                                </div>
                                <div class="flex justify-between flex-wrap">
                                    <span class="text-slate-500 text-xs">Fecha y Hora de Presentación:</span>
                                    <span class="font-bold text-amber-700">{{ $contrato->evento->fecha_evento->format('d/m/Y') }} a las {{ $contrato->evento->hora_evento }}</span>
                                </div>
                                <div class="flex justify-between flex-wrap">
                                    <span class="text-slate-500 text-xs">Lugar / Dirección:</span>
                                    <span class="text-slate-700">{{ $contrato->evento->direccion_evento }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Estado Económico -->
                    <div>
                        <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-2 flex items-center">
                            <i class="fa-solid fa-wallet text-amber-500 mr-1.5"></i> Estado Económico del Contrato
                        </h3>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-slate-50 border border-slate-200 rounded p-2.5">
                                <div class="text-[10px] text-slate-500 font-bold uppercase">Monto Total</div>
                                <div class="text-sm font-black text-slate-800">Bs {{ number_format($contrato->monto_total, 2, ',', '.') }}</div>
                            </div>
                            <div class="bg-emerald-50 border border-emerald-200 rounded p-2.5">
                                <div class="text-[10px] text-emerald-700 font-bold uppercase">Total Pagado</div>
                                <div class="text-sm font-black text-emerald-800">Bs {{ number_format($contrato->total_pagado, 2, ',', '.') }}</div>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded p-2.5">
                                <div class="text-[10px] text-red-700 font-bold uppercase">Saldo Pendiente</div>
                                <div class="text-sm font-black text-red-800">Bs {{ number_format($contrato->saldo_pendiente, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Metadatos de Auditoría e Integridad -->
                    <div class="border-t border-slate-200 pt-4 space-y-2">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Registrado Originalmente Por:</span>
                            <span class="font-semibold text-slate-700">{{ $contrato->user ? $contrato->user->name : 'Sistema Oficial' }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Fecha Original de Creación:</span>
                            <span class="font-semibold text-slate-700">{{ $contrato->created_at ? $contrato->created_at->format('d/m/Y H:i:s') : $contrato->fecha_contrato->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span><i class="fa-regular fa-clock mr-1"></i> Fecha y Hora de Verificación:</span>
                            <span class="font-semibold text-slate-700">{{ $fechaConsulta }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 break-all font-mono bg-slate-50 p-2 rounded border border-slate-200">
                            <span class="text-slate-500 font-sans font-semibold">Hash de Integridad Criptográfica:</span><br>
                            {{ $token }}
                        </div>
                    </div>

                    <!-- Botón de Descarga de PDF -->
                    <div class="text-center pt-2">
                        <a href="{{ route('comercial.contratos.pdf', $contrato->id) }}" target="_blank"
                           class="inline-flex items-center justify-center space-x-2 bg-[#0b1329] hover:bg-[#15254d] text-amber-400 font-bold px-6 py-3 rounded-lg shadow transition duration-200 text-sm">
                            <i class="fa-solid fa-file-pdf text-red-400 text-lg"></i>
                            <span>Descargar Contrato Oficial (PDF)</span>
                        </a>
                    </div>

                </div>
            </div>
        @endif

        <!-- Footer Informativo -->
        <div class="text-center mt-6 text-xs text-slate-500 space-y-1">
            <p><strong>{{ $empresa->nombre_comercial }}</strong> &bull; NIT: {{ $empresa->nit_ruc }}</p>
            <p>{{ $empresa->direccion_fisica }} &bull; {{ $empresa->ciudad_pais }}</p>
            <p class="text-[11px] text-slate-400">Sistema Oficial de Contrataciones &copy; {{ date('Y') }}</p>
        </div>
    </main>

</body>
</html>
