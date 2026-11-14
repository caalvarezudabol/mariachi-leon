@php
    $bs = fn ($n) => 'Bs ' . number_format((float) $n, 2);
    $primerNombre = explode(' ', trim($persona->nombre_completo))[0];

    $estadoCfg = fn ($e) => match ($e) {
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
            $txt = $m >= 60 ? intdiv($m, 60) . 'h ' . ($m % 60) . 'm' : $m . ' min';
            if ($m <= 30) return ['ya', 'EN ' . $txt, 'bg-red-600 text-white animate-pulse'];
            if ($m <= 240) return ['pronto', 'EN ' . $txt, 'bg-orange-500 text-white'];
            if ($ev->fecha_evento->isToday()) return ['hoy', 'HOY', 'bg-red-600 text-white'];
            if ($ev->fecha_evento->isTomorrow()) return ['manana', 'MAÑANA', 'bg-sky-500 text-white'];
        }
        return null;
    };

    // Aviso grande: el primer show próximo, si es hoy o mañana
    $aviso = null;
    if (!$verPasados && $shows->isNotEmpty()) {
        $ev0 = $shows->first()->evento;
        $t0 = $tiempo($ev0);
        if ($t0) {
            [$clave, $txtT] = $t0;
            [$avBg, $avTitulo, $avSub, $avSuena] = match ($clave) {
                'curso' => ['bg-emerald-600', '¡Tu show está en curso!', 'Ya empezó', true],
                'ya', 'pronto' => ['bg-red-600', '¡Tu show empieza en ' . substr($txtT, 3) . '!', 'Prepárate', true],
                'hoy' => ['bg-red-600', '¡Hoy tienes show!', 'No te olvides', true],
                default => ['bg-sky-600', 'Mañana tienes show', 'Ve preparando todo', false],
            };
            $aviso = [$avBg, $avTitulo, $avSub, $avSuena, $ev0];
        }
    }
@endphp

<div class="space-y-3 pb-20" wire:poll.60s>
    <style>
        @keyframes campana { 0%,100% { transform: rotate(0) } 10% { transform: rotate(20deg) } 20% { transform: rotate(-20deg) } 30% { transform: rotate(16deg) } 40% { transform: rotate(-16deg) } 50% { transform: rotate(9deg) } 60% { transform: rotate(-9deg) } 70% { transform: rotate(0) } }
        .campana { display: inline-block; transform-origin: 50% 8%; animation: campana 1.6s ease-in-out infinite; }
    </style>

    {{-- Saludo --}}
    <div class="px-1">
        <h1 class="text-xl font-bold text-ink-primary">Hola, {{ $primerNombre }} 👋</h1>
        <p class="text-sm text-ink-secondary">{{ ucfirst(now()->translatedFormat('l, d \d\e F')) }}</p>
    </div>

    {{-- Aviso grande del próximo show --}}
    @if ($aviso)
        @php [$avBg, $avTitulo, $avSub, $avSuena, $avEv] = $aviso; @endphp
        <div class="rounded-2xl p-4 text-white shadow-lg {{ $avBg }}">
            <div class="flex items-center gap-3">
                <span class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center shrink-0 text-3xl">
                    <i class="fa-solid fa-bell {{ $avSuena ? 'campana' : '' }}"></i>
                </span>
                <div class="min-w-0 flex-1 leading-tight">
                    <div class="text-lg font-extrabold">{{ $avTitulo }}</div>
                    <div class="text-base font-semibold mt-0.5">{{ substr($avEv->hora_evento, 0, 5) }} · {{ $avEv->cliente->nombre_completo ?? 'Evento' }}</div>
                    <div class="text-sm opacity-90">{{ $avEv->servicio->nombre ?? '' }}</div>
                </div>
            </div>
        </div>
    @endif

    @if ($tab === 'shows')
        {{-- Aviso de pago pendiente --}}
        @if ($saldo > 0.001)
            <button type="button" wire:click="$set('tab', 'pagos')"
                class="w-full flex items-center gap-2.5 rounded-xl border border-amber-500/50 bg-amber-500/10 px-3 py-2 text-left">
                <span class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0"><i class="fa-solid fa-coins text-sm"></i></span>
                <span class="min-w-0 flex-1 leading-tight">
                    <span class="block text-sm font-semibold text-ink-primary">Se te debe {{ $bs($saldo) }}</span>
                    <span class="block text-xs text-ink-secondary">Toca para ver el detalle de tus pagos</span>
                </span>
                <i class="fa-solid fa-chevron-right text-xs text-ink-muted"></i>
            </button>
        @endif

        <div class="flex items-center justify-between gap-2 px-1">
            <h2 class="text-sm font-bold uppercase tracking-wide text-ink-secondary">{{ $verPasados ? 'Shows anteriores' : 'Próximos shows' }}</h2>
            <button type="button" wire:click="$toggle('verPasados')" class="h-8 px-3 rounded-full border border-outline bg-surface-card text-xs font-semibold text-ink-primary">
                {{ $verPasados ? 'Ver próximos' : 'Ver anteriores' }}
            </button>
        </div>

        @forelse ($shows as $ep)
            @php
                $ev = $ep->evento;
                $t = $tiempo($ev);
                [$bgEst, $bordeEst] = $estadoCfg($ev->estado);
                $hoyEs = $ev->fecha_evento->isToday();
                $dir = trim((string) $ev->direccion_evento);
                $amigos = $companeros[$ev->id] ?? [];
            @endphp
            <div class="rounded-xl border-2 bg-surface-card overflow-hidden shadow-sm {{ $t && in_array($t[0], ['curso', 'ya']) ? 'border-red-500' : $bordeEst }}" wire:key="show-{{ $ev->id }}">
                <div class="flex items-stretch">
                    <div class="w-16 shrink-0 flex flex-col items-center justify-center py-3 text-white {{ $bgEst }}">
                        <span class="text-2xl font-extrabold leading-none tabular-nums">{{ $ev->fecha_evento->format('d') }}</span>
                        <span class="text-xs uppercase font-bold mt-0.5">{{ $ev->fecha_evento->translatedFormat('M') }}</span>
                        <span class="text-[10px] uppercase opacity-90">{{ $ev->fecha_evento->translatedFormat('D') }}</span>
                    </div>
                    <div class="min-w-0 flex-1 px-3 py-2.5 space-y-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-lg font-extrabold text-ink-primary tabular-nums leading-none"><i class="fa-regular fa-clock text-sm text-ink-muted mr-1"></i>{{ substr($ev->hora_evento, 0, 5) }}</span>
                            @if ($t)<span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $t[2] }}">{{ $t[1] }}</span>@endif
                            <span class="ml-auto px-2 py-0.5 rounded text-[11px] font-bold text-white {{ $bgEst }}">{{ $ev->estado }}</span>
                        </div>
                        <div class="text-base font-bold text-ink-primary leading-tight">{{ $ev->cliente->nombre_completo ?? 'Evento' }}</div>
                        <div class="text-sm text-ink-secondary leading-tight">{{ $ev->servicio->nombre ?? '' }}@if ($ep->funcion) <span class="text-ink-muted">·</span> {{ $ep->funcion }}@endif</div>
                        @if ($dir !== '')
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($dir) }}" target="_blank" rel="noopener"
                                class="inline-flex items-start gap-1.5 text-sm text-sky-700 dark:text-sky-400 font-medium leading-tight">
                                <i class="fa-solid fa-location-dot mt-0.5"></i><span class="underline decoration-dotted">{{ $dir }}</span>
                            </a>
                        @endif
                        @if ($amigos)
                            <div class="flex flex-wrap items-center gap-1 pt-0.5">
                                <i class="fa-solid fa-people-group text-xs text-ink-muted"></i>
                                @foreach ($amigos as $nm)
                                    <span class="px-1.5 py-px rounded-full bg-surface-sunken border border-outline text-[11px] text-ink-secondary">{{ explode(' ', $nm)[0] }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @if ((float) $ep->monto_asignado > 0)
                    <div class="flex items-center justify-between px-3 py-1.5 bg-surface-sunken border-t border-outline">
                        <span class="text-xs text-ink-secondary">Te toca por este show</span>
                        <span class="text-base font-extrabold tabular-nums text-emerald-700 dark:text-emerald-400">{{ $bs($ep->monto_asignado) }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="py-12 text-center rounded-xl border border-outline bg-surface-card">
                <i class="fa-regular fa-calendar-xmark text-3xl text-ink-muted block mb-2"></i>
                <div class="text-sm text-ink-secondary">{{ $verPasados ? 'No tienes shows anteriores.' : 'No tienes shows próximos asignados.' }}</div>
            </div>
        @endforelse
    @else
        {{-- Resumen de pagos --}}
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-xl border border-outline bg-surface-card px-2.5 py-2 text-center">
                <div class="text-[11px] text-ink-secondary leading-tight">{{ $esMensual ? 'Sueldo mensual' : 'Total · ' . $totalShows . ' shows' }}</div>
                <div class="text-base font-extrabold tabular-nums text-ink-primary mt-0.5">{{ $bs($asignado) }}</div>
            </div>
            <div class="rounded-xl border border-emerald-500/50 bg-emerald-500/10 px-2.5 py-2 text-center">
                <div class="text-[11px] text-ink-secondary leading-tight">Recibido</div>
                <div class="text-base font-extrabold tabular-nums text-emerald-700 dark:text-emerald-400 mt-0.5">{{ $bs($recibido) }}</div>
            </div>
            <div class="rounded-xl border px-2.5 py-2 text-center {{ $saldo > 0.001 ? 'border-amber-500/60 bg-amber-500/10' : ($saldo < -0.001 ? 'border-rose-500/50 bg-rose-500/10' : 'border-outline bg-surface-card') }}">
                <div class="text-[11px] text-ink-secondary leading-tight">{{ $saldo < -0.001 ? 'Anticipo a descontar' : 'Se te debe' }}</div>
                <div class="text-base font-extrabold tabular-nums mt-0.5 {{ $saldo > 0.001 ? 'text-amber-700 dark:text-amber-400' : ($saldo < -0.001 ? 'text-rose-700 dark:text-rose-400' : 'text-ink-primary') }}">{{ $bs(abs($saldo)) }}</div>
            </div>
        </div>

        @if (!$esMensual)
            <div class="rounded-xl border border-outline bg-surface-card overflow-hidden">
                <div class="px-3 py-2 border-b border-outline text-xs font-bold uppercase tracking-wide text-ink-secondary">Mis shows</div>
                @forelse ($showsPago as $ep)
                    @php $ev = $ep->evento; [$bgEst] = $estadoCfg($ev->estado); @endphp
                    <div class="flex items-center gap-2.5 px-3 py-2 border-b border-outline/60 last:border-0" wire:key="sp-{{ $ep->id }}">
                        <span class="w-12 shrink-0 text-center rounded-md px-1 py-1 text-xs font-bold text-white tabular-nums {{ $bgEst }}">{{ $ev->fecha_evento->format('d/m') }}</span>
                        <span class="min-w-0 flex-1 leading-tight">
                            <span class="block text-sm font-semibold text-ink-primary truncate">{{ $ev->cliente->nombre_completo ?? 'Evento' }}</span>
                            <span class="block text-xs text-ink-secondary truncate">{{ $ev->servicio->nombre ?? '' }}</span>
                        </span>
                        <span class="text-sm font-bold tabular-nums text-ink-primary shrink-0">{{ $bs($ep->monto_asignado) }}</span>
                    </div>
                @empty
                    <div class="px-3 py-4 text-sm text-ink-secondary">Aún no tienes shows asignados.</div>
                @endforelse
            </div>
        @endif

        <div class="rounded-xl border border-outline bg-surface-card overflow-hidden">
            <div class="px-3 py-2 border-b border-outline text-xs font-bold uppercase tracking-wide text-ink-secondary">Lo que ya recibiste{{ $esMensual ? ' este mes' : '' }}</div>
            @forelse ($pagos as $pg)
                <div class="flex items-center gap-2.5 px-3 py-2 border-b border-outline/60 last:border-0">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-white text-xs {{ $pg->tipo === 'anticipo' ? 'bg-violet-600' : 'bg-emerald-600' }}"><i class="fa-solid {{ $pg->tipo === 'anticipo' ? 'fa-forward' : 'fa-check' }}"></i></span>
                    <span class="min-w-0 flex-1 leading-tight">
                        <span class="block text-sm font-semibold text-ink-primary">{{ $pg->tipo === 'anticipo' ? 'Anticipo' : 'Pago' }} <span class="font-normal text-ink-secondary">· {{ $pg->metodo_pago }}</span></span>
                        <span class="block text-xs text-ink-secondary truncate">{{ \Carbon\Carbon::parse($pg->fecha_pago)->format('d/m/y H:i') }}@if ($pg->glosa) · {{ $pg->glosa }}@endif</span>
                    </span>
                    <span class="text-sm font-extrabold tabular-nums text-emerald-700 dark:text-emerald-400 shrink-0">{{ $bs($pg->monto) }}</span>
                </div>
            @empty
                <div class="px-3 py-4 text-sm text-ink-secondary">Aún no se registraron pagos.</div>
            @endforelse
        </div>
    @endif

    {{-- Barra inferior de pestañas (como una app) --}}
    <nav class="fixed bottom-0 inset-x-0 z-30 bg-surface-card border-t border-outline">
        <div class="max-w-3xl mx-auto grid grid-cols-2">
            <button type="button" wire:click="$set('tab', 'shows')"
                class="h-14 flex flex-col items-center justify-center gap-0.5 text-xs font-bold transition-colors {{ $tab === 'shows' ? 'text-gold-700 dark:text-gold-400 border-t-2 border-gold-500 -mt-px' : 'text-ink-secondary' }}">
                <i class="fa-solid fa-calendar-days text-lg"></i> Mis shows
            </button>
            <button type="button" wire:click="$set('tab', 'pagos')"
                class="h-14 flex flex-col items-center justify-center gap-0.5 text-xs font-bold transition-colors relative {{ $tab === 'pagos' ? 'text-gold-700 dark:text-gold-400 border-t-2 border-gold-500 -mt-px' : 'text-ink-secondary' }}">
                <i class="fa-solid fa-hand-holding-dollar text-lg"></i> Mis pagos
                @if ($saldo > 0.001)<span class="absolute top-1.5 right-[28%] w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-surface-card"></span>@endif
            </button>
        </div>
    </nav>
</div>