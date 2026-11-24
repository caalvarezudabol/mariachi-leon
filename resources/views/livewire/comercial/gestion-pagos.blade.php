@php
    $metodosMap = ['EFECTIVO' => 'Efectivo', 'QR' => 'QR banco', 'TRANSFERENCIA' => 'Transferencia'];
    $usuarioActual = auth()->user();
    $puedeGestionarPagos =
        $usuarioActual && ($usuarioActual->can('gestionar-pagos') || $usuarioActual->hasRole('Administrador'));
@endphp

<div class="space-y-3">

    {{-- Avisos --}}
    @if (session()->has('success_pago'))
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-xl text-emerald-600 dark:text-emerald-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-ink-primary">{{ session('success_pago') }}</p>

                </div>
            </div>
            @if ($ultimoRecibo)
                <div class="flex items-center gap-2 shrink-0">
                    <x-ui.button size="sm"
                        href="{{ route('comercial.pagos.recibo', $ultimoRecibo['codigo_verificacion']) }}"
                        target="_blank">
                        <i class="fa-solid fa-print"></i>
                        <span>Ver recibo</span>
                    </x-ui.button>
                    <x-ui.button size="sm" variant="secondary"
                        href="{{ route('comercial.pagos.pdf', $ultimoRecibo['codigo_verificacion']) }}" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>PDF</span>
                    </x-ui.button>
                </div>
            @endif
        </div>
    @endif
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
                <i class="fa-solid fa-receipt text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Cobros</h1>
                <x-ui.badge
                    variant="neutral">{{ $verDetalle ? $estados->sum() : $conteosContratos['todos'] }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nuevo cobro</span>
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
                        placeholder="{{ $verDetalle ? 'Buscar recibo, cliente...' : 'Buscar cliente o evento...' }}"
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($verDetalle)
                        <select wire:model.live="metodo_filtro"
                            class="h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors">
                            <option value="">Efectivo y QR</option>
                            <option value="EFECTIVO">Efectivo</option>
                            <option value="QR">QR</option>
                        </select>
                        <x-ui.filter-chips>
                            <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                                Todos <span class="opacity-70">{{ $estados->sum() }}</span>
                            </x-ui.filter-chip>
                            <x-ui.filter-chip wire:click="$set('estado_filtro', 'Valido')" :active="$estado_filtro === 'Valido'">
                                Válidos <span class="opacity-70">{{ $estados['Valido'] ?? 0 }}</span>
                            </x-ui.filter-chip>
                            <x-ui.filter-chip wire:click="$set('estado_filtro', 'Anulado')" :active="$estado_filtro === 'Anulado'">
                                Anulados <span class="opacity-70">{{ $estados['Anulado'] ?? 0 }}</span>
                            </x-ui.filter-chip>
                        </x-ui.filter-chips>
                    @else
                        <x-ui.filter-chips>
                            <x-ui.filter-chip wire:click="$set('contrato_estado', 'saldo')" :active="$contrato_estado === 'saldo'">
                                Con saldo <span class="opacity-70">{{ $conteosContratos['saldo'] }}</span>
                            </x-ui.filter-chip>
                            <x-ui.filter-chip wire:click="$set('contrato_estado', 'pagados')" :active="$contrato_estado === 'pagados'">
                                Pagados <span class="opacity-70">{{ $conteosContratos['pagados'] }}</span>
                            </x-ui.filter-chip>
                            <x-ui.filter-chip wire:click="$set('contrato_estado', '')" :active="$contrato_estado === ''">
                                Todos <span class="opacity-70">{{ $conteosContratos['todos'] }}</span>
                            </x-ui.filter-chip>
                        </x-ui.filter-chips>
                    @endif
                </div>
            </div>
            @if ($verDetalle && $contrato_filtro)
                @php $ctrFiltro = \App\Models\Contrato::with('cliente')->find($contrato_filtro); @endphp
                <div
                    class="mt-2 flex items-center justify-between gap-2 px-3 py-1.5 rounded-lg bg-amber-500/10 border-l-[3px] border-amber-500 text-xs text-ink-primary">
                    <span>Pagos de <strong>{{ $ctrFiltro->cliente->nombre_completo ?? 'contrato' }}</strong></span>
                    <button type="button" wire:click="quitarFiltroContrato"
                        class="font-semibold text-gold-700 dark:text-gold-400 hover:underline">Ver todos</button>
                </div>
            @endif
        </x-slot:filters>

        @if (!$verDetalle)
            {{-- ===================== POR CONTRATO ===================== --}}
            <div class="hidden md:block">
                <x-ui.table>
                    <x-slot:header>
                        <th class="px-2 py-1 border-r border-outline">Cliente</th>
                        <th class="px-2 py-1 border-r border-outline text-right">Contrato</th>
                        <th class="px-2 py-1 border-r border-outline text-right">A cuenta</th>
                        <th class="px-2 py-1 border-r border-outline text-right">Saldo</th>
                        <th class="px-2 py-1 border-r border-outline whitespace-nowrap">Último cobro</th>
                        <th class="px-2 py-1 w-px whitespace-nowrap text-right">Acciones</th>
                    </x-slot:header>

                    @forelse ($contratosLista as $ct)
                        @php
                            $cancelado = ($ct->evento->estado ?? '') === 'Cancelado';
                            $puedeCobrar = $ct->saldo_pendiente > 0 && !$cancelado;
                            $abierto = in_array($ct->id, $abiertos);
                        @endphp
                        <tr
                            class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $cancelado ? 'opacity-60' : '' }} {{ $abierto ? 'bg-gold-500/5' : '' }}">
                            <td class="px-2 py-0.5 border-r border-outline whitespace-nowrap">
                                <button type="button" wire:click="toggleContrato({{ $ct->id }})"
                                    class="inline-flex items-center gap-1.5 text-left"
                                    title="{{ $ct->numero_contrato }} - {{ $ct->evento->codigo_evento ?? '' }}">
                                    <i
                                        class="fa-solid fa-chevron-right text-[10px] text-ink-muted transition-transform {{ $abierto ? 'rotate-90' : '' }}"></i>
                                    <span
                                        class="font-semibold text-sm text-ink-primary inline-block max-w-[170px] truncate align-bottom">{{ $ct->cliente->nombre_completo ?? 'N/A' }}</span>
                                    <span
                                        class="text-xs text-ink-secondary inline-block max-w-[150px] truncate align-bottom">{{ $ct->servicio->nombre ?? '' }}</span>
                                </button>
                            </td>
                            <td
                                class="px-2 py-0.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-sm font-semibold text-ink-primary">
                                {{ number_format($ct->monto_total, 2) }}</td>
                            <td
                                class="px-2 py-0.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-sm text-emerald-600 dark:text-emerald-400">
                                {{ number_format($ct->total_pagado, 2) }}</td>
                            <td
                                class="px-2 py-0.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-sm font-bold {{ $cancelado ? 'text-ink-muted' : ($ct->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                {{ $cancelado ? '-' : number_format($ct->saldo_pendiente, 2) }}
                            </td>
                            <td
                                class="px-2 py-0.5 border-r border-outline whitespace-nowrap text-xs text-ink-secondary tabular-nums">
                                {{ $ct->ultimo_pago ? \Illuminate\Support\Carbon::parse($ct->ultimo_pago)->format('d/m/y H:i') : 'Sin cobros' }}
                                @if ($ct->pagos_validos > 1)
                                    <span class="text-ink-muted">({{ $ct->pagos_validos }})</span>
                                @endif
                            </td>
                            <td class="px-2 py-0.5 w-px whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-0.5">
                                    @if ($puedeCobrar)
                                        <x-ui.icon-button class="!h-6 !w-6" variant="primary"
                                            wire:click="cobrarContrato({{ $ct->id }})" title="Registrar cobro">
                                            <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                                        </x-ui.icon-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if ($abierto)
                            <tr class="border-b border-outline bg-surface-sunken/60">
                                <td colspan="6" class="px-4 py-2">
                                    @php
                                        $pagosCt = $ct->pagos;
                                    @endphp
                                    @if ($pagosCt->isEmpty())
                                        <p class="text-xs text-ink-muted py-1">Este contrato aún no tiene cobros.</p>
                                    @else
                                        <div class="space-y-1">
                                            @foreach ($pagosCt as $pg)
                                                @php
                                                    $anuladoPg = $pg->estado === 'Anulado';
                                                    $evPg = $ct->evento;
                                                    $concluidoPg =
                                                        $evPg &&
                                                        in_array($evPg->estado, [
                                                            'Realizado',
                                                            'Finalizado',
                                                            'Concluido',
                                                        ]);
                                                @endphp
                                                <div class="flex items-center gap-2 text-xs {{ $anuladoPg ? 'opacity-60' : '' }}"
                                                    wire:key="det-{{ $pg->id }}">
                                                    <span
                                                        class="font-semibold w-24 shrink-0 {{ $anuladoPg ? 'line-through text-rose-700 dark:text-rose-400' : 'text-gold-700 dark:text-gold-400' }}">{{ $pg->numero_recibo }}</span>
                                                    <span
                                                        class="tabular-nums text-ink-secondary w-28 shrink-0">{{ $pg->fecha_pago->format('d/m/y H:i') }}</span>
                                                    <span
                                                        class="px-1.5 py-px rounded text-[10px] font-bold text-white shrink-0 {{ $pg->metodo_pago === 'QR' ? 'bg-violet-600' : 'bg-slate-600' }}">{{ $pg->metodo_pago === 'QR' ? 'QR' : 'Efectivo' }}</span>
                                                    <span
                                                        class="tabular-nums font-semibold w-24 text-right shrink-0 {{ $anuladoPg ? 'line-through text-ink-muted' : 'text-emerald-600 dark:text-emerald-400' }}">Bs
                                                        {{ number_format($pg->monto, 2) }}</span>
                                                    @if ($anuladoPg)
                                                        <span
                                                            class="px-1.5 py-px rounded text-[10px] font-bold bg-rose-600 text-white"
                                                            title="Motivo: {{ $pg->motivo_anulacion ?: 'Sin motivo' }}">Anulado</span>
                                                    @endif
                                                    <span class="ml-auto inline-flex items-center gap-0.5 shrink-0">
                                                        <x-ui.icon-button class="!h-6 !w-6" variant="primary"
                                                            href="{{ route('comercial.pagos.recibo', $pg->codigo_verificacion) }}"
                                                            target="_blank" title="Ver e imprimir recibo">
                                                            <i class="fa-solid fa-print text-xs"></i>
                                                        </x-ui.icon-button>
                                                        <x-ui.icon-button class="!h-6 !w-6"
                                                            href="{{ route('comercial.pagos.pdf', $pg->codigo_verificacion) }}"
                                                            target="_blank" title="Recibo en PDF">
                                                            <i class="fa-solid fa-file-pdf text-xs text-rose-600"></i>
                                                        </x-ui.icon-button>
                                                        @if (!$anuladoPg && $puedeGestionarPagos && !$concluidoPg)
                                                            <x-ui.icon-button class="!h-6 !w-6" variant="danger"
                                                                wire:click="abrirModalAnular({{ $pg->id }})"
                                                                title="Anular pago">
                                                                <i class="fa-solid fa-ban text-xs"></i>
                                                            </x-ui.icon-button>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-sm text-ink-secondary">No hay contratos en
                                este filtro.</td>
                        </tr>
                    @endforelse

                    @if ($contratosLista->total() > 0)
                        <tr class="bg-surface-sunken border-t-2 border-outline font-bold">
                            <td class="px-2 py-1.5 text-xs text-ink-secondary uppercase tracking-wide">Total
                                ({{ $contratosLista->total() }} contratos)</td>
                            <td class="px-2 py-1.5 text-right tabular-nums text-sm text-ink-primary whitespace-nowrap">
                                {{ number_format($totalesContratos['contratado'], 2) }}</td>
                            <td
                                class="px-2 py-1.5 text-right tabular-nums text-sm text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                {{ number_format($totalesContratos['abonado'], 2) }}</td>
                            <td
                                class="px-2 py-1.5 text-right tabular-nums text-sm text-rose-600 dark:text-rose-400 whitespace-nowrap">
                                {{ number_format($totalesContratos['saldo'], 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    @endif
                </x-ui.table>
            </div>

            <div class="md:hidden divide-y divide-outline">
                @forelse ($contratosLista as $ct)
                    @php
                        $cancelado = ($ct->evento->estado ?? '') === 'Cancelado';
                        $puedeCobrar = $ct->saldo_pendiente > 0 && !$cancelado;
                        $abierto = in_array($ct->id, $abiertos);
                    @endphp
                    <div class="flex items-center gap-2 px-3 py-2 {{ $cancelado ? 'opacity-60' : '' }}">
                        <div class="min-w-0 flex-1" wire:click="toggleContrato({{ $ct->id }})">
                            <div class="font-semibold text-sm text-ink-primary truncate"><i
                                    class="fa-solid fa-chevron-right text-[10px] text-ink-muted mr-1.5 transition-transform {{ $abierto ? 'rotate-90' : '' }}"></i>{{ $ct->cliente->nombre_completo ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-ink-muted truncate">{{ $ct->servicio->nombre ?? '' }}</div>
                            <div class="text-xs tabular-nums">
                                <span class="text-ink-secondary">Bs {{ number_format($ct->monto_total, 0) }}</span>
                                <span class="text-emerald-600 dark:text-emerald-400">- a cuenta
                                    {{ number_format($ct->total_pagado, 0) }}</span>
                                <span
                                    class="font-semibold {{ $cancelado ? 'text-ink-muted' : ($ct->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400') }}">-
                                    saldo {{ $cancelado ? '-' : number_format($ct->saldo_pendiente, 0) }}</span>
                            </div>
                        </div>
                        @if ($puedeCobrar)
                            <x-ui.icon-button variant="primary" wire:click="cobrarContrato({{ $ct->id }})"
                                title="Registrar cobro">
                                <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                            </x-ui.icon-button>
                        @endif
                    </div>
                    @if ($abierto)
                        <div class="px-3 py-2 bg-surface-sunken/60">
                            @php
                                $pagosCt = $ct->pagos;
                            @endphp
                            @if ($pagosCt->isEmpty())
                                <p class="text-xs text-ink-muted py-1">Este contrato aún no tiene cobros.</p>
                            @else
                                <div class="space-y-1">
                                    @foreach ($pagosCt as $pg)
                                        @php
                                            $anuladoPg = $pg->estado === 'Anulado';
                                            $evPg = $ct->evento;
                                            $concluidoPg =
                                                $evPg &&
                                                in_array($evPg->estado, ['Realizado', 'Finalizado', 'Concluido']);
                                        @endphp
                                        <div class="flex items-center gap-2 text-xs {{ $anuladoPg ? 'opacity-60' : '' }}"
                                            wire:key="det-{{ $pg->id }}">
                                            <span
                                                class="font-semibold w-24 shrink-0 {{ $anuladoPg ? 'line-through text-rose-700 dark:text-rose-400' : 'text-gold-700 dark:text-gold-400' }}">{{ $pg->numero_recibo }}</span>
                                            <span
                                                class="tabular-nums text-ink-secondary w-28 shrink-0">{{ $pg->fecha_pago->format('d/m/y H:i') }}</span>
                                            <span
                                                class="px-1.5 py-px rounded text-[10px] font-bold text-white shrink-0 {{ $pg->metodo_pago === 'QR' ? 'bg-violet-600' : 'bg-slate-600' }}">{{ $pg->metodo_pago === 'QR' ? 'QR' : 'Efectivo' }}</span>
                                            <span
                                                class="tabular-nums font-semibold w-24 text-right shrink-0 {{ $anuladoPg ? 'line-through text-ink-muted' : 'text-emerald-600 dark:text-emerald-400' }}">Bs
                                                {{ number_format($pg->monto, 2) }}</span>
                                            @if ($anuladoPg)
                                                <span
                                                    class="px-1.5 py-px rounded text-[10px] font-bold bg-rose-600 text-white"
                                                    title="Motivo: {{ $pg->motivo_anulacion ?: 'Sin motivo' }}">Anulado</span>
                                            @endif
                                            <span class="ml-auto inline-flex items-center gap-0.5 shrink-0">
                                                <x-ui.icon-button class="!h-6 !w-6" variant="primary"
                                                    href="{{ route('comercial.pagos.recibo', $pg->codigo_verificacion) }}"
                                                    target="_blank" title="Ver e imprimir recibo">
                                                    <i class="fa-solid fa-print text-xs"></i>
                                                </x-ui.icon-button>
                                                <x-ui.icon-button class="!h-6 !w-6"
                                                    href="{{ route('comercial.pagos.pdf', $pg->codigo_verificacion) }}"
                                                    target="_blank" title="Recibo en PDF">
                                                    <i class="fa-solid fa-file-pdf text-xs text-rose-600"></i>
                                                </x-ui.icon-button>
                                                @if (!$anuladoPg && $puedeGestionarPagos && !$concluidoPg)
                                                    <x-ui.icon-button class="!h-6 !w-6" variant="danger"
                                                        wire:click="abrirModalAnular({{ $pg->id }})"
                                                        title="Anular pago">
                                                        <i class="fa-solid fa-ban text-xs"></i>
                                                    </x-ui.icon-button>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @empty
                    <div class="text-center py-8 text-sm text-ink-secondary">No hay contratos en este filtro.</div>
                @endforelse
                @if ($contratosLista->total() > 0)
                    <div class="px-3 py-2 bg-surface-sunken text-xs font-bold tabular-nums">
                        <span class="text-ink-secondary uppercase">Total {{ $contratosLista->total() }}:</span>
                        <span class="text-ink-primary">Bs
                            {{ number_format($totalesContratos['contratado'], 0) }}</span>
                        <span class="text-emerald-600 dark:text-emerald-400">- a cuenta
                            {{ number_format($totalesContratos['abonado'], 0) }}</span>
                        <span class="text-rose-600 dark:text-rose-400">- saldo
                            {{ number_format($totalesContratos['saldo'], 0) }}</span>
                    </div>
                @endif
            </div>
        @else
            {{-- ===================== DETALLE DE CADA PAGO ===================== --}}
            <div class="hidden md:block">
                <x-ui.table>
                    <x-slot:header>
                        <th class="px-2 py-1 border-r border-outline">Recibo</th>
                        <th class="px-2 py-1 border-r border-outline">Cliente</th>
                        <th class="px-2 py-1 border-r border-outline whitespace-nowrap">Fecha</th>
                        <th class="px-2 py-1 border-r border-outline text-center">Método</th>
                        <th class="px-2 py-1 border-r border-outline text-right">Monto</th>
                        <th class="px-2 py-1 border-r border-outline text-center">Estado</th>
                        <th class="px-2 py-1 w-px whitespace-nowrap text-right">Acciones</th>
                    </x-slot:header>

                    @forelse ($pagos as $p)
                        @php
                            $anulado = $p->estado === 'Anulado';
                            $eventoPago = $p->evento ?: ($p->contrato ? $p->contrato->evento : null);
                            $eventoConcluido =
                                $eventoPago && in_array($eventoPago->estado, ['Realizado', 'Finalizado', 'Concluido']);
                            $puedeAnular = $puedeGestionarPagos && !$eventoConcluido;
                        @endphp
                        <tr
                            class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $anulado ? 'opacity-70' : '' }}">
                            <td class="px-2 py-0.5 border-r border-outline whitespace-nowrap">
                                <span
                                    class="font-semibold text-sm {{ $anulado ? 'line-through text-rose-700 dark:text-rose-400' : 'text-gold-700 dark:text-gold-400' }}"
                                    @if ($p->comprobante_referencia) title="Ref: {{ $p->comprobante_referencia }}" @endif>{{ $p->numero_recibo }}</span>
                            </td>
                            <td class="px-2 py-0.5 border-r border-outline whitespace-nowrap">
                                <span
                                    class="font-semibold text-sm text-ink-primary inline-block max-w-[190px] truncate align-bottom"
                                    title="{{ $p->contrato->numero_contrato ?? '' }} - {{ $eventoPago->codigo_evento ?? '' }}">{{ $p->cliente->nombre_completo ?? 'N/A' }}</span>
                            </td>
                            <td
                                class="px-2 py-0.5 border-r border-outline whitespace-nowrap tabular-nums text-xs text-ink-secondary">
                                {{ $p->fecha_pago->format('d/m/y H:i') }}</td>
                            <td class="px-2 py-0.5 border-r border-outline text-center whitespace-nowrap">
                                <span
                                    class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold {{ $p->metodo_pago === 'QR' ? 'bg-violet-600 text-white' : 'bg-slate-600 text-white' }}">{{ $p->metodo_pago === 'QR' ? 'QR' : 'Efectivo' }}</span>
                            </td>
                            <td
                                class="px-2 py-0.5 border-r border-outline text-right whitespace-nowrap tabular-nums text-sm font-semibold {{ $anulado ? 'line-through text-ink-muted' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ number_format($p->monto, 2) }}</td>
                            <td class="px-2 py-0.5 border-r border-outline text-center whitespace-nowrap">
                                @if ($anulado)
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-600 text-white cursor-help"
                                        title="Motivo: {{ $p->motivo_anulacion ?: 'Sin motivo' }} - Anulado por: {{ $p->anuladoPor ? $p->anuladoPor->name : 'Admin' }}">Anulado</span>
                                @else
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-600 text-white">Válido</span>
                                @endif
                            </td>
                            <td class="px-2 py-0.5 w-px whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-0.5">
                                    <x-ui.icon-button class="!h-6 !w-6" variant="primary"
                                        href="{{ route('comercial.pagos.recibo', $p->codigo_verificacion) }}"
                                        target="_blank" title="Ver e imprimir recibo">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </x-ui.icon-button>
                                    <x-ui.icon-button class="!h-6 !w-6"
                                        href="{{ route('comercial.pagos.pdf', $p->codigo_verificacion) }}"
                                        target="_blank" title="Recibo en PDF">
                                        <i class="fa-solid fa-file-pdf text-xs text-rose-600"></i>
                                    </x-ui.icon-button>
                                    @if (!$anulado)
                                        @if ($puedeAnular)
                                            <x-ui.icon-button class="!h-6 !w-6" variant="danger"
                                                wire:click="abrirModalAnular({{ $p->id }})"
                                                title="Anular pago">
                                                <i class="fa-solid fa-ban text-xs"></i>
                                            </x-ui.icon-button>
                                        @elseif ($eventoConcluido)
                                            <span
                                                class="inline-flex items-center justify-center w-6 h-6 text-ink-muted cursor-not-allowed"
                                                title="No se puede anular: el evento ya concluyó">
                                                <i class="fa-solid fa-lock text-xs"></i>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-sm text-ink-secondary">No se encontraron
                                cobros.</td>
                        </tr>
                    @endforelse

                    @if ($pagos->total() > 0)
                        <tr class="bg-surface-sunken border-t-2 border-outline font-bold">
                            <td colspan="4" class="px-2 py-1.5 text-xs text-ink-secondary uppercase tracking-wide">
                                Total cobrado, solo válidos ({{ $pagos->total() }})</td>
                            <td
                                class="px-2 py-1.5 text-right tabular-nums text-sm text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                {{ number_format($totalPagosValidos, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    @endif
                </x-ui.table>
            </div>

            <div class="md:hidden divide-y divide-outline">
                @forelse ($pagos as $p)
                    @php
                        $anulado = $p->estado === 'Anulado';
                        $eventoPago = $p->evento ?: ($p->contrato ? $p->contrato->evento : null);
                        $eventoConcluido =
                            $eventoPago && in_array($eventoPago->estado, ['Realizado', 'Finalizado', 'Concluido']);
                        $puedeAnular = $puedeGestionarPagos && !$eventoConcluido;
                    @endphp
                    <div class="flex items-center gap-2 px-3 py-2 {{ $anulado ? 'opacity-70' : '' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="font-semibold text-sm text-ink-primary truncate">{{ $p->cliente->nombre_completo ?? 'N/A' }}</span>
                                <span
                                    class="shrink-0 px-1.5 py-px rounded text-[10px] font-bold text-white {{ $anulado ? 'bg-rose-600' : 'bg-emerald-600' }}">{{ $anulado ? 'Anulado' : 'Válido' }}</span>
                            </div>
                            <div class="text-xs text-ink-muted truncate">{{ $p->numero_recibo }} -
                                {{ $p->metodo_pago === 'QR' ? 'QR' : 'Efectivo' }} -
                                {{ $p->fecha_pago->format('d/m/y H:i') }}</div>
                            <div
                                class="text-sm tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Bs {{ number_format($p->monto, 2) }}</div>
                        </div>
                        <x-ui.action-menu>
                            <x-ui.action-menu-item icon="fa-solid fa-print"
                                href="{{ route('comercial.pagos.recibo', $p->codigo_verificacion) }}"
                                target="_blank">Ver e imprimir recibo</x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-file-pdf"
                                href="{{ route('comercial.pagos.pdf', $p->codigo_verificacion) }}"
                                target="_blank">Recibo en PDF</x-ui.action-menu-item>
                            @if (!$anulado && $puedeAnular)
                                <x-ui.action-menu-item variant="danger" icon="fa-solid fa-ban"
                                    wire:click="abrirModalAnular({{ $p->id }})">Anular
                                    pago</x-ui.action-menu-item>
                            @endif
                        </x-ui.action-menu>
                    </div>
                @empty
                    <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron cobros.</div>
                @endforelse
                @if ($pagos->total() > 0)
                    <div class="px-3 py-2 bg-surface-sunken text-xs font-bold tabular-nums">
                        <span class="text-ink-secondary uppercase">Total válidos:</span>
                        <span class="text-emerald-600 dark:text-emerald-400">Bs
                            {{ number_format($totalPagosValidos, 2) }}</span>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <label class="inline-flex items-center gap-2 text-sm text-ink-primary cursor-pointer select-none">
                <input type="checkbox" wire:model.live="verDetalle"
                    class="w-4 h-4 rounded border-outline accent-amber-500">
                <span>Ver detalle de cada pago</span>
            </label>
            @php $lista = $verDetalle ? $pagos : $contratosLista; @endphp
            <span class="text-xs text-ink-secondary">{{ $lista->firstItem() ?? 0 }}–{{ $lista->lastItem() ?? 0 }}
                de {{ $lista->total() }}</span>
            <div>{{ $lista->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Nuevo Pago --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardar" wire:confirm="¿Está seguro de realizar el pago?">
            <x-ui.modal icon="fa-solid fa-receipt" title="Registrar Pago de Cliente" maxWidth="max-w-xl"
                onClose="$set('modalOpen', false)">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 px-5 py-4">
                    <x-ui.input label="N° Recibo" required readonly type="text" name="numero_recibo"
                        wire:model="numero_recibo" class="!bg-surface-hover font-semibold cursor-default" />

                    <x-ui.input label="Fecha y hora del pago" required type="datetime-local" name="fecha_pago"
                        wire:model="fecha_pago" />

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-ink-primary mb-1">
                            Contrato:
                            <span class="text-rose-500 font-black text-base leading-none">*</span>
                        </label>
                        <x-ui.select-search name="contrato_id" :options="$contratosOptions"
                            placeholder="Buscar contrato con saldo pendiente..." />
                        @error('contrato_id')
                            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <x-ui.input label="Monto (Bs)" required type="number" step="0.50" inputmode="decimal"
                        name="monto" wire:model="monto"
                        class="font-semibold text-emerald-700 dark:text-emerald-400" />

                    <div class="min-w-0">
                        <label class="block text-sm font-semibold text-ink-primary mb-1">Método: <span
                                class="text-rose-500 font-black text-base leading-none">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['EFECTIVO' => ['Efectivo', 'fa-money-bill-wave'], 'QR' => ['QR', 'fa-qrcode']] as $valor => [$texto, $ico])
                                <button type="button" wire:click="$set('metodo_pago', '{{ $valor }}')"
                                    class="h-9 rounded-lg border text-sm font-semibold flex items-center justify-center gap-1.5 transition-colors
                                        {{ $metodo_pago === $valor ? 'border-gold-500 bg-gold-500/10 text-ink-primary' : 'border-outline bg-surface-card text-ink-secondary hover:border-gold-500/50' }}">
                                    <i class="fa-solid {{ $ico }} text-xs"></i> {{ $texto }}
                                </button>
                            @endforeach
                        </div>
                        @error('metodo_pago')
                            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror

                        @if ($metodo_pago === 'QR')
                            <div class="mt-2">
                                <button type="button" wire:click="iniciarCobroQrAdmin" wire:loading.attr="disabled"
                                    class="w-full py-2 px-3 rounded-lg bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow transition-all">
                                    <i class="fa-solid fa-qrcode"></i>
                                    <span>Generar QR Banco Económico (4 min)</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.input label="Comprobante" type="text" name="comprobante_referencia"
                            wire:model="comprobante_referencia" placeholder="N° de transacción (opcional)" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.textarea label="Observaciones" name="observaciones" rows="1"
                            wire:model="observaciones" />
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

    {{-- Modal Anular Pago --}}
    @if ($modalAnularOpen && $pagoParaAnular)
        <form wire:submit.prevent="confirmarAnulacion">
            <x-ui.modal maxWidth="max-w-lg" onClose="$set('modalAnularOpen', false)">
                <x-slot:header>
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-9 h-9 rounded-lg bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-700 dark:text-rose-400 shrink-0">
                            <i class="fa-solid fa-ban"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-ink-primary leading-tight">Anular cobro / recibo</h3>
                            <p class="text-xs text-ink-secondary">Recibo N° {{ $pagoParaAnular->numero_recibo }}</p>
                        </div>
                    </div>
                </x-slot:header>

                <div class="px-6 py-5 space-y-4">
                    <div
                        class="flex items-start gap-2.5 p-3 rounded-lg border-l-[3px] border-rose-500 bg-rose-500/10 text-sm text-ink-primary">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 mt-0.5"></i>
                        <div class="space-y-1">
                            <p class="font-bold">Acción irreversible</p>
                            <p>El monto de <strong>Bs {{ number_format($pagoParaAnular->monto, 2) }}</strong> se
                                descontará del total pagado y volverá al saldo pendiente del contrato
                                <strong>{{ $pagoParaAnular->contrato->numero_contrato ?? 'N/A' }}</strong>.</p>
                            <p class="text-xs text-ink-secondary">Se registrará la fecha, la hora y el usuario que
                                autoriza en la bitácora de auditoría.</p>
                        </div>
                    </div>

                    <div class="bg-surface-sunken border border-outline rounded-lg p-3 text-sm space-y-1.5">
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Cliente:</span>
                            <span
                                class="font-semibold text-ink-primary text-right">{{ $pagoParaAnular->cliente->nombre_completo ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Monto del pago:</span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">Bs
                                {{ number_format($pagoParaAnular->monto, 2) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Fecha original:</span>
                            <span
                                class="text-ink-primary tabular-nums">{{ $pagoParaAnular->fecha_pago->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <x-ui.textarea label="Motivo de la anulación" required name="motivoAnulacion"
                        wire:model="motivoAnulacion" rows="1" class="!min-h-[72px]"
                        placeholder="Ej: error de digitación, pago rebotado, etc." />
                </div>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="$set('modalAnularOpen', false)">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </x-ui.button>
                    <x-ui.button type="submit" variant="danger" wire:loading.attr="disabled"
                        wire:target="confirmarAnulacion">
                        <i class="fa-solid fa-ban" wire:loading.remove wire:target="confirmarAnulacion"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="confirmarAnulacion"></i>
                        <span>Confirmar anulación</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </form>
    @endif

    {{-- Modal Cobro QR Banco Económico (4 Minutos con detección en vivo) --}}
    @if ($modalQrAdminOpen)
        <div x-data="{
                open: @entangle('modalQrAdminOpen'),
                segundos: @entangle('qrAdminSegundosRestantes'),
                timer: null,
                iniciarTimer() {
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (this.segundos > 0) {
                            this.segundos--;
                        } else {
                            clearInterval(this.timer);
                            $wire.tiempoExpiradoAdmin();
                        }
                    }, 1000);
                },
                format(s) {
                    const m = Math.floor(Math.max(0, s) / 60);
                    const sec = Math.max(0, s) % 60;
                    return `${m.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
                }
            }"
            x-init="iniciarTimer()"
            class="fixed inset-0 z-50 overflow-y-auto">
            
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
                wire:click="cancelarQrAdmin"></div>

            <div class="flex min-h-full items-center justify-center p-4"
                @if ($modalQrAdminOpen && $qrAdminId && !$qrAdminExpirado) wire:poll.3s="verificarEstadoQrAdmin" @endif>
                
                <div class="relative w-full max-w-md transform rounded-2xl bg-surface-card border border-outline p-6 shadow-2xl transition-all space-y-4">
                    
                    @if ($qrAdminExpirado)
                        <!-- QR EXPIRADO -->
                        <div class="text-center space-y-3 py-4">
                            <div class="w-14 h-14 mx-auto rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-500 text-2xl">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <h3 class="font-bold text-lg text-ink-primary">Tiempo de cobro agotado</h3>
                            <p class="text-xs text-ink-secondary">
                                El límite de <strong>4 minutos</strong> ha expirado. Puedes regenerar el QR para reiniciar el tiempo.
                            </p>
                            <div class="flex items-center gap-2 pt-2">
                                <x-ui.button type="button" variant="secondary" class="flex-1" wire:click="cancelarQrAdmin">
                                    Cerrar
                                </x-ui.button>
                                <x-ui.button type="button" class="flex-1" wire:click="iniciarCobroQrAdmin">
                                    <i class="fa-solid fa-rotate-right"></i>
                                    <span>Reintentar (4 min)</span>
                                </x-ui.button>
                            </div>
                        </div>

                    @elseif ($qrAdminGenerando)
                        <!-- GENERANDO QR -->
                        <div class="text-center py-10 space-y-3">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-gold-500"></i>
                            <h4 class="font-bold text-sm text-ink-primary">Generando QR con Banco Económico...</h4>
                            <p class="text-xs text-ink-muted">Por favor espere un momento...</p>
                        </div>

                    @elseif ($qrAdminImageBase64)
                        <!-- VISOR DEL QR -->
                        <div class="text-center space-y-3">
                            <div class="flex items-center justify-between border-b border-outline pb-3">
                                <div class="text-left">
                                    <span class="text-[10px] font-bold text-ink-muted uppercase tracking-wider block">Cobro por QR</span>
                                    <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">Bs {{ number_format($monto, 2) }}</span>
                                </div>
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border"
                                    :class="segundos <= 60 ? 'bg-rose-500/10 border-rose-500/40 text-rose-500 animate-pulse' : 'bg-gold-500/10 border-gold-500/30 text-gold-500'">
                                    <i class="fa-regular fa-clock text-xs"></i>
                                    <span class="font-mono font-bold text-sm tabular-nums" x-text="format(segundos)">04:00</span>
                                </div>
                            </div>

                            <div class="p-2 bg-white rounded-xl inline-block shadow border border-slate-200">
                                <img src="data:image/png;base64,{{ $qrAdminImageBase64 }}" alt="QR Cobro" class="w-52 h-52 object-contain mx-auto">
                            </div>

                            <div class="flex items-center justify-center gap-2 text-xs font-semibold text-gold-600 dark:text-gold-400">
                                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                <span>Esperando que el cliente transfiera desde su banco...</span>
                            </div>

                            <div class="text-left text-[11px] bg-surface-sunken p-2.5 rounded-lg border border-outline space-y-1 text-ink-secondary">
                                <div class="font-bold text-ink-primary">Instrucciones para el cliente:</div>
                                <ol class="list-decimal list-inside space-y-0.5">
                                    <li>Escanear desde cualquier app bancaria (<strong>BCP, BNB, Banco Unión, Banco Económico, Mercantil Santa Cruz, FIE, BISA, Ganadero</strong>, etc.).</li>
                                    <li>Confirmar el importe de <strong>Bs {{ number_format($monto, 2) }}</strong>.</li>
                                    <li>Al completarse, el cobro se guardará y registrará automáticamente.</li>
                                </ol>
                            </div>

                            <div class="pt-2 space-y-2">
                                <x-ui.button type="button" variant="secondary" class="w-full" wire:click="cancelarQrAdmin">
                                    <i class="fa-solid fa-xmark"></i>
                                    <span>Cancelar cobro QR</span>
                                </x-ui.button>

                                @if (config('app.debug'))
                                    <button type="button" wire:click="simularPagoAdminPrueba"
                                        class="w-full py-1.5 px-2 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold text-[11px] border border-amber-500/30 transition-colors flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-flask"></i>
                                        <span>Simular Pago Aprobado (Ambiente de Pruebas Sandbox)</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>