@php
    $bs = fn($n) => 'Bs ' . number_format((float) $n, 2);
    $primerNombre = explode(' ', trim($persona->nombre_completo))[0];

    $estadoCfg = fn($e) => match ($e) {
        'Confirmado' => ['bg-emerald-600', 'border-emerald-500'],
        'Realizado', 'Finalizado' => ['bg-sky-600', 'border-sky-500'],
        default => ['bg-amber-500', 'border-amber-500'],
    };

    // Estado de tiempo de un show: clave, texto, clases del aviso
    $tiempo = function ($ev) {
        if (!in_array($ev->estado, ['Confirmado', 'Reservado', 'Pendiente', 'Cotizado'])) {
            return null;
        }
        $ini = $ev->fecha_evento->copy()->setTimeFromTimeString(substr($ev->hora_evento, 0, 8));
        $fin = $ini->copy()->addMinutes((int) round(((float) $ev->duracion_horas) * 60));
        $ahora = now();
        if ($ahora->gte($ini) && $ahora->lt($fin)) {
            return ['curso', 'EN CURSO', 'bg-lime-400 text-slate-900 animate-pulse'];
        }
        if ($ahora->lt($ini)) {
            $m = (int) $ahora->diffInMinutes($ini);
            $txt = $m >= 60 ? intdiv($m, 60) . 'h ' . $m % 60 . 'm' : $m . ' min';
            if ($m <= 30) {
                return ['ya', 'EN ' . $txt, 'bg-red-600 text-white animate-pulse'];
            }
            if ($m <= 240) {
                return ['pronto', 'EN ' . $txt, 'bg-orange-500 text-white'];
            }
            if ($ev->fecha_evento->isToday()) {
                return ['hoy', 'HOY', 'bg-red-600 text-white'];
            }
            if ($ev->fecha_evento->isTomorrow()) {
                return ['manana', 'MAÑANA', 'bg-sky-500 text-white'];
            }
        }
        return null;
    };

    // Aviso grande: el primer show próximo que esté por empezar, en curso, de hoy o de mañana
    $aviso = null;
    foreach ($proximos as $epP) {
        $evP = $epP->evento;
        $tP = $tiempo($evP);
        if (!$tP) {
            continue;
        }
        [$clave, $txtT] = $tP;
        [$avBg, $avTitulo, $avSuena] = match ($clave) {
            'curso' => ['bg-emerald-600', '¡Tu show está en curso!', true],
            'ya', 'pronto' => ['bg-red-600', '¡Tu show empieza en ' . substr($txtT, 3) . '!', true],
            'hoy' => ['bg-red-600', '¡Hoy tienes show!', true],
            default => ['bg-sky-600', 'Mañana tienes show', false],
        };
        $aviso = [$avBg, $avTitulo, $avSuena, $evP];
        break;
    }
@endphp

<div class="space-y-3 pb-20 lg:pb-6" wire:poll.60s>
    <style>
        @keyframes campana {

            0%,
            100% {
                transform: rotate(0)
            }

            10% {
                transform: rotate(20deg)
            }

            20% {
                transform: rotate(-20deg)
            }

            30% {
                transform: rotate(16deg)
            }

            40% {
                transform: rotate(-16deg)
            }

            50% {
                transform: rotate(9deg)
            }

            60% {
                transform: rotate(-9deg)
            }

            70% {
                transform: rotate(0)
            }
        }

        .campana {
            display: inline-block;
            transform-origin: 50% 8%;
            animation: campana 1.6s ease-in-out infinite;
        }
    </style>

    {{-- Saludo --}}
    <div class="px-1">
        <h1 class="text-xl lg:text-3xl font-extrabold text-ink-primary">Hola, <span
                class="text-gold-600 dark:text-gold-400">{{ $primerNombre }}</span></h1>
        <p class="text-sm text-ink-secondary">{{ ucfirst(now()->translatedFormat('l, d \d\e F')) }}</p>
    </div>

    {{-- Pestañas (escritorio) --}}
    <div class="hidden lg:grid grid-cols-2 gap-1 p-1 rounded-xl bg-surface-card border border-outline max-w-md">
        <button type="button" wire:click="$set('tab', 'shows')"
            class="h-11 rounded-lg text-sm font-bold inline-flex items-center justify-center gap-2 transition-colors {{ $tab === 'shows' ? 'bg-gold-500 text-gray-900 shadow' : 'text-ink-secondary hover:text-ink-primary' }}">
            <i class="fa-solid fa-calendar-days"></i> Mis shows
        </button>
        <button type="button" wire:click="$set('tab', 'pagos')"
            class="h-11 rounded-lg text-sm font-bold inline-flex items-center justify-center gap-2 transition-colors {{ $tab === 'pagos' ? 'bg-gold-500 text-gray-900 shadow' : 'text-ink-secondary hover:text-ink-primary' }}">
            <i class="fa-solid fa-hand-holding-dollar"></i> Mis pagos
            @if ($saldo > 0.001)
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            @endif
        </button>
    </div>

    {{-- Aviso grande del próximo show --}}
    @if ($aviso)
        @php [$avBg, $avTitulo, $avSuena, $avEv] = $aviso; @endphp
        <div class="rounded-2xl p-4 text-white shadow-lg {{ $avBg }}">
            <div class="flex items-center gap-3">
                <span class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center shrink-0 text-3xl">
                    <i class="fa-solid fa-bell {{ $avSuena ? 'campana' : '' }}"></i>
                </span>
                <div class="min-w-0 flex-1 leading-tight">
                    <div class="text-lg font-extrabold">{{ $avTitulo }}</div>
                    <div class="text-base font-semibold mt-0.5">{{ substr($avEv->hora_evento, 0, 5) }} ·
                        {{ $avEv->cliente->nombre_completo ?? 'Evento' }}</div>
                    <div class="text-sm opacity-90">{{ $avEv->servicio->nombre ?? '' }}</div>
                </div>
            </div>
        </div>
    @endif

    @if ($tab === 'shows')
        <div class="space-y-3 lg:space-y-0 lg:grid lg:grid-cols-12 lg:gap-6 lg:items-start">
            <div class="space-y-3 lg:col-span-5 lg:sticky lg:top-24">
                {{-- Aviso de pago pendiente (una línea) --}}
                @if ($saldo > 0.001)
                    <button type="button" wire:click="$set('tab', 'pagos')"
                        class="w-full flex items-center gap-2.5 rounded-lg border border-amber-500/50 bg-amber-500/10 px-3 py-1.5 text-left">
                        <i class="fa-solid fa-coins text-amber-600 dark:text-amber-400"></i>
                        <span class="min-w-0 flex-1 text-sm font-semibold text-ink-primary">Se te debe
                            {{ $bs($saldo) }}</span>
                        <span class="text-xs text-ink-secondary">Ver pagos</span>
                        <i class="fa-solid fa-chevron-right text-xs text-ink-muted"></i>
                    </button>
                @endif

                {{-- Navegación de semana --}}
                <div class="rounded-xl border border-outline bg-surface-card p-2 space-y-2">
                    <div class="flex items-center justify-between">
                        <button type="button" wire:click="semanaAnterior"
                            class="w-9 h-9 rounded-lg bg-surface-sunken border border-outline text-ink-primary"
                            title="Semana anterior"><i class="fa-solid fa-chevron-left"></i></button>
                        <button type="button" wire:click="irHoy"
                            class="text-sm font-bold text-ink-primary text-center leading-tight">
                            {{ $ini->translatedFormat('d M') }} - {{ $fin->translatedFormat('d M') }}
                            <span
                                class="block text-[11px] font-semibold {{ $semana === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-sky-600 dark:text-sky-400' }}">{{ $semana === 0 ? 'Esta semana' : 'Ir a hoy' }}</span>
                        </button>
                        <button type="button" wire:click="semanaSiguiente"
                            class="w-9 h-9 rounded-lg bg-surface-sunken border border-outline text-ink-primary"
                            title="Semana siguiente"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        @for ($d = 0; $d < 7; $d++)
                            @php
                                $f = $ini->copy()->addDays($d);
                                $fk = $f->format('Y-m-d');
                                $n = $conteoDia[$fk] ?? 0;
                                $esHoy = $f->isToday();
                                $sel = $dia === $fk;
                            @endphp
                            <button type="button" wire:click="elegirDia('{{ $fk }}')"
                                wire:key="dia-{{ $fk }}"
                                class="relative rounded-lg py-1.5 flex flex-col items-center leading-tight border transition-colors
                            {{ $sel ? 'bg-slate-800 text-white border-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:border-slate-100' : ($esHoy ? 'border-red-500 text-ink-primary' : 'border-outline bg-surface-sunken text-ink-primary') }}">
                                <span
                                    class="text-[10px] uppercase font-semibold opacity-80">{{ mb_substr($f->translatedFormat('D'), 0, 3) }}</span>
                                <span class="text-base font-extrabold tabular-nums">{{ $f->format('d') }}</span>
                                <span class="h-4 flex items-center">
                                    @if ($n > 0)
                                        <span
                                            class="min-w-[16px] h-4 px-1 rounded-full text-[10px] font-bold flex items-center justify-center {{ $sel ? 'bg-white text-slate-900 dark:bg-slate-800 dark:text-white' : 'bg-red-600 text-white' }}">{{ $n }}</span>
                                    @endif
                                </span>
                            </button>
                        @endfor
                    </div>
                </div>

            </div>
            <div class="space-y-3 lg:col-span-7">
                {{-- Agenda por día --}}
                @forelse ($porDia as $fecha => $items)
                    @php $fd = \Carbon\Carbon::parse($fecha); @endphp
                    <div class="space-y-1" wire:key="grp-{{ $fecha }}">
                        <div class="flex items-center gap-2 px-1 pt-1">
                            <span
                                class="text-xs font-extrabold uppercase tracking-wide {{ $fd->isToday() ? 'text-red-600 dark:text-red-400' : 'text-ink-secondary' }}">
                                {{ $fd->isToday() ? 'Hoy' : ($fd->isTomorrow() ? 'Mañana' : ucfirst($fd->translatedFormat('l'))) }}
                            </span>
                            <span class="text-xs text-ink-muted">{{ $fd->translatedFormat('d \d\e M') }}</span>
                            <span class="flex-1 h-px bg-outline"></span>
                        </div>

                        @foreach ($items as $ep)
                            @php
                                $ev = $ep->evento;
                                $t = $tiempo($ev);
                                [$bgEst, $bordeEst] = $estadoCfg($ev->estado);
                                $dir = trim((string) $ev->direccion_evento);
                                $amigos = $companeros[$ev->id] ?? [];
                            @endphp
                            <div x-data="{ o: {{ $t && in_array($t[0], ['curso', 'ya', 'pronto']) ? 'true' : 'false' }} }" wire:key="show-{{ $ev->id }}"
                                class="rounded-lg border bg-surface-card overflow-hidden {{ $t && in_array($t[0], ['curso', 'ya']) ? 'border-red-500' : 'border-outline' }}">
                                <button type="button" @click="o = !o" class="w-full flex items-stretch text-left">
                                    <span class="w-1.5 shrink-0 {{ $bgEst }}"></span>
                                    <span class="flex-1 min-w-0 px-2.5 py-2 flex items-center gap-2.5">
                                        <span
                                            class="w-12 shrink-0 text-base font-extrabold tabular-nums text-ink-primary">{{ substr($ev->hora_evento, 0, 5) }}</span>
                                        <span class="min-w-0 flex-1 leading-tight">
                                            <span
                                                class="block text-sm font-bold text-ink-primary truncate">{{ $ev->cliente->nombre_completo ?? 'Evento' }}</span>
                                            <span
                                                class="block text-xs text-ink-secondary truncate">{{ $ev->servicio->nombre ?? '' }}
                                                @if ($ep->funcion)
                                                    · {{ $ep->funcion }}
                                                @endif
                                            </span>
                                        </span>
                                        @if ($t)
                                            <span
                                                class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold shrink-0 {{ $t[2] }}">{{ $t[1] }}</span>
                                        @else
                                            <span
                                                class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white shrink-0 {{ $bgEst }}">{{ $ev->estado }}</span>
                                        @endif
                                        <i class="fa-solid fa-chevron-down text-[10px] text-ink-muted transition-transform"
                                            :class="o ? 'rotate-180' : ''"></i>
                                    </span>
                                </button>
                                <div x-show="o" x-cloak
                                    class="border-t border-outline bg-surface-sunken/60 px-3 py-2 space-y-1.5 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2 py-0.5 rounded text-[11px] font-bold text-white {{ $bgEst }}">{{ $ev->estado }}</span>
                                        @if ((float) $ep->monto_asignado > 0)
                                            <span class="ml-auto text-xs text-ink-secondary">Te toca <b
                                                    class="text-sm tabular-nums text-emerald-700 dark:text-emerald-400">{{ $bs($ep->monto_asignado) }}</b></span>
                                        @endif
                                    </div>
                                    @if ($dir !== '')
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($dir) }}"
                                            target="_blank" rel="noopener"
                                            class="flex items-start gap-1.5 text-sky-700 dark:text-sky-400 font-medium leading-tight">
                                            <i class="fa-solid fa-location-dot mt-0.5"></i><span
                                                class="underline decoration-dotted">{{ $dir }}</span>
                                        </a>
                                    @endif
                                    @if ($amigos)
                                        <div class="flex flex-wrap items-center gap-1">
                                            <i class="fa-solid fa-people-group text-xs text-ink-muted"></i>
                                            @foreach ($amigos as $nm)
                                                <span
                                                    class="px-1.5 py-px rounded-full bg-surface-card border border-outline text-[11px] text-ink-secondary">{{ explode(' ', $nm)[0] }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="py-10 text-center rounded-xl border border-outline bg-surface-card">
                        <i class="fa-regular fa-calendar-xmark text-3xl text-ink-muted block mb-2"></i>
                        <div class="text-sm text-ink-secondary">
                            {{ $dia ? 'No hay shows ese día.' : 'No hay shows esta semana.' }}</div>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        {{-- Resumen de pagos --}}
        <div class="space-y-3 lg:space-y-0 lg:grid lg:grid-cols-2 lg:gap-6 lg:items-start">
            <div class="space-y-3">
                <div class="grid grid-cols-3 gap-2">
                    <div class="rounded-xl border border-outline bg-surface-card px-2.5 py-2 text-center">
                        <div class="text-[11px] text-ink-secondary leading-tight">
                            {{ $esMensual ? 'Sueldo mensual' : 'Total · ' . $totalShows . ' shows' }}</div>
                        <div class="text-base font-extrabold tabular-nums text-ink-primary mt-0.5">
                            {{ $bs($asignado) }}</div>
                    </div>
                    <div class="rounded-xl border border-emerald-500/50 bg-emerald-500/10 px-2.5 py-2 text-center">
                        <div class="text-[11px] text-ink-secondary leading-tight">Recibido</div>
                        <div
                            class="text-base font-extrabold tabular-nums text-emerald-700 dark:text-emerald-400 mt-0.5">
                            {{ $bs($recibido) }}</div>
                    </div>
                    <div
                        class="rounded-xl border px-2.5 py-2 text-center {{ $saldo > 0.001 ? 'border-amber-500/60 bg-amber-500/10' : ($saldo < -0.001 ? 'border-rose-500/50 bg-rose-500/10' : 'border-outline bg-surface-card') }}">
                        <div class="text-[11px] text-ink-secondary leading-tight">
                            {{ $saldo < -0.001 ? 'Anticipo a descontar' : 'Se te debe' }}</div>
                        <div
                            class="text-base font-extrabold tabular-nums mt-0.5 {{ $saldo > 0.001 ? 'text-amber-700 dark:text-amber-400' : ($saldo < -0.001 ? 'text-rose-700 dark:text-rose-400' : 'text-ink-primary') }}">
                            {{ $bs(abs($saldo)) }}</div>
                    </div>
                </div>

                @if (!$esMensual)
                    <div class="rounded-xl border border-outline bg-surface-card overflow-hidden">
                        <div
                            class="px-3 py-2 border-b border-outline text-xs font-bold uppercase tracking-wide text-ink-secondary">
                            Mis shows</div>
                        @forelse ($showsPago as $ep)
                            @php
                                $ev = $ep->evento;
                                [$bgEst] = $estadoCfg($ev->estado);
                            @endphp
                            <div class="flex items-center gap-2.5 px-3 py-2 border-b border-outline/60 last:border-0"
                                wire:key="sp-{{ $ep->id }}">
                                <span
                                    class="w-12 shrink-0 text-center rounded-md px-1 py-1 text-xs font-bold text-white tabular-nums {{ $bgEst }}">{{ $ev->fecha_evento->format('d/m') }}</span>
                                <span class="min-w-0 flex-1 leading-tight">
                                    <span
                                        class="block text-sm font-semibold text-ink-primary truncate">{{ $ev->cliente->nombre_completo ?? 'Evento' }}</span>
                                    <span
                                        class="block text-xs text-ink-secondary truncate">{{ $ev->servicio->nombre ?? '' }}</span>
                                </span>
                                <span
                                    class="text-sm font-bold tabular-nums text-ink-primary shrink-0">{{ $bs($ep->monto_asignado) }}</span>
                            </div>
                        @empty
                            <div class="px-3 py-4 text-sm text-ink-secondary">Aún no tienes shows asignados.</div>
                        @endforelse
                    </div>
                @endif

            </div>
            <div class="rounded-xl border border-outline bg-surface-card overflow-hidden">
                <div
                    class="px-3 py-2 border-b border-outline text-xs font-bold uppercase tracking-wide text-ink-secondary">
                    Lo que ya recibiste{{ $esMensual ? ' este mes' : '' }}</div>
                @forelse ($pagos as $pg)
                    <div class="flex items-center gap-2.5 px-3 py-2 border-b border-outline/60 last:border-0">
                        <span
                            class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-white text-xs {{ $pg->tipo === 'anticipo' ? 'bg-violet-600' : 'bg-emerald-600' }}"><i
                                class="fa-solid {{ $pg->tipo === 'anticipo' ? 'fa-forward' : 'fa-check' }}"></i></span>
                        <span class="min-w-0 flex-1 leading-tight">
                            <span
                                class="block text-sm font-semibold text-ink-primary">{{ $pg->tipo === 'anticipo' ? 'Anticipo' : 'Pago' }}
                                <span class="font-normal text-ink-secondary">· {{ $pg->metodo_pago }}</span></span>
                            <span
                                class="block text-xs text-ink-secondary truncate">{{ \Carbon\Carbon::parse($pg->fecha_pago)->format('d/m/y H:i') }}
                                @if ($pg->glosa)
                                    · {{ $pg->glosa }}
                                @endif
                            </span>
                        </span>
                        <span
                            class="text-sm font-extrabold tabular-nums text-emerald-700 dark:text-emerald-400 shrink-0">{{ $bs($pg->monto) }}</span>
                    </div>
                @empty
                    <div class="px-3 py-4 text-sm text-ink-secondary">Aún no se registraron pagos.</div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Barra inferior de pestañas (como una app) --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-surface-card border-t border-outline">
        <div class="max-w-3xl mx-auto grid grid-cols-2">
            <button type="button" wire:click="$set('tab', 'shows')"
                class="h-14 flex flex-col items-center justify-center gap-0.5 text-xs font-bold transition-colors {{ $tab === 'shows' ? 'text-gold-700 dark:text-gold-400 border-t-2 border-gold-500 -mt-px' : 'text-ink-secondary' }}">
                <i class="fa-solid fa-calendar-days text-lg"></i> Mis shows
            </button>
            <button type="button" wire:click="$set('tab', 'pagos')"
                class="h-14 flex flex-col items-center justify-center gap-0.5 text-xs font-bold transition-colors relative {{ $tab === 'pagos' ? 'text-gold-700 dark:text-gold-400 border-t-2 border-gold-500 -mt-px' : 'text-ink-secondary' }}">
                <i class="fa-solid fa-hand-holding-dollar text-lg"></i> Mis pagos
                @if ($saldo > 0.001)
                    <span
                        class="absolute top-1.5 right-[28%] w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-surface-card"></span>
                @endif
            </button>
        </div>
    </nav>
</div>