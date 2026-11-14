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

    // Accesos directos (compactos): etiqueta corta + número si corresponde
    $accesos = [
        [
            'label' => 'Eventos',
            'n' => $eventosSemana,
            'tip' => $eventosSemana . ' en los próximos 7 días',
            'icon' => 'fa-calendar-plus',
            'tono' => 'text-gray-900',
            'principal' => true,
            'href' => route('admin.eventos'),
        ],
        [
            'label' => 'Agenda',
            'n' => $eventosMes,
            'tip' => $eventosMes . ' este mes',
            'icon' => 'fa-calendar-days',
            'tono' => 'text-sky-600 dark:text-sky-400',
            'href' => route('admin.agenda'),
        ],
    ];
    if ($puedeFinanzas) {
        $accesos[] = [
            'label' => 'Cobros',
            'n' => $contratosConSaldo,
            'tip' => $contratosConSaldo . ' contratos con saldo',
            'icon' => 'fa-money-bill-wave',
            'tono' => 'text-emerald-600 dark:text-emerald-400',
            'href' => route('admin.pagos'),
        ];
        $accesos[] = [
            'label' => 'Pagos a personal',
            'icon' => 'fa-hand-holding-dollar',
            'tono' => 'text-violet-600 dark:text-violet-400',
            'href' => route('admin.pagos-personal'),
        ];
        $accesos[] = [
            'label' => 'Ingresos / Egresos',
            'icon' => 'fa-money-bill-transfer',
            'tono' => 'text-rose-600 dark:text-rose-400',
            'href' => route('admin.finanzas.movimientos'),
        ];
    }
    $accesos[] = [
        'label' => 'Personal',
        'n' => $personasActivas,
        'tip' => $personasActivas . ' activos',
        'icon' => 'fa-users',
        'tono' => 'text-ink-secondary',
        'href' => route('admin.musicos'),
    ];

    // Indicadores en una sola franja
    $stats = [];
    if ($puedeFinanzas) {
        $stats[] = [
            'label' => 'Efectivo',
            'valor' => 'Bs ' . number_format($caja['Efectivo'] ?? 0, 2),
            'icon' => 'fa-money-bill-wave',
            'href' => route('admin.finanzas.movimientos'),
        ];
        $stats[] = [
            'label' => 'QR',
            'valor' => 'Bs ' . number_format($caja['QR'] ?? 0, 2),
            'icon' => 'fa-qrcode',
            'href' => route('admin.finanzas.movimientos'),
        ];
        $stats[] = [
            'label' => 'Debes al personal',
            'valor' => 'Bs ' . number_format($deudaPersonal, 2),
            'icon' => 'fa-hand-holding-dollar',
            'rojo' => $deudaPersonal > 0,
            'href' => route('admin.pagos-personal'),
        ];
        $stats[] = [
            'label' => 'Cobrado este mes',
            'valor' => $moneda . ' ' . number_format($cobradoMes, 2),
            'href' => route('admin.pagos'),
        ];
        $stats[] = [
            'label' => 'Por cobrar',
            'valor' => $moneda . ' ' . number_format($porCobrar, 2),
            'href' => route('admin.contratos'),
        ];
    }
    $stats[] = ['label' => 'Eventos del mes', 'valor' => $eventosMes, 'href' => route('admin.agenda')];
    $stats[] = [
        'label' => 'Solicitudes web',
        'valor' => $contactosNuevos,
        'alerta' => $contactosNuevos > 0,
        'href' => route('admin.solicitudes-web'),
    ];

    // Estado del evento como etiqueta de color
    $badgeEstado = fn($e) => match ($e) {
        'Confirmado' => 'bg-emerald-600',
        'Realizado', 'Finalizado' => 'bg-sky-600',
        'Cancelado' => 'bg-rose-600',
        default => 'bg-amber-500',
    };

    // Campanita
    $nRojas = collect($alertas)->where('nivel', 'rojo')->count();
    $nAlertas = collect($alertas)
        ->whereIn('nivel', ['rojo', 'ambar'])
        ->count();
@endphp

<div class="space-y-3" wire:poll.60s>

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
            {{-- Campanita de alertas --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false"
                @keydown.escape.window="open = false">
                <button type="button" @click="open = !open" title="Alertas"
                    class="relative h-8 w-9 inline-flex items-center justify-center rounded-lg border transition-colors {{ $nRojas > 0 ? 'border-rose-500 bg-rose-500/10 text-rose-600 dark:text-rose-400' : ($nAlertas > 0 ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'border-outline bg-surface-card text-ink-secondary') }}">
                    <i class="fa-solid fa-bell {{ $nRojas > 0 ? 'animate-pulse' : '' }}"></i>
                    @if ($nAlertas > 0)
                        <span
                            class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-bold text-white flex items-center justify-center {{ $nRojas > 0 ? 'bg-rose-600' : 'bg-amber-500' }}">{{ $nAlertas }}</span>
                    @endif
                </button>
                <div x-show="open" x-cloak x-transition.opacity
                    class="absolute right-0 z-30 mt-1.5 w-[min(92vw,22rem)] max-h-[70vh] overflow-y-auto rounded-lg border border-outline bg-surface-card shadow-xl">
                    <div class="px-3 py-2 border-b border-outline text-sm font-semibold text-ink-primary">Alertas</div>
                    @forelse ($alertas as $al)
                        @php
                            $cAl = match ($al['nivel']) {
                                'rojo' => 'bg-rose-600',
                                'ambar' => 'bg-amber-500',
                                default => 'bg-emerald-600',
                            };
                        @endphp
                        <a href="{{ $al['href'] }}" wire:navigate
                            class="flex items-center gap-2.5 px-3 py-2 border-b border-outline last:border-0 hover:bg-gold-500/5">
                            <span
                                class="w-7 h-7 rounded-md flex items-center justify-center shrink-0 text-white {{ $cAl }}"><i
                                    class="fa-solid {{ $al['icono'] }} text-xs"></i></span>
                            <span class="min-w-0">
                                <span
                                    class="block text-sm font-semibold text-ink-primary truncate">{{ $al['titulo'] }}</span>
                                <span class="block text-xs text-ink-secondary">{{ $al['texto'] }}</span>
                            </span>
                        </a>
                    @empty
                        <div class="px-3 py-6 text-center text-sm text-ink-secondary">
                            <i class="fa-solid fa-circle-check text-xl text-emerald-500 block mb-1"></i>
                            Todo en orden, sin alertas.
                        </div>
                    @endforelse
                </div>
            </div>
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

    {{-- Accesos directos (compactos) --}}
    <div class="flex flex-wrap gap-1.5">
        @foreach ($accesos as $a)
            <a href="{{ $a['href'] }}" wire:navigate title="{{ $a['tip'] ?? $a['label'] }}"
                class="h-9 px-3 flex-1 basis-[130px] inline-flex items-center justify-center gap-2 rounded-lg border text-sm font-semibold whitespace-nowrap transition-colors
                    {{ !empty($a['principal']) ? 'border-gold-500 bg-gold-500 hover:bg-gold-600 text-gray-900' : 'border-outline bg-surface-card hover:border-gold-500/60 text-ink-primary' }}">
                <i class="fa-solid {{ $a['icon'] }} text-sm {{ $a['tono'] }}"></i>
                <span>{{ $a['label'] }}</span>
                @if (isset($a['n']))
                    <span
                        class="px-1.5 min-w-[20px] text-center rounded-full text-[11px] font-bold tabular-nums {{ !empty($a['principal']) ? 'bg-white/40' : 'bg-surface-sunken text-ink-secondary' }}">{{ $a['n'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Indicadores y caja en una franja --}}
    <div
        class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-{{ min(count($stats), 7) }} rounded-lg border border-outline bg-surface-card overflow-hidden divide-x divide-y divide-outline">
        @foreach ($stats as $t)
            <a href="{{ $t['href'] }}" wire:navigate
                class="px-3 py-1.5 -mb-px -mr-px hover:bg-gold-500/5 transition-colors min-w-0">
                <span class="block text-[11px] text-ink-secondary truncate">
                    @if (!empty($t['icon']))
                        <i class="fa-solid {{ $t['icon'] }} mr-1"></i>
                    @endif{{ $t['label'] }}
                </span>
                <span
                    class="block text-base font-bold leading-tight tabular-nums truncate {{ !empty($t['rojo']) ? 'text-rose-700 dark:text-rose-400' : (!empty($t['alerta']) ? 'text-amber-700 dark:text-amber-400' : 'text-ink-primary') }}">{{ $t['valor'] }}</span>
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
                    <div class="flex items-center gap-2.5 px-3 py-1">
                        <div class="w-10 shrink-0 text-center leading-tight">
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
                        <span
                            class="px-2 py-0.5 rounded text-[11px] font-bold text-white shrink-0 {{ $badgeEstado($ev->estado) }}">{{ $ev->estado }}</span>
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
                        <div class="px-3 py-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-semibold text-ink-primary truncate">
                                        {{ $c->cliente->nombre_completo ?? 'N/A' }}</div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-1 flex-1 rounded-full bg-surface-sunken overflow-hidden">
                                            <div class="h-full rounded-full bg-emerald-500"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span
                                            class="text-[11px] text-ink-secondary tabular-nums">{{ $pct }}%</span>
                                    </div>
                                </div>
                                <span
                                    class="text-sm font-semibold tabular-nums text-amber-700 dark:text-amber-400 shrink-0">{{ $moneda }}
                                    {{ number_format($c->saldo_pendiente, 2) }}</span>
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