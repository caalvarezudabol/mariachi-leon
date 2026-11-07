<div class="space-y-4">
    <!-- Mensajes de Notificación -->
    @if (session()->has('success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <x-ui.panel>
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-users text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Clientes</h1>
                <x-ui.badge variant="neutral" class="hidden sm:inline-flex">{{ $totalClientes }}</x-ui.badge>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-ui.button variant="secondary" size="sm" class="hidden sm:inline-flex"
                    href="{{ route('admin.clientes.excel', ['estado' => $estado_filtro, 'search' => $search]) }}"
                    target="_blank" title="Exportar clientes filtrados a Excel (.xlsx)">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Excel</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" wire:click="abrirModal">
                    <i class="fa-solid fa-plus"></i>
                    <span class="hidden sm:inline">Nuevo cliente</span>
                    <span class="sm:hidden">Nuevo</span>
                </x-ui.button>
            </div>
        </x-slot:header>

        <x-slot:filters>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <x-ui.input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar cliente..." class="pl-9 h-8 text-sm" />
                </div>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                        Todos <span class="opacity-70">{{ $totalClientes }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', 'Activo')" :active="$estado_filtro === 'Activo'">
                        Activos <span class="opacity-70">{{ $activosCount }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', 'Inactivo')" :active="$estado_filtro === 'Inactivo'">
                        Inactivos <span class="opacity-70">{{ $inactivosCount }}</span>
                    </x-ui.filter-chip>
                </x-ui.filter-chips>
            </div>
        </x-slot:filters>

        <!-- Tabla (desktop) -->
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="py-1.5 px-3 border-r border-outline">Cliente</th>
                    <th class="py-1.5 px-3 border-r border-outline">Teléfono</th>
                    <th class="py-1.5 px-3 border-r border-outline text-center">Eventos</th>
                    <th class="py-1.5 px-3 border-r border-outline">Estado</th>
                    <th class="py-1.5 px-3 text-right w-px whitespace-nowrap">Acciones</th>
                </x-slot:header>

                @forelse($clientes as $cliente)
                    @php
                        $iniciales = collect(explode(' ', trim($cliente->nombre_completo)))
                            ->filter()
                            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                            ->take(2)
                            ->implode('');
                        $telefonoMostrar = $cliente->whatsapp ?: $cliente->telefono;
                    @endphp
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="py-1.5 px-3 border-r border-outline">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gold-500/15 border border-gold-500/30 text-gold-700 dark:text-gold-400 text-[11px] font-bold flex items-center justify-center shrink-0">
                                    {{ $iniciales }}
                                </div>
                                <div class="truncate flex-1 min-w-0"
                                    title="{{ $cliente->nombre_completo }}{{ $cliente->direccion ? ' - ' . $cliente->direccion : '' }}">
                                    <span class="font-semibold text-sm text-ink-primary">{{ $cliente->nombre_completo }}</span>
                                    @if ($cliente->direccion)
                                        <span class="text-sm text-ink-muted"> - {{ $cliente->direccion }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-sm text-emerald-600 dark:text-emerald-400">
                            {{ $telefonoMostrar ?: '-' }}
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-center">
                            <span class="font-bold text-sm text-ink-primary">{{ $cliente->eventos_count }}</span>
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline">
                            <button wire:click="cambiarEstado({{ $cliente->id }})"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-secondary hover:text-ink-primary transition-colors">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $cliente->estado === 'Activo' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $cliente->estado }}
                            </button>
                        </td>
                        <td class="py-1.5 px-3 text-right w-px whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button wire:click="editar({{ $cliente->id }})" title="Editar Cliente">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="fa-solid fa-file-invoice-dollar"
                                        href="{{ route('admin.clientes.estado-cuenta', \App\Services\Security\DocumentToken::encodeId($cliente->id, 'cliente')) }}">
                                        Estados de Cuenta
                                    </x-ui.action-menu-item>
                                    <x-ui.action-menu-item icon="fa-solid fa-file-contract" target="_blank"
                                        href="{{ route('admin.clientes.historial-contratos.pdf', \App\Services\Security\DocumentToken::encodeId($cliente->id, 'cliente')) }}">
                                        Historial de Contratos (PDF)
                                    </x-ui.action-menu-item>
                                    <x-ui.action-menu-item icon="fa-solid fa-file-pdf" target="_blank"
                                        href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => \App\Services\Security\DocumentToken::encodeId($cliente->id, 'cliente'), 'tipo' => 'general']) }}">
                                        Extracto de Pagos (PDF)
                                    </x-ui.action-menu-item>
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-ink-muted text-sm">
                            No se encontraron clientes registrados.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        <!-- Filas (móvil) -->
        <div class="md:hidden divide-y divide-outline">
            @forelse($clientes as $cliente)
                @php
                    $iniciales = collect(explode(' ', trim($cliente->nombre_completo)))
                        ->filter()
                        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                        ->take(2)
                        ->implode('');
                    $telefonoMostrar = $cliente->whatsapp ?: $cliente->telefono;
                @endphp
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <div class="w-[34px] h-[34px] rounded-full bg-gold-500/15 border border-gold-500/30 text-gold-700 dark:text-gold-400 text-xs font-bold flex items-center justify-center shrink-0">
                        {{ $iniciales }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-sm text-ink-primary truncate">{{ $cliente->nombre_completo }}</span>
                            <span class="w-2 h-2 rounded-full shrink-0 {{ $cliente->estado === 'Activo' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        </div>
                        <div class="text-xs text-ink-muted truncate">
                            @if ($telefonoMostrar)
                                <span class="text-emerald-600 dark:text-emerald-400">{{ $telefonoMostrar }}</span>
                            @endif
                            @if ($telefonoMostrar && $cliente->direccion)
                                <span> - </span>
                            @endif
                            {{ $cliente->direccion }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-bold text-ink-secondary">{{ $cliente->eventos_count }}</span>
                        <x-ui.action-menu>
                            <x-ui.action-menu-item icon="fa-solid fa-pen-to-square" wire:click="editar({{ $cliente->id }})">
                                Editar Cliente
                            </x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-file-invoice-dollar"
                                href="{{ route('admin.clientes.estado-cuenta', \App\Services\Security\DocumentToken::encodeId($cliente->id, 'cliente')) }}">
                                Estados de Cuenta
                            </x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-file-contract" target="_blank"
                                href="{{ route('admin.clientes.historial-contratos.pdf', \App\Services\Security\DocumentToken::encodeId($cliente->id, 'cliente')) }}">
                                Historial de Contratos (PDF)
                            </x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-file-pdf" target="_blank"
                                href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => \App\Services\Security\DocumentToken::encodeId($cliente->id, 'cliente'), 'tipo' => 'general']) }}">
                                Extracto de Pagos (PDF)
                            </x-ui.action-menu-item>
                        </x-ui.action-menu>
                    </div>
                </div>
            @empty
                <p class="text-center py-8 text-ink-muted text-sm">No se encontraron clientes registrados.</p>
            @endforelse
        </div>

        <x-slot:footer>
            <span>
                @if ($clientes->total() > 0)
                    Mostrando {{ $clientes->firstItem() }}–{{ $clientes->lastItem() }} de {{ $clientes->total() }}
                @else
                    Sin resultados
                @endif
            </span>
            <div>{{ $clientes->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    <!-- Modal Crear/Editar Cliente -->
    @if ($modalOpen)
        <x-ui.modal :title="$isEdit ? 'Editar Cliente' : 'Nuevo Cliente'" icon="fa-solid fa-user-gear"
            onClose="$set('modalOpen', false)">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
                <x-ui.input class="sm:col-span-2" label="Nombre Completo" required name="nombre_completo"
                    wire:model="nombre_completo" />

                <x-ui.input label="C.I. / NIT" name="ci_nit" wire:model="ci_nit" placeholder="ej. 10293847" />

                <x-ui.select label="Estado" wire:model="estado">
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </x-ui.select>

                <x-ui.input label="Teléfono Principal" wire:model="telefono" placeholder="ej. 70000000" />

                <x-ui.input label="WhatsApp Comercial" wire:model="whatsapp" placeholder="ej. +591 70000000" />

                <x-ui.input class="sm:col-span-2" type="email" label="Correo Electrónico" name="email"
                    wire:model="email" placeholder="cliente@ejemplo.com" />

                <x-ui.input class="sm:col-span-2" label="Dirección de Domicilio / Oficina" wire:model="direccion"
                    placeholder="Av. Principal #123" />

                <x-ui.textarea class="sm:col-span-2" label="Observaciones" wire:model="observaciones" rows="1" />
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" wire:click="$set('modalOpen', false)">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" wire:click="guardar"
                    wire:loading.attr="disabled" wire:target="guardar">
                    <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardar"></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardar"></i>
                    <span>Guardar</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
