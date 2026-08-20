<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Asignaciones de Equipos & Bienes</h1>
            <p class="text-xs text-slate-400">Entrega formal de instrumentos, trajes y equipos al personal o músicos responsables.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-check"></i>
            <span>Nueva Asignación</span>
        </button>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">N° Asig / Fecha</th>
                        <th class="px-6 py-4">Artículo Asignado</th>
                        <th class="px-6 py-4">Músico / Responsable</th>
                        <th class="px-6 py-4">Cantidad</th>
                        <th class="px-6 py-4">Condición Entrega</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Registrado Por</th>
                        <th class="px-6 py-4 text-right">Comprobante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($asignaciones as $asig)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs">
                                <div class="font-bold text-gold-400">ASIG-{{ str_pad($asig->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $asig->fecha_asignacion->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $asig->asset->nombre ?? 'N/A' }}</div>
                                <div class="text-xs font-mono text-gold-400">{{ $asig->asset->codigo ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-200">
                                <i class="fa-solid fa-user-tie text-gold-400 mr-1.5"></i>{{ $asig->responsable->nombre_completo ?? 'N/A' }}
                                <div class="text-[11px] font-normal text-slate-400">{{ $asig->responsable->tipo ?? 'Músico' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-200">
                                {{ number_format($asig->cantidad, 2) }} und.
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300">
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-semibold">{{ $asig->condicion_entrega }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($asig->estado === 'activo')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        Asignado Activo
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Devuelto
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                <i class="fa-solid fa-user-gear text-slate-500 mr-1"></i>{{ $asig->user->name ?? 'Sistema' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.activos-fijos.comprobante', ['tipo' => 'asignacion', 'id' => $asig->id]) }}" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gold-500/10 text-gold-400 hover:bg-gold-500/20 border border-gold-500/30 transition-colors inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-file-pdf"></i>
                                    <span>Comprobante</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">No se registran asignaciones de activos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $asignaciones->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-xl bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">Nueva Asignación de Activo / Equipo</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Seleccionar Artículo a Asignar</label>
                        <select wire:model.live="asset_id" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione Artículo Disponible...</option>
                            @foreach($articulos as $item)
                                <option value="{{ $item->id }}">[{{ $item->codigo }}] {{ $item->nombre }} - (Disponible: {{ number_format($item->existencia, 2) }})</option>
                            @endforeach
                        </select>
                        @error('asset_id') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Músico / Personal Responsable (Catálogo Centralizado)</label>
                        <select wire:model.defer="responsable_id" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione Responsable...</option>
                            @foreach($responsables as $resp)
                                <option value="{{ $resp->id }}">{{ $resp->nombre_completo }} ({{ $resp->tipo }} - Tel: {{ $resp->telefono ?: 'S/N' }})</option>
                            @endforeach
                        </select>
                        @error('responsable_id') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fecha / Hora</label>
                            <input type="datetime-local" wire:model.defer="fecha_asignacion" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('fecha_asignacion') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Cantidad</label>
                            <input type="number" step="0.01" wire:model.defer="cantidad" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('cantidad') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Condición Entrega</label>
                            <input type="text" wire:model.defer="condicion_entrega" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Excelente / Bueno / Operativo">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Observaciones</label>
                        <textarea wire:model.defer="observaciones" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Detalles de funda, cables o acuerdo de uso..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20">Registrar Asignación</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
