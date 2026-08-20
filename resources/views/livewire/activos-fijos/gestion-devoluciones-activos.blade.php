<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Devoluciones de Activos</h1>
            <p class="text-xs text-slate-400">Recepción y reingreso al inventario de equipos devueltos por el personal o músicos.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-cyan-500 to-cyan-600 text-slate-950 hover:from-cyan-400 hover:to-cyan-500 shadow-lg shadow-cyan-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-arrow-rotate-left"></i>
            <span>Registrar Devolución</span>
        </button>
    </div>

    <!-- Section: Asignaciones Pendientes de Devolución -->
    <div class="bg-brand-card p-5 rounded-2xl border border-brand-border space-y-4">
        <h3 class="font-bold text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-gold-400"></i>
            <span>Asignaciones Activas Pendientes de Devolución ({{ $asignacionesActivas->count() }})</span>
        </h3>

        @if($asignacionesActivas->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($asignacionesActivas as $asig)
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-bold text-gold-400">ASIG-{{ str_pad($asig->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-[11px] text-slate-400">{{ $asig->fecha_asignacion->format('d/m/Y') }}</span>
                            </div>
                            <div class="font-bold text-white mt-1">{{ $asig->asset->nombre ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $asig->asset->codigo ?? '' }}</div>
                            <div class="text-xs text-emerald-400 font-bold mt-2">
                                <i class="fa-solid fa-user-tie mr-1"></i>{{ $asig->responsable->nombre_completo ?? 'N/A' }}
                            </div>
                        </div>
                        <button wire:click="registrarDevolucionDirecta({{ $asig->id }})" class="w-full py-2 px-3 rounded-lg text-xs font-bold bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500/20 border border-cyan-500/30 transition-colors flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span>Procesar Devolución</span>
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-500 italic">No hay asignaciones pendientes de devolución en este momento.</p>
        @endif
    </div>

    <!-- Table of Returns -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border bg-slate-950/60">
            <h3 class="font-bold text-white text-sm">Historial de Devoluciones Procesadas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">N° Dev / Fecha</th>
                        <th class="px-6 py-4">Artículo Devuelto</th>
                        <th class="px-6 py-4">Músico que Devuelve</th>
                        <th class="px-6 py-4">Cantidad</th>
                        <th class="px-6 py-4">Condición Recepción</th>
                        <th class="px-6 py-4">Recibido Por</th>
                        <th class="px-6 py-4 text-right">Comprobante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($devoluciones as $dev)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs">
                                <div class="font-bold text-cyan-400">DEV-{{ str_pad($dev->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $dev->fecha_devolucion->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $dev->asset->nombre ?? 'N/A' }}</div>
                                <div class="text-xs font-mono text-gold-400">{{ $dev->asset->codigo ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-200">
                                <i class="fa-solid fa-user-tie text-emerald-400 mr-1.5"></i>{{ $dev->responsable->nombre_completo ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-200">
                                {{ number_format($dev->cantidad, 2) }} und.
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300">
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-semibold">{{ $dev->condicion_recepcion }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                <i class="fa-solid fa-user-gear text-slate-500 mr-1"></i>{{ $dev->user->name ?? 'Sistema' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.activos-fijos.comprobante', ['tipo' => 'devolucion', 'id' => $dev->id]) }}" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500/20 border border-cyan-500/30 transition-colors inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-file-pdf"></i>
                                    <span>Comprobante</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">No se registran devoluciones procesadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $devoluciones->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-xl bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">Registrar Devolución de Activo</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Seleccionar Asignación Activa a Devolver</label>
                        <select wire:model.defer="asset_assignment_id" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione Asignación...</option>
                            @foreach($asignacionesActivas as $asig)
                                <option value="{{ $asig->id }}">
                                    [ASIG-{{ str_pad($asig->id, 5, '0', STR_PAD_LEFT) }}] {{ $asig->asset->nombre ?? 'N/A' }} - Devuelve: {{ $asig->responsable->nombre_completo ?? 'Músico' }}
                                </option>
                            @endforeach
                        </select>
                        @error('asset_assignment_id') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fecha y Hora Devolución</label>
                            <input type="datetime-local" wire:model.defer="fecha_devolucion" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('fecha_devolucion') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Condición de Recepción</label>
                            <input type="text" wire:model.defer="condicion_recepcion" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Excelente / Bueno / Con desgaste">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Observaciones</label>
                        <textarea wire:model.defer="observaciones" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Detalles de la entrega, mantenimiento requerido o novedad..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-cyan-500 text-slate-950 hover:bg-cyan-400 shadow-lg shadow-cyan-500/20">Registrar Devolución</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
