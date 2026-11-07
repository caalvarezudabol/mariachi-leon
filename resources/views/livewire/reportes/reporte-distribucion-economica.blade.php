@php
    $campoFiltro =
        'h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors';
    $paramsExport = [
        'modalidad' => $modalidad,
        'evento_id' => $evento_id,
        'persona_id' => $persona_id,
        'desde' => $fecha_desde,
        'hasta' => $fecha_hasta,
    ];

    // Estado de pago de una asignación: [texto, color del punto]
    $estadoPago = fn($estado) => match ($estado) {
        'PAGADO' => ['Pagado', 'bg-emerald-500'],
        'PAGO PARCIAL' => ['Pago parcial', 'bg-amber-500'],
        default => ['Pendiente', 'bg-rose-500'],
    };

    $titulos = [
        'consolidado' => 'Consolidado de todos los integrantes',
        'por_evento' => 'Detalle de un evento',
        'por_participante' => 'Historial de un integrante',
    ];
@endphp

<div class="space-y-3">
    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-users-viewfinder text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Distribución económica</h1>
                <span
                    class="hidden sm:inline text-xs text-ink-secondary truncate">{{ $titulos[$modalidad] ?? '' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.button size="sm" target="_blank"
                    href="{{ route('admin.reportes.distribucion.pdf', $paramsExport) }}">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>PDF</span>
                </x-ui.button>
                <x-ui.action-menu>
                    <x-ui.action-menu-item icon="fa-solid fa-file-excel"
                        href="{{ route('admin.reportes.distribucion.excel', $paramsExport) }}">Exportar
                        Excel</x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-chart-line" href="{{ route('admin.finanzas.dashboard') }}"
                        wire:navigate>Dashboard financiero</x-ui.action-menu-item>
                </x-ui.action-menu>
            </div>
        </x-slot:header>

        {{-- Modalidad + filtros (una sola fila) --}}
        <x-slot:filters>
            <div class="flex flex-col xl:flex-row xl:items-center gap-2">
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('modalidad', 'consolidado')" :active="$modalidad === 'consolidado'">
                        <i class="fa-solid fa-table-list mr-1"></i>Consolidado
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('modalidad', 'por_evento')" :active="$modalidad === 'por_evento'">
                        <i class="fa-solid fa-calendar-day mr-1"></i>Por evento
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('modalidad', 'por_participante')" :active="$modalidad === 'por_participante'">
                        <i class="fa-solid fa-user mr-1"></i>Por integrante
                    </x-ui.filter-chip>
                </x-ui.filter-chips>

                <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                    @if ($modalidad === 'consolidado')
                        <div class="relative flex-1 min-w-[200px]">
                            <i
                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Buscar integrante por nombre, CI o teléfono..."
                                class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                        </div>
                        <input type="date" wire:model.live="fecha_desde" title="Desde" class="{{ $campoFiltro }}">
                        <input type="date" wire:model.live="fecha_hasta" title="Hasta" class="{{ $campoFiltro }}">
                    @elseif ($modalidad === 'por_evento')
                        <select wire:model.live="evento_id" class="{{ $campoFiltro }} flex-1 min-w-[220px]">
                            <option value="">Selecciona un evento...</option>
                            @foreach ($eventosLista as $ev)
                                <option value="{{ $ev->id }}">{{ $ev->codigo_evento }} -
                                    {{ \Carbon\Carbon::parse($ev->fecha_evento)->format('d/m/Y') }} -
                                    {{ $ev->cliente->nombre_completo ?? 'Cliente S/N' }}</option>
                            @endforeach
                        </select>
                    @else
                        <select wire:model.live="persona_id" class="{{ $campoFiltro }} flex-1 min-w-[220px]">
                            <option value="">Selecciona un integrante...</option>
                            @foreach ($musicosLista as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre_completo }} ({{ $m->tipo }})
                                </option>
                            @endforeach
                        </select>
                        <input type="date" wire:model.live="fecha_desde" title="Desde" class="{{ $campoFiltro }}">
                        <input type="date" wire:model.live="fecha_hasta" title="Hasta" class="{{ $campoFiltro }}">
                    @endif
                </div>
            </div>
        </x-slot:filters>

        {{-- ===================== CONSOLIDADO ===================== --}}
        @if ($modalidad === 'consolidado')
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 p-4 border-b border-outline">
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Integrantes</span><i class="fa-solid fa-users text-ink-muted"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-ink-primary">
                        {{ number_format($resumenGlobal['total_musicos']) }}</div>
                    <div class="text-xs text-ink-secondary">Músicos y personal</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Total asignado</span><i
                            class="fa-solid fa-hand-holding-dollar text-gold-600 dark:text-gold-400"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-ink-primary">Bs
                        {{ number_format($resumenGlobal['total_asignado'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Comisiones comprometidas</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Total pagado</span><i
                            class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">Bs
                        {{ number_format($resumenGlobal['total_pagado'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Efectivo o QR transferido</div>
                </div>
                <div
                    class="rounded-lg border px-3 py-2 {{ $resumenGlobal['saldo_pendiente'] > 0 ? 'border-rose-500/30 bg-rose-500/10' : 'border-emerald-500/30 bg-emerald-500/10' }}">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Saldo pendiente</span><i
                            class="fa-solid fa-clock {{ $resumenGlobal['saldo_pendiente'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}"></i>
                    </div>
                    <div
                        class="text-lg font-semibold tabular-nums {{ $resumenGlobal['saldo_pendiente'] > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                        Bs {{ number_format($resumenGlobal['saldo_pendiente'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Por liquidar a integrantes</div>
                </div>
            </div>

            <div class="hidden md:block">
                <x-ui.table>
                    <x-slot:header>
                        <th class="px-3 py-2 border-r border-outline">Integrante</th>
                        <th class="px-3 py-2 border-r border-outline">Tipo</th>
                        <th class="px-3 py-2 border-r border-outline">Teléfono</th>
                        <th class="px-3 py-2 border-r border-outline text-center">Eventos</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Asignado</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Pagado</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Saldo</th>
                        <th class="px-3 py-2">Estado</th>
                    </x-slot:header>

                    @forelse ($filasConsolidadas as $fc)
                        @php $alDia = $fc['saldo_pendiente'] <= 0.01; @endphp
                        <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                            <td
                                class="px-3 py-1.5 border-r border-outline whitespace-nowrap font-semibold text-ink-primary">
                                {{ $fc['nombre_completo'] }}</td>
                            <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-ink-secondary">
                                {{ $fc['tipo'] }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums text-ink-secondary">
                                {{ $fc['telefono'] ?: '-' }}</td>
                            <td class="px-3 py-1.5 border-r border-outline text-center tabular-nums text-ink-primary">
                                {{ $fc['total_eventos'] }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-ink-primary">
                                Bs {{ number_format($fc['total_asignado'], 2) }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">
                                Bs {{ number_format($fc['total_pagado'], 2) }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold {{ $alDia ? 'text-ink-muted' : 'text-rose-700 dark:text-rose-400' }}">
                                Bs {{ number_format($fc['saldo_pendiente'], 2) }}</td>
                            <td class="px-3 py-1.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                    <span
                                        class="w-2 h-2 rounded-full {{ $alDia ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>{{ $alDia ? 'Al día' : 'Pendiente' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-sm text-ink-secondary">No hay integrantes
                                para este período.</td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </div>

            <div class="md:hidden divide-y divide-outline">
                @forelse ($filasConsolidadas as $fc)
                    @php $alDia = $fc['saldo_pendiente'] <= 0.01; @endphp
                    <div class="px-4 py-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="text-sm font-semibold text-ink-primary truncate">{{ $fc['nombre_completo'] }}</span>
                            <span class="inline-flex items-center gap-1 text-xs text-ink-secondary shrink-0">
                                <span
                                    class="w-2 h-2 rounded-full {{ $alDia ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>{{ $alDia ? 'Al día' : 'Pendiente' }}
                            </span>
                        </div>
                        <div class="text-xs text-ink-secondary">{{ $fc['tipo'] }} - {{ $fc['total_eventos'] }}
                            evento(s)</div>
                        <div class="text-xs tabular-nums text-ink-secondary">
                            Asignado Bs {{ number_format($fc['total_asignado'], 2) }} -
                            <span class="text-emerald-700 dark:text-emerald-400">Pagado Bs
                                {{ number_format($fc['total_pagado'], 2) }}</span> -
                            <span class="{{ $alDia ? '' : 'text-rose-700 dark:text-rose-400 font-semibold' }}">Saldo
                                Bs {{ number_format($fc['saldo_pendiente'], 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-sm text-ink-secondary">No hay integrantes para este período.
                    </div>
                @endforelse
            </div>

            <x-slot:footer>
                <span>{{ $filasConsolidadas->count() }} integrante(s)</span>
            </x-slot:footer>

            {{-- ===================== POR EVENTO ===================== --}}
        @elseif ($modalidad === 'por_evento')
            @if ($evento)
                <div class="p-4 space-y-3 border-b border-outline">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span
                                    class="font-semibold text-gold-700 dark:text-gold-400">{{ $evento->codigo_evento }}</span>
                                <span class="text-ink-muted">-</span>
                                <h2 class="text-base font-semibold text-ink-primary truncate">
                                    {{ $evento->cliente->nombre_completo ?? 'Cliente S/N' }}</h2>
                            </div>
                            <p class="text-sm text-ink-secondary tabular-nums">
                                {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} -
                                {{ substr($evento->hora_evento, 0, 5) }} -
                                {{ $evento->servicio->nombre ?? 'Estándar' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-5 text-sm">
                            <div class="lg:text-right">
                                <div class="text-xs text-ink-secondary">Contratado</div>
                                <div class="font-semibold tabular-nums text-ink-primary">Bs
                                    {{ number_format($evento->monto_contratado, 2) }}</div>
                            </div>
                            <div class="lg:text-right">
                                <div class="text-xs text-ink-secondary">Cobrado</div>
                                <div class="font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">Bs
                                    {{ number_format($evento->total_abonado, 2) }}</div>
                            </div>
                            <div class="lg:text-right">
                                <div class="text-xs text-ink-secondary">Saldo cliente</div>
                                <div class="font-semibold tabular-nums text-rose-700 dark:text-rose-400">Bs
                                    {{ number_format($evento->saldo_pendiente, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                            <div class="text-xs text-ink-secondary">Comisiones asignadas</div>
                            <div class="text-lg font-semibold tabular-nums text-ink-primary">Bs
                                {{ number_format($resumenEvento['total_asignado'], 2) }}</div>
                            <div class="text-xs text-ink-secondary">{{ $resumenEvento['cantidad_participantes'] }}
                                integrante(s)</div>
                        </div>
                        <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                            <div class="text-xs text-ink-secondary">Pagado a músicos</div>
                            <div class="text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">Bs
                                {{ number_format($resumenEvento['total_pagado'], 2) }}</div>
                            <div class="text-xs text-ink-secondary">Distribuciones ya pagadas</div>
                        </div>
                        <div
                            class="rounded-lg border px-3 py-2 {{ $resumenEvento['saldo_pendiente'] > 0 ? 'border-rose-500/30 bg-rose-500/10' : 'border-emerald-500/30 bg-emerald-500/10' }}">
                            <div class="text-xs text-ink-secondary">Saldo pendiente a músicos</div>
                            <div
                                class="text-lg font-semibold tabular-nums {{ $resumenEvento['saldo_pendiente'] > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                Bs {{ number_format($resumenEvento['saldo_pendiente'], 2) }}</div>
                            <div class="text-xs text-ink-secondary">Por liquidar en este evento</div>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block">
                    <x-ui.table>
                        <x-slot:header>
                            <th class="px-3 py-2 border-r border-outline">Integrante</th>
                            <th class="px-3 py-2 border-r border-outline">Función</th>
                            <th class="px-3 py-2 border-r border-outline">Concepto</th>
                            <th class="px-3 py-2 border-r border-outline text-right">Asignado</th>
                            <th class="px-3 py-2 border-r border-outline text-right">Pagado</th>
                            <th class="px-3 py-2 border-r border-outline text-right">Saldo</th>
                            <th class="px-3 py-2">Estado</th>
                        </x-slot:header>

                        @forelse ($participantes as $p)
                            @php [$txtEstado, $puntoEstado] = $estadoPago($p->estado_calculado); @endphp
                            <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                                <td
                                    class="px-3 py-1.5 border-r border-outline whitespace-nowrap font-semibold text-ink-primary">
                                    {{ $p->persona->nombre_completo ?? 'No asignado' }}</td>
                                <td class="px-3 py-1.5 border-r border-outline text-ink-primary">{{ $p->funcion }}
                                </td>
                                <td class="px-3 py-1.5 border-r border-outline text-ink-secondary">
                                    {{ $p->concepto_pago }}</td>
                                <td
                                    class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-ink-primary">
                                    Bs {{ number_format($p->monto_asignado, 2) }}</td>
                                <td
                                    class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">
                                    Bs {{ number_format($p->total_pagado, 2) }}</td>
                                <td
                                    class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold {{ $p->saldo_pendiente > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-ink-muted' }}">
                                    Bs {{ number_format($p->saldo_pendiente, 2) }}</td>
                                <td class="px-3 py-1.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary"><span
                                            class="w-2 h-2 rounded-full {{ $puntoEstado }}"></span>{{ $txtEstado }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-sm text-ink-secondary">No hay
                                    participantes asignados a este evento.</td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </div>

                <div class="md:hidden divide-y divide-outline">
                    @forelse ($participantes as $p)
                        @php [$txtEstado, $puntoEstado] = $estadoPago($p->estado_calculado); @endphp
                        <div class="px-4 py-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    class="text-sm font-semibold text-ink-primary truncate">{{ $p->persona->nombre_completo ?? 'No asignado' }}</span>
                                <span class="inline-flex items-center gap-1 text-xs text-ink-secondary shrink-0"><span
                                        class="w-2 h-2 rounded-full {{ $puntoEstado }}"></span>{{ $txtEstado }}</span>
                            </div>
                            <div class="text-xs text-ink-secondary truncate">{{ $p->funcion }}@if ($p->concepto_pago)
                                    - {{ $p->concepto_pago }}
                                @endif
                            </div>
                            <div class="text-xs tabular-nums text-ink-secondary">
                                Asignado Bs {{ number_format($p->monto_asignado, 2) }} -
                                <span class="text-emerald-700 dark:text-emerald-400">Pagado Bs
                                    {{ number_format($p->total_pagado, 2) }}</span> -
                                <span
                                    class="{{ $p->saldo_pendiente > 0 ? 'text-rose-700 dark:text-rose-400 font-semibold' : '' }}">Saldo
                                    Bs {{ number_format($p->saldo_pendiente, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-sm text-ink-secondary">No hay participantes asignados a este
                            evento.</div>
                    @endforelse
                </div>
            @else
                <div class="py-10 text-center text-sm text-ink-secondary">
                    <i class="fa-solid fa-calendar-day text-2xl text-ink-muted block mb-2"></i>
                    Selecciona un evento para ver su distribución económica.
                </div>
            @endif

            {{-- ===================== POR INTEGRANTE ===================== --}}
        @else
            @if ($persona)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 p-4 border-b border-outline">
                    <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                        <div class="text-xs text-ink-secondary">Eventos asignados</div>
                        <div class="text-lg font-semibold tabular-nums text-ink-primary">
                            {{ $resumenParticipante['total_eventos'] }}</div>
                        <div class="text-xs text-ink-secondary">En el período</div>
                    </div>
                    <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                        <div class="text-xs text-ink-secondary">Comisiones asignadas</div>
                        <div class="text-lg font-semibold tabular-nums text-ink-primary">Bs
                            {{ number_format($resumenParticipante['total_asignado'], 2) }}</div>
                        <div class="text-xs text-ink-secondary">Monto pactado</div>
                    </div>
                    <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                        <div class="text-xs text-ink-secondary">Total cobrado</div>
                        <div class="text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">Bs
                            {{ number_format($resumenParticipante['total_pagado'], 2) }}</div>
                        <div class="text-xs text-ink-secondary">Pagos recibidos</div>
                    </div>
                    <div
                        class="rounded-lg border px-3 py-2 {{ $resumenParticipante['saldo_pendiente'] > 0 ? 'border-rose-500/30 bg-rose-500/10' : 'border-emerald-500/30 bg-emerald-500/10' }}">
                        <div class="text-xs text-ink-secondary">Saldo por cobrar</div>
                        <div
                            class="text-lg font-semibold tabular-nums {{ $resumenParticipante['saldo_pendiente'] > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                            Bs {{ number_format($resumenParticipante['saldo_pendiente'], 2) }}</div>
                        <div class="text-xs text-ink-secondary">Pendiente de desembolso</div>
                    </div>
                </div>

                <div class="px-4 py-2.5 border-b border-outline">
                    <h2 class="text-sm font-semibold text-ink-primary">Historial de participaciones de
                        {{ $persona->nombre_completo }}</h2>
                </div>

                <div class="hidden md:block">
                    <x-ui.table>
                        <x-slot:header>
                            <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                            <th class="px-3 py-2 border-r border-outline">Evento</th>
                            <th class="px-3 py-2 border-r border-outline">Cliente</th>
                            <th class="px-3 py-2 border-r border-outline">Función</th>
                            <th class="px-3 py-2 border-r border-outline text-right">Asignado</th>
                            <th class="px-3 py-2 border-r border-outline text-right">Pagado</th>
                            <th class="px-3 py-2 border-r border-outline text-right">Saldo</th>
                            <th class="px-3 py-2">Estado</th>
                        </x-slot:header>

                        @forelse ($participaciones as $part)
                            @php [$txtEstado, $puntoEstado] = $estadoPago($part->estado_calculado); @endphp
                            <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                                <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                                    {{ $part->evento ? \Carbon\Carbon::parse($part->evento->fecha_evento)->format('d/m/Y') : '-' }}
                                </td>
                                <td
                                    class="px-3 py-1.5 border-r border-outline whitespace-nowrap font-semibold text-gold-700 dark:text-gold-400">
                                    {{ $part->evento->codigo_evento ?? 'S/E' }}</td>
                                <td class="px-3 py-1.5 border-r border-outline max-w-xs truncate text-ink-primary">
                                    {{ $part->evento->cliente->nombre_completo ?? 'Cliente S/N' }}</td>
                                <td class="px-3 py-1.5 border-r border-outline text-ink-primary">{{ $part->funcion }}
                                </td>
                                <td
                                    class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-ink-primary">
                                    Bs {{ number_format($part->monto_asignado, 2) }}</td>
                                <td
                                    class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">
                                    Bs {{ number_format($part->total_pagado, 2) }}</td>
                                <td
                                    class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold {{ $part->saldo_pendiente > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-ink-muted' }}">
                                    Bs {{ number_format($part->saldo_pendiente, 2) }}</td>
                                <td class="px-3 py-1.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary"><span
                                            class="w-2 h-2 rounded-full {{ $puntoEstado }}"></span>{{ $txtEstado }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-sm text-ink-secondary">No hay
                                    participaciones de este integrante en el rango de fechas.</td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </div>

                <div class="md:hidden divide-y divide-outline">
                    @forelse ($participaciones as $part)
                        @php [$txtEstado, $puntoEstado] = $estadoPago($part->estado_calculado); @endphp
                        <div class="px-4 py-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    class="text-sm font-semibold text-gold-700 dark:text-gold-400">{{ $part->evento->codigo_evento ?? 'S/E' }}</span>
                                <span class="inline-flex items-center gap-1 text-xs text-ink-secondary shrink-0"><span
                                        class="w-2 h-2 rounded-full {{ $puntoEstado }}"></span>{{ $txtEstado }}</span>
                            </div>
                            <div class="text-sm text-ink-primary truncate">
                                {{ $part->evento->cliente->nombre_completo ?? 'Cliente S/N' }}</div>
                            <div class="text-xs text-ink-secondary">
                                {{ $part->evento ? \Carbon\Carbon::parse($part->evento->fecha_evento)->format('d/m/Y') : '-' }}
                                - {{ $part->funcion }}</div>
                            <div class="text-xs tabular-nums text-ink-secondary">
                                Asignado Bs {{ number_format($part->monto_asignado, 2) }} -
                                <span class="text-emerald-700 dark:text-emerald-400">Pagado Bs
                                    {{ number_format($part->total_pagado, 2) }}</span> -
                                <span
                                    class="{{ $part->saldo_pendiente > 0 ? 'text-rose-700 dark:text-rose-400 font-semibold' : '' }}">Saldo
                                    Bs {{ number_format($part->saldo_pendiente, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-sm text-ink-secondary">No hay participaciones de este
                            integrante en el rango de fechas.</div>
                    @endforelse
                </div>
            @else
                <div class="py-10 text-center text-sm text-ink-secondary">
                    <i class="fa-solid fa-user text-2xl text-ink-muted block mb-2"></i>
                    Selecciona un integrante para ver su historial de distribución.
                </div>
            @endif
        @endif
    </x-ui.panel>
</div>