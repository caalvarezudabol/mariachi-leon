<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar (No Imprimible) -->
    <div class="flex items-center justify-between bg-brand-card p-4 rounded-2xl border border-brand-border print:hidden">
        <div>
            <h2 class="text-lg font-bold text-white">Comprobante Oficial de {{ ucfirst($tipo) }}</h2>
            <p class="text-xs text-slate-400">Documento imprimible para la firma y custodia de activos.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>Imprimir / Guardar en PDF</span>
            </button>
        </div>
    </div>

    <!-- Voucher Printable Document -->
    <div class="bg-white text-slate-900 p-8 sm:p-12 rounded-3xl shadow-2xl border border-slate-200 print:border-none print:shadow-none print:p-0 print:rounded-none space-y-8 font-sans">
        <!-- Header Document -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-950 text-gold-400 flex items-center justify-center text-3xl font-extrabold shadow-md border border-gold-500">
                    🎷
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-wider text-slate-950">Mariachi León Guanajuato</h1>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-widest">Sistema de Control de Activos Fijos & Bienes</p>
                    <p class="text-xs text-slate-500">León, Guanajuato, México • Santa Cruz, Bolivia</p>
                </div>
            </div>
            <div class="text-right font-mono">
                <div class="text-xs font-bold uppercase text-slate-500">Comprobante N°</div>
                <div class="text-xl font-extrabold text-slate-950">
                    {{ strtoupper($tipo) === 'ASIGNACION' ? 'ASIG-' . str_pad($registro->id, 5, '0', STR_PAD_LEFT) : 'DEV-' . str_pad($registro->id, 5, '0', STR_PAD_LEFT) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    {{ $tipo === 'asignacion' ? $registro->fecha_asignacion->format('d/m/Y H:i') : $registro->fecha_devolucion->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Voucher Title Banner -->
        <div class="bg-slate-100 p-4 rounded-xl text-center border border-slate-200">
            <h2 class="text-lg font-black text-slate-900 uppercase tracking-wider">
                COMPROBANTE OFICIAL DE {{ strtoupper($tipo) }} DE ACTIVO FIJO
            </h2>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-2 gap-6 text-sm">
            <!-- Left: Datos del Responsable -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2">
                    Músico / Personal Responsable
                </h3>
                <div class="space-y-1">
                    <div class="text-xs text-slate-500">Nombre Completo:</div>
                    <div class="font-bold text-base text-slate-950">{{ $registro->responsable->nombre_completo ?? 'N/A' }}</div>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                    <div>
                        <span class="text-slate-500">Tipo:</span>
                        <div class="font-semibold text-slate-900">{{ $registro->responsable->tipo ?? 'Músico' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-500">Teléfono:</span>
                        <div class="font-semibold text-slate-900">{{ $registro->responsable->telefono ?? 'S/N' }}</div>
                    </div>
                </div>
            </div>

            <!-- Right: Trazabilidad Operador -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2">
                    Trazabilidad de Registro (Operador)
                </h3>
                <div class="space-y-1">
                    <div class="text-xs text-slate-500">Usuario Autenticado:</div>
                    <div class="font-bold text-base text-slate-950">{{ $registro->user->name ?? 'Sistema' }}</div>
                </div>
                <div class="text-xs text-slate-600 pt-1">
                    <span class="text-slate-500">Fecha de Operación:</span>
                    <div class="font-mono font-semibold text-slate-900">
                        {{ $tipo === 'asignacion' ? $registro->fecha_asignacion->format('d/m/Y H:i:s') : $registro->fecha_devolucion->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Specifications Table -->
        <div class="space-y-3">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-500">Especificación del Artículo / Equipo</h3>
            <table class="w-full text-left text-sm border-collapse border border-slate-300">
                <thead class="bg-slate-100 text-xs uppercase font-bold text-slate-700">
                    <tr>
                        <th class="p-3 border border-slate-300">Código AF</th>
                        <th class="p-3 border border-slate-300">Nombre del Artículo</th>
                        <th class="p-3 border border-slate-300">Categoría</th>
                        <th class="p-3 border border-slate-300">Marca / Modelo / Serie</th>
                        <th class="p-3 border border-slate-300 text-center">Cantidad</th>
                        <th class="p-3 border border-slate-300">Condición</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    <tr>
                        <td class="p-3 border border-slate-300 font-mono font-bold text-slate-950">
                            {{ $registro->asset->codigo ?? 'N/A' }}
                        </td>
                        <td class="p-3 border border-slate-300 font-bold text-slate-950">
                            {{ $registro->asset->nombre ?? 'N/A' }}
                        </td>
                        <td class="p-3 border border-slate-300 text-slate-700">
                            {{ $registro->asset->category->nombre ?? 'N/A' }}
                        </td>
                        <td class="p-3 border border-slate-300 text-slate-700">
                            {{ $registro->asset->marca }} {{ $registro->asset->modelo }}
                            @if($registro->asset->numero_serie)
                                <div class="font-mono text-[11px] text-slate-500">SN: {{ $registro->asset->numero_serie }}</div>
                            @endif
                        </td>
                        <td class="p-3 border border-slate-300 font-mono font-bold text-center text-slate-950">
                            {{ number_format($registro->cantidad, 2) }}
                        </td>
                        <td class="p-3 border border-slate-300 font-semibold text-slate-900">
                            {{ $tipo === 'asignacion' ? $registro->condicion_entrega : $registro->condicion_recepcion }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Observaciones -->
        @if($registro->observaciones)
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-1">
                <span class="font-bold text-slate-700 uppercase">Observaciones:</span>
                <p class="text-slate-800 italic">{{ $registro->observaciones }}</p>
            </div>
        @endif

        <!-- Declaración y Compromiso -->
        <div class="text-xs text-slate-600 leading-relaxed border-t border-slate-200 pt-4 space-y-1">
            <p class="font-semibold text-slate-800">Declaración de Responsabilidad:</p>
            @if($tipo === 'asignacion')
                <p>El Músico/Personal responsable declara haber recibido a entera satisfacción el bien o equipo arriba detallado en las condiciones descritas, comprometiéndose a mantenerlo en buen estado y devolverlo en la fecha establecida o cuando sea requerido por la administración de la agrupación Mariachi León Guanajuato.</p>
            @else
                <p>El operador del sistema certifica haber recibido el bien o equipo devuelto por el Músico/Personal en las condiciones indicadas, reingresándolo al inventario activo de la agrupación Mariachi León Guanajuato.</p>
            @endif
        </div>

        <!-- Signatures Block -->
        <div class="grid grid-cols-2 gap-12 pt-16 text-center">
            <div class="space-y-2 border-t-2 border-slate-900 pt-3">
                <div class="font-bold text-sm text-slate-950">{{ $registro->user->name ?? 'Carlos Álvarez' }}</div>
                <div class="text-xs text-slate-500 uppercase font-semibold">Firma Entregador / Operador</div>
                <div class="text-[10px] text-slate-400">Mariachi León Guanajuato</div>
            </div>

            <div class="space-y-2 border-t-2 border-slate-900 pt-3">
                <div class="font-bold text-sm text-slate-950">{{ $registro->responsable->nombre_completo ?? 'Juan Pérez' }}</div>
                <div class="text-xs text-slate-500 uppercase font-semibold">Firma Receptor / Responsable</div>
                <div class="text-[10px] text-slate-400">CI: ____________________</div>
            </div>
        </div>
    </div>
</div>
