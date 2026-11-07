@php
    $totalPersonas = $totalActivos + $totalInactivos;

    // Icono y color según el tipo de persona (mismos que el modal de eventos)
    $estiloTipo = function ($tipo) {
        $t = mb_strtolower((string) $tipo);
        if (str_contains($t, 'transp') || str_contains($t, 'bus') || str_contains($t, 'chofer')) {
            return ['fa-bus', 'bg-sky-500/15 text-sky-600 dark:text-sky-400'];
        }
        if (str_contains($t, 'sonid')) {
            return ['fa-volume-high', 'bg-violet-500/15 text-violet-600 dark:text-violet-400'];
        }
        if ($t === '' || str_contains($t, 'músic') || str_contains($t, 'music') || str_contains($t, 'cantan')) {
            return ['fa-music', 'bg-amber-500/15 text-amber-600 dark:text-amber-400'];
        }
        return ['fa-user', 'bg-slate-500/15 text-slate-600 dark:text-slate-300'];
    };
@endphp

<div class="space-y-3">

    @if (session()->has('success'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-people-group text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Músicos y personal</h1>
                <x-ui.badge variant="neutral">{{ $totalPersonas }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nueva persona</span>
                <span class="sm:hidden">Nueva</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Filtros --}}
        <x-slot:filters>
            <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nombre, teléfono o CI..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <select wire:model.live="tipo_filtro"
                    class="h-8 px-2.5 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none lg:w-44">
                    <option value="">Todos los tipos</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                        Todos <span class="opacity-70">{{ $totalPersonas }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', 'Activo')" :active="$estado_filtro === 'Activo'">
                        Activos <span class="opacity-70">{{ $totalActivos }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', 'Inactivo')" :active="$estado_filtro === 'Inactivo'">
                        Inactivos <span class="opacity-70">{{ $totalInactivos }}</span>
                    </x-ui.filter-chip>
                </x-ui.filter-chips>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline">Persona</th>
                    <th class="px-3 py-2 border-r border-outline">Tipo</th>
                    <th class="px-3 py-2 border-r border-outline">Pago</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Tarifa</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($personas as $p)
                    @php
                        [$ico, $cls] = $estiloTipo($p->tipo);
                        $mensual = ($p->modalidad_pago ?? 'por_evento') === 'mensual';
                        $activo = $p->estado === 'Activo';
                    @endphp
                    <tr
                        class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $activo ? '' : 'opacity-60' }}">
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span
                                    class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $cls }}">
                                    <i class="fa-solid {{ $ico }} text-sm"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="font-semibold text-ink-primary truncate">{{ $p->nombre_completo }}
                                    </div>
                                    <div class="text-xs text-ink-secondary truncate">
                                        {{ $p->telefono ?: 'Sin teléfono' }}
                                        @if ($p->va_en_todos_eventos)
                                            <span class="ml-1 text-gold-700 dark:text-gold-400"
                                                title="Se agrega solo a cada evento nuevo">
                                                <i class="fa-solid fa-thumbtack text-[10px]"></i> En todos los eventos
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-ink-primary">
                            {{ $p->tipo ?: 'Músico' }}</td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            @if ($mensual)
                                <span class="text-sky-600 dark:text-sky-400 font-medium">Mensual</span>
                            @else
                                <span class="text-ink-primary">Por evento</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-right tabular-nums">
                            @if ($mensual)
                                <span class="font-semibold text-ink-primary">Bs
                                    {{ number_format((float) $p->sueldo_mensual, 2) }}</span>
                                <span class="text-xs text-ink-secondary">/ mes</span>
                            @elseif ($p->tarifa_evento !== null && (float) $p->tarifa_evento > 0)
                                <span class="font-semibold text-ink-primary">Bs
                                    {{ number_format((float) $p->tarifa_evento, 2) }}</span>
                            @else
                                <span class="text-amber-600 dark:text-amber-400">Sin tarifa</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <span
                                    class="w-2 h-2 rounded-full {{ $activo ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $p->estado }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            <div class="inline-flex items-center gap-1">
                                <x-ui.icon-button variant="primary" wire:click="editar({{ $p->id }})"
                                    title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="fa-solid fa-thumbtack"
                                        wire:click="alternarEnTodos({{ $p->id }})">
                                        {{ $p->va_en_todos_eventos ? 'Quitar de "En todos los eventos"' : 'Agregar a todos los eventos' }}
                                    </x-ui.action-menu-item>
                                    <x-ui.action-menu-item
                                        icon="fa-solid {{ $activo ? 'fa-user-slash' : 'fa-user-check' }}"
                                        wire:click="cambiarEstado({{ $p->id }})">
                                        {{ $activo ? 'Desactivar' : 'Activar' }}
                                    </x-ui.action-menu-item>
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-sm text-ink-secondary">
                            <i class="fa-solid fa-people-group text-2xl text-ink-muted block mb-2"></i>
                            No se encontraron personas con esos filtros.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($personas as $p)
                @php
                    [$ico, $cls] = $estiloTipo($p->tipo);
                    $mensual = ($p->modalidad_pago ?? 'por_evento') === 'mensual';
                    $activo = $p->estado === 'Activo';
                @endphp
                <div class="flex items-center gap-3 px-3 py-2.5 {{ $activo ? '' : 'opacity-60' }}">
                    <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $cls }}">
                        <i class="fa-solid {{ $ico }}"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[14.5px] font-semibold text-ink-primary truncate">{{ $p->nombre_completo }}
                        </div>
                        <div class="text-[13px] text-ink-secondary truncate">
                            {{ $p->tipo ?: 'Músico' }} -
                            @if ($mensual)
                                <span class="text-sky-600 dark:text-sky-400">Mensual Bs
                                    {{ number_format((float) $p->sueldo_mensual, 0) }}</span>
                            @elseif ($p->tarifa_evento !== null && (float) $p->tarifa_evento > 0)
                                Bs {{ number_format((float) $p->tarifa_evento, 0) }}
                            @else
                                <span class="text-amber-600 dark:text-amber-400">Sin tarifa</span>
                            @endif
                            @unless ($activo)
                                - <span class="text-rose-600 dark:text-rose-400">Inactivo</span>
                            @endunless
                        </div>
                    </div>
                    <x-ui.action-menu>
                        <x-ui.action-menu-item icon="fa-solid fa-pen-to-square"
                            wire:click="editar({{ $p->id }})">Editar</x-ui.action-menu-item>
                        <x-ui.action-menu-item icon="fa-solid fa-thumbtack"
                            wire:click="alternarEnTodos({{ $p->id }})">
                            {{ $p->va_en_todos_eventos ? 'Quitar de todos los eventos' : 'Agregar a todos los eventos' }}
                        </x-ui.action-menu-item>
                        <x-ui.action-menu-item icon="fa-solid {{ $activo ? 'fa-user-slash' : 'fa-user-check' }}"
                            wire:click="cambiarEstado({{ $p->id }})">
                            {{ $activo ? 'Desactivar' : 'Activar' }}
                        </x-ui.action-menu-item>
                    </x-ui.action-menu>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron personas con esos filtros.
                </div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $personas->firstItem() ?? 0 }}–{{ $personas->lastItem() ?? 0 }} de
                {{ $personas->total() }}</span>
            <div>{{ $personas->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Nueva / Editar persona --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardar">
            <x-ui.modal icon="fa-solid fa-people-group" :title="$isEdit ? 'Editar persona' : 'Nueva persona'" maxWidth="max-w-2xl" onClose="cerrarModal">

                <div class="grid grid-cols-1 sm:grid-cols-6 gap-x-3 gap-y-3 px-6 py-4">
                    <div class="sm:col-span-4 min-w-0">
                        <x-ui.input label="Nombre completo" required type="text" name="nombre_completo"
                            wire:model.live.debounce.400ms="nombre_completo"
                            placeholder="Ej: Juan Pérez o Minibús Don Pedro" />
                    </div>

                    <div class="sm:col-span-2 min-w-0">
                        <x-ui.select label="Tipo" required name="tipo" wire:model="tipo">
                            @foreach ($tipos as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                            @if ($tipo && !$tipos->contains($tipo))
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endif
                        </x-ui.select>
                    </div>

                    @if ($posibleDuplicado)
                        <p
                            class="sm:col-span-6 -mt-1 text-xs text-amber-700 dark:text-amber-400 flex items-start gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                            <span>Ya existe una persona con este nombre ({{ $posibleDuplicado->tipo ?: 'Músico' }} -
                                {{ $posibleDuplicado->estado }}). Revisa que no sea la misma.</span>
                        </p>
                    @endif

                    <div class="sm:col-span-2 min-w-0">
                        <x-ui.input label="Teléfono" type="text" name="telefono" wire:model="telefono"
                            placeholder="70000000" />
                    </div>

                    <div class="sm:col-span-2 min-w-0">
                        <x-ui.select label="Pago" required name="modalidad_pago" wire:model.live="modalidad_pago">
                            <option value="por_evento">Por evento</option>
                            <option value="mensual">Mensual</option>
                        </x-ui.select>
                    </div>

                    <div class="sm:col-span-2 min-w-0">
                        @if ($modalidad_pago === 'mensual')
                            <x-ui.input label="Sueldo (Bs)" required type="number" step="0.50" min="0"
                                name="sueldo_mensual" wire:model="sueldo_mensual" placeholder="0.00" />
                        @else
                            <x-ui.input label="Cobra (Bs)" required type="number" step="0.50" min="0"
                                name="tarifa_evento" wire:model="tarifa_evento" placeholder="0.00" />
                        @endif
                    </div>

                    <div class="sm:col-span-2 min-w-0">
                        <x-ui.input label="CI / NIT" type="text" name="ci_nit" wire:model="ci_nit" />
                    </div>

                    <div class="sm:col-span-2 min-w-0">
                        <x-ui.select label="Estado" name="estado" wire:model="estado">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </x-ui.select>
                    </div>

                    <div class="sm:col-span-2 flex items-end pb-1.5">
                        <label
                            class="inline-flex items-center gap-2 text-sm font-semibold text-ink-primary cursor-pointer"
                            title="Se agrega solo al crear un evento. Úsalo para el equipo base y los mensualistas.">
                            <input type="checkbox" wire:model="va_en_todos_eventos"
                                class="w-4 h-4 rounded border-outline accent-amber-500">
                            <span>En todos los eventos</span>
                        </label>
                    </div>

                    <div class="sm:col-span-6 min-w-0">
                        <x-ui.textarea label="Observaciones" name="observaciones" rows="1"
                            wire:model="observaciones" placeholder="Instrumento, datos de pago, etc." />
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