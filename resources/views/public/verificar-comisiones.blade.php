<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Oficial de Comisiones - {{ $empresa->nombre_comercial ?? 'Mariachi León' }}</title>
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
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-amber-500 text-[#0b1329] flex items-center justify-center font-bold text-lg shadow">
                    🦁
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-wider uppercase text-amber-400 leading-tight">
                        {{ $empresa->nombre_comercial ?? 'MARIACHI LEÓN GUANAJUATO' }}
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

    <main class="max-w-3xl mx-auto px-4 mt-6">
        @if(!$evento)
            <!-- Evento no encontrado -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 overflow-hidden">
                <div class="bg-red-600 px-6 py-6 text-white text-center">
                    <i class="fa-solid fa-triangle-exclamation text-5xl mb-2 text-red-100"></i>
                    <h2 class="text-xl font-bold tracking-wide">PLANILLA NO VÁLIDA O INEXISTENTE</h2>
                    <p class="text-sm text-red-100 mt-1">El código escaneado no corresponde a ninguna planilla oficial de liquidación de evento registrada.</p>
                </div>
                <div class="p-6 text-center">
                    <p class="text-sm text-slate-600 mb-4">
                        Este código QR podría ser fraudulento o haber caducado. Comuníquese directamente con la administración de <strong>{{ $empresa->nombre_comercial }}</strong>.
                    </p>
                    <div class="bg-slate-50 border border-slate-200 rounded p-3 text-xs font-mono text-slate-500 break-all mb-4">
                        Token consultado: {{ $token }}
                    </div>
                    <div class="text-xs text-slate-500">
                        Teléfono oficial: {{ $empresa->telefono }} | Correo: {{ $empresa->email }}
                    </div>
                </div>
            </div>
        @else
            <!-- Planilla Encontrada y Auténtica -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="bg-emerald-600 px-6 py-5 text-white text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-700 mb-2 shadow-inner">
                        <i class="fa-solid fa-shield-check text-3xl text-white"></i>
                    </div>
                    <h2 class="text-xl font-black uppercase tracking-wider">PLANILLA OFICIAL DE COMISIONES</h2>
                    <p class="text-xs text-emerald-100 mt-0.5">Emitida y certificada por el sistema comercial de {{ $empresa->nombre_comercial }}</p>
                </div>

                <div class="p-5 sm:p-7 space-y-6">
                    <!-- Ficha del Evento -->
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <div>
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Código del Evento</span>
                                <div class="font-mono font-extrabold text-lg text-slate-900">{{ $evento->codigo_evento }}</div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Contrato</span>
                                <div class="font-mono font-bold text-amber-700">{{ $evento->contrato->numero_contrato ?? 'Sin Contrato Formal' }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-600">
                            <div>
                                <strong class="text-slate-800">Cliente:</strong> {{ $evento->cliente->nombre_completo ?? 'N/A' }}
                            </div>
                            <div>
                                <strong class="text-slate-800">Servicio:</strong> {{ $evento->servicio->nombre ?? 'N/A' }}
                            </div>
                            <div>
                                <strong class="text-slate-800">Fecha de Presentación:</strong> {{ $evento->fecha_evento ? $evento->fecha_evento->format('d/m/Y') : '-' }} ({{ $evento->hora_evento ?: 'Hora a convenir' }})
                            </div>
                            <div>
                                <strong class="text-slate-800">Lugar:</strong> {{ $evento->direccion_evento ?? ($evento->lugar ?? 'No especificado') }}
                            </div>
                        </div>
                    </div>

                    <!-- Resumen Financiero -->
                    @php
                        $montoContratado = $evento->contrato ? (float)$evento->contrato->monto_total : (float)($evento->monto_total ?? 0);
                        $totalComisiones = (float)$evento->participantes->sum('monto_asignado');
                        $totalPagado = (float)$evento->participantes->where('estado_distribucion', 'Distribuido')->sum('monto_asignado');
                        $margen = $montoContratado - $totalComisiones;
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <span class="text-[10px] uppercase font-bold text-blue-700 block">Total Evento</span>
                            <span class="font-mono font-black text-sm text-blue-900">Bs {{ number_format($montoContratado, 2) }}</span>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <span class="text-[10px] uppercase font-bold text-amber-700 block">Comisiones Total</span>
                            <span class="font-mono font-black text-sm text-amber-900">Bs {{ number_format($totalComisiones, 2) }}</span>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                            <span class="text-[10px] uppercase font-bold text-emerald-700 block">Total Pagado</span>
                            <span class="font-mono font-black text-sm text-emerald-900">Bs {{ number_format($totalPagado, 2) }}</span>
                        </div>
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                            <span class="text-[10px] uppercase font-bold text-purple-700 block">Margen Grupo</span>
                            <span class="font-mono font-black text-sm text-purple-900">Bs {{ number_format($margen, 2) }}</span>
                        </div>
                    </div>

                    <!-- Tabla de Participantes -->
                    <div>
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span>Integrantes Asignados ({{ $evento->participantes->count() }})</span>
                            <span class="text-[11px] text-slate-500 font-normal">Información oficial verificada</span>
                        </h3>
                        <div class="overflow-x-auto border border-slate-200 rounded-lg">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-3">Músico / Personal</th>
                                        <th class="py-2.5 px-3">Rol / Función</th>
                                        <th class="py-2.5 px-3 text-right">Comisión (Bs)</th>
                                        <th class="py-2.5 px-3 text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 text-slate-700">
                                    @forelse($evento->participantes as $part)
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-2 px-3 font-semibold text-slate-900">
                                                {{ $part->persona->nombre_completo ?? ($part->persona->nombre ?? 'N/A') }}
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-800 text-[10px] font-medium">
                                                    {{ $part->funcion }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">
                                                Bs {{ number_format($part->monto_asignado, 2) }}
                                            </td>
                                            <td class="py-2 px-3 text-center">
                                                @if($part->estado_distribucion === 'Distribuido')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        PAGADO
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                        PENDIENTE
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-slate-500">
                                                Sin integrantes registrados aún.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Metadatos de Validación -->
                    <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-[11px] text-slate-500 space-y-1">
                        <div><strong>Fecha de Consulta:</strong> {{ $fechaConsulta }}</div>
                        <div class="font-mono text-[10px] break-all"><strong>Token de Seguridad:</strong> {{ $token }}</div>
                    </div>
                </div>
            </div>
        @endif
    </main>

    <footer class="max-w-3xl mx-auto px-4 mt-8 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} {{ $empresa->nombre_comercial ?? 'Mariachi León Guanajuato' }}. Todos los derechos reservados.
    </footer>
</body>
</html>
