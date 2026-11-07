@php
    // Colores por estado del evento (barra lateral, fondo suave y punto)
    $estadoCfg = function ($estado) {
        if (in_array($estado, ['Confirmado', 'Realizado', 'Finalizado'])) {
            return ['bar' => 'border-emerald-500', 'bg' => 'bg-emerald-500/10', 'dot' => 'bg-emerald-500'];
        }
        if (in_array($estado, ['Reservado', 'Cotizado'])) {
            return ['bar' => 'border-amber-500', 'bg' => 'bg-amber-500/10', 'dot' => 'bg-amber-500'];
        }
        if ($estado === 'Cancelado') {
            return ['bar' => 'border-rose-500', 'bg' => 'bg-rose-500/10', 'dot' => 'bg-rose-500'];
        }
        return ['bar' => 'border-sky-500', 'bg' => 'bg-sky-500/10', 'dot' => 'bg-sky-500'];
    };

    // Enlace de WhatsApp del cliente de un evento (null si no hay teléfono)
    $waEvento = function ($ev) {
        $tel = preg_replace(
            '/[^0-9]/',
            '',
            $ev->telefono_contacto ?: ($ev->cliente?->telefono ?: ($ev->cliente?->whatsapp ?: '')),
        );
        if (!$tel) {
            return null;
        }
        if (strlen($tel) === 8) {
            $tel = '591' . $tel;
        }
        $msg = urlencode(
            'Hola ' .
                ($ev->cliente?->nombre_completo ?? '') .
                ', le escribimos del Mariachi León Guanajuato sobre el evento ' .
                $ev->codigo_evento,
        );
        return "https://api.whatsapp.com/send?phone={$tel}&text={$msg}";
    };

    $vistas = [
        'dia' => ['label' => 'Día', 'icon' => 'fa-calendar-day'],
        'semana' => ['label' => 'Semana', 'icon' => 'fa-calendar-week'],
        'mes' => ['label' => 'Mes', 'icon' => 'fa-calendar-days'],
    ];
    $diasCorto = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    if ($vistaModo === 'dia') {
        $etiqueta = ucfirst($fechaActual->translatedFormat('l, d \d\e F \d\e Y'));
    } elseif ($vistaModo === 'semana') {
        $etiqueta = $inicioPeriodo->translatedFormat('d M') . ' - ' . $finPeriodo->translatedFormat('d M Y');
    } else {
        $etiqueta = ucfirst($fechaActual->translatedFormat('F Y'));
    }
@endphp

<div class="space-y-3">

    @if (session()->has('success'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Aviso de conflictos de horario --}}
    @if (count($conflictos) > 0)
        <div
            class="flex items-start gap-3 p-3 rounded-lg border-l-[3px] border-rose-500 bg-rose-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-triangle-exclamation text-lg text-rose-600 dark:text-rose-400 mt-0.5"></i>
            <div class="min-w-0">
                <p class="font-semibold">Hay eventos con horarios que se cruzan</p>
                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    @foreach (array_slice($conflictosPares, 0, 4) as $par)
                        <button type="button" wire:click="irADia('{{ $par['fecha'] }}')"
                            class="text-xs text-ink-secondary hover:text-gold-700 dark:hover:text-gold-400 hover:underline"
                            title="Ver ese día">
                            {{ $par['a'] }} y {{ $par['b'] }} -
                            {{ \Carbon\Carbon::parse($par['fecha'])->format('d/m/Y') }}
                        </button>
                    @endforeach
                    @if (count($conflictosPares) > 4)
                        <span class="text-xs text-ink-muted">y {{ count($conflictosPares) - 4 }} más</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-calendar-days text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Agenda</h1>
                <x-ui.badge variant="neutral">{{ $eventos->count() }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirNuevoEvento">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nuevo evento</span>
                <span class="sm:hidden">Nuevo</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Barra de navegación --}}
        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1">
                    <x-ui.icon-button wire:click="fechaAnterior" title="Anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </x-ui.icon-button>
                    <x-ui.button size="sm" variant="secondary" wire:click="fechaHoy">Hoy</x-ui.button>
                    <x-ui.icon-button wire:click="fechaSiguiente" title="Siguiente">
                        <i class="fa-solid fa-chevron-right"></i>
                    </x-ui.icon-button>
                </div>

                <div
                    class="order-last basis-full md:order-none md:basis-auto md:flex-1 text-center text-base font-semibold text-ink-primary truncate">
                    {{ $etiqueta }}
                </div>

                <div
                    class="ml-auto md:ml-0 inline-flex items-center rounded-lg border border-outline bg-surface-sunken p-0.5">
                    @foreach ($vistas as $clave => $v)
                        <button type="button" wire:click="cambiarVista('{{ $clave }}')"
                            class="inline-flex items-center gap-1.5 h-7 px-3 rounded-md text-sm transition-colors {{ $vistaModo === $clave ? 'bg-gold-500/15 text-gold-700 dark:text-gold-400 font-semibold' : 'text-ink-secondary hover:text-ink-primary' }}">
                            <i class="fa-solid {{ $v['icon'] }} text-xs"></i>
                            <span>{{ $v['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-secondary">
                <span><strong class="text-ink-primary tabular-nums">{{ $resumen['total'] }}</strong>
                    {{ $resumen['total'] === 1 ? 'evento' : 'eventos' }}
                    {{ $vistaModo === 'mes' ? 'este mes' : ($vistaModo === 'semana' ? 'esta semana' : 'este día') }}</span>
                <span class="inline-flex items-center gap-1.5"><span
                        class="w-2 h-2 rounded-full bg-emerald-500"></span><strong
                        class="text-ink-primary tabular-nums">{{ $resumen['confirmados'] }}</strong> confirmados</span>
                <span class="inline-flex items-center gap-1.5"><span
                        class="w-2 h-2 rounded-full bg-amber-500"></span><strong
                        class="text-ink-primary tabular-nums">{{ $resumen['porConfirmar'] }}</strong> por
                    confirmar</span>
                @if (count($conflictos) > 0)
                    <span class="inline-flex items-center gap-1.5 font-semibold text-rose-700 dark:text-rose-400"><i
                            class="fa-solid fa-triangle-exclamation"></i>{{ count($conflictos) }} con conflicto</span>
                @endif
            </div>
        </x-slot:filters>

        {{-- ====================== VISTA MES ====================== --}}
        @if ($vistaModo === 'mes')
            <div>
                <div class="grid grid-cols-7 bg-surface-sunken border-b border-outline">
                    @foreach ($diasCorto as $nombreDia)
                        <div
                            class="py-1.5 text-center text-xs font-semibold {{ in_array($nombreDia, ['Sáb', 'Dom']) ? 'text-gold-700 dark:text-gold-400' : 'text-ink-secondary' }}">
                            {{ $nombreDia }}</div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7">
                    @foreach ($diasCalendario as $dia)
                        @php
                            $evs = $dia['eventos'];
                            $finde = $dia['fecha']->dayOfWeekIso >= 6;
                        @endphp
                        <div wire:key="dia-{{ $dia['dateKey'] }}"
                            class="min-h-[52px] sm:min-h-[96px] p-1 group/dia border-b border-r border-outline [&:nth-child(7n)]:border-r-0 flex flex-col gap-0.5
                                {{ $dia['esHoy'] ? 'bg-gold-500/10' : (!$dia['esMesActual'] ? 'bg-surface-sunken/70' : ($finde ? 'bg-surface-sunken/40' : '')) }}">

                            <div class="flex items-center justify-between gap-1">
                                <button type="button" wire:click="irADia('{{ $dia['dateKey'] }}')"
                                    title="Ver el día {{ $dia['fecha']->format('d/m/Y') }}"
                                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs tabular-nums transition-colors
                                        {{ $dia['esHoy'] ? 'bg-gold-500 text-slate-900 font-bold' : ($dia['esMesActual'] ? 'text-ink-primary font-semibold hover:bg-surface-hover' : 'text-ink-muted hover:bg-surface-hover') }}">
                                    {{ $dia['diaNumero'] }}
                                </button>
                                <button type="button" wire:click="abrirNuevoEvento('{{ $dia['dateKey'] }}')"
                                    title="Nuevo evento el {{ $dia['fecha']->format('d/m/Y') }}"
                                    class="hidden sm:flex w-5 h-5 rounded items-center justify-center text-[11px] text-ink-muted hover:text-slate-900 hover:bg-gold-500 opacity-0 group-hover/dia:opacity-100 focus:opacity-100 transition">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                            {{-- Escritorio: chips de evento --}}
                            <div class="hidden sm:flex flex-col gap-0.5 min-w-0">
                                @foreach ($evs->take(3) as $ev)
                                    @php
                                        $c = $estadoCfg($ev->estado);
                                        $conf = isset($conflictos[$ev->id]);
                                    @endphp
                                    <button type="button" wire:key="chip-{{ $ev->id }}"
                                        wire:click="verDetalleEvento({{ $ev->id }})"
                                        title="{{ $ev->hora_txt }} - {{ $ev->cliente->nombre_completo ?? $ev->codigo_evento }} - {{ $ev->servicio->nombre ?? 'Servicio' }}"
                                        class="w-full flex items-center gap-1 px-1.5 py-0.5 rounded border-l-[3px] {{ $c['bar'] }} {{ $c['bg'] }} {{ $conf ? 'ring-1 ring-rose-500' : '' }} {{ $ev->estado === 'Cancelado' ? 'opacity-60 line-through' : '' }} text-left text-xs hover:brightness-95 dark:hover:brightness-125 transition">
                                        <span
                                            class="tabular-nums font-semibold shrink-0 text-ink-primary">{{ $ev->hora_txt }}</span>
                                        <span
                                            class="truncate text-ink-primary">{{ $ev->cliente->nombre_completo ?? $ev->codigo_evento }}</span>
                                        @if ($conf)
                                            <i
                                                class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-[10px] ml-auto shrink-0"></i>
                                        @endif
                                    </button>
                                @endforeach
                                @if ($evs->count() > 3)
                                    <button type="button" wire:click="irADia('{{ $dia['dateKey'] }}')"
                                        class="text-left text-xs font-semibold text-gold-700 dark:text-gold-400 hover:underline px-1">
                                        + {{ $evs->count() - 3 }} más
                                    </button>
                                @endif
                            </div>

                            {{-- Móvil: puntos de color (toca para ver el día) --}}
                            @if ($evs->count() > 0)
                                <button type="button" wire:click="irADia('{{ $dia['dateKey'] }}')"
                                    class="sm:hidden flex flex-wrap gap-0.5 px-0.5">
                                    @foreach ($evs->take(4) as $ev)
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ isset($conflictos[$ev->id]) ? 'bg-rose-500' : $estadoCfg($ev->estado)['dot'] }}"></span>
                                    @endforeach
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ====================== VISTA SEMANA ====================== --}}
        @if ($vistaModo === 'semana')
            <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x divide-outline">
                @foreach ($diasCalendario as $dia)
                    @php $evs = $dia['eventos']; @endphp
                    <div wire:key="sem-{{ $dia['dateKey'] }}"
                        class="min-w-0 flex flex-col {{ $dia['esHoy'] ? 'bg-gold-500/5' : '' }}">
                        <div class="relative">
                            <button type="button" wire:click="irADia('{{ $dia['dateKey'] }}')"
                                class="w-full flex md:flex-col items-center justify-start md:justify-center gap-2 md:gap-0 px-3 py-2 border-b border-outline bg-surface-sunken hover:bg-surface-hover transition-colors"
                                title="Ver el día">
                                <span
                                    class="text-xs {{ $dia['esHoy'] ? 'text-gold-700 dark:text-gold-400 font-semibold' : 'text-ink-secondary' }}">{{ ucfirst($dia['fecha']->translatedFormat('D')) }}</span>
                                <span
                                    class="text-sm tabular-nums {{ $dia['esHoy'] ? 'text-gold-700 dark:text-gold-400 font-bold' : 'text-ink-primary font-semibold' }}">{{ $dia['fecha']->translatedFormat('d M') }}</span>
                                @if ($evs->count() > 0)
                                    <span class="md:hidden ml-auto mr-8 text-xs text-ink-secondary">{{ $evs->count() }}
                                        {{ $evs->count() === 1 ? 'evento' : 'eventos' }}</span>
                                @endif
                            </button>
                            <button type="button" wire:click="abrirNuevoEvento('{{ $dia['dateKey'] }}')"
                                title="Nuevo evento el {{ $dia['fecha']->format('d/m/Y') }}"
                                class="absolute top-1.5 right-1.5 w-6 h-6 rounded flex items-center justify-center text-xs text-ink-muted hover:text-gold-700 dark:hover:text-gold-400 hover:bg-surface-hover transition-colors">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>

                        <div class="p-2 space-y-1.5 md:min-h-[260px]">
                            @forelse ($evs as $ev)
                                @php
                                    $c = $estadoCfg($ev->estado);
                                    $conf = isset($conflictos[$ev->id]);
                                @endphp
                                <button type="button" wire:key="sev-{{ $ev->id }}"
                                    wire:click="verDetalleEvento({{ $ev->id }})"
                                    class="w-full text-left px-2 py-1.5 rounded-lg border-l-[3px] {{ $c['bar'] }} {{ $c['bg'] }} {{ $conf ? 'ring-1 ring-rose-500' : '' }} {{ $ev->estado === 'Cancelado' ? 'opacity-60' : '' }} hover:brightness-95 dark:hover:brightness-125 transition space-y-0.5">
                                    <div class="flex items-center justify-between gap-1 text-xs">
                                        <span class="font-semibold tabular-nums text-ink-primary">{{ $ev->hora_txt }}
                                            - {{ $ev->hora_fin_txt }}</span>
                                        @if ($conf)
                                            <i
                                                class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400"></i>
                                        @endif
                                    </div>
                                    <div
                                        class="text-sm font-semibold text-ink-primary leading-tight truncate {{ $ev->estado === 'Cancelado' ? 'line-through' : '' }}">
                                        {{ $ev->cliente->nombre_completo ?? 'N/A' }}</div>
                                    <div class="text-xs text-ink-secondary truncate">
                                        {{ $ev->servicio->nombre ?? 'Servicio' }}</div>
                                    @if ($ev->direccion_evento)
                                        <div class="text-xs text-ink-muted truncate"><i
                                                class="fa-solid fa-location-dot mr-1"></i>{{ $ev->direccion_evento }}
                                        </div>
                                    @endif
                                </button>
                            @empty
                                <div class="py-2 md:py-8 text-center text-xs text-ink-muted">Sin eventos</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ====================== VISTA DÍA ====================== --}}
        @if ($vistaModo === 'dia')
            <div class="divide-y divide-outline">
                @forelse ($eventos as $ev)
                    @php
                        $c = $estadoCfg($ev->estado);
                        $conf = isset($conflictos[$ev->id]);
                        $wa = $waEvento($ev);
                    @endphp
                    <div wire:key="dev-{{ $ev->id }}"
                        class="flex flex-col sm:flex-row sm:items-center gap-3 px-4 py-3 border-l-[3px] {{ $conf ? 'border-rose-500 bg-rose-500/5' : $c['bar'] }} {{ $ev->estado === 'Cancelado' ? 'opacity-70' : '' }} hover:bg-gold-500/5 transition-colors">

                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-20 shrink-0 text-center">
                                <div class="text-base font-semibold tabular-nums text-ink-primary leading-tight">
                                    {{ $ev->hora_txt }}</div>
                                <div class="text-xs text-ink-secondary tabular-nums">a {{ $ev->hora_fin_txt }}</div>
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap text-sm">
                                    <span
                                        class="font-semibold text-gold-700 dark:text-gold-400">{{ $ev->codigo_evento }}</span>
                                    <span class="inline-flex items-center gap-1.5 text-ink-primary">
                                        <span
                                            class="w-2 h-2 rounded-full {{ $c['dot'] }}"></span>{{ $ev->estado }}
                                    </span>
                                    @if ($conf)
                                        <span
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-rose-700 dark:text-rose-400">
                                            <i class="fa-solid fa-triangle-exclamation"></i>Conflicto de horario
                                        </span>
                                    @endif
                                </div>
                                <div
                                    class="text-base font-semibold text-ink-primary truncate {{ $ev->estado === 'Cancelado' ? 'line-through' : '' }}">
                                    {{ $ev->cliente->nombre_completo ?? 'N/A' }}</div>
                                <div class="text-sm text-ink-secondary truncate">
                                    {{ $ev->servicio->nombre ?? 'N/A' }}
                                    @if ($ev->direccion_evento)
                                        - {{ $ev->direccion_evento }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 sm:shrink-0 justify-end">
                            <x-ui.icon-button variant="primary" wire:click="verDetalleEvento({{ $ev->id }})"
                                title="Ver detalle">
                                <i class="fa-solid fa-eye"></i>
                            </x-ui.icon-button>
                            @if ($wa)
                                <x-ui.icon-button href="{{ $wa }}" target="_blank"
                                    title="WhatsApp del cliente">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </x-ui.icon-button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-sm text-ink-secondary">
                        <i class="fa-regular fa-calendar-xmark text-2xl text-ink-muted block mb-2"></i>
                        <p>No hay presentaciones programadas para este día.</p>
                        <x-ui.button size="sm" class="mt-3"
                            wire:click="abrirNuevoEvento('{{ $fechaActual->format('Y-m-d') }}')">
                            <i class="fa-solid fa-plus"></i>
                            <span>Agendar evento</span>
                        </x-ui.button>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- Pie: leyenda y conflictos --}}
        <x-slot:footer>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-secondary">
                <span class="inline-flex items-center gap-1.5"><span
                        class="w-2 h-2 rounded-full bg-emerald-500"></span>Confirmado</span>
                <span class="inline-flex items-center gap-1.5"><span
                        class="w-2 h-2 rounded-full bg-amber-500"></span>Reservado</span>
                <span class="inline-flex items-center gap-1.5"><span
                        class="w-2 h-2 rounded-full bg-sky-500"></span>Pendiente</span>
                <span class="inline-flex items-center gap-1.5"><span
                        class="w-2 h-2 rounded-full bg-rose-500"></span>Cancelado</span>
            </div>
            @if (count($conflictos) > 0)
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700 dark:text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation"></i>{{ count($conflictos) }}
                    {{ count($conflictos) === 1 ? 'evento' : 'eventos' }} con conflicto
                </span>
            @endif
        </x-slot:footer>
    </x-ui.panel>


    {{-- Modal Nuevo / Editar evento (el mismo de Eventos) --}}
    @include('livewire.comercial.partials.modal-evento')

    {{-- Modal de detalle del evento --}}
    @if ($modalDetalleOpen && $eventoDetalle)
        @php
            $det = $eventoDetalle;
            $cDet = $estadoCfg($det->estado);
            $waDet = $waEvento($det);
        @endphp
        <x-ui.modal icon="fa-solid fa-calendar-check" title="Evento {{ $det->codigo_evento }}" maxWidth="max-w-2xl"
            onClose="cerrarModalDetalle">

            <div class="px-6 py-5 space-y-4">
                @if (isset($conflictos[$det->id]))
                    <div
                        class="flex items-start gap-2.5 p-3 rounded-lg border-l-[3px] border-rose-500 bg-rose-500/10 text-sm text-ink-primary">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 mt-0.5"></i>
                        <span>Este evento se cruza en horario con otro de la agenda.</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    <div>
                        <span class="block text-xs text-ink-secondary">Cliente:</span>
                        <span
                            class="text-sm font-semibold text-ink-primary">{{ $det->cliente->nombre_completo ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">Teléfono:</span>
                        <span
                            class="text-sm text-ink-primary tabular-nums">{{ $det->telefono_contacto ?? ($det->cliente->telefono ?? 'N/A') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">Servicio:</span>
                        <span
                            class="text-sm font-semibold text-gold-700 dark:text-gold-400">{{ $det->servicio->nombre ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">Estado:</span>
                        <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                            <span class="w-2 h-2 rounded-full {{ $cDet['dot'] }}"></span>{{ $det->estado }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">Fecha y hora:</span>
                        <span class="text-sm text-ink-primary tabular-nums">
                            {{ $det->fecha_evento->format('d/m/Y') }} - {{ $det->hora_txt }} a
                            {{ $det->hora_fin_txt }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">Duración:</span>
                        <span
                            class="text-sm text-ink-primary">{{ rtrim(rtrim(number_format((float) $det->duracion_horas, 2, '.', ''), '0'), '.') }}
                            h</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-xs text-ink-secondary">Lugar:</span>
                        <span class="text-sm text-ink-primary">
                            <i
                                class="fa-solid fa-location-dot text-rose-500 mr-1"></i>{{ $det->direccion_evento ?: 'No especificado' }}
                        </span>
                    </div>
                </div>

                @if ($det->observaciones)
                    <div>
                        <span class="block text-xs text-ink-secondary mb-1">Observaciones:</span>
                        <div
                            class="p-3 rounded-lg bg-surface-sunken border border-outline text-sm text-ink-primary whitespace-pre-wrap">
                            {{ $det->observaciones }}</div>
                    </div>
                @endif
            </div>

            <x-slot:footer>
                @if ($waDet)
                    <x-ui.button type="button" variant="secondary" href="{{ $waDet }}" target="_blank">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </x-ui.button>
                @endif
                <x-ui.button type="button" variant="secondary" wire:click="editarDesdeDetalle">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Editar</span>
                </x-ui.button>
                <x-ui.button type="button" wire:click="cerrarModalDetalle">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cerrar</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>