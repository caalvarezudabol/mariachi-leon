@php
    $campoFiltro =
        'h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors';
    $paramsExport = [
        'desde' => $fecha_desde,
        'hasta' => $fecha_hasta,
        'campo_fecha' => $campo_fecha,
        'cliente_id' => $cliente_id,
        'servicio_id' => $servicio_id,
        'estado_contrato' => $estado_contrato,
        'estado_evento' => $estado_evento,
        'search_lugar' => $search_lugar,
    ];

    // Color del punto según el estado
    $puntoEstado = fn($estado) => match ($estado) {
        'Confirmado' => 'bg-emerald-500',
        'Finalizado' => 'bg-sky-500',
        'En Ejecucion', 'En Proceso' => 'bg-amber-500',
        'Cancelado' => 'bg-rose-500',
        default => 'bg-slate-400',
    };
@endphp

<div class="space-y-3">
    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-file-contract text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Eventos y contratos</h1>
                <x-ui.badge variant="neutral">{{ number_format($resumen['total_registros']) }}</x-ui.badge>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.button size="sm" target="_blank"
                    href="{{ route('admin.reportes.eventos-contratos.pdf', $paramsExport) }}">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>PDF</span>
                </x-ui.button>
                <x-ui.action-menu>
                    <x-ui.action-menu-item icon="fa-solid fa-file-excel"
                        href="{{ route('admin.reportes.eventos-contratos.excel', $paramsExport) }}">Exportar
                        Excel</x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-chart-line" href="{{ route('admin.finanzas.dashboard') }}"
                        wire:navigate>Dashboard financiero</x-ui.action-menu-item>
                </x-ui.action-menu>
            </div>
        </x-slot:header>

        {{-- Resumen + filtros --}}
        <x-slot:filters>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-3">
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Eventos / contratos</span><i class="fa-solid fa-list-check text-ink-muted"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-ink-primary">
                        {{ number_format($resumen['total_registros']) }}</div>
                    <div class="text-xs text-ink-secondary">Que cumplen los filtros</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Total contratado</span><i
                            class="fa-solid fa-handshake text-gold-600 dark:text-gold-400"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-gold-700 dark:text-gold-400">Bs
                        {{ number_format($resumen['total_contratado'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Suma pactada en contratos</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Total cobrado</span><i
                            class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">Bs
                        {{ number_format($resumen['total_cobrado'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Pagos y anticipos confirmados</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Saldo por cobrar</span><i class="fa-solid fa-clock text-rose-600 dark:text-rose-400"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-rose-700 dark:text-rose-400">Bs
                        {{ number_format($resumen['total_saldo_pendiente'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Pendiente de cobro a clientes</div>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                    <div class="relative flex-1 min-w-0">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar código de evento, contrato o cliente..."
                            class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                    </div>
                    <div class="relative flex-1 min-w-0">
                        <i
                            class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                        <input type="text" wire:model.live.debounce.300ms="search_lugar"
                            placeholder="Ubicación, salón, zona o municipio..."
                            class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <select wire:model.live="campo_fecha" class="{{ $campoFiltro }}">
                            <option value="fecha_evento">Fecha del evento</option>
                            <option value="fecha_contrato">Fecha del contrato</option>
                        </select>
                        <input type="date" wire:model.live="fecha_desde" title="Desde" class="{{ $campoFiltro }}">
                        <input type="date" wire:model.live="fecha_hasta" title="Hasta" class="{{ $campoFiltro }}">
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select wire:model.live="cliente_id" class="{{ $campoFiltro }} max-w-[200px]">
                        <option value="">Todos los clientes</option>
                        @foreach ($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre_completo }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="servicio_id" class="{{ $campoFiltro }} max-w-[200px]">
                        <option value="">Todos los servicios</option>
                        @foreach ($servicios as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="estado_contrato" class="{{ $campoFiltro }}">
                        <option value="">Contrato: todos</option>
                        <option value="Confirmado">Contrato confirmado</option>
                        <option value="Borrador">Contrato en borrador</option>
                        <option value="En Ejecucion">Contrato en ejecución</option>
                        <option value="Finalizado">Contrato finalizado</option>
                        <option value="Cancelado">Contrato cancelado</option>
                        <option value="sin_contrato">Sin contrato</option>
                    </select>
                    <select wire:model.live="estado_evento" class="{{ $campoFiltro }}">
                        <option value="">Evento: todos</option>
                        <option value="Programado">Evento programado</option>
                        <option value="Confirmado">Evento confirmado</option>
                        <option value="En Proceso">Evento en proceso</option>
                        <option value="Finalizado">Evento finalizado</option>
                        <option value="Cancelado">Evento cancelado</option>
                    </select>
                    <x-ui.icon-button wire:click="limpiarFiltros" title="Restablecer filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </x-ui.icon-button>
                </div>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline">Evento / contrato</th>
                    <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                    <th class="px-3 py-2 border-r border-outline">Cliente</th>
                    <th class="px-3 py-2 border-r border-outline">Ubicación</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Contratado</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Cobrado</th>
                    <th class="px-3 py-2 text-right">Saldo</th>
                </x-slot:header>

                @forelse ($eventos as $ev)
                    @php $saldoPendiente = $ev->saldo_pendiente; @endphp
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <div class="font-semibold text-ink-primary">{{ $ev->codigo_evento }}</div>
                            @if ($ev->contrato)
                                <div class="text-xs font-semibold text-gold-700 dark:text-gold-400">
                                    {{ $ev->contrato->numero_contrato }}</div>
                            @else
                                <div class="text-xs text-ink-muted">Sin contrato</div>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                            {{ \Carbon\Carbon::parse($ev->fecha_evento)->format('d/m/Y') }}<span
                                class="text-ink-secondary"> - {{ substr($ev->hora_evento, 0, 5) }}</span>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-[200px]">
                            <div class="font-semibold text-ink-primary truncate"
                                title="{{ $ev->cliente->nombre_completo ?? '' }}">
                                {{ $ev->cliente->nombre_completo ?? 'Cliente no asignado' }}</div>
                            <div class="text-xs text-ink-secondary truncate">
                                {{ $ev->servicio->nombre ?? 'Servicio estándar' }}@if ($ev->telefono_contacto ?: $ev->cliente->telefono ?? null)
                                    - {{ $ev->telefono_contacto ?: $ev->cliente->telefono }}
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-[180px] truncate text-ink-secondary"
                            title="{{ $ev->direccion_evento }}">{{ $ev->direccion_evento ?: 'No especificada' }}</td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <span
                                    class="w-2 h-2 rounded-full {{ $puntoEstado($ev->estado) }}"></span>{{ $ev->estado }}
                            </div>
                            <div class="text-xs text-ink-secondary">
                                @if ($ev->contrato)
                                    <span class="inline-flex items-center gap-1"><span
                                            class="w-1.5 h-1.5 rounded-full {{ $puntoEstado($ev->contrato->estado) }}"></span>Contrato
                                        {{ mb_strtolower($ev->contrato->estado) }}</span>
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                        <td
                            class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-ink-primary">
                            Bs {{ number_format($ev->monto_contratado, 2) }}</td>
                        <td
                            class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">
                            Bs {{ number_format($ev->total_abonado, 2) }}</td>
                        <td
                            class="px-3 py-1.5 text-right whitespace-nowrap tabular-nums font-semibold {{ $saldoPendiente > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-ink-muted' }}">
                            Bs {{ number_format($saldoPendiente, 2) }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-sm text-ink-secondary">
                                <i class="fa-solid fa-calendar-xmark text-2xl text-ink-muted block mb-2"></i>
                                No se encontraron eventos o contratos. Prueba cambiando las fechas, el cliente o el estado.
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </div>

            {{-- Lista compacta (móvil) --}}
            <div class="md:hidden divide-y divide-outline">
                @forelse ($eventos as $ev)
                    @php $saldoPendiente = $ev->saldo_pendiente; @endphp
                    <div class="px-4 py-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-semibold text-sm text-ink-primary">{{ $ev->codigo_evento }}</span>
                                @if ($ev->contrato)
                                    <span
                                        class="text-xs font-semibold text-gold-700 dark:text-gold-400">{{ $ev->contrato->numero_contrato }}</span>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs text-ink-secondary shrink-0">
                                <span
                                    class="w-2 h-2 rounded-full {{ $puntoEstado($ev->estado) }}"></span>{{ $ev->estado }}
                            </span>
                        </div>
                        <div class="text-sm text-ink-primary truncate">
                            {{ $ev->cliente->nombre_completo ?? 'Cliente no asignado' }}</div>
                        <div class="text-xs text-ink-secondary truncate">
                            {{ \Carbon\Carbon::parse($ev->fecha_evento)->format('d/m/Y') }}
                            {{ substr($ev->hora_evento, 0, 5) }} - {{ $ev->servicio->nombre ?? 'Servicio estándar' }}
                        </div>
                        <div class="text-xs tabular-nums text-ink-secondary">
                            Contratado Bs {{ number_format($ev->monto_contratado, 2) }} -
                            <span class="text-emerald-700 dark:text-emerald-400">Cobrado Bs
                                {{ number_format($ev->total_abonado, 2) }}</span> -
                            <span
                                class="{{ $saldoPendiente > 0 ? 'text-rose-700 dark:text-rose-400 font-semibold' : '' }}">Saldo
                                Bs {{ number_format($saldoPendiente, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron eventos o contratos.</div>
                @endforelse
            </div>

            <x-slot:footer>
                <span>Mostrando {{ $eventos->firstItem() ?? 0 }}–{{ $eventos->lastItem() ?? 0 }} de
                    {{ $eventos->total() }}</span>
                <div>{{ $eventos->links() }}</div>
            </x-slot:footer>
        </x-ui.panel>
    </div>
