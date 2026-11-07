@php
    $campoFiltro =
        'h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors';
    $presets = ['este_mes' => 'Este mes', 'mes_anterior' => 'Mes anterior', 'este_ano' => 'Este año', 'todo' => 'Todo'];
    $paramsExport = [
        'inicio' => $fecha_inicio,
        'fin' => $fecha_fin,
        'cliente_id' => $cliente_id,
        'evento_id' => $evento_id,
        'servicio_id' => $servicio_id,
        'tipo' => $tipo_balance,
    ];
    $resultado = (float) ($kpis['resultado_operativo_estimado'] ?? 0);
    $cantidadFila = match ($tipo_balance) {
        'por_evento' => $balancesEventos->count(),
        'gastos_operativos' => $gastosOperativos->count(),
        default => null,
    };
@endphp

<div class="space-y-3">
    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-scale-balanced text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Reporte financiero y balances</h1>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.button size="sm" target="_blank"
                    href="{{ route('admin.reportes.financieros.pdf', $paramsExport) }}">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span class="hidden sm:inline">Reporte ejecutivo</span>
                    <span class="sm:hidden">PDF</span>
                </x-ui.button>
                <x-ui.action-menu>
                    <x-ui.action-menu-item icon="fa-solid fa-file-excel"
                        href="{{ route('admin.reportes.financieros.excel', $paramsExport) }}">Exportar
                        Excel</x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-chart-line" href="{{ route('admin.finanzas.dashboard') }}"
                        wire:navigate>Dashboard financiero</x-ui.action-menu-item>
                </x-ui.action-menu>
            </div>
        </x-slot:header>

        {{-- Tipo de balance + filtros --}}
        <x-slot:filters>
            <div class="flex flex-col xl:flex-row xl:items-center gap-2">
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('tipo_balance', 'consolidado')" :active="$tipo_balance === 'consolidado'">
                        <i class="fa-solid fa-chart-pie mr-1"></i>Consolidado
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('tipo_balance', 'por_evento')" :active="$tipo_balance === 'por_evento'">
                        <i class="fa-solid fa-list-ol mr-1"></i>Por evento
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('tipo_balance', 'gastos_operativos')" :active="$tipo_balance === 'gastos_operativos'">
                        <i class="fa-solid fa-building-columns mr-1"></i>Gastos operativos
                    </x-ui.filter-chip>
                </x-ui.filter-chips>

                <x-ui.filter-chips>
                    @foreach ($presets as $clave => $texto)
                        <x-ui.filter-chip wire:click="aplicarPreset('{{ $clave }}')"
                            :active="$periodo_preset === $clave">{{ $texto }}</x-ui.filter-chip>
                    @endforeach
                </x-ui.filter-chips>
            </div>

            <div class="flex flex-wrap items-center gap-2 mt-2">
                <input type="date" wire:model.live="fecha_inicio" title="Desde" class="{{ $campoFiltro }}">
                <input type="date" wire:model.live="fecha_fin" title="Hasta" class="{{ $campoFiltro }}">
                <select wire:model.live="cliente_id" class="{{ $campoFiltro }} max-w-[200px]">
                    <option value="">Todos los clientes</option>
                    @foreach ($clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre_completo }}</option>
                    @endforeach
                </select>
                <select wire:model.live="evento_id" class="{{ $campoFiltro }} max-w-[180px]">
                    <option value="">Todos los eventos</option>
                    @foreach ($eventosLista as $ev)
                        <option value="{{ $ev->id }}">{{ $ev->codigo_evento }}</option>
                    @endforeach
                </select>
                <select wire:model.live="servicio_id" class="{{ $campoFiltro }} max-w-[200px]">
                    <option value="">Todos los servicios</option>
                    @foreach ($servicios as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>

        {{-- ===================== BALANCE CONSOLIDADO ===================== --}}
        @if ($tipo_balance === 'consolidado')
            <div class="p-4 space-y-4">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                    <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                        <div class="flex items-center justify-between text-xs text-ink-secondary">
                            <span>Total ingresos</span>
                            <i class="fa-solid fa-arrow-trend-up text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <div class="text-xl font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">Bs
                            {{ number_format($kpis['total_ingresos_consolidados'], 2) }}</div>
                        <div class="text-xs text-ink-secondary">Cobrado a clientes + ingresos extra</div>
                    </div>
                    <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                        <div class="flex items-center justify-between text-xs text-ink-secondary">
                            <span>Costos y gastos</span>
                            <i class="fa-solid fa-arrow-trend-down text-rose-600 dark:text-rose-400"></i>
                        </div>
                        <div class="text-xl font-semibold tabular-nums text-rose-700 dark:text-rose-400">Bs
                            {{ number_format($kpis['costos_totales_reconocidos'], 2) }}</div>
                        <div class="text-xs text-ink-secondary">Músicos + gastos de eventos + operativos</div>
                    </div>
                    <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                        <div class="flex items-center justify-between text-xs text-ink-secondary">
                            <span>Balance bruto de eventos</span>
                            <i class="fa-solid fa-scale-balanced text-gold-600 dark:text-gold-400"></i>
                        </div>
                        <div class="text-xl font-semibold tabular-nums text-ink-primary">Bs
                            {{ number_format($kpis['balance_bruto_eventos'], 2) }}</div>
                        <div class="text-xs text-ink-secondary">Cobrado - asignado a músicos</div>
                    </div>
                    <div
                        class="rounded-lg border px-3 py-2 {{ $resultado >= 0 ? 'border-emerald-500/30 bg-emerald-500/10' : 'border-rose-500/30 bg-rose-500/10' }}">
                        <div class="flex items-center justify-between text-xs text-ink-secondary">
                            <span>Resultado operativo</span>
                            <i
                                class="fa-solid {{ $resultado >= 0 ? 'fa-chart-line text-emerald-600 dark:text-emerald-400' : 'fa-triangle-exclamation text-rose-600 dark:text-rose-400' }}"></i>
                        </div>
                        <div
                            class="text-xl font-semibold tabular-nums {{ $resultado >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                            {{ $resultado >= 0 ? '+' : '-' }}Bs {{ number_format(abs($resultado), 2) }}
                        </div>
                        <div class="text-xs text-ink-secondary">Margen neto del período</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="rounded-lg border border-outline overflow-hidden">
                        <h2
                            class="px-3 py-2 text-sm font-semibold text-ink-primary bg-surface-sunken border-b border-outline flex items-center gap-2">
                            <i class="fa-solid fa-file-contract text-gold-600 dark:text-gold-400"></i>Comercial y
                            cobranzas
                        </h2>
                        <dl class="px-3 divide-y divide-outline text-sm">
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Total contratado</dt>
                                <dd class="tabular-nums font-semibold text-ink-primary">Bs
                                    {{ number_format($kpis['total_contratado'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Total cobrado</dt>
                                <dd class="tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">Bs
                                    {{ number_format($kpis['total_cobrado'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Saldo por cobrar</dt>
                                <dd class="tabular-nums font-semibold text-rose-700 dark:text-rose-400">Bs
                                    {{ number_format($kpis['saldo_pendiente_clientes'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Ingresos extraordinarios</dt>
                                <dd class="tabular-nums font-semibold text-ink-primary">Bs
                                    {{ number_format($kpis['total_ingresos_adicionales'], 2) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-outline overflow-hidden">
                        <h2
                            class="px-3 py-2 text-sm font-semibold text-ink-primary bg-surface-sunken border-b border-outline flex items-center gap-2">
                            <i class="fa-solid fa-money-bill-transfer text-rose-600 dark:text-rose-400"></i>Músicos,
                            logística y operaciones
                        </h2>
                        <dl class="px-3 divide-y divide-outline text-sm">
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Asignado a músicos</dt>
                                <dd class="tabular-nums font-semibold text-ink-primary">Bs
                                    {{ number_format($kpis['total_asignado_participantes'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Pagado a músicos</dt>
                                <dd class="tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">Bs
                                    {{ number_format($kpis['total_pagado_participantes'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Saldo por pagar a músicos</dt>
                                <dd class="tabular-nums font-semibold text-rose-700 dark:text-rose-400">Bs
                                    {{ number_format($kpis['saldo_pendiente_participantes'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Gastos directos de eventos</dt>
                                <dd class="tabular-nums font-semibold text-amber-700 dark:text-amber-400">Bs
                                    {{ number_format($kpis['total_gastos_eventos'], 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <dt class="text-ink-secondary">Gastos operativos</dt>
                                <dd class="tabular-nums font-semibold text-ink-primary">Bs
                                    {{ number_format($kpis['total_gastos_operativos'], 2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- ===================== RENTABILIDAD POR EVENTO ===================== --}}
        @elseif ($tipo_balance === 'por_evento')
            <div class="hidden md:block">
                <x-ui.table>
                    <x-slot:header>
                        <th class="px-3 py-2 border-r border-outline">Evento</th>
                        <th class="px-3 py-2 border-r border-outline">Cliente</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Contratado</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Cobrado</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Músicos</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Gastos</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Balance bruto</th>
                        <th class="px-3 py-2 text-right">Resultado neto</th>
                    </x-slot:header>

                    @forelse ($balancesEventos as $be)
                        <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                            <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                                <span
                                    class="font-semibold text-gold-700 dark:text-gold-400">{{ $be['codigo_evento'] }}</span>
                                <div class="text-xs text-ink-secondary tabular-nums">
                                    {{ \Carbon\Carbon::parse($be['fecha_evento'])->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-3 py-1.5 border-r border-outline max-w-xs truncate text-ink-primary"
                                title="{{ $be['cliente'] }}">{{ $be['cliente'] }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-ink-primary">
                                Bs {{ number_format($be['monto_contratado'], 2) }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">
                                Bs {{ number_format($be['total_cobrado'], 2) }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-ink-primary">
                                Bs {{ number_format($be['total_asignado'], 2) }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-amber-700 dark:text-amber-400">
                                Bs {{ number_format($be['gastos_evento'], 2) }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums {{ $be['balance_bruto'] >= 0 ? 'text-ink-primary' : 'text-rose-700 dark:text-rose-400' }}">
                                Bs {{ number_format($be['balance_bruto'], 2) }}</td>
                            <td
                                class="px-3 py-1.5 text-right whitespace-nowrap tabular-nums font-semibold {{ $be['balance_neto'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                {{ $be['balance_neto'] >= 0 ? '+' : '-' }}Bs
                                {{ number_format(abs($be['balance_neto']), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-sm text-ink-secondary">No se encontraron
                                eventos en el período consultado.</td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </div>

            <div class="md:hidden divide-y divide-outline">
                @forelse ($balancesEventos as $be)
                    <div class="px-4 py-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="font-semibold text-sm text-gold-700 dark:text-gold-400">{{ $be['codigo_evento'] }}</span>
                            <span
                                class="text-sm tabular-nums font-semibold {{ $be['balance_neto'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                {{ $be['balance_neto'] >= 0 ? '+' : '-' }}Bs
                                {{ number_format(abs($be['balance_neto']), 2) }}
                            </span>
                        </div>
                        <div class="text-sm text-ink-primary truncate">{{ $be['cliente'] }}</div>
                        <div class="text-xs text-ink-secondary">
                            {{ \Carbon\Carbon::parse($be['fecha_evento'])->format('d/m/Y') }} - Cobrado Bs
                            {{ number_format($be['total_cobrado'], 2) }} - Músicos Bs
                            {{ number_format($be['total_asignado'], 2) }} - Gastos Bs
                            {{ number_format($be['gastos_evento'], 2) }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron eventos en el período
                        consultado.</div>
                @endforelse
            </div>

            {{-- ===================== GASTOS OPERATIVOS ===================== --}}
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3">
                <div class="lg:border-r border-b lg:border-b-0 border-outline p-4 space-y-2">
                    <h2 class="text-sm font-semibold text-ink-primary">Resumen por categoría</h2>
                    @forelse ($gastosPorCategoria as $gpc)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <div class="min-w-0">
                                <div class="text-ink-primary truncate">{{ $gpc->categoria }}</div>
                                <div class="text-xs text-ink-secondary">{{ $gpc->cantidad }} registro(s)</div>
                            </div>
                            <span
                                class="tabular-nums font-semibold text-rose-700 dark:text-rose-400 whitespace-nowrap">Bs
                                {{ number_format($gpc->total, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-ink-secondary">Sin gastos operativos registrados.</p>
                    @endforelse
                </div>

                <div class="lg:col-span-2 min-w-0">
                    <div class="px-4 py-2.5 border-b border-outline flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-ink-primary">Detalle de gastos operativos</h2>
                        <span class="text-sm text-ink-secondary">Total: <strong
                                class="tabular-nums text-rose-700 dark:text-rose-400">Bs
                                {{ number_format($gastosOperativos->sum('monto'), 2) }}</strong></span>
                    </div>

                    <div class="hidden md:block">
                        <x-ui.table>
                            <x-slot:header>
                                <th class="px-3 py-2 border-r border-outline">Fecha</th>
                                <th class="px-3 py-2 border-r border-outline">Categoría</th>
                                <th class="px-3 py-2 border-r border-outline">Concepto</th>
                                <th class="px-3 py-2 border-r border-outline">Proveedor</th>
                                <th class="px-3 py-2 text-right">Monto</th>
                            </x-slot:header>

                            @forelse ($gastosOperativos as $go)
                                <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                                    <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($go->fecha)->format('d/m/Y') }}</td>
                                    <td
                                        class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-gold-700 dark:text-gold-400">
                                        {{ $go->categoria }}</td>
                                    <td class="px-3 py-1.5 border-r border-outline text-ink-primary">
                                        {{ $go->concepto }}</td>
                                    <td class="px-3 py-1.5 border-r border-outline text-ink-secondary">
                                        {{ $go->proveedor ?: '-' }}</td>
                                    <td
                                        class="px-3 py-1.5 text-right whitespace-nowrap tabular-nums font-semibold text-rose-700 dark:text-rose-400">
                                        Bs {{ number_format($go->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-sm text-ink-secondary">No se
                                        encontraron gastos operativos en este período.</td>
                                </tr>
                            @endforelse
                        </x-ui.table>
                    </div>

                    <div class="md:hidden divide-y divide-outline">
                        @forelse ($gastosOperativos as $go)
                            <div class="px-4 py-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="text-sm font-semibold text-ink-primary truncate">{{ $go->concepto }}</span>
                                    <span
                                        class="text-sm tabular-nums font-semibold text-rose-700 dark:text-rose-400 whitespace-nowrap">Bs
                                        {{ number_format($go->monto, 2) }}</span>
                                </div>
                                <div class="text-xs text-ink-secondary truncate">
                                    {{ \Carbon\Carbon::parse($go->fecha)->format('d/m/Y') }} - {{ $go->categoria }}
                                    @if ($go->proveedor)
                                        - {{ $go->proveedor }}
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron gastos
                                operativos en este período.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        @if ($cantidadFila !== null)
            <x-slot:footer>
                <span>{{ $cantidadFila }}
                    {{ $tipo_balance === 'por_evento' ? 'evento(s) evaluado(s)' : 'registro(s)' }}</span>
            </x-slot:footer>
        @endif
    </x-ui.panel>
</div>