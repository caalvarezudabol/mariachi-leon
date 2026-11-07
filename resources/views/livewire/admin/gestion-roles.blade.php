@php
    // Nombre legible de un permiso: "gestionar-pagos" -> "Gestionar pagos"
    $legible = fn($nombre) => ucfirst(str_replace(['-', '_', '.'], ' ', $nombre));
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

    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-user-shield text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Roles y permisos</h1>
                <x-ui.badge variant="neutral">{{ $roles->count() }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nuevo rol</span>
                <span class="sm:hidden">Nuevo</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline">Rol</th>
                    <th class="px-3 py-2 border-r border-outline">Permisos</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Usuarios</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($roles as $role)
                    @php $permisos = $role->permissions->sortBy('name')->values(); @endphp
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="w-8 h-8 rounded-lg bg-violet-500/15 text-violet-700 dark:text-violet-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-user-shield text-sm"></i>
                                </span>
                                <span class="font-semibold text-ink-primary">{{ $role->name }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            @if ($permisos->isEmpty())
                                <span class="text-xs text-ink-muted">Sin permisos específicos</span>
                            @else
                                <div class="flex flex-wrap items-center gap-1">
                                    <span
                                        class="text-xs font-semibold text-ink-primary mr-1">{{ $permisos->count() }}</span>
                                    @foreach ($permisos->take(5) as $p)
                                        <span
                                            class="px-1.5 py-0.5 rounded border border-outline bg-surface-sunken text-xs text-ink-secondary">{{ $legible($p->name) }}</span>
                                    @endforeach
                                    @if ($permisos->count() > 5)
                                        <span class="text-xs text-ink-muted cursor-help"
                                            title="{{ $permisos->slice(5)->map(fn($p) => $legible($p->name))->join(', ') }}">+
                                            {{ $permisos->count() - 5 }} más</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline text-right tabular-nums text-ink-primary">
                            {{ $role->users_count }}</td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            <x-ui.icon-button variant="primary" wire:click="editar({{ $role->id }})"
                                title="Editar rol y permisos">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </x-ui.icon-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-sm text-ink-secondary">No hay roles registrados.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($roles as $role)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <span
                        class="w-9 h-9 rounded-lg bg-violet-500/15 text-violet-700 dark:text-violet-400 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user-shield"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm text-ink-primary truncate">{{ $role->name }}</div>
                        <div class="text-xs text-ink-secondary">
                            {{ $role->permissions->count() }}
                            {{ $role->permissions->count() === 1 ? 'permiso' : 'permisos' }} -
                            {{ $role->users_count }} {{ $role->users_count === 1 ? 'usuario' : 'usuarios' }}
                        </div>
                    </div>
                    <x-ui.icon-button variant="primary" wire:click="editar({{ $role->id }})"
                        title="Editar rol y permisos">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </x-ui.icon-button>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No hay roles registrados.</div>
            @endforelse
        </div>
    </x-ui.panel>

    {{-- Modal Nuevo / Editar rol --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardar">
            <x-ui.modal icon="fa-solid fa-user-shield" title="{{ $isEdit ? 'Editar rol' : 'Nuevo rol' }}"
                maxWidth="max-w-2xl" onClose="$set('modalOpen', false)">

                <div class="px-6 py-5 space-y-4" x-data="{ q: '' }">
                    <x-ui.input label="Nombre del rol" required name="name" wire:model="name"
                        placeholder="Ej. Contador, Músico" autofocus />

                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                            <label class="text-sm font-semibold text-ink-primary">
                                Permisos:
                                <span
                                    class="ml-1 text-xs font-normal text-ink-secondary">{{ count($selectedPermissions) }}
                                    de {{ $permissions->count() }} seleccionados</span>
                            </label>
                            <div class="flex items-center gap-3 text-xs font-semibold">
                                <button type="button" wire:click="seleccionarTodos"
                                    class="text-gold-700 dark:text-gold-400 hover:underline">Todos</button>
                                <button type="button" wire:click="quitarTodos"
                                    class="text-ink-secondary hover:underline">Ninguno</button>
                            </div>
                        </div>

                        <div class="relative mb-2">
                            <i
                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                            <input type="text" x-model="q" placeholder="Buscar permiso..."
                                class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-1 p-2 bg-surface-sunken rounded-lg border border-outline max-h-64 overflow-y-auto">
                            @foreach ($permissions as $perm)
                                <label wire:key="perm-{{ $perm->id }}"
                                    data-n="{{ mb_strtolower($perm->name . ' ' . $legible($perm->name)) }}"
                                    x-show="!q || $el.dataset.n.includes(q.toLowerCase())"
                                    class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-surface-hover cursor-pointer"
                                    title="{{ $perm->name }}">
                                    <input type="checkbox" wire:model.live="selectedPermissions"
                                        value="{{ $perm->name }}"
                                        class="w-4 h-4 rounded border-outline accent-gold-500 shrink-0">
                                    <span class="text-sm text-ink-primary truncate">{{ $legible($perm->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
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