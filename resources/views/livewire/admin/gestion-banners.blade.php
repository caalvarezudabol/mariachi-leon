@php
    $totalBanners = $totalActivos + $totalInactivos;
    $imgSrc = fn ($u) => \Illuminate\Support\Str::startsWith((string) $u, ['http://', 'https://', '//']) ? $u : asset(ltrim((string) $u, '/'));
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
                <i class="fa-solid fa-images text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Banners de inicio</h1>
                <x-ui.badge variant="neutral">{{ $totalBanners }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nuevo banner</span>
                <span class="sm:hidden">Nuevo</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Filtros: una sola fila --}}
        <x-slot:filters>
            <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por título o subtítulo..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                        Todos <span class="opacity-70">{{ $totalBanners }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', '1')" :active="$estado_filtro === '1'">
                        Activos <span class="opacity-70">{{ $totalActivos }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', '0')" :active="$estado_filtro === '0'">
                        Inactivos <span class="opacity-70">{{ $totalInactivos }}</span>
                    </x-ui.filter-chip>
                </x-ui.filter-chips>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline w-px whitespace-nowrap">Orden</th>
                    <th class="px-3 py-2 border-r border-outline w-px">Imagen</th>
                    <th class="px-3 py-2 border-r border-outline">Título</th>
                    <th class="px-3 py-2 border-r border-outline">Botón</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($banners as $b)
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $b->activo ? '' : 'opacity-70' }}">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums font-semibold text-gold-700 dark:text-gold-400">#{{ $b->orden }}</td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="w-20 h-11 rounded-md overflow-hidden bg-surface-sunken border border-outline">
                                <img src="{{ $imgSrc($b->imagen_url) }}" alt="{{ $b->titulo }}" loading="lazy" class="w-full h-full object-cover">
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-sm">
                            <div class="font-semibold text-ink-primary truncate" title="{{ $b->titulo }}">{{ $b->titulo }}</div>
                            <div class="text-xs text-ink-secondary truncate" title="{{ $b->subtitulo }}">{{ $b->subtitulo ?: 'Sin subtítulo' }}</div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-[200px]">
                            @if ($b->boton_texto)
                                <div class="text-ink-primary truncate">{{ $b->boton_texto }}</div>
                                <div class="text-xs text-ink-secondary truncate">{{ $b->boton_link ?: '-' }}</div>
                            @else
                                <span class="text-ink-muted">Sin botón</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <span class="w-2 h-2 rounded-full {{ $b->activo ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $b->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            <div class="inline-flex items-center gap-1">
                                <x-ui.icon-button variant="primary" wire:click="editar({{ $b->id }})" title="Editar banner">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </x-ui.icon-button>
                                <x-ui.icon-button variant="danger" wire:click="eliminar({{ $b->id }})"
                                    wire:confirm="¿Eliminar este banner de inicio?" title="Eliminar banner">
                                    <i class="fa-solid fa-trash"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="fa-solid {{ $b->activo ? 'fa-eye-slash' : 'fa-eye' }}" wire:click="cambiarEstado({{ $b->id }})">
                                        {{ $b->activo ? 'Desactivar (ocultar del sitio)' : 'Activar (mostrar en el sitio)' }}
                                    </x-ui.action-menu-item>
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-sm text-ink-secondary">
                            <i class="fa-solid fa-images text-2xl text-ink-muted block mb-2"></i>
                            No hay banners de inicio configurados.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($banners as $b)
                <div class="flex items-center gap-3 px-4 py-2.5 {{ $b->activo ? '' : 'opacity-70' }}">
                    <div class="w-16 h-10 rounded-md overflow-hidden bg-surface-sunken border border-outline shrink-0">
                        <img src="{{ $imgSrc($b->imagen_url) }}" alt="{{ $b->titulo }}" loading="lazy" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold tabular-nums text-gold-700 dark:text-gold-400">#{{ $b->orden }}</span>
                            <span class="inline-flex items-center gap-1 text-xs text-ink-secondary">
                                <span class="w-2 h-2 rounded-full {{ $b->activo ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $b->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-ink-primary truncate">{{ $b->titulo }}</div>
                        <div class="text-xs text-ink-secondary truncate">{{ $b->subtitulo ?: 'Sin subtítulo' }}</div>
                    </div>
                    <x-ui.action-menu>
                        <x-ui.action-menu-item icon="fa-solid fa-pen-to-square" wire:click="editar({{ $b->id }})">Editar</x-ui.action-menu-item>
                        <x-ui.action-menu-item icon="fa-solid {{ $b->activo ? 'fa-eye-slash' : 'fa-eye' }}" wire:click="cambiarEstado({{ $b->id }})">{{ $b->activo ? 'Desactivar' : 'Activar' }}</x-ui.action-menu-item>
                        <x-ui.action-menu-item variant="danger" icon="fa-solid fa-trash" wire:click="eliminar({{ $b->id }})" wire:confirm="¿Eliminar este banner de inicio?">Eliminar</x-ui.action-menu-item>
                    </x-ui.action-menu>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No hay banners de inicio configurados.</div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $banners->firstItem() ?? 0 }}–{{ $banners->lastItem() ?? 0 }} de {{ $banners->total() }}</span>
            <div>{{ $banners->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Nuevo / Editar banner --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardar">
            <x-ui.modal icon="fa-solid fa-images" :title="$isEdit ? 'Editar banner' : 'Nuevo banner'"
                maxWidth="max-w-xl" onClose="cerrarModal">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
                    <div class="sm:col-span-2">
                        <x-ui.input label="Título del banner" required type="text" name="titulo" wire:model="titulo"
                            placeholder="Ej: El mejor mariachi de gala" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.textarea label="Subtítulo / descripción corta" name="subtitulo" rows="1" wire:model="subtitulo"
                            placeholder="Ej: Puntualidad y elegancia en cada evento..." />
                    </div>

                    <div class="sm:col-span-2">
                        <span class="block text-sm font-semibold text-ink-primary mb-1">Imagen del banner: <span class="text-rose-500 font-black text-base leading-none">*</span></span>
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
                                    Se optimiza automáticamente. Recomendado: horizontal, 1920 x 800 px.
                                    Formatos JPG, PNG o WEBP, hasta 5 MB.
                                </p>
                                @error('imagen_file')
                                    <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                                @enderror
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="inline-flex items-center gap-2 h-8 px-3 rounded-lg border border-outline bg-surface-card text-sm font-semibold text-ink-primary hover:border-gold-500 cursor-pointer transition-colors">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>{{ $imagen_url || $imagen_file ? 'Cambiar imagen' : 'Seleccionar imagen' }}</span>
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
                    </div>

                    <x-ui.input label="Texto del botón" type="text" name="boton_texto" wire:model="boton_texto" />
                    <x-ui.input label="Enlace del botón" type="text" name="boton_link" wire:model="boton_link"
                        placeholder="#cotizar o /servicios" />

                    <x-ui.input label="Orden de aparición" required type="number" min="1" name="orden" wire:model="orden" />

                    <div class="flex items-end pb-1.5">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-primary cursor-pointer">
                            <input type="checkbox" wire:model="activo" class="w-4 h-4 rounded border-outline accent-amber-500">
                            <span>Banner activo (visible en el sitio)</span>
                        </label>
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