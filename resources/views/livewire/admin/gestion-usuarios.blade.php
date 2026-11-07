@php
    $totalUsuarios = $totalActivos + $totalInactivos;
@endphp

<div class="space-y-3">
    {{-- Avisos --}}
    @if (session()->has('success'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-rose-500/30 bg-rose-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-triangle-exclamation text-lg text-rose-600 dark:text-rose-400"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-users text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Usuarios</h1>
                <x-ui.badge variant="neutral">{{ $totalUsuarios }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-user-plus"></i>
                <span class="hidden sm:inline">Nuevo usuario</span>
                <span class="sm:hidden">Nuevo</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Filtros: una sola fila --}}
        <x-slot:filters>
            <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nombre o correo..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select wire:model.live="rol_filtro"
                        class="h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors">
                        <option value="">Todos los roles</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                    <x-ui.filter-chips>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                            Todos <span class="opacity-70">{{ $totalUsuarios }}</span>
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', 'activo')" :active="$estado_filtro === 'activo'">
                            Activos <span class="opacity-70">{{ $totalActivos }}</span>
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', 'inactivo')" :active="$estado_filtro === 'inactivo'">
                            Inactivos <span class="opacity-70">{{ $totalInactivos }}</span>
                        </x-ui.filter-chip>
                    </x-ui.filter-chips>
                </div>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline">Usuario</th>
                    <th class="px-3 py-2 border-r border-outline">Teléfono</th>
                    <th class="px-3 py-2 border-r border-outline">Rol</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($usuarios as $user)
                    @php $esYo = Auth::id() === $user->id; @endphp
                    <tr
                        class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ !$user->activo ? 'opacity-70' : '' }}">
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="w-8 h-8 rounded-full bg-gold-500/15 text-gold-700 dark:text-gold-400 flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <div class="font-semibold text-ink-primary truncate">
                                        {{ $user->name }}
                                        @if ($esYo)
                                            <span class="ml-1 text-xs font-normal text-ink-secondary">(tú)</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-ink-secondary truncate">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums text-ink-primary">
                            {{ $user->telefono ?: '-' }}
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            @forelse ($user->roles as $r)
                                <span
                                    class="inline-block px-2 py-0.5 rounded-md border border-outline bg-surface-sunken text-xs font-semibold text-ink-primary">{{ $r->name }}</span>
                            @empty
                                <span class="text-xs text-ink-muted">Sin rol</span>
                            @endforelse
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <span
                                    class="w-2 h-2 rounded-full {{ $user->activo ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $user->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            <div class="inline-flex items-center gap-1">
                                <x-ui.icon-button variant="primary" wire:click="editar({{ $user->id }})"
                                    title="Editar usuario">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </x-ui.icon-button>
                                @if (!$esYo)
                                    <x-ui.action-menu>
                                        <x-ui.action-menu-item
                                            icon="{{ $user->activo ? 'fa-solid fa-user-slash' : 'fa-solid fa-user-check' }}"
                                            wire:click="cambiarEstado({{ $user->id }})">
                                            {{ $user->activo ? 'Desactivar' : 'Activar' }}
                                        </x-ui.action-menu-item>
                                        <x-ui.action-menu-item variant="danger" icon="fa-solid fa-trash-can"
                                            wire:click="eliminar({{ $user->id }})"
                                            wire:confirm="¿Está seguro de eliminar este usuario?">Eliminar</x-ui.action-menu-item>
                                    </x-ui.action-menu>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-sm text-ink-secondary">No se encontraron
                            usuarios.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($usuarios as $user)
                @php $esYo = Auth::id() === $user->id; @endphp
                <div class="flex items-center gap-3 px-4 py-2.5 {{ !$user->activo ? 'opacity-70' : '' }}">
                    <span
                        class="w-9 h-9 rounded-full bg-gold-500/15 text-gold-700 dark:text-gold-400 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-sm text-ink-primary truncate">{{ $user->name }}
                                @if ($esYo)
                                    <span class="font-normal text-ink-secondary">(tú)</span>
                                @endif
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs text-ink-secondary shrink-0">
                                <span
                                    class="w-2 h-2 rounded-full {{ $user->activo ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $user->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div class="text-xs text-ink-secondary truncate">{{ $user->email }}</div>
                        <div class="text-xs text-ink-secondary truncate">
                            {{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}@if ($user->telefono)
                                - {{ $user->telefono }}
                            @endif
                        </div>
                    </div>
                    <x-ui.action-menu>
                        <x-ui.action-menu-item icon="fa-solid fa-pen-to-square"
                            wire:click="editar({{ $user->id }})">Editar</x-ui.action-menu-item>
                        @if (!$esYo)
                            <x-ui.action-menu-item
                                icon="{{ $user->activo ? 'fa-solid fa-user-slash' : 'fa-solid fa-user-check' }}"
                                wire:click="cambiarEstado({{ $user->id }})">
                                {{ $user->activo ? 'Desactivar' : 'Activar' }}
                            </x-ui.action-menu-item>
                            <x-ui.action-menu-item variant="danger" icon="fa-solid fa-trash-can"
                                wire:click="eliminar({{ $user->id }})"
                                wire:confirm="¿Está seguro de eliminar este usuario?">Eliminar</x-ui.action-menu-item>
                        @endif
                    </x-ui.action-menu>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron usuarios.</div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $usuarios->firstItem() ?? 0 }}–{{ $usuarios->lastItem() ?? 0 }} de
                {{ $usuarios->total() }}</span>
            <div>{{ $usuarios->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Nuevo / Editar usuario --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardar">
            <x-ui.modal icon="fa-solid {{ $isEdit ? 'fa-user-pen' : 'fa-user-plus' }}"
                title="{{ $isEdit ? 'Editar usuario' : 'Nuevo usuario' }}" maxWidth="max-w-lg"
                onClose="$set('modalOpen', false)">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
                    <x-ui.input class="sm:col-span-2" label="Nombre Completo" required name="name" wire:model="name"
                        autofocus />

                    <x-ui.input class="sm:col-span-2" type="email" label="Correo Electrónico" required
                        name="email" wire:model="email" placeholder="usuario@ejemplo.com" />

                    <x-ui.input label="Teléfono" name="telefono" wire:model="telefono" placeholder="ej. 70000000" />

                    <x-ui.select label="Rol" required name="role" wire:model="role">
                        <option value="">Seleccione un rol</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <div class="sm:col-span-2">
                        <x-ui.input type="password" label="Contraseña" :required="!$isEdit" name="password"
                            wire:model="password" autocomplete="new-password" />
                        @if ($isEdit)
                            <p class="text-xs text-ink-muted mt-1">Déjala en blanco para conservar la actual.</p>
                        @else
                            <p class="text-xs text-ink-muted mt-1">Mínimo 6 caracteres.</p>
                        @endif
                    </div>

                    <label
                        class="sm:col-span-2 inline-flex items-center gap-2 text-sm font-semibold text-ink-primary cursor-pointer">
                        <input type="checkbox" wire:model="activo"
                            class="w-4 h-4 rounded border-outline accent-gold-500">
                        <span>Usuario activo</span>
                    </label>
                </div>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="$set('modalOpen', false)">
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