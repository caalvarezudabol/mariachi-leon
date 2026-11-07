<div class="space-y-4">
    <!-- Mensajes de Notificación -->
    @if (session()->has('success'))
        <div
            class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <x-ui.panel>
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-music text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Servicios</h1>
                <x-ui.badge variant="neutral" class="hidden sm:inline-flex">{{ $totalServicios }}</x-ui.badge>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-ui.button variant="primary" size="sm" wire:click="abrirModal">
                    <i class="fa-solid fa-plus"></i>
                    <span class="hidden sm:inline">Nuevo servicio</span>
                    <span class="sm:hidden">Nuevo</span>
                </x-ui.button>
            </div>
        </x-slot:header>

        <x-slot:filters>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <x-ui.input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nombre, tipo o descripción..." class="pl-9 h-8 text-sm" />
                </div>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('tipo_filtro', '')" :active="$tipo_filtro === ''">
                        Todos <span class="opacity-70">{{ $totalServicios }}</span>
                    </x-ui.filter-chip>
                    @foreach ($tipos as $tipo => $count)
                        <x-ui.filter-chip wire:click="$set('tipo_filtro', '{{ $tipo }}')" :active="$tipo_filtro === $tipo">
                            {{ $tipo }} <span class="opacity-70">{{ $count }}</span>
                        </x-ui.filter-chip>
                    @endforeach
                </x-ui.filter-chips>
            </div>
        </x-slot:filters>

        <!-- Tabla (desktop) -->
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="py-1.5 px-3 border-r border-outline">Servicio</th>
                    <th class="py-1.5 px-3 border-r border-outline">Tipo</th>
                    <th class="py-1.5 px-3 border-r border-outline text-right">Precio base</th>
                    <th class="py-1.5 px-3 border-r border-outline text-center">Duración</th>
                    <th class="py-1.5 px-3 border-r border-outline">Estado</th>
                    <th class="py-1.5 px-3 text-right w-px whitespace-nowrap">Acciones</th>
                </x-slot:header>

                @forelse ($servicios as $s)
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="py-1.5 px-3 border-r border-outline">
                            <div class="truncate max-w-[280px]"
                                title="{{ $s->nombre }}{{ $s->descripcion ? ' - ' . $s->descripcion : '' }}">
                                <span class="font-semibold text-sm text-ink-primary">{{ $s->nombre }}</span>
                                @if ($s->descripcion)
                                    <span class="text-sm text-ink-muted"> - {{ $s->descripcion }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-sm text-ink-secondary">
                            {{ $s->tipo_servicio ?: 'Musical' }}
                        </td>
                        <td
                            class="py-1.5 px-3 border-r border-outline text-right text-sm text-ink-primary tabular-nums">
                            Bs {{ number_format($s->precio_base, 2) }}
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-center text-sm text-ink-secondary">
                            {{ $s->duracion_minutos }} min
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline">
                            <button wire:click="cambiarEstado({{ $s->id }})"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-secondary hover:text-ink-primary transition-colors">
                                <span
                                    class="w-2 h-2 rounded-full shrink-0 {{ $s->activo ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $s->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="py-1.5 px-3 text-right w-px whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button wire:click="editar({{ $s->id }})" title="Editar Servicio">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="fa-solid fa-trash-can" variant="danger"
                                        wire:click="eliminar({{ $s->id }})"
                                        wire:confirm="¿Está seguro de eliminar este servicio?">
                                        Eliminar
                                    </x-ui.action-menu-item>
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-ink-muted text-sm">
                            No se encontraron servicios registrados.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        <!-- Tarjetas (móvil) -->
        <div class="md:hidden divide-y divide-outline">
            @forelse ($servicios as $s)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-sm text-ink-primary truncate">{{ $s->nombre }}</span>
                            <span
                                class="w-2 h-2 rounded-full shrink-0 {{ $s->activo ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        </div>
                        <div class="text-xs text-ink-muted truncate">
                            {{ $s->tipo_servicio ?: 'Musical' }} - Bs {{ number_format($s->precio_base, 2) }} -
                            {{ $s->duracion_minutos }} min
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <x-ui.action-menu>
                            <x-ui.action-menu-item icon="fa-solid fa-pen-to-square"
                                wire:click="editar({{ $s->id }})">
                                Editar
                            </x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-trash-can" variant="danger"
                                wire:click="eliminar({{ $s->id }})"
                                wire:confirm="¿Está seguro de eliminar este servicio?">
                                Eliminar
                            </x-ui.action-menu-item>
                        </x-ui.action-menu>
                    </div>
                </div>
            @empty
                <p class="text-center py-8 text-ink-muted text-sm">No se encontraron servicios registrados.</p>
            @endforelse
        </div>

        <x-slot:footer>
            <span>
                @if ($servicios->total() > 0)
                    Mostrando {{ $servicios->firstItem() }}–{{ $servicios->lastItem() }} de {{ $servicios->total() }}
                @else
                    Sin resultados
                @endif
            </span>
            <div>{{ $servicios->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    <!-- Modal Crear/Editar Servicio -->
    @if ($modalOpen)
        <x-ui.modal :title="$isEdit ? 'Editar Servicio' : 'Nuevo Servicio'" icon="fa-solid fa-music" onClose="$set('modalOpen', false)">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
                <x-ui.input class="sm:col-span-2" label="Nombre del Servicio" required name="nombre"
                    wire:model="nombre" placeholder="ej. Serenata Completa 1 Hora" />

                <x-ui.select label="Tipo de Servicio" required wire:model="tipo_servicio">
                    <option value="Musical">Musical (Mariachi / Serenatas)</option>
                    <option value="Alquiler Sonido">Alquiler de Sonido</option>
                    <option value="Técnico">Servicio Técnico</option>
                    <option value="Transporte">Transporte</option>
                    <option value="Otro">Otros Servicios</option>
                </x-ui.select>

                <x-ui.select label="Estado" wire:model="activo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </x-ui.select>

                <x-ui.input type="number" step="0.50" label="Precio Base (Bs)" required name="precio_base"
                    wire:model="precio_base" />

                <x-ui.input type="number" label="Duración Estimada (min)" required name="duracion_minutos"
                    wire:model="duracion_minutos" placeholder="60" />

                <x-ui.textarea class="sm:col-span-2" label="Descripción del Servicio" name="descripcion"
                    wire:model="descripcion" rows="1"
                    placeholder="Detalles de repertorio, número de canciones o equipamiento incluido..." />

            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" wire:click="$set('modalOpen', false)">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" wire:click="guardar" wire:loading.attr="disabled"
                    wire:target="guardar">
                    <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardar"></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardar"></i>
                    <span>Guardar</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>