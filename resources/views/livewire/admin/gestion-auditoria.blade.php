@php
    $hayFiltros = $search !== '' || $modulo !== '' || $usuario !== '' || $fecha_desde !== '' || $fecha_hasta !== '';
    $campo =
        'h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors';
@endphp

<div class="space-y-3">
    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-shield-halved text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Auditoría</h1>
                <x-ui.badge variant="neutral">{{ $logs->total() }}</x-ui.badge>
            </div>
        </x-slot:header>

        {{-- Filtros: una sola fila --}}
        <x-slot:filters>
            <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar en la descripción, acción o usuario..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select wire:model.live="modulo" class="{{ $campo }}">
                        <option value="">Todos los módulos</option>
                        @foreach ($modulos as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="usuario" class="{{ $campo }}">
                        <option value="">Todos los usuarios</option>
                        @foreach ($usuarios as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" wire:model.live="fecha_desde" title="Desde" class="{{ $campo }}">
                    <input type="date" wire:model.live="fecha_hasta" title="Hasta" class="{{ $campo }}">
                    @if ($hayFiltros)
                        <x-ui.icon-button wire:click="limpiarFiltros" title="Limpiar filtros">
                            <i class="fa-solid fa-rotate-left"></i>
                        </x-ui.icon-button>
                    @endif
                </div>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha y hora</th>
                    <th class="px-3 py-2 border-r border-outline">Usuario</th>
                    <th class="px-3 py-2 border-r border-outline">Módulo y acción</th>
                    <th class="px-3 py-2 border-r border-outline">Descripción</th>
                    <th class="px-3 py-2 whitespace-nowrap">IP</th>
                </x-slot:header>

                @forelse ($logs as $log)
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                            <div class="text-ink-primary">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-ink-secondary">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td
                            class="px-3 py-1.5 border-r border-outline whitespace-nowrap font-semibold text-ink-primary">
                            {{ $log->user->name ?? 'Sistema' }}
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="font-semibold text-gold-700 dark:text-gold-400">{{ $log->accion }}</div>
                            <div class="text-xs text-ink-secondary">{{ $log->modulo }}</div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline text-ink-primary">{{ $log->descripcion }}</td>
                        <td class="px-3 py-1.5 whitespace-nowrap text-xs tabular-nums text-ink-secondary">
                            {{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-sm text-ink-secondary">No se registraron
                            entradas de auditoría.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($logs as $log)
                <div class="px-4 py-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span
                            class="text-sm font-semibold text-gold-700 dark:text-gold-400 truncate">{{ $log->accion }}</span>
                        <span
                            class="text-xs text-ink-secondary tabular-nums shrink-0">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="text-sm text-ink-primary">{{ $log->descripcion }}</div>
                    <div class="text-xs text-ink-secondary">
                        {{ $log->user->name ?? 'Sistema' }} - {{ $log->modulo }}@if ($log->ip_address)
                            - {{ $log->ip_address }}
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No se registraron entradas de auditoría.</div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} de {{ $logs->total() }}</span>
            <div>{{ $logs->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>
</div>