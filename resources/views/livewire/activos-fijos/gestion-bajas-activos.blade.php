<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Bajas de Activos & Equipos</h1>
            <p class="text-xs text-slate-400">Retiro definitivo o desincorporación por obsolescencia, daño irreparable o pérdida.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-rose-500 to-rose-600 text-white hover:from-rose-400 hover:to-rose-500 shadow-lg shadow-rose-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-trash-can"></i>
            <span>Registrar Baja de Activo</span>
        </button>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">N° Baja / Fecha</th>
                        <th class="px-6 py-4">Artículo Dado de Baja</th>
                        <th class="px-6 py-4">Motivo de Baja</th>
                        <th class="px-6 py-4">Cantidad</th>
                        <th class="px-6 py-4">Observaciones / Justificación</th>
                        <th class="px-6 py-4">Responsable</th>
                        <th class="px-6 py-4">Registrado Por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($bajas as $baja)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs">
                                <div class="font-bold text-rose-400">BAJA-{{ str_pad($baja->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $baja->fecha_baja->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $baja->asset->nombre ?? 'N/A' }}</div>
                                <div class="text-xs font-mono text-gold-400">{{ $baja->asset->codigo ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">
                                    {{ str_replace('_', ' ', $baja->motivo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-rose-400">
                                -{{ number_format($baja->cantidad, 2) }} und.
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300 max-w-xs">
                                {{ $baja->observaciones }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                {{ $baja->responsable->nombre_completo ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                <i class="fa-solid fa-user-gear text-slate-500 mr-1"></i>{{ $baja->user->name ?? 'Sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">No se registran bajas de activos en el sistema.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $bajas->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-xl bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">Registrar Baja de Activo Fijo</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Seleccionar Artículo a Dar de Baja</label>
                        <select wire:model.live="asset_id" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione Artículo...</option>
                            @foreach($articulos as $item)
                                <option value="{{ $item->id }}">[{{ $item->codigo }}] {{ $item->nombre }} - (Disponible: {{ number_format($item->existencia, 2) }})</option>
                            @endforeach
                        </select>
                        @error('asset_id') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fecha de Baja</label>
                            <input type="date" wire:model.defer="fecha_baja" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('fecha_baja') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Motivo Obligatorio</label>
                            <select wire:model.defer="motivo" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="obsolescencia">Obsolescencia</option>
                                <option value="dano_irreparable">Daño Irreparable</option>
                                <option value="deterioro">Deterioro Extremo</option>
                                <option value="perdida">Pérdida / Extravío</option>
                                <option value="desuso">Desuso Prolongado</option>
                                <option value="otro">Otro Motivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Cantidad a Dar de Baja</label>
                            <input type="number" step="0.01" wire:model.defer="cantidad" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('cantidad') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Responsable Asociado (Opcional)</label>
                            <select wire:model.defer="responsable_id" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="">Sin Responsable</option>
                                @foreach($responsables as $resp)
                                    <option value="{{ $resp->id }}">{{ $resp->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Justificación / Observaciones de Baja</label>
                        <textarea wire:model.defer="observaciones" rows="3" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Explicación detallada del motivo de desincorporación..."></textarea>
                        @error('observaciones') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-rose-500 text-white hover:bg-rose-400 shadow-lg shadow-rose-500/20">Confirmar Baja de Activo</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
