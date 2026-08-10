<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Galería (Fotos, Videos & Facebook)</h1>
            <p class="text-xs text-slate-400">Publica fotos y enlaces de videos (YouTube / Facebook) para mostrarlos en el sitio web institucional.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Nueva Publicación</span>
        </button>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Vista Previa / Tipo</th>
                        <th class="px-6 py-4">Título / Categoría</th>
                        <th class="px-6 py-4">Tipo Recurso</th>
                        <th class="px-6 py-4">Fecha Evento</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($items as $g)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="w-16 h-12 rounded-xl overflow-hidden bg-slate-950 border border-slate-800 flex items-center justify-center relative">
                                    @if($g->tipo === 'facebook')
                                        <i class="fa-brands fa-facebook-f text-blue-500 text-xl"></i>
                                    @elseif($g->tipo === 'video')
                                        <i class="fa-solid fa-play text-gold-400 text-xl"></i>
                                    @else
                                        <img src="{{ $g->imagen_url }}" alt="{{ $g->titulo }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ $g->titulo }}</div>
                                <div class="text-xs text-gold-400 font-semibold">{{ $g->categoria }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($g->tipo === 'facebook')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-600/20 text-blue-400 border border-blue-500/30">
                                        <i class="fa-brands fa-facebook mr-1"></i> Video Facebook
                                    </span>
                                @elseif($g->tipo === 'video')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <i class="fa-solid fa-video mr-1"></i> Video YouTube
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <i class="fa-solid fa-image mr-1"></i> Fotografía
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-300 font-mono text-xs">
                                {{ $g->fecha_evento ? $g->fecha_evento->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button wire:click="editar({{ $g->id }})" class="p-2 text-slate-400 hover:text-gold-400 hover:bg-gold-500/10 rounded-lg transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button wire:click="eliminar({{ $g->id }})" onclick="return confirm('¿Eliminar esta publicación?')" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No hay publicaciones en la galería.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-lg bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">{{ $isEdit ? 'Editar Publicación' : 'Nueva Publicación' }}</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Título de la Publicación</label>
                        <input type="text" wire:model.defer="titulo" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Presentación Especial en Facebook">
                        @error('titulo') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Tipo de Recurso</label>
                            <select wire:model.live="tipo" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="foto">Fotografía</option>
                                <option value="video">Video YouTube / Directo</option>
                                <option value="facebook">Video de Facebook (Embed & Like)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Categoría</label>
                            <select wire:model.defer="categoria" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="Bodas">Bodas</option>
                                <option value="XV Años">XV Años</option>
                                <option value="Serenatas">Serenatas</option>
                                <option value="Corporativos">Corporativos</option>
                                <option value="Cumpleaños">Cumpleaños</option>
                                <option value="General">General</option>
                            </select>
                        </div>
                    </div>

                    @if($tipo === 'foto')
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">URL de la Imagen</label>
                            <input type="text" wire:model.defer="imagen_url" placeholder="https://..." class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('imagen_url') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @elseif($tipo === 'facebook')
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Enlace Oficial de Video en Facebook (URL)</label>
                            <input type="text" wire:model.defer="facebook_url" placeholder="https://www.facebook.com/watch/?v=... o https://fb.watch/..." class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <span class="text-[11px] text-slate-400 mt-1 block">Permitirá reproducciones y dará opción de Me Gusta directo a tu página de Facebook.</span>
                            @error('facebook_url') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">URL del Video (YouTube Embed)</label>
                            <input type="text" wire:model.defer="video_url" placeholder="https://www.youtube.com/embed/..." class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('video_url') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fecha del Evento</label>
                        <input type="date" wire:model.defer="fecha_evento" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('fecha_evento') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Descripción</label>
                        <textarea wire:model.defer="descripcion" rows="3" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" wire:model="destacado" id="galeria_destacado" class="w-4 h-4 rounded bg-slate-950 text-gold-500">
                        <label for="galeria_destacado" class="text-sm font-semibold text-slate-300">Destacar en la Web</label>
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
