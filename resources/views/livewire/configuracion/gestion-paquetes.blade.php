<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Paquetes de Servicios</h1>
            <p class="text-xs text-slate-400">Combos especiales de servicios con tarifa preferencial para clientes.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo Paquete</span>
        </button>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Paquete</th>
                        <th class="px-6 py-4">Servicios Incluidos</th>
                        <th class="px-6 py-4">Precio Paquete</th>
                        <th class="px-6 py-4">Estado / Destacado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($paquetes as $p)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-white">
                                <div class="flex items-center gap-2">
                                    <span>{{ $p->nombre }}</span>
                                    @if($p->destacado)
                                        <i class="fa-solid fa-star text-gold-400 text-xs" title="Destacado en Web"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($p->servicios as $serv)
                                        <span class="px-2 py-0.5 rounded text-xs bg-slate-950 text-slate-300 border border-slate-800">
                                            {{ $serv->nombre }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-gold-400">
                                Bs. {{ number_format($p->precio_paquete, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($p->activo)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button wire:click="editar({{ $p->id }})" class="p-2 text-slate-400 hover:text-gold-400 hover:bg-gold-500/10 rounded-lg transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button wire:click="eliminar({{ $p->id }})" onclick="return confirm('¿Eliminar este paquete?')" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No hay paquetes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $paquetes->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-lg bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">{{ $isEdit ? 'Editar Paquete' : 'Nuevo Paquete' }}</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre del Paquete</label>
                        <input type="text" wire:model.defer="nombre" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Paquete Boda Premium">
                        @error('nombre') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Descripción</label>
                        <textarea wire:model.defer="descripcion" rows="3" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Precio del Paquete (Bs.)</label>
                        <input type="number" step="0.01" wire:model.defer="precio_paquete" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('precio_paquete') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Servicios Incluidos</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto p-3 bg-slate-950 border border-slate-800 rounded-xl">
                            @foreach($allServicios as $s)
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                                    <input type="checkbox" wire:model="selectedServicios" value="{{ $s->id }}" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-gold-500">
                                    <span>{{ $s->nombre }} (Bs. {{ number_format($s->precio_base, 2) }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="destacado" class="w-4 h-4 rounded bg-slate-950 text-gold-500">
                            <span class="text-xs font-semibold text-slate-300">Destacado en Web</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="activo" class="w-4 h-4 rounded bg-slate-950 text-gold-500">
                            <span class="text-xs font-semibold text-slate-300">Paquete Activo</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
