<div class="py-10 px-4 max-w-7xl mx-auto space-y-6">
    <div class="text-center space-y-2 max-w-2xl mx-auto">
        <h1 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white">Galería</h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300">Fotos y videos de nuestras presentaciones.</p>
    </div>

    @php
        $chip = 'px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors whitespace-nowrap';
        $chipOn = 'bg-gold-500 text-slate-950 border-gold-500';
        $chipOff =
            'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-gold-500/50';
        $categorias = ['Bodas', 'XV Años', 'Serenatas', 'Corporativos', 'Cumpleaños'];
        $tipos = ['foto' => 'Fotos', 'video' => 'Videos', 'facebook' => 'Facebook'];
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    @endphp

    <!-- Filtros compactos -->
    <div class="space-y-3">
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <button wire:click="$set('categoria_filtro', '')"
                class="{{ $chip }} {{ !$categoria_filtro ? $chipOn : $chipOff }}">Todo</button>
            @foreach ($categorias as $cat)
                <button wire:click="$set('categoria_filtro', '{{ $cat }}')"
                    class="{{ $chip }} {{ $categoria_filtro === $cat ? $chipOn : $chipOff }}">{{ $cat }}</button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @foreach ($tipos as $valor => $texto)
                <button wire:click="$set('tipo_filtro', '{{ $tipo_filtro === $valor ? '' : $valor }}')"
                    class="{{ $chip }} {{ $tipo_filtro === $valor ? $chipOn : $chipOff }}">{{ $texto }}</button>
            @endforeach

            <select wire:model.live="anio_filtro"
                class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-slate-900 dark:text-white text-xs focus:border-gold-500">
                <option value="">Año</option>
                @foreach ($anios as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>

            <select wire:model.live="mes_filtro"
                class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-slate-900 dark:text-white text-xs focus:border-gold-500">
                <option value="">Mes</option>
                @foreach ($meses as $n => $nombreMes)
                    <option value="{{ $n }}">{{ $nombreMes }}</option>
                @endforeach
            </select>

            @if ($mes_filtro || $anio_filtro || $tipo_filtro || $categoria_filtro)
                <button wire:click="limpiarFiltros"
                    class="text-xs text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1 px-2">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar
                </button>
            @endif
        </div>
    </div>

    <!-- Galería -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($items as $item)
            <div class="group rounded-2xl overflow-hidden bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col hover:border-gold-500/40 transition-all hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
                data-reveal>
                @if ($item->tipo === 'facebook' || $item->facebook_url)
                    <div class="aspect-video w-full bg-white dark:bg-slate-950 relative overflow-hidden">
                        <iframe
                            src="https://www.facebook.com/plugins/video.php?height=314&href={{ urlencode($item->facebook_url ?: $item->video_url) }}&show_text=false"
                            class="w-full h-full border-0" allowfullscreen
                            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                    </div>
                @elseif($item->tipo === 'video')
                    <div class="aspect-video w-full bg-white dark:bg-slate-950 relative overflow-hidden">
                        @if (str_contains($item->video_url, 'embed') || str_contains($item->video_url, 'youtube'))
                            <iframe src="{{ $item->video_url }}" class="w-full h-full border-0"
                                allowfullscreen></iframe>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-4 text-center">
                                <i class="fa-solid fa-circle-play text-4xl text-gold-700 dark:text-gold-400 mb-2"></i>
                                <a href="{{ $item->video_url }}" target="_blank"
                                    class="text-xs text-gold-700 dark:text-gold-400 underline font-bold">Ver video</a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="aspect-video w-full bg-white dark:bg-slate-950 overflow-hidden relative">
                        <img src="{{ $item->imagen_url }}" alt="{{ $item->titulo }}" loading="lazy"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                @endif

                <div class="p-4 space-y-1.5 flex-1">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-bold text-gold-700 dark:text-gold-400 uppercase tracking-widest">{{ $item->categoria }}</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400"><i
                                class="fa-regular fa-calendar mr-1"></i>{{ $item->fecha_evento ? $item->fecha_evento->format('d/m/Y') : 'Reciente' }}</span>
                    </div>
                    <h3
                        class="font-bold text-base text-slate-900 dark:text-white group-hover:text-gold-700 dark:group-hover:text-gold-300 transition-colors">
                        {{ $item->titulo }}</h3>
                    @if ($item->descripcion)
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">
                            {{ $item->descripcion }}</p>
                    @endif
                </div>

                @if ($item->tipo === 'facebook' || $item->facebook_url)
                    <div class="px-4 pb-4">
                        <a href="{{ $item->facebook_url ?: $item->video_url }}" target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-all">
                            <i class="fa-brands fa-facebook-f"></i>
                            <span>Ver en Facebook</span>
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full py-12 text-center space-y-3">
                <i class="fa-solid fa-images text-4xl text-slate-600"></i>
                <h4 class="text-base font-bold text-slate-900 dark:text-white">No hay publicaciones con esos filtros
                </h4>
                <button wire:click="limpiarFiltros"
                    class="px-4 py-2 bg-gold-500 text-slate-950 font-bold rounded-xl text-xs">
                    Ver últimas publicaciones
                </button>
            </div>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $items->links() }}
    </div>
</div>