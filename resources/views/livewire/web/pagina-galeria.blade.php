<div class="py-16 px-4 max-w-7xl mx-auto space-y-12">
    <!-- Header & Linktree CTA -->
    <div class="text-center space-y-4 max-w-3xl mx-auto">
        <span class="px-4 py-1.5 rounded-full bg-gold-500/10 text-gold-400 font-bold text-xs uppercase tracking-widest border border-gold-500/20">Galería Multimedia</span>
        <h1 class="font-serif font-bold text-4xl sm:text-5xl text-white">Fotos & Videos de Actuaciones</h1>
        <p class="text-base text-slate-300">
            Revisa las últimas 15 publicaciones, interactúa con nuestros videos de Facebook o explora el histórico por calendario.
        </p>
        
        <div class="pt-2 flex flex-wrap items-center justify-center gap-4">
            <a href="https://linktr.ee/mariachileonguanajuato" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 text-sm transition-all">
                <i class="fa-solid fa-tree text-lg"></i>
                <span>Ver Redes Sociales Completas (Linktree)</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Calendar Navigation & Filter Bar -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="font-bold text-sm text-gold-400 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-calendar-days"></i> Navegar por Calendario de Eventos
            </h3>
            @if($mes_filtro || $anio_filtro || $tipo_filtro || $categoria_filtro)
                <button wire:click="limpiarFiltros" class="text-xs text-rose-400 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar Filtros
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Year Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Año del Evento</label>
                <select wire:model.live="anio_filtro" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Años</option>
                    @foreach($anios as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Month Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mes del Evento</label>
                <select wire:model.live="mes_filtro" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Meses</option>
                    <option value="1">Enero</option>
                    <option value="2">Febrero</option>
                    <option value="3">Marzo</option>
                    <option value="4">Abril</option>
                    <option value="5">Mayo</option>
                    <option value="6">Junio</option>
                    <option value="7">Julio</option>
                    <option value="8">Agosto</option>
                    <option value="9">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>

            <!-- Type Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tipo de Recurso</label>
                <select wire:model.live="tipo_filtro" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos (Fotos, Videos & Facebook)</option>
                    <option value="foto">Solo Fotografías</option>
                    <option value="video">Solo Videos YouTube</option>
                    <option value="facebook">Solo Facebook Videos</option>
                </select>
            </div>

            <!-- Category Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Categoría</label>
                <select wire:model.live="categoria_filtro" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todas las Categorías</option>
                    <option value="Bodas">Bodas</option>
                    <option value="XV Años">XV Años</option>
                    <option value="Serenatas">Serenatas</option>
                    <option value="Corporativos">Corporativos</option>
                    <option value="Cumpleaños">Cumpleaños</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Gallery Grid (15 items max por página) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($items as $item)
            <div class="group rounded-3xl overflow-hidden bg-slate-900 border border-slate-800 shadow-xl space-y-4 flex flex-col justify-between hover:border-gold-500/40 transition-all">
                <div class="space-y-4">
                    @if($item->tipo === 'facebook' || $item->facebook_url)
                        <div class="aspect-video w-full bg-slate-950 relative overflow-hidden">
                            <iframe src="https://www.facebook.com/plugins/video.php?height=314&href={{ urlencode($item->facebook_url ?: $item->video_url) }}&show_text=false" class="w-full h-full border-0" allowfullscreen allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                        </div>
                    @elseif($item->tipo === 'video')
                        <div class="aspect-video w-full bg-slate-950 relative overflow-hidden">
                            @if(str_contains($item->video_url, 'embed') || str_contains($item->video_url, 'youtube'))
                                <iframe src="{{ $item->video_url }}" class="w-full h-full border-0" allowfullscreen></iframe>
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-4 text-center">
                                    <i class="fa-solid fa-circle-play text-4xl text-gold-400 mb-2"></i>
                                    <a href="{{ $item->video_url }}" target="_blank" class="text-xs text-gold-400 underline font-bold">Ver Video en Plataforma</a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="aspect-video w-full bg-slate-950 overflow-hidden relative">
                            <img src="{{ $item->imagen_url }}" alt="{{ $item->titulo }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    @endif

                    <div class="p-6 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gold-400 uppercase tracking-widest">{{ $item->categoria }}</span>
                            <span class="text-xs text-slate-400 font-mono"><i class="fa-regular fa-calendar mr-1"></i>{{ $item->fecha_evento ? $item->fecha_evento->format('d/m/Y') : 'Reciente' }}</span>
                        </div>
                        <h3 class="font-bold text-lg text-white group-hover:text-gold-300 transition-colors">{{ $item->titulo }}</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ $item->descripcion }}</p>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-2 flex items-center justify-between text-xs text-slate-500 border-t border-slate-800/80">
                    <span class="flex items-center gap-1">
                        <i class="fa-solid {{ $item->tipo === 'facebook' ? 'fa-brands fa-facebook text-blue-400' : ($item->tipo === 'video' ? 'fa-video text-rose-400' : 'fa-image text-emerald-400') }}"></i>
                        <span class="capitalize">{{ $item->tipo }}</span>
                    </span>

                    @if($item->tipo === 'facebook' || $item->facebook_url)
                        <a href="{{ $item->facebook_url ?: $item->video_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-all shadow-md hover:scale-105">
                            <i class="fa-brands fa-facebook-f"></i>
                            <span>👍 Me gusta en Facebook</span>
                        </a>
                    @elseif($item->destacado)
                        <span class="text-gold-400 font-semibold"><i class="fa-solid fa-star"></i> Destacado</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center space-y-4">
                <i class="fa-solid fa-images text-4xl text-slate-600"></i>
                <h4 class="text-lg font-bold text-white">No se encontraron publicaciones en el calendario</h4>
                <p class="text-xs text-slate-400">Prueba cambiando el mes o el año seleccionado.</p>
                <button wire:click="limpiarFiltros" class="px-4 py-2 bg-gold-500 text-slate-950 font-bold rounded-xl text-xs">
                    Ver Últimas Publicaciones
                </button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-6">
        {{ $items->links() }}
    </div>
</div>
