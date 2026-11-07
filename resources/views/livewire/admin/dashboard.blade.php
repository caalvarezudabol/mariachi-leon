@php
    $hora = (int) now()->format('H');
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $primerNombre = explode(' ', trim(Auth::user()->name))[0];
    $moneda = $empresa->moneda_simbolo ?? 'Bs.';

    $estadoDot = function ($estado) {
        if (in_array($estado, ['Confirmado', 'Realizado', 'Finalizado'])) {
            return 'bg-emerald-500';
        }
        if (in_array($estado, ['Reservado', 'Cotizado'])) {
            return 'bg-amber-500';
        }
        if ($estado === 'Cancelado') {
            return 'bg-rose-500';
        }
        return 'bg-sky-500';
    };

    // Accesos directos a lo más usado
    $accesos = [
        [
            'label' => 'Gestión de eventos',
            'sub' => $eventosSemana . ' en 7 días',
            'icon' => 'fa-calendar-plus',
            'tono' => 'bg-gold-500 text-gray-900',
            'principal' => true,
            'href' => route('admin.eventos'),
        ],
        [
            'label' => 'Agenda',
            'sub' => $eventosMes . ' este mes',
            'icon' => 'fa-calendar-days',
            'tono' => 'bg-sky-500/15 text-sky-700 dark:text-sky-400',
            'href' => route('admin.agenda'),
        ],
    ];
    if ($puedeFinanzas) {
        $accesos[] = [
            'label' => 'Cobros',
            'sub' => $contratosConSaldo . ' con saldo',
            'icon' => 'fa-money-bill-wave',
            'tono' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400',
            'href' => route('admin.pagos'),
        ];
        $accesos[] = [
            'label' => 'Pagos a personal',
            'sub' => 'Músicos y equipo',
            'icon' => 'fa-hand-holding-dollar',
            'tono' => 'bg-violet-500/15 text-violet-700 dark:text-violet-400',
            'href' => route('admin.pagos-personal'),
        ];
        $accesos[] = [
            'label' => 'Ingresos / Egresos',
            'sub' => 'Registrar gasto',
            'icon' => 'fa-arrow-trend-down',
            'tono' => 'bg-rose-500/15 text-rose-700 dark:text-rose-400',
            'href' => route('admin.finanzas.movimientos'),
        ];
    }
    $accesos[] = [
        'label' => 'Músicos y personal',
        'sub' => $personasActivas . ' activos',
        'icon' => 'fa-users',
        'tono' => 'bg-gray-500/15 text-ink-secondary',
        'href' => route('admin.musicos'),
    ];

    // Indicadores (pocos y claros)
    $tarjetas = [];
    if ($puedeFinanzas) {
        $tarjetas[] = [
            'label' => 'Cobrado este mes',
            'valor' => $moneda . ' ' . number_format($cobradoMes, 2),
            'href' => route('admin.pagos'),
        ];
        $tarjetas[] = [
            'label' => 'Por cobrar',
            'valor' => $moneda . ' ' . number_format($porCobrar, 2),
            'href' => route('admin.contratos'),
        ];
    }
    $tarjetas[] = ['label' => 'Eventos del mes', 'valor' => $eventosMes, 'href' => route('admin.agenda')];
    $tarjetas[] = [
        'label' => 'Solicitudes web nuevas',
        'valor' => $contactosNuevos,
        'href' => route('admin.solicitudes-web'),
        'alerta' => $contactosNuevos > 0,
    ];
@endphp

<div class="space-y-3">

    {{-- Saludo --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if ($empresa->logo_url)
                <div
                    class="w-11 h-11 rounded-lg bg-surface-card border border-outline p-1.5 flex items-center justify-center shrink-0">
                    <img src="{{ asset($empresa->logo_url) }}" alt="Logo" class="max-w-full max-h-full object-contain">
                </div>
            @endif
            <div class="min-w-0">
                <h1 class="text-lg font-semibold text-ink-primary truncate">{{ $saludo }}, {{ $primerNombre }}</h1>
                <p class="text-sm text-ink-secondary truncate">
                    {{ $empresa->nombre_comercial }} - {{ ucfirst(now()->translatedFormat('l, d \d\e F \d\e Y')) }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if ($puedeFinanzas)
                <x-ui.button size="sm" variant="secondary" href="{{ route('admin.reporte-ejecutivo.pdf') }}"
                    target="_blank">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span class="hidden sm:inline">Resumen del mes</span>
                </x-ui.button>
            @endif
            <x-ui.button size="sm" variant="secondary" href="{{ route('web.home') }}" target="_blank">
                <i class="fa-solid fa-globe"></i>
                <span class="hidden sm:inline">Ver sitio web</span>
            </x-ui.button>
        </div>
    </div>

    {{-- Accesos directos --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
        @foreach ($accesos as $a)
            <a href="{{ $a['href'] }}" wire:navigate
                class="flex items-center gap-2.5 px-3 py-3 rounded-lg border transition-colors
                    {{ !empty($a['principal']) ? 'border-gold-500 bg-gold-500 hover:bg-gold-600' : 'border-outline bg-surface-card hover:border-gold-500/60' }}">
                <span
                    class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ !empty($a['principal']) ? 'bg-white/35 text-gray-900' : $a['tono'] }}">
                    <i class="fa-solid {{ $a['icon'] }}"></i>
                </span>
                <span class="min-w-0">
                    <span
                        class="block text-sm font-semibold leading-tight {{ !empty($a['principal']) ? 'text-gray-900' : 'text-ink-primary' }}">{{ $a['label'] }}</span>
                    <span
                        class="block text-xs {{ !empty($a['principal']) ? 'text-gray-800' : 'text-ink-secondary' }}">{{ $a['sub'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Caja --}}
    @if ($puedeFinanzas)
        <x-ui.caja-resumen />
    @endif

    {{-- Indicadores --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
        @foreach ($tarjetas as $t)
            <a href="{{ $t['href'] }}" wire:navigate
                class="px-3 py-2.5 rounded-lg border border-outline bg-surface-card hover:border-gold-500/60 transition-colors">
                <span class="block text-xs text-ink-secondary truncate">{{ $t['label'] }}</span>
                <span
                    class="block text-lg font-bold leading-tight tabular-nums truncate {{ !empty($t['alerta']) ? 'text-amber-700 dark:text-amber-400' : 'text-ink-primary' }}">{{ $t['valor'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Próximos eventos y contratos con saldo --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <x-ui.panel>
            <x-slot:header>
                <div class="flex items-center gap-2.5 min-w-0">
                    <i class="fa-solid fa-calendar-check text-lg text-gold-600 dark:text-gold-400"></i>
                    <h2 class="text-base font-semibold text-ink-primary">Próximos eventos</h2>
                </div>
                <x-ui.button size="sm" variant="secondary" href="{{ route('admin.agenda') }}" wire:navigate>
                    <span>Ver agenda</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </x-ui.button>
            </x-slot:header>

            <div class="divide-y divide-outline">
                @forelse ($proximosEventos as $ev)
                    @php
                        $cuando = $ev->fecha_evento->isToday()
                            ? 'Hoy'
                            : ($ev->fecha_evento->isTomorrow()
                                ? 'Mañana'
                                : null);
                    @endphp
                    <div class="flex items-center gap-3 px-4 py-2">
                        <div class="w-11 shrink-0 text-center leading-tight">
                            <div
                                class="text-base font-bold tabular-nums {{ $cuando === 'Hoy' ? 'text-gold-700 dark:text-gold-400' : 'text-ink-primary' }}">
                                {{ $ev->fecha_evento->format('d') }}</div>
                            <div class="text-xs text-ink-secondary">
                                {{ ucfirst($ev->fecha_evento->translatedFormat('M')) }}</div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-ink-primary truncate">
                                {{ $ev->cliente->nombre_completo ?? 'N/A' }}</div>
                            <div class="text-xs text-ink-secondary truncate">
                                {{ substr($ev->hora_evento, 0, 5) }} - {{ $ev->servicio->nombre ?? 'Servicio' }}
                                @if ($cuando)
                                    - <span
                                        class="font-semibold text-gold-700 dark:text-gold-400">{{ $cuando }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs text-ink-secondary shrink-0">
                            <span class="w-2 h-2 rounded-full {{ $estadoDot($ev->estado) }}"></span>
                            <span class="hidden sm:inline">{{ $ev->estado }}</span>
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-ink-secondary">
                        <i class="fa-regular fa-calendar-xmark text-2xl text-ink-muted block mb-2"></i>
                        No hay eventos próximos programados.
                    </div>
                @endforelse
            </div>
        </x-ui.panel>

        @if ($puedeFinanzas)
            <x-ui.panel>
                <x-slot:header>
                    <div class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-file-contract text-lg text-gold-600 dark:text-gold-400"></i>
                        <h2 class="text-base font-semibold text-ink-primary">Contratos con saldo pendiente</h2>
                    </div>
                    <x-ui.button size="sm" variant="secondary" href="{{ route('admin.contratos') }}"
                        wire:navigate>
                        <span>Ver contratos</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </x-ui.button>
                </x-slot:header>

                <div class="divide-y divide-outline">
                    @forelse ($contratosSaldo as $c)
                        @php $pct = (float) $c->monto_total > 0 ? min(100, round(((float) $c->total_pagado / (float) $c->monto_total) * 100)) : 0; @endphp
                        <div class="px-4 py-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-ink-primary truncate">
                                        {{ $c->cliente->nombre_completo ?? 'N/A' }}</div>
                                    <div class="text-xs text-ink-secondary truncate">
                                        <span
                                            class="text-gold-700 dark:text-gold-400 font-semibold">{{ $c->numero_contrato }}</span>
                                        - Pagado {{ $pct }}%
                                    </div>
                                </div>
                                <span
                                    class="text-sm font-semibold tabular-nums text-amber-700 dark:text-amber-400 shrink-0">{{ $moneda }}
                                    {{ number_format($c->saldo_pendiente, 2) }}</span>
                            </div>
                            <div class="mt-1.5 h-1 rounded-full bg-surface-sunken overflow-hidden">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $pct }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-secondary">
                            <i class="fa-solid fa-circle-check text-2xl text-emerald-500 block mb-2"></i>
                            No hay contratos con saldo pendiente.
                        </div>
                    @endforelse
                </div>
            </x-ui.panel>
        @endif
    </div>
</div>