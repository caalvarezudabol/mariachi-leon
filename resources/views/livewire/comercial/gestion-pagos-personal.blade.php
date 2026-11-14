@php
    $estiloTipo = function ($tipo) {
        $t = mb_strtolower((string) $tipo);
        if (str_contains($t, 'transp') || str_contains($t, 'bus') || str_contains($t, 'chofer')) {
            return ['fa-bus', 'bg-sky-500/15 text-sky-600 dark:text-sky-400'];
        }
        if (str_contains($t, 'sonid')) {
            return ['fa-volume-high', 'bg-violet-500/15 text-violet-600 dark:text-violet-400'];
        }
        if ($t === '' || str_contains($t, 'músic') || str_contains($t, 'music') || str_contains($t, 'cantan')) {
            return ['fa-music', 'bg-amber-500/15 text-amber-600 dark:text-amber-400'];
        }
        return ['fa-user', 'bg-slate-500/15 text-slate-600 dark:text-slate-300'];
    };
    $bs = fn($n) => 'Bs ' . number_format(abs((float) $n), 2);
@endphp

<div class="space-y-3">
    @if (session()->has('success'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <x-ui.caja-resumen />

    <x-ui.panel>
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-hand-holding-dollar text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Pagos a personal</h1>
            </div>
        </x-slot:header>

        <x-slot:filters>
            <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar persona..."
                        class="w-full h-9 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('filtro', 'pendientes')" :active="$filtro === 'pendientes'">
                        Pendientes <span class="opacity-70">{{ $conteos['pendientes'] }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('filtro', 'hoy')" :active="$filtro === 'hoy'">
                        Hoy <span class="opacity-70">{{ $conteos['hoy'] }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('filtro', 'semana')" :active="$filtro === 'semana'">
                        Semana <span class="opacity-70">{{ $conteos['semana'] }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('filtro', 'mes')" :active="$filtro === 'mes'">
                        Mes <span class="opacity-70">{{ $conteos['mes'] }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('filtro', 'todos')" :active="$filtro === 'todos'">
                        Todos <span class="opacity-70">{{ $conteos['todos'] }}</span>
                    </x-ui.filter-chip>
                </x-ui.filter-chips>
            </div>
        </x-slot:filters>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 p-2 items-start">
            @forelse ($personas as $p)
                @php
                    [$icono, $tonoIcono] = $estiloTipo($p->tipo);
                    $abierto = in_array($p->id, $abiertos);
                    $debe = $p->saldo > 0.001;
                    $adelanto = $p->saldo < -0.001;
                    $esDueno = (bool) ($p->es_dueno ?? false);
                @endphp
                <div wire:key="persona-{{ $p->id }}"
                    class="rounded-lg border {{ $esDueno ? 'border-gold-500 bg-gold-500/5' : 'border-outline bg-surface-card' }} overflow-hidden {{ $abierto ? 'lg:col-span-2' : '' }}">
                    <div class="flex items-center gap-2 px-2.5 py-1.5">
                        <button type="button" wire:click="toggleAbierto({{ $p->id }})"
                            class="flex items-center gap-2 min-w-0 flex-1 text-left" title="Ver detalle">
                            <span
                                class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 {{ $esDueno ? 'bg-gold-500 text-white' : $tonoIcono }}">
                                <i class="fa-solid {{ $esDueno ? 'fa-crown' : $icono }} text-[10px]"></i>
                            </span>
                            <span class="min-w-0">
                                <span
                                    class="block text-sm font-semibold text-ink-primary truncate leading-tight">{{ $p->nombre_completo }}
                                    @if ($esDueno)
                                        <span
                                            class="ml-1 px-1.5 py-px rounded text-[9px] font-bold uppercase bg-gold-500 text-white align-middle">Dueño</span>
                                    @endif
                                </span>
                                <span class="block text-[11px] text-ink-secondary truncate leading-tight">
                                    {{ $p->tipo }} -
                                    @if ($p->es_mensual)
                                        Mensual
                                    @else
                                        {{ $p->num_shows }} {{ $p->num_shows === 1 ? 'show' : 'shows' }}
                                    @endif
                                    @if ($p->entregado > 0 && $debe)
                                        - entregado {{ $bs($p->entregado) }}
                                    @endif
                                </span>
                            </span>
                        </button>

                        <div class="text-right shrink-0 leading-tight">
                            @if ($debe)
                                <div class="text-sm font-bold tabular-nums text-ink-primary">{{ $bs($p->saldo) }}</div>
                                <div class="text-[11px] text-ink-secondary">
                                    {{ $p->es_mensual ? 'Sueldo ' . now()->locale('es')->monthName : 'Se le debe' }}
                                </div>
                            @elseif ($adelanto)
                                <div class="text-sm font-bold tabular-nums text-rose-700 dark:text-rose-400">-
                                    {{ $bs($p->saldo) }}</div>
                                <div class="text-[11px] text-ink-secondary">Anticipo a descontar</div>
                            @else
                                <div class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                                    {{ $p->es_mensual ? 'Pagado' : 'Al día' }}</div>
                            @endif
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            @if ($debe)
                                <x-ui.button size="sm" class="!h-7 !px-2.5"
                                    wire:click="abrir({{ $p->id }}, 'pago')">Pagar</x-ui.button>
                            @endif
                            <x-ui.button size="sm" variant="secondary" class="!h-7 !px-2"
                                wire:click="abrir({{ $p->id }}, 'anticipo')"
                                title="Dar anticipo">Anticipo</x-ui.button>
                            <button type="button" wire:click="toggleAbierto({{ $p->id }})"
                                class="w-6 h-7 text-ink-muted hover:text-ink-primary" title="Ver detalle">
                                <i
                                    class="fa-solid fa-chevron-down text-[10px] transition-transform {{ $abierto ? 'rotate-180' : '' }}"></i>
                            </button>
                        </div>
                    </div>

                    @if ($abierto)
                        @php $d = $detalle[$p->id] ?? ['shows' => collect(), 'pagos' => collect()]; @endphp
                        <div
                            class="bg-surface-sunken/60 px-3 py-2 border-t border-outline grid grid-cols-1 {{ $p->es_mensual ? '' : 'md:grid-cols-2' }} gap-x-6 gap-y-2">
                            @if ($p->es_mensual)
                                <div class="text-xs text-ink-secondary">Sueldo fijo: {{ $bs($p->deuda) }} - entregado
                                    este mes: {{ $bs($p->entregado) }}. Se reinicia cada mes y no depende de los shows.
                                </div>
                            @else
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wide text-ink-secondary mb-0.5">
                                        Shows</div>
                                    @forelse ($d['shows'] as $ep)
                                        @php
                                            $evp = $ep->evento;
                                            $estEv = $evp->estado ?? '';
                                            $chip = match ($estEv) {
                                                'Realizado', 'Finalizado' => 'bg-sky-600',
                                                'Confirmado' => 'bg-emerald-600',
                                                default => 'bg-amber-500',
                                            };
                                        @endphp
                                        <div
                                            class="flex items-center gap-2 py-1 border-b border-outline/60 last:border-0">
                                            <span
                                                class="w-11 shrink-0 text-center rounded px-1 py-0.5 text-[11px] font-bold text-white tabular-nums {{ $chip }}"
                                                title="{{ $estEv }}">{{ optional($evp->fecha_evento)->format('d/m') }}</span>
                                            <span class="min-w-0 flex-1 leading-tight">
                                                <span
                                                    class="block text-xs font-semibold text-ink-primary truncate">{{ $evp->cliente->nombre_completo ?? ($evp->codigo_evento ?? 'Evento') }}</span>
                                                <span
                                                    class="block text-[11px] text-ink-secondary truncate">{{ $evp->servicio->nombre ?? '' }}{{ ($evp->servicio->nombre ?? '') !== '' && $ep->funcion ? ' - ' : '' }}{{ $ep->funcion }}</span>
                                            </span>
                                            <span
                                                class="tabular-nums shrink-0 text-xs font-bold text-ink-primary">{{ $bs($ep->monto_asignado) }}</span>
                                        </div>
                                    @empty
                                        <div class="text-xs text-ink-muted">Sin shows asignados.</div>
                                    @endforelse
                                </div>
                            @endif

                            <div>
                                <div class="text-xs font-bold uppercase tracking-wide text-ink-secondary mb-0.5">
                                    Entregado</div>
                                @forelse ($d['pagos'] as $pg)
                                    <div class="flex items-center justify-between gap-2 py-0.5 text-xs"
                                        wire:key="pg-{{ $pg->id }}">
                                        <span class="min-w-0 truncate text-ink-primary">
                                            {{ $pg->fecha_pago->format('d/m H:i') }} -
                                            {{ $pg->glosa ?: ucfirst($pg->tipo ?? 'pago') }}
                                            <span class="text-ink-secondary">
                                                - {{ $pg->metodo_pago }}
                                                @if ($pg->participante && $pg->participante->evento)
                                                    -
                                                    {{ $pg->participante->evento->cliente->nombre_completo ?? $pg->participante->evento->codigo_evento }}
                                                @endif
                                            </span>
                                        </span>
                                        <span class="flex items-center gap-2 shrink-0">
                                            <span class="tabular-nums text-ink-primary">{{ $bs($pg->monto) }}</span>
                                            <button type="button" wire:click="pedirAnular({{ $pg->id }})"
                                                class="text-ink-muted hover:text-rose-600" title="Anular">
                                                <i class="fa-solid fa-rotate-left text-xs"></i>
                                            </button>
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-xs text-ink-muted">Aún no se le entregó nada.</div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="lg:col-span-2 py-10 text-center text-sm text-ink-secondary">
                    <i class="fa-solid fa-circle-check text-2xl text-emerald-500 block mb-2"></i>
                    @if ($filtro === 'pendientes')
                        Nadie tiene saldo pendiente.
                    @else
                        No hay personas para este filtro.
                    @endif
                </div>
            @endforelse
        </div>
    </x-ui.panel>

    {{-- Modal pagar / anticipo --}}
    @if ($modalOpen && $modalFila)
        <form wire:submit.prevent="guardar">
            <x-ui.modal icon="fa-solid fa-hand-holding-dollar" :title="$modalFila->nombre_completo" maxWidth="max-w-md"
                onClose="cerrarModal">
                <div class="px-5 py-4 space-y-3">
                    <p class="text-sm text-ink-secondary -mt-1">
                        {{ $modalFila->tipo }} -
                        @if ($modalFila->saldo > 0.001)
                            Se le debe {{ $bs($modalFila->saldo) }}
                        @elseif ($modalFila->saldo < -0.001)
                            Tiene un anticipo de {{ $bs($modalFila->saldo) }} por descontar
                        @else
                            Sin saldo pendiente
                        @endif
                    </p>

                    {{-- Pagar saldo / Anticipo --}}
                    <div class="grid grid-cols-2 gap-1 p-1 rounded-lg bg-surface-sunken">
                        <button type="button" wire:click="cambiarModo('pago')" @disabled($modalFila->saldo <= 0.001)
                            class="h-9 rounded-md text-sm font-semibold transition-colors disabled:opacity-40
                                {{ $modo === 'pago' ? 'bg-surface-card text-ink-primary shadow-sm' : 'text-ink-secondary' }}">
                            {{ $modalFila->es_mensual ? 'Pagar sueldo' : 'Pagar saldo' }}
                        </button>
                        <button type="button" wire:click="cambiarModo('anticipo')"
                            class="h-9 rounded-md text-sm font-semibold transition-colors
                                {{ $modo === 'anticipo' ? 'bg-surface-card text-ink-primary shadow-sm' : 'text-ink-secondary' }}">
                            Anticipo
                        </button>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink-primary mb-1">Monto: <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" inputmode="decimal"
                                wire:model.live.debounce.300ms="monto" autofocus
                                class="w-full h-12 pl-4 pr-12 bg-surface-sunken border border-outline rounded-lg text-xl font-bold text-ink-primary tabular-nums focus:border-gold-500 focus:outline-none">
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-ink-secondary">Bs</span>
                        </div>
                        @error('monto')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        @if ($modo === 'pago' && is_numeric($monto) && (float) $monto > $modalFila->saldo + 0.001)
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Es más de lo que se le debe
                                ({{ $bs($modalFila->saldo) }}). Para un adelanto usa Anticipo.</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink-primary mb-1">Método: <span
                                class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['Efectivo' => 'fa-money-bill-wave', 'QR' => 'fa-qrcode'] as $m => $ic)
                                <button type="button" wire:click="$set('metodo', '{{ $m }}')"
                                    class="h-10 rounded-lg border text-sm font-semibold flex items-center justify-center gap-2 transition-colors
                                        {{ $metodo === $m ? 'border-gold-500 bg-gold-500/10 text-ink-primary' : 'border-outline bg-surface-card text-ink-secondary hover:border-gold-500/50' }}">
                                    <i class="fa-solid {{ $ic }}"></i> {{ $m }}
                                </button>
                            @endforeach
                        </div>
                        @php
                            $cajaMetodo = $caja[$metodo] ?? 0;
                            $montoNum = is_numeric($monto) ? (float) $monto : 0;
                        @endphp
                        <p
                            class="text-xs mt-1.5 {{ $cajaMetodo - $montoNum < 0 ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-ink-secondary' }}">
                            Caja {{ $metodo === 'QR' ? 'QR' : 'efectivo' }}: {{ $bs($cajaMetodo) }}
                            @if ($montoNum > 0)
                                - quedarían
                                {{ $cajaMetodo - $montoNum < 0 ? '- ' : '' }}{{ $bs($cajaMetodo - $montoNum) }}
                            @endif
                        </p>
                    </div>

                    {{-- Fondo común (opcional) --}}
                    @php $fondoNum = is_numeric($fondo) ? (float) $fondo : 0; @endphp
                    <div>
                        <label class="block text-sm font-semibold text-ink-primary mb-1"><i
                                class="fa-solid fa-vault text-xs mr-1"></i>Dejar en el fondo común: <span
                                class="text-xs font-normal text-ink-secondary">(opcional)</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" inputmode="decimal"
                                wire:model.live.debounce.300ms="fondo" placeholder="0.00"
                                class="w-full h-10 pl-3 pr-10 bg-surface-sunken border border-outline rounded-lg text-sm font-semibold text-ink-primary tabular-nums focus:border-gold-500 focus:outline-none">
                            <span
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-ink-secondary">Bs</span>
                        </div>
                        @if (($pctFondo ?? 0) > 0 && $montoNum > 0)
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <button type="button" wire:click="usarFondoSugerido"
                                    class="h-7 px-2.5 rounded-full border border-gold-500/60 bg-gold-500/10 text-xs font-semibold text-ink-primary hover:bg-gold-500/20 transition-colors">
                                    Sugerido {{ rtrim(rtrim(number_format($pctFondo, 2), '0'), '.') }}%
                                </button>
                                @if ($fondoNum > 0)
                                    <button type="button" wire:click="$set('fondo', '')"
                                        class="h-7 px-2.5 rounded-full border border-outline text-xs font-semibold text-ink-secondary hover:bg-surface-hover transition-colors">Quitar</button>
                                @endif
                            </div>
                        @endif
                        @error('fondo')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        @if ($fondoNum > 0 && $montoNum > 0)
                            <p
                                class="text-xs mt-1 {{ $fondoNum > $montoNum ? 'text-amber-700 dark:text-amber-400' : 'text-ink-secondary' }}">
                                @if ($fondoNum > $montoNum)
                                    Es más que el monto.
                                @else
                                    Recibe {{ $bs($montoNum - $fondoNum) }} - al fondo {{ $bs($fondoNum) }}
                                @endif
                            </p>
                        @endif
                    </div>

                    @if ($modo === 'anticipo')
                        <p class="text-xs text-ink-secondary">El anticipo se descuenta solo de lo que se le asigne en
                            los shows.</p>
                    @endif
                </div>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="cerrarModal">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </x-ui.button>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="guardar" :disabled="!is_numeric($monto) || (float) $monto <= 0 || ($modo === 'pago' && (float) $monto > $modalFila->saldo + 0.001) || (float) $monto > ($caja[$metodo] ?? 0) + 0.001 || (is_numeric($fondo) && (float) $fondo > (float) $monto + 0.001)">
                        <i class="fa-solid fa-check" wire:loading.remove wire:target="guardar"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardar"></i>
                        <span>{{ $modo === 'anticipo' ? 'Dar anticipo' : 'Pagar' }}{{ is_numeric($monto) && $monto > 0 ? ' ' . $bs($monto) : '' }}</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </form>
    @endif

    {{-- Modal anular --}}
    @if ($anularId)
        <form wire:submit.prevent="anular">
            <x-ui.modal icon="fa-solid fa-rotate-left" title="Anular pago" maxWidth="max-w-md"
                onClose="cancelarAnular">
                <div class="px-5 py-4 space-y-2">
                    <p class="text-sm text-ink-secondary">El saldo de la persona volverá a su valor anterior y el
                        dinero regresa a la caja.</p>
                    <x-ui.input label="Motivo" required type="text" name="motivo" wire:model="motivo"
                        placeholder="Ej: monto equivocado" />
                </div>
                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="cancelarAnular">
                        <span>Volver</span>
                    </x-ui.button>
                    <x-ui.button type="submit" variant="danger">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Anular pago</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </form>
    @endif
</div>
