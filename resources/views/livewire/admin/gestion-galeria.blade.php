@php
    $imgSrc = fn ($u) => \Illuminate\Support\Str::startsWith((string) $u, ['http://', 'https://', '//']) ? $u : asset(ltrim((string) $u, '/'));
    $totalItems = $totalFotos + $totalVideos + $totalFacebook;
    $tipoEtiqueta = ['foto' => 'Fotografía', 'video' => 'Video YouTube', 'facebook' => 'Video Facebook'];
    $tipoIcono = ['foto' => 'fa-solid fa-image', 'video' => 'fa-solid fa-video', 'facebook' => 'fa-brands fa-facebook'];
    $selectFiltro = 'h-8 px-2.5 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors';
@endphp

<div class="space-y-3">

    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-photo-film text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Galería</h1>
                <x-ui.badge variant="neutral">{{ $totalItems }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nueva publicación</span>
                <span class="sm:hidden">Nueva</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Filtros: una sola fila --}}
        <x-slot:filters>
            <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por título o descripción..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <select wire:model.live="categoria_filtro" class="{{ $selectFiltro }}">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('tipo_filtro', '')" :active="$tipo_filtro === ''">
                        Todos <span class="opacity-70">{{ $totalItems }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('tipo_filtro', 'foto')" :active="$tipo_filtro === 'foto'">
                        Fotos <span class="opacity-70">{{ $totalFotos }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('tipo_filtro', 'video')" :active="$tipo_filtro === 'video'">
                        YouTube <span class="opacity-70">{{ $totalVideos }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('tipo_filtro', 'facebook')" :active="$tipo_filtro === 'facebook'">
                        Facebook <span class="opacity-70">{{ $totalFacebook }}</span>
                    </x-ui.filter-chip>
                </x-ui.filter-chips>
                @if ($search || $tipo_filtro !== '' || $categoria_filtro !== '')
                    <x-ui.button size="sm" variant="secondary" wire:click="limpiarFiltros">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Limpiar</span>
                    </x-ui.button>
                @endif
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline w-px">Vista</th>
                    <th class="px-3 py-2 border-r border-outline">Título</th>
                    <th class="px-3 py-2 border-r border-outline">Categoría</th>
                    <th class="px-3 py-2 border-r border-outline">Tipo</th>
                    <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha evento</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($items as $g)
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="w-16 h-11 rounded-md overflow-hidden bg-surface-sunken border border-outline flex items-center justify-center">
                                @if ($g->tipo === 'facebook')
                                    <i class="fa-brands fa-facebook-f text-blue-500 text-lg"></i>
                                @elseif ($g->tipo === 'video')
                                    <i class="fa-solid fa-play text-gold-600 dark:text-gold-400 text-lg"></i>
                                @else
                                    <img src="{{ $imgSrc($g->imagen_url) }}" alt="{{ $g->titulo }}" loading="lazy" class="w-full h-full object-cover">
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-sm">
                            <div class="flex items-center gap-1.5">
                                @if ($g->destacado)
                                    <i class="fa-solid fa-star text-xs text-gold-600 dark:text-gold-400" title="Destacada en la web"></i>
                                @endif
                                <span class="font-semibold text-ink-primary truncate" title="{{ $g->titulo }}">{{ $g->titulo }}</span>
                            </div>
                            @if ($g->descripcion)
                                <div class="text-xs text-ink-secondary truncate" title="{{ $g->descripcion }}">{{ $g->descripcion }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-ink-primary">{{ $g->categoria }}</td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <i class="{{ $tipoIcono[$g->tipo] ?? 'fa-solid fa-image' }} text-xs text-ink-muted"></i>{{ $tipoEtiqueta[$g->tipo] ?? $g->tipo }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums text-ink-secondary">
                            {{ $g->fecha_evento ? $g->fecha_evento->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            <div class="inline-flex items-center gap-1">
                                <x-ui.icon-button variant="primary" wire:click="editar({{ $g->id }})" title="Editar publicación">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </x-ui.icon-button>
                                <x-ui.icon-button variant="danger" wire:click="eliminar({{ $g->id }})"
                                    wire:confirm="¿Eliminar esta publicación?" title="Eliminar publicación">
                                    <i class="fa-solid fa-trash"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="{{ $g->destacado ? 'fa-regular fa-star' : 'fa-solid fa-star' }}" wire:click="alternarDestacado({{ $g->id }})">
                                        {{ $g->destacado ? 'Quitar de destacados' : 'Destacar en la web' }}
                                    </x-ui.action-menu-item>
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-sm text-ink-secondary">
                            <i class="fa-solid fa-photo-film text-2xl text-ink-muted block mb-2"></i>
                            No hay publicaciones en la galería.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($items as $g)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <div class="w-16 h-10 rounded-md overflow-hidden bg-surface-sunken border border-outline shrink-0 flex items-center justify-center">
                        @if ($g->tipo === 'facebook')
                            <i class="fa-brands fa-facebook-f text-blue-500"></i>
                        @elseif ($g->tipo === 'video')
                            <i class="fa-solid fa-play text-gold-600 dark:text-gold-400"></i>
                        @else
                            <img src="{{ $imgSrc($g->imagen_url) }}" alt="{{ $g->titulo }}" loading="lazy" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            @if ($g->destacado)
                                <i class="fa-solid fa-star text-xs text-gold-600 dark:text-gold-400"></i>
                            @endif
                            <span class="text-sm font-semibold text-ink-primary truncate">{{ $g->titulo }}</span>
                        </div>
                        <div class="text-xs text-ink-secondary truncate">
                            {{ $g->categoria }} - {{ $tipoEtiqueta[$g->tipo] ?? $g->tipo }} - {{ $g->fecha_evento ? $g->fecha_evento->format('d/m/Y') : 'sin fecha' }}
                        </div>
                    </div>
                    <x-ui.action-menu>
                        <x-ui.action-menu-item icon="fa-solid fa-pen-to-square" wire:click="editar({{ $g->id }})">Editar</x-ui.action-menu-item>
                        <x-ui.action-menu-item icon="{{ $g->destacado ? 'fa-regular fa-star' : 'fa-solid fa-star' }}" wire:click="alternarDestacado({{ $g->id }})">{{ $g->destacado ? 'Quitar destacado' : 'Destacar' }}</x-ui.action-menu-item>
                        <x-ui.action-menu-item variant="danger" icon="fa-solid fa-trash" wire:click="eliminar({{ $g->id }})" wire:confirm="¿Eliminar esta publicación?">Eliminar</x-ui.action-menu-item>
                    </x-ui.action-menu>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No hay publicaciones en la galería.</div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} de {{ $items->total() }}</span>
            <div>{{ $items->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Nueva / Editar publicación --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardar">
            <x-ui.modal icon="fa-solid fa-photo-film" :title="$isEdit ? 'Editar publicación' : 'Nueva publicación'"
                maxWidth="max-w-xl" onClose="cerrarModal">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
                    <div class="sm:col-span-2">
                        <x-ui.input label="Título de la publicación" required type="text" name="titulo" wire:model="titulo"
                            placeholder="Ej: Presentación especial en Facebook" />
                    </div>

                    <x-ui.select label="Tipo de recurso" required name="tipo" wire:model.live="tipo">
                        <option value="foto">Fotografía</option>
                        <option value="video">Video YouTube / directo</option>
                        <option value="facebook">Video de Facebook</option>
                    </x-ui.select>

                    <x-ui.select label="Categoría" required name="categoria" wire:model="categoria">
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </x-ui.select>

                    <div class="sm:col-span-2">
                        @if ($tipo === 'foto')
                            <span class="block text-sm font-semibold text-ink-primary mb-1">Fotografía: <span class="text-rose-500 font-black text-base leading-none">*</span></span>
                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                <div class="relative w-full sm:w-56 h-32 rounded-lg bg-surface-sunken border border-dashed border-outline flex flex-col items-center justify-center overflow-hidden shrink-0">
                                    @if ($imagen_file)
                                        <img src="{{ $imagen_file->temporaryUrl() }}" alt="Vista previa" class="w-full h-full object-cover">
                                        <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-500 text-slate-900">Nueva</span>
                                    @elseif ($imagen_url)
                                        <img src="{{ $imgSrc($imagen_url) }}" alt="Imagen actual" class="w-full h-full object-cover">
                                        <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30">Actual</span>
                                    @else
                                        <i class="fa-regular fa-image text-3xl text-ink-muted mb-1"></i>
                                        <span class="text-xs text-ink-muted">Sin imagen</span>
                                    @endif
                                    <div wire:loading wire:target="imagen_file" class="absolute inset-0 bg-surface-card/80 flex flex-col items-center justify-center text-gold-700 dark:text-gold-400 text-xs font-semibold gap-1.5">
                                        <i class="fa-solid fa-spinner fa-spin text-lg"></i>
                                        <span>Cargando...</span>
                                    </div>
                                </div>
                                <div class="space-y-2 min-w-0">
                                    <p class="text-xs text-ink-secondary">
                                        Se optimiza automáticamente. Formatos JPG, PNG o WEBP, hasta 5 MB.
                                    </p>
                                    @error('imagen_file')
                                        <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                                    @enderror
                                    <div class="flex flex-wrap items-center gap-2">
                                        <label class="inline-flex items-center gap-2 h-8 px-3 rounded-lg border border-outline bg-surface-card text-sm font-semibold text-ink-primary hover:border-gold-500 cursor-pointer transition-colors">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                            <span>{{ $imagen_url || $imagen_file ? 'Cambiar foto' : 'Seleccionar foto' }}</span>
                                            <input type="file" wire:model="imagen_file" accept="image/png,image/jpeg,image/webp" class="hidden">
                                        </label>
                                        @if ($imagen_file)
                                            <x-ui.button type="button" size="sm" variant="secondary" wire:click="quitarImagenNueva">
                                                <i class="fa-solid fa-xmark"></i>
                                                <span>Descartar</span>
                                            </x-ui.button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif ($tipo === 'facebook')
                            <x-ui.input label="Enlace del video en Facebook" required type="text" name="facebook_url" wire:model="facebook_url"
                                placeholder="https://www.facebook.com/watch/?v=... o https://fb.watch/..." />
                            <p class="mt-1 text-xs text-ink-muted">Permite reproducir el video y dar Me gusta directo a tu página de Facebook.</p>
                        @else
                            <x-ui.input label="URL del video (YouTube embed)" required type="text" name="video_url" wire:model="video_url"
                                placeholder="https://www.youtube.com/embed/..." />
                        @endif
                    </div>

                    <x-ui.input label="Fecha del evento" required type="date" name="fecha_evento" wire:model="fecha_evento" />

                    <div class="flex items-end pb-1.5">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-primary cursor-pointer">
                            <input type="checkbox" wire:model="destacado" class="w-4 h-4 rounded border-outline accent-amber-500">
                            <span>Destacar en la web</span>
                        </label>
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.textarea label="Descripción" name="descripcion" rows="1" wire:model="descripcion" />
                    </div>
                </div>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="cerrarModal">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </x-ui.button>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="guardar">
                        <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardar"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardar"></i>
                        <span>Guardar</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </form>
    @endif
</div>