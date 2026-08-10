<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Banner de Inicio (Hero Slider)</h1>
            <p class="text-xs text-slate-400">Administra las imágenes, títulos y textos del carrusel principal de la página de inicio.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo Banner</span>
        </button>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Orden</th>
                        <th class="px-6 py-4">Imagen Preview</th>
                        <th class="px-6 py-4">Título / Subtítulo</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($banners as $b)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-gold-400">
                                #{{ $b->orden }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-20 h-12 rounded-xl overflow-hidden bg-slate-950 border border-slate-800">
                                    <img src="{{ $b->imagen_url }}" alt="{{ $b->titulo }}" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <div class="font-semibold text-white truncate">{{ $b->titulo }}</div>
                                <div class="text-xs text-slate-400 truncate">{{ $b->subtitulo }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($b->activo)
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
                                <button wire:click="editar({{ $b->id }})" class="p-2 text-slate-400 hover:text-gold-400 hover:bg-gold-500/10 rounded-lg transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button wire:click="eliminar({{ $b->id }})" onclick="return confirm('¿Eliminar este banner de inicio?')" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No hay banners de inicio configurados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $banners->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-lg bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">{{ $isEdit ? 'Editar Banner' : 'Nuevo Banner' }}</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Título del Banner</label>
                        <input type="text" wire:model.defer="titulo" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. El Mejor Mariachi de Gala">
                        @error('titulo') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Subtítulo / Descripción Corta</label>
                        <textarea wire:model.defer="subtitulo" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Puntualidad y elegancia en cada evento..."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">URL de la Imagen de Fondo</label>
                        <input type="text" wire:model.defer="imagen_url" required placeholder="/assets/images/... o https://..." class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('imagen_url') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Texto Botón</label>
                            <input type="text" wire:model.defer="boton_texto" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Orden de Aparición</label>
                            <input type="number" wire:model.defer="orden" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('orden') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" wire:model="activo" id="banner_activo" class="w-4 h-4 rounded bg-slate-950 text-gold-500">
                        <label for="banner_activo" class="text-sm font-semibold text-slate-300">Banner Activo</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20">Guardar Banner</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
