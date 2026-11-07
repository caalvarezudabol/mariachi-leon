@php
    $campoFiltro =
        'h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors';
    $presets = ['este_mes' => 'Este mes', 'mes_anterior' => 'Mes anterior', 'este_ano' => 'Este año', 'todo' => 'Todo'];

    $k = fn($clave) => (float) ($kpis[$clave] ?? 0);
    $resultado = $k('resultado_operativo_estimado');
    $balanceBruto = $k('balance_bruto_eventos');
    $saldoClientes = $k('saldo_pendiente_clientes');
    $saldoMusicos = $k('saldo_pendiente_participantes');

    $meses = collect($graficos['meses'] ?? []);
    $porServicio = collect($graficos['ingresos_por_servicio'] ?? []);
    $porCategoria = collect($graficos['gastos_por_categoria'] ?? []);

    $maxMes = max(1, (float) $meses->max(fn($m) => max($m['ingresos'] ?? 0, $m['egresos'] ?? 0)));
    $maxServ = max(1, (float) $porServicio->max('total'));
    $maxCat = max(1, (float) $porCategoria->max('total'));
@endphp

<div class="space-y-3">

    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-chart-line text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Dashboard financiero</h1>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.button size="sm" variant="secondary" target="_blank"
                    href="{{ route('admin.reportes.financieros.pdf', ['inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'cliente_id' => $cliente_id, 'evento_id' => $evento_id]) }}">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span class="hidden sm:inline">Reporte ejecutivo</span>
                    <span class="sm:hidden">PDF</span>
                </x-ui.button>
                <x-ui.action-menu>
                    <x-ui.action-menu-item icon="fa-solid fa-money-bill-transfer"
                        href="{{ route('admin.finanzas.movimientos') }}" wire:navigate>Ingresos y
                        gastos</x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-vault" href="{{ route('admin.finanzas.fondo-comun') }}"
                        wire:navigate>Fondo común</x-ui.action-menu-item>
                </x-ui.action-menu>
            </div>
        </x-slot:header>

        {{-- Filtros del período --}}
        <x-slot:filters>
            <div class="flex flex-col xl:flex-row xl:items-center gap-2">
                <x-ui.filter-chips>
                    @foreach ($presets as $clave => $texto)
                        <x-ui.filter-chip wire:click="aplicarPreset('{{ $clave }}')"
                            :active="$periodo_preset === $clave">{{ $texto }}</x-ui.filter-chip>
                    @endforeach
                </x-ui.filter-chips>
                <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                    <input type="date" wire:model.live="fecha_inicio" title="Desde" class="{{ $campoFiltro }}">
                    <input type="date" wire:model.live="fecha_fin" title="Hasta" class="{{ $campoFiltro }}">
                    <select wire:model.live="cliente_id" class="{{ $campoFiltro }} max-w-[200px]">
                        <option value="">Todos los clientes</option>
                        @foreach ($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre_completo }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="evento_id" class="{{ $campoFiltro }} max-w-[220px]">
                        <option value="">Todos los eventos</option>
                        @foreach ($eventos as $ev)
                            <option value="{{ $ev->id }}">{{ $ev->codigo_evento }}
                                ({{ $ev->cliente->nombre_completo ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                    <select wire:model.live="servicio_id" class="{{ $campoFiltro }} max-w-[200px]">
                        <option value="">Todos los servicios</option>
                        @foreach ($servicios as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                    <x-ui.icon-button wire:click="limpiarFiltros" title="Restablecer filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </x-ui.icon-button>
                </div>
            </div>
        </x-slot:filters>

        <div class="p-4 space-y-4">

            {{-- Indicadores principales --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Ingresos</span>
                        <i class="fa-solid fa-arrow-trend-up text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div class="text-xl font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">Bs
                        {{ number_format($k('total_ingresos_consolidados'), 2) }}</div>
                    <div class="text-xs text-ink-secondary">Cobrado a clientes + ingresos extra</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Egresos</span>
                        <i class="fa-solid fa-arrow-trend-down text-rose-600 dark:text-rose-400"></i>
                    </div>
                    <div class="text-xl font-semibold tabular-nums text-rose-700 dark:text-rose-400">Bs
                        {{ number_format($k('total_egresos'), 2) }}</div>
                    <div class="text-xs text-ink-secondary">Gastos operativos + de eventos</div>
                </div>
                <div
                    class="rounded-lg border px-3 py-2 {{ $resultado >= 0 ? 'border-emerald-500/30 bg-emerald-500/10' : 'border-rose-500/30 bg-rose-500/10' }}">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Resultado operativo</span>
                        <i
                            class="fa-solid {{ $resultado >= 0 ? 'fa-scale-balanced text-emerald-600 dark:text-emerald-400' : 'fa-triangle-exclamation text-rose-600 dark:text-rose-400' }}"></i>
                    </div>
                    <div
                        class="text-xl font-semibold tabular-nums {{ $resultado >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                        {{ $resultado >= 0 ? '' : '-' }}Bs {{ number_format(abs($resultado), 2) }}
                    </div>
                    <div class="text-xs text-ink-secondary">Ingresos - (comisiones + gastos)</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Saldo fondo común</span>
                        <i class="fa-solid fa-vault text-gold-600 dark:text-gold-400"></i>
                    </div>
                    <div class="text-xl font-semibold tabular-nums text-gold-700 dark:text-gold-400">Bs
                        {{ number_format($k('saldo_fondo_comun'), 2) }}</div>
                    <div class="text-xs text-ink-secondary">Disponible en el fondo</div>
                </div>
            </div>

            {{-- Detalle por área --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                {{-- Clientes y contratos --}}
                <div class="rounded-lg border border-outline overflow-hidden">
                    <h2
                        class="px-3 py-2 text-sm font-semibold text-ink-primary bg-surface-sunken border-b border-outline flex items-center gap-2">
                        <i class="fa-solid fa-file-contract text-sky-600 dark:text-sky-400"></i>Clientes y contratos
                    </h2>
                    <dl class="px-3 divide-y divide-outline text-sm">
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Total contratado <span
                                    class="text-xs text-ink-muted">({{ (int) $k('count_contratos') }})</span></dt>
                            <dd class="tabular-nums font-semibold text-ink-primary">Bs
                                {{ number_format($k('total_contratado'), 2) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Cobrado</dt>
                            <dd class="tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">Bs
                                {{ number_format($k('total_cobrado'), 2) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Saldo por cobrar</dt>
                            <dd
                                class="tabular-nums font-semibold {{ $saldoClientes > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                Bs {{ number_format($saldoClientes, 2) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Ingresos extra</dt>
                            <dd class="tabular-nums font-semibold text-ink-primary">Bs
                                {{ number_format($k('total_ingresos_adicionales'), 2) }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Músicos --}}
                <div class="rounded-lg border border-outline overflow-hidden">
                    <h2
                        class="px-3 py-2 text-sm font-semibold text-ink-primary bg-surface-sunken border-b border-outline flex items-center gap-2">
                        <i class="fa-solid fa-users text-violet-600 dark:text-violet-400"></i>Músicos y comisiones
                    </h2>
                    <dl class="px-3 divide-y divide-outline text-sm">
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Asignado</dt>
                            <dd class="tabular-nums font-semibold text-ink-primary">Bs
                                {{ number_format($k('total_asignado_participantes'), 2) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Pagado</dt>
                            <dd class="tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">Bs
                                {{ number_format($k('total_pagado_participantes'), 2) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Saldo por pagar</dt>
                            <dd
                                class="tabular-nums font-semibold {{ $saldoMusicos > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                Bs {{ number_format($saldoMusicos, 2) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary"
                                title="Total cobrado a clientes menos total asignado a músicos">Balance bruto de eventos
                            </dt>
                            <dd
                                class="tabular-nums font-semibold {{ $balanceBruto >= 0 ? 'text-sky-700 dark:text-sky-400' : 'text-rose-700 dark:text-rose-400' }}">
                                Bs {{ number_format($balanceBruto, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Gastos --}}
                <div class="rounded-lg border border-outline overflow-hidden">
                    <h2
                        class="px-3 py-2 text-sm font-semibold text-ink-primary bg-surface-sunken border-b border-outline flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-rose-600 dark:text-rose-400"></i>Gastos
                    </h2>
                    <dl class="px-3 divide-y divide-outline text-sm">
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Operativos <span class="text-xs text-ink-muted">(alquiler,
                                    servicios)</span></dt>
                            <dd class="tabular-nums font-semibold text-ink-primary">Bs
                                {{ number_format($k('total_gastos_operativos'), 2) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">De eventos <span
                                    class="text-xs text-ink-muted">(transporte, viáticos)</span></dt>
                            <dd class="tabular-nums font-semibold text-amber-700 dark:text-amber-400">Bs
                                {{ number_format($k('total_gastos_eventos'), 2) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-ink-secondary">Total egresos</dt>
                            <dd class="tabular-nums font-semibold text-rose-700 dark:text-rose-400">Bs
                                {{ number_format($k('total_egresos'), 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </x-ui.panel>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        {{-- Evolución mensual --}}
        <div class="lg:col-span-2 rounded-lg border border-outline bg-surface-card overflow-hidden">
            <div class="px-4 py-2.5 border-b border-outline flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-ink-primary flex items-center gap-2">
                    <i class="fa-solid fa-chart-column text-gold-600 dark:text-gold-400"></i>Evolución mensual
                </h2>
                <div class="flex items-center gap-3 text-xs text-ink-secondary">
                    <span class="inline-flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>Ingresos</span>
                    <span class="inline-flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span>Egresos</span>
                </div>
            </div>
            <div class="p-4">
                @if ($meses->isEmpty())
                    <div class="py-10 text-center text-sm text-ink-secondary">No hay movimientos en el período
                        consultado.</div>
                @else
                    <div class="grid gap-2 sm:gap-4 items-end h-44 border-b border-outline px-1"
                        style="grid-template-columns: repeat({{ $meses->count() }}, minmax(0, 1fr));">
                        @foreach ($meses as $m)
                            @php
                                $hIng = min(100, max(3, (($m['ingresos'] ?? 0) / $maxMes) * 100));
                                $hEgr = min(100, max(3, (($m['egresos'] ?? 0) / $maxMes) * 100));
                            @endphp
                            <div class="h-full flex items-end justify-center gap-1 sm:gap-1.5">
                                <div class="w-3 sm:w-5 bg-emerald-500 rounded-t" style="height: {{ $hIng }}%"
                                    title="Ingresos: Bs {{ number_format($m['ingresos'] ?? 0, 2) }}"></div>
                                <div class="w-3 sm:w-5 bg-rose-500 rounded-t" style="height: {{ $hEgr }}%"
                                    title="Egresos: Bs {{ number_format($m['egresos'] ?? 0, 2) }}"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="grid gap-2 sm:gap-4 pt-1.5 px-1"
                        style="grid-template-columns: repeat({{ $meses->count() }}, minmax(0, 1fr));">
                        @foreach ($meses as $m)
                            <div class="text-center">
                                <div class="text-xs font-semibold text-ink-secondary truncate">{{ $m['label'] }}
                                </div>
                                <div class="text-[11px] tabular-nums text-emerald-700 dark:text-emerald-400 truncate">
                                    {{ number_format($m['ingresos'] ?? 0, 0) }}</div>
                                <div class="text-[11px] tabular-nums text-rose-700 dark:text-rose-400 truncate">
                                    {{ number_format($m['egresos'] ?? 0, 0) }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Distribución --}}
        <div class="rounded-lg border border-outline bg-surface-card overflow-hidden">
            <div class="px-4 py-2.5 border-b border-outline">
                <h2 class="text-sm font-semibold text-ink-primary flex items-center gap-2">
                    <i class="fa-solid fa-music text-gold-600 dark:text-gold-400"></i>Ingresos por servicio
                </h2>
            </div>
            <div class="p-4 space-y-2.5">
                @forelse ($porServicio as $serv)
                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-2 text-sm">
                            <span class="text-ink-primary truncate">{{ $serv->servicio }}</span>
                            <span
                                class="tabular-nums font-semibold text-emerald-700 dark:text-emerald-400 whitespace-nowrap">Bs
                                {{ number_format($serv->total, 2) }}</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-sunken overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full"
                                style="width: {{ min(100, round(($serv->total / $maxServ) * 100)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-ink-secondary">Sin datos de servicios en el período.</p>
                @endforelse
            </div>

            <div class="px-4 py-2.5 border-y border-outline">
                <h2 class="text-sm font-semibold text-ink-primary flex items-center gap-2">
                    <i class="fa-solid fa-tags text-rose-600 dark:text-rose-400"></i>Gastos por categoría
                </h2>
            </div>
            <div class="p-4 space-y-2.5">
                @forelse ($porCategoria as $cat)
                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-2 text-sm">
                            <span class="text-ink-primary truncate">{{ $cat->categoria }}</span>
                            <span
                                class="tabular-nums font-semibold text-rose-700 dark:text-rose-400 whitespace-nowrap">Bs
                                {{ number_format($cat->total, 2) }}</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-sunken overflow-hidden">
                            <div class="h-full bg-rose-500 rounded-full"
                                style="width: {{ min(100, round(($cat->total / $maxCat) * 100)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-ink-secondary">Sin gastos registrados en el período.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>