@php
    // Aviso de hora: en curso / falta poco / ya pasó (solo eventos por realizar)
    $alertaEvento = function ($ev) {
        if (!in_array($ev->estado, ['Confirmado', 'Reservado', 'Pendiente', 'Cotizado'])) {
            return null;
        }
        $ini = $ev->fecha_evento->copy()->setTimeFromTimeString(substr($ev->hora_evento, 0, 8));
        $fin = $ini->copy()->addMinutes((int) round(((float) $ev->duracion_horas) * 60));
        $ahora = now();
        if ($ahora->gte($ini) && $ahora->lt($fin)) {
            return ['En curso', 'bg-emerald-600 text-white', 'bg-emerald-500/10'];
        }
        if ($ahora->gte($fin)) {
            return ['Pasó', 'bg-rose-600 text-white', 'bg-rose-500/5'];
        }
        $m = (int) ceil(($ini->timestamp - $ahora->timestamp) / 60);
        if ($m <= 30) {
            return ['En ' . $m . ' min', 'bg-amber-500 text-white animate-pulse', 'bg-amber-500/10'];
        }
        if ($m <= 240) {
            return [
                'En ' . intdiv($m, 60) . 'h ' . $m % 60 . 'm',
                'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-100',
                '',
            ];
        }
        return null;
    };
    $sePuedeRefrescar = !$modalOpen && !$modalCobroOpen && !$modalHistorialPagosOpen;
@endphp
<div class="space-y-4" @if ($sePuedeRefrescar) wire:poll.60s @endif>
    <!-- Mensajes de Notificación -->
    @if (session()->has('success'))
        <div
            class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
            @if (session('recibo_codigo'))
                <a href="{{ route('comercial.pagos.recibo', session('recibo_codigo')) }}" target="_blank"
                    class="ml-auto shrink-0 font-semibold underline hover:no-underline">
                    <i class="fa-solid fa-print mr-1"></i>Ver recibo
                </a>
            @endif
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-lg text-rose-700 dark:text-rose-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <x-ui.panel>
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-calendar-check text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Eventos</h1>
                <x-ui.badge variant="neutral" class="hidden sm:inline-flex">{{ $totalEventos }}</x-ui.badge>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-ui.button variant="secondary" size="sm" class="hidden sm:inline-flex"
                    href="{{ route('admin.eventos.pdf', ['search' => $search, 'cliente_id' => $cliente_filtro, 'estado' => in_array($estado_filtro, ['porrealizar', 'realizados', 'cancelados']) ? '' : $estado_filtro]) }}"
                    target="_blank" title="Exportar Programación de Eventos y Estado Financiero a PDF">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Exportar PDF</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" wire:click="abrirModal">
                    <i class="fa-solid fa-plus"></i>
                    <span class="hidden sm:inline">Nuevo evento</span>
                    <span class="sm:hidden">Nuevo</span>
                </x-ui.button>
            </div>
        </x-slot:header>

        <x-slot:filters>
            <div class="flex flex-col gap-2">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <div class="relative flex-1 min-w-0">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                        <x-ui.input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por código, cliente, dirección..." class="pl-9 h-8 text-sm" />
                    </div>
                    @php
                        $opcionesFiltroClientes = collect([
                            ['id' => '', 'label' => 'Todos los clientes', 'meta' => '', 'search' => 'todos'],
                        ])
                            ->concat(
                                $clientes->map(
                                    fn($cli) => [
                                        'id' => $cli->id,
                                        'label' => $cli->nombre_completo,
                                        'meta' => trim(
                                            ($cli->ci_nit ? 'CI: ' . $cli->ci_nit : '') .
                                                ($cli->telefono ? ' - Tel: ' . $cli->telefono : ''),
                                        ),
                                        'search' => mb_strtolower(
                                            $cli->nombre_completo . ' ' . $cli->ci_nit . ' ' . $cli->telefono,
                                        ),
                                    ],
                                ),
                            )
                            ->values();
                    @endphp
                    <div class="sm:w-56 shrink-0">
                        <x-ui.select-search name="cliente_filtro" :options="$opcionesFiltroClientes" placeholder="Buscar cliente..." />
                    </div>
                    <x-ui.filter-chips>
                        @foreach (['porrealizar' => 'Por realizar', 'realizados' => 'Realizados', 'cancelados' => 'Cancelados'] as $clave => $texto)
                            <x-ui.filter-chip wire:click="$set('estado_filtro', '{{ $clave }}')"
                                :active="$estado_filtro === $clave">
                                {{ $texto }} <span class="opacity-70">{{ $conteos[$clave] ?? 0 }}</span>
                            </x-ui.filter-chip>
                        @endforeach
                        <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                            Todos <span class="opacity-70">{{ $totalEventos }}</span>
                        </x-ui.filter-chip>
                    </x-ui.filter-chips>
                </div>

                @if ($cliente_filtro)
                    @php $clienteActivo = $clientes->firstWhere('id', $cliente_filtro); @endphp
                    @if ($clienteActivo)
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 rounded-lg bg-amber-500/10 border-l-[3px] border-amber-500 text-xs">
                            <div class="flex items-center gap-2 text-ink-primary">
                                <i class="fa-solid fa-user-check text-amber-600 dark:text-amber-400"></i>
                                <span>Filtrando por cliente: <strong>{{ $clienteActivo->nombre_completo }}</strong>
                                    @if ($clienteActivo->ci_nit)
                                        <span class="text-ink-muted">({{ $clienteActivo->ci_nit }})</span>
                                    @endif
                                </span>
                            </div>
                            <button wire:click="$set('cliente_filtro', '')"
                                class="inline-flex items-center gap-1.5 text-amber-700 dark:text-amber-400 hover:underline font-semibold self-start sm:self-auto">
                                <i class="fa-solid fa-xmark"></i>
                                <span>Quitar filtro</span>
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </x-slot:filters>

        <!-- Tabla (desktop): una línea por evento -->
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="py-1 px-2 border-r border-outline">Cliente</th>
                    <th class="py-1 px-2 border-r border-outline">Fecha y hora</th>
                    <th class="py-1 px-2 border-r border-outline">Servicio</th>
                    <th class="py-1 px-2 border-r border-outline text-right">Contrato</th>
                    <th class="py-1 px-2 border-r border-outline text-right">A cuenta</th>
                    <th class="py-1 px-2 border-r border-outline text-right">Saldo</th>
                    <th class="py-1 px-2 border-r border-outline text-center">Estado</th>
                    <th class="py-1.5 px-2 text-right w-px whitespace-nowrap">Acciones</th>
                </x-slot:header>

                @forelse ($eventos as $ev)
                    @php
                        $telRow = preg_replace('/[^0-9]/', '', $ev->telefono_contacto ?: $ev->cliente->telefono ?? '');
                        if ($telRow && strlen($telRow) === 8) {
                            $telRow = '591' . $telRow;
                        }
                        $waUrl = $telRow
                            ? 'https://api.whatsapp.com/send?phone=' .
                                $telRow .
                                '&text=' .
                                urlencode(
                                    'Hola ' .
                                        ($ev->cliente->nombre_completo ?? '') .
                                        ', le contactamos del Mariachi León Guanajuato sobre su evento ' .
                                        $ev->codigo_evento,
                                )
                            : null;
                        $dotColor = match (true) {
                            in_array($ev->estado, ['Confirmado', 'Realizado', 'Finalizado']) => 'bg-emerald-500',
                            in_array($ev->estado, ['Reservado', 'Cotizado']) => 'bg-amber-500',
                            $ev->estado === 'Cancelado' => 'bg-rose-500',
                            default => 'bg-slate-400',
                        };
                        $badgeColor = match (true) {
                            in_array($ev->estado, ['Confirmado']) => 'bg-emerald-600',
                            in_array($ev->estado, ['Realizado', 'Finalizado']) => 'bg-sky-600',
                            in_array($ev->estado, ['Reservado', 'Cotizado']) => 'bg-amber-500',
                            $ev->estado === 'Cancelado' => 'bg-rose-600',
                            default => 'bg-slate-500',
                        };
                        $durMin = (int) round(((float) $ev->duracion_horas) * 60);
                        $durTxt =
                            $durMin >= 60
                                ? rtrim(rtrim(number_format($durMin / 60, 1), '0'), '.') . ' h'
                                : $durMin . ' min';
                        $conCobro = $ev->contrato && $ev->saldo_pendiente > 0 && $ev->estado !== 'Cancelado';
                        $al = $alertaEvento($ev);
                    @endphp
                    <tr
                        class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $ev->estado === 'Cancelado' ? 'opacity-60' : '' }} {{ $al[2] ?? '' }}">
                        <td class="py-0.5 px-2 border-r border-outline whitespace-nowrap">
                            <span
                                class="text-sm font-semibold text-ink-primary inline-block max-w-[150px] truncate align-bottom"
                                title="{{ $ev->codigo_evento }} - {{ $ev->cliente->nombre_completo ?? '' }}">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</span>
                        </td>
                        <td
                            class="py-0.5 px-2 border-r border-outline text-xs text-ink-secondary whitespace-nowrap tabular-nums">
                            {{ $ev->fecha_evento->format('d/m/y') }} {{ substr($ev->hora_evento, 0, 5) }}
                            @if ($al)
                                <span
                                    class="ml-1 px-1.5 py-px rounded text-[10px] font-bold {{ $al[1] }}">{{ $al[0] }}</span>
                            @else
                                <span class="text-xs text-ink-muted">- {{ $durTxt }}</span>
                            @endif
                        </td>
                        <td class="py-0.5 px-2 border-r border-outline text-sm text-ink-secondary">
                            <span class="inline-block max-w-[140px] truncate align-bottom"
                                title="{{ $ev->servicio->nombre ?? '' }}">{{ $ev->servicio->nombre ?? 'N/A' }}</span>
                        </td>
                        <td
                            class="py-0.5 px-2 border-r border-outline text-right whitespace-nowrap tabular-nums text-sm">
                            @if ($ev->contrato)
                                <span
                                    class="font-semibold text-ink-primary">{{ number_format($ev->monto_contratado, 2) }}</span>
                            @else
                                <span class="text-xs text-ink-muted italic">Sin contrato</span>
                            @endif
                        </td>
                        <td
                            class="py-0.5 px-2 border-r border-outline text-right whitespace-nowrap tabular-nums text-sm text-emerald-600 dark:text-emerald-400">
                            {{ $ev->contrato ? number_format($ev->total_abonado, 2) : '-' }}
                        </td>
                        <td
                            class="py-0.5 px-2 border-r border-outline text-right whitespace-nowrap tabular-nums text-sm font-semibold {{ $ev->estado === 'Cancelado' ? 'text-ink-muted' : ($ev->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                            {{ $ev->estado === 'Cancelado' ? '-' : number_format($ev->saldo_pendiente, 2) }}
                        </td>
                        <td class="py-0.5 px-2 border-r border-outline text-center whitespace-nowrap">
                            <span
                                class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold text-white {{ $badgeColor }}">{{ $ev->estado }}</span>
                        </td>
                        <td class="py-0.5 px-2 text-right w-px whitespace-nowrap">
                            <div class="flex items-center justify-end gap-0.5">
                                @if ($conCobro)
                                    <x-ui.icon-button class="!h-6 !w-6" variant="primary"
                                        wire:click="abrirCobro({{ $ev->id }})" title="Registrar cobro">
                                        <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                                    </x-ui.icon-button>
                                @endif
                                @if (in_array($ev->estado, ['Confirmado', 'Reservado', 'Pendiente', 'Cotizado']) &&
                                        $ev->contrato &&
                                        $ev->saldo_pendiente <= 0)
                                    <x-ui.icon-button class="!h-6 !w-6"
                                        wire:click="marcarRealizado({{ $ev->id }})"
                                        wire:confirm="{{ $ev->fecha_evento->gt(today()) ? 'El show es el ' . $ev->fecha_evento->format('d/m/Y') . ' y aún no pasa. ¿Marcarlo como realizado?' : 'Pagado por completo. ¿Marcar este evento como realizado?' }}"
                                        title="Pagado: marcar como realizado">
                                        <i class="fa-solid fa-circle-check text-xs text-emerald-600"></i>
                                    </x-ui.icon-button>
                                @endif
                                @if ($ev->contrato && $ev->contrato->codigo_verificacion)
                                    <x-ui.icon-button class="!h-6 !w-6"
                                        href="{{ route('admin.contratos.pdf', $ev->contrato->codigo_verificacion) }}"
                                        target="_blank" title="Imprimir contrato">
                                        <i class="fa-solid fa-file-pdf text-xs text-rose-600"></i>
                                    </x-ui.icon-button>
                                @endif
                                <x-ui.icon-button class="!h-6 !w-6" wire:click="editar({{ $ev->id }})"
                                    title="Editar evento">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="fa-solid fa-receipt"
                                        wire:click="abrirHistorialPagos({{ $ev->id }})">
                                        Historial de pagos
                                    </x-ui.action-menu-item>
                                    @if (in_array($ev->estado, ['Confirmado', 'Reservado', 'Pendiente', 'Cotizado']))
                                        <x-ui.action-menu-item icon="fa-solid fa-circle-check"
                                            wire:click="marcarRealizado({{ $ev->id }})"
                                            wire:confirm="{{ $ev->saldo_pendiente > 0 ? 'Este evento aún tiene saldo pendiente de Bs ' . number_format($ev->saldo_pendiente, 2) . '. ¿Marcarlo como realizado de todos modos?' : '¿Marcar este evento como realizado?' }}">
                                            Marcar realizado
                                        </x-ui.action-menu-item>
                                    @endif
                                    @if ($ev->estado !== 'Cancelado')
                                        <x-ui.action-menu-item icon="fa-solid fa-ban"
                                            wire:click="cancelarEvento({{ $ev->id }})"
                                            wire:confirm="¿Cancelar este evento? Dejará de ocupar la agenda. Los cobros ya registrados se mantienen.">
                                            Cancelar evento
                                        </x-ui.action-menu-item>
                                    @else
                                        <x-ui.action-menu-item icon="fa-solid fa-rotate-left"
                                            wire:click="reactivarEvento({{ $ev->id }})">
                                            Reactivar evento
                                        </x-ui.action-menu-item>
                                    @endif
                                    <x-ui.action-menu-item icon="fa-solid fa-trash-can" variant="danger"
                                        wire:click="eliminarEvento({{ $ev->id }})"
                                        wire:confirm="¿Eliminar este evento? Solo se puede si no tiene cobros ni pagos.">
                                        Eliminar
                                    </x-ui.action-menu-item>
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-ink-muted text-sm">
                            No se encontraron eventos registrados.
                        </td>
                    </tr>
                @endforelse

                @if ($eventos->total() > 0)
                    <tr class="bg-surface-sunken border-t-2 border-outline font-bold">
                        <td colspan="3" class="py-1.5 px-2 text-xs text-ink-secondary uppercase tracking-wide">
                            Total ({{ $eventos->total() }} eventos)
                        </td>
                        <td class="py-1.5 px-2 text-right tabular-nums text-sm text-ink-primary whitespace-nowrap">
                            {{ number_format($totales['contratado'], 2) }}</td>
                        <td
                            class="py-1.5 px-2 text-right tabular-nums text-sm text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            {{ number_format($totales['abonado'], 2) }}</td>
                        <td
                            class="py-1.5 px-2 text-right tabular-nums text-sm text-rose-600 dark:text-rose-400 whitespace-nowrap">
                            {{ number_format($totales['saldo'], 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endif
            </x-ui.table>
        </div>

        <!-- Filas (móvil): 3 líneas -->
        <div class="md:hidden divide-y divide-outline">
            @forelse ($eventos as $ev)
                @php
                    $telRow = preg_replace('/[^0-9]/', '', $ev->telefono_contacto ?: $ev->cliente->telefono ?? '');
                    if ($telRow && strlen($telRow) === 8) {
                        $telRow = '591' . $telRow;
                    }
                    $waUrl = $telRow
                        ? 'https://api.whatsapp.com/send?phone=' .
                            $telRow .
                            '&text=' .
                            urlencode(
                                'Hola ' .
                                    ($ev->cliente->nombre_completo ?? '') .
                                    ', le contactamos del Mariachi León Guanajuato sobre su evento ' .
                                    $ev->codigo_evento,
                            )
                        : null;
                    $dotColor = match (true) {
                        in_array($ev->estado, ['Confirmado', 'Realizado', 'Finalizado']) => 'bg-emerald-500',
                        in_array($ev->estado, ['Reservado', 'Cotizado']) => 'bg-amber-500',
                        $ev->estado === 'Cancelado' => 'bg-rose-500',
                        default => 'bg-slate-400',
                    };
                    $badgeColor = match (true) {
                        $ev->estado === 'Confirmado' => 'bg-emerald-600',
                        in_array($ev->estado, ['Realizado', 'Finalizado']) => 'bg-sky-600',
                        in_array($ev->estado, ['Reservado', 'Cotizado']) => 'bg-amber-500',
                        $ev->estado === 'Cancelado' => 'bg-rose-600',
                        default => 'bg-slate-500',
                    };
                    $conCobro = $ev->contrato && $ev->saldo_pendiente > 0 && $ev->estado !== 'Cancelado';
                    $al = $alertaEvento($ev);
                @endphp
                <div
                    class="flex items-center gap-2 px-3 py-2 {{ $ev->estado === 'Cancelado' ? 'opacity-60' : '' }} {{ $al[2] ?? '' }}">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span
                                class="font-semibold text-sm text-ink-primary truncate">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</span>
                            <span
                                class="shrink-0 px-1.5 py-px rounded text-[10px] font-bold text-white {{ $badgeColor }}">{{ $ev->estado }}</span>
                        </div>
                        <div class="text-xs text-ink-muted truncate">
                            {{ $ev->fecha_evento->format('d/m') }}
                            {{ substr($ev->hora_evento, 0, 5) }} - {{ $ev->servicio->nombre ?? 'N/A' }}
                            @if ($al)
                                <span
                                    class="ml-1 px-1.5 py-px rounded text-[10px] font-bold {{ $al[1] }}">{{ $al[0] }}</span>
                            @endif
                        </div>
                        <div class="text-xs tabular-nums">
                            @if ($ev->contrato)
                                <span class="text-ink-secondary">Bs
                                    {{ number_format($ev->monto_contratado, 0) }}</span>
                                <span class="text-emerald-600 dark:text-emerald-400">- a cuenta
                                    {{ number_format($ev->total_abonado, 0) }}</span>
                                <span
                                    class="font-semibold {{ $ev->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">-
                                    saldo {{ number_format($ev->saldo_pendiente, 0) }}</span>
                            @else
                                <span class="text-ink-muted italic">Sin contrato</span>
                            @endif
                        </div>
                    </div>
                    @if ($conCobro)
                        <x-ui.icon-button variant="primary" wire:click="abrirCobro({{ $ev->id }})"
                            title="Registrar cobro">
                            <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                        </x-ui.icon-button>
                    @endif
                    <x-ui.action-menu>
                        @if ($ev->contrato && $ev->contrato->codigo_verificacion)
                            <x-ui.action-menu-item icon="fa-solid fa-file-pdf"
                                href="{{ route('admin.contratos.pdf', $ev->contrato->codigo_verificacion) }}"
                                target="_blank">
                                Imprimir contrato
                            </x-ui.action-menu-item>
                        @endif
                        <x-ui.action-menu-item icon="fa-solid fa-pen-to-square"
                            wire:click="editar({{ $ev->id }})">
                            Editar evento
                        </x-ui.action-menu-item>
                        <x-ui.action-menu-item icon="fa-solid fa-receipt"
                            wire:click="abrirHistorialPagos({{ $ev->id }})">
                            Historial de pagos
                        </x-ui.action-menu-item>
                        @if (in_array($ev->estado, ['Confirmado', 'Reservado', 'Pendiente', 'Cotizado']))
                            <x-ui.action-menu-item icon="fa-solid fa-circle-check"
                                wire:click="marcarRealizado({{ $ev->id }})"
                                wire:confirm="{{ $ev->saldo_pendiente > 0 ? 'Este evento aún tiene saldo pendiente de Bs ' . number_format($ev->saldo_pendiente, 2) . '. ¿Marcarlo como realizado de todos modos?' : '¿Marcar este evento como realizado?' }}">
                                Marcar realizado
                            </x-ui.action-menu-item>
                        @endif
                        @if ($ev->estado !== 'Cancelado')
                            <x-ui.action-menu-item icon="fa-solid fa-ban"
                                wire:click="cancelarEvento({{ $ev->id }})"
                                wire:confirm="¿Cancelar este evento? Dejará de ocupar la agenda. Los cobros ya registrados se mantienen.">
                                Cancelar evento
                            </x-ui.action-menu-item>
                        @else
                            <x-ui.action-menu-item icon="fa-solid fa-rotate-left"
                                wire:click="reactivarEvento({{ $ev->id }})">
                                Reactivar evento
                            </x-ui.action-menu-item>
                        @endif
                        <x-ui.action-menu-item icon="fa-solid fa-trash-can" variant="danger"
                            wire:click="eliminarEvento({{ $ev->id }})"
                            wire:confirm="¿Eliminar este evento? Solo se puede si no tiene cobros ni pagos.">
                            Eliminar
                        </x-ui.action-menu-item>
                    </x-ui.action-menu>
                </div>
            @empty
                <p class="text-center py-8 text-ink-muted text-sm">No se encontraron eventos registrados.</p>
            @endforelse
            @if ($eventos->total() > 0)
                <div class="px-3 py-2 bg-surface-sunken text-xs font-bold tabular-nums">
                    <span class="text-ink-secondary uppercase">Total {{ $eventos->total() }}:</span>
                    <span class="text-ink-primary">Bs {{ number_format($totales['contratado'], 0) }}</span>
                    <span class="text-emerald-600 dark:text-emerald-400">- a cuenta
                        {{ number_format($totales['abonado'], 0) }}</span>
                    <span class="text-rose-600 dark:text-rose-400">- saldo
                        {{ number_format($totales['saldo'], 0) }}</span>
                </div>
            @endif
        </div>

        <x-slot:footer>
            <span>
                @if ($eventos->total() > 0)
                    Mostrando {{ $eventos->firstItem() }}–{{ $eventos->lastItem() }} de {{ $eventos->total() }}
                @else
                    Sin resultados
                @endif
            </span>
            <div>{{ $eventos->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Nuevo / Editar evento (compartido con la Agenda) --}}
    @include('livewire.comercial.partials.modal-evento')

    <!-- Modal Cobro rápido -->
    @if ($modalCobroOpen && $eventoCobro && $eventoCobro->contrato)
        @php
            $ctC = $eventoCobro->contrato;
            $saldoC = (float) $ctC->saldo_pendiente;
        @endphp
        <x-ui.modal title="Registrar cobro" icon="fa-solid fa-hand-holding-dollar" maxWidth="max-w-sm"
            onClose="cerrarCobro">
            <div class="px-5 py-4 space-y-3">
                <div>
                    <div class="text-sm font-semibold text-ink-primary truncate">
                        {{ $eventoCobro->cliente->nombre_completo ?? 'Cliente' }}</div>
                    <div class="text-xs text-ink-secondary">{{ $eventoCobro->codigo_evento }} - Contrato
                        {{ $ctC->numero_contrato }}</div>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-lg border border-outline bg-surface-sunken px-2 py-1.5">
                        <div class="text-[11px] text-ink-secondary">Contratado</div>
                        <div class="text-sm font-bold tabular-nums text-ink-primary">
                            {{ number_format((float) $ctC->monto_total, 0) }}</div>
                    </div>
                    <div class="rounded-lg border border-outline bg-surface-sunken px-2 py-1.5">
                        <div class="text-[11px] text-ink-secondary">Abonado</div>
                        <div class="text-sm font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                            {{ number_format((float) $ctC->total_pagado, 0) }}</div>
                    </div>
                    <div class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-2 py-1.5">
                        <div class="text-[11px] text-ink-secondary">Saldo</div>
                        <div class="text-sm font-bold tabular-nums text-rose-600 dark:text-rose-400">
                            {{ number_format($saldoC, 0) }}</div>
                    </div>
                </div>

                <x-ui.input label="Monto a cobrar (Bs)" required type="number" step="0.01" inputmode="decimal"
                    name="cobro_monto" wire:model="cobro_monto" />

                <div>
                    <label class="block text-sm font-semibold text-ink-primary mb-1">Método:</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['EFECTIVO' => 'Efectivo', 'QR' => 'QR'] as $val => $lbl)
                            <button type="button" wire:click="$set('cobro_metodo', '{{ $val }}')"
                                class="h-9 rounded-lg border text-sm font-semibold transition-colors {{ $cobro_metodo === $val ? 'border-gold-500 bg-gold-500/10 text-ink-primary' : 'border-outline bg-surface-card text-ink-secondary' }}">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <x-ui.input label="Nota (opcional)" name="cobro_obs" wire:model="cobro_obs"
                    placeholder="Referencia, comprobante..." />
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" wire:click="cerrarCobro">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" type="button" wire:click="guardarCobro"
                    wire:loading.attr="disabled" wire:target="guardarCobro">
                    <i class="fa-solid fa-check" wire:loading.remove wire:target="guardarCobro"></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardarCobro"></i>
                    <span>Registrar cobro</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Historial de Pagos (compacto) -->
    @if ($modalHistorialPagosOpen && $eventoHistorial)
        @php
            $hTotal = (float) $resumenFinanciero['monto_total'];
            $hAbonado = (float) $resumenFinanciero['total_abonado'];
            $hSaldo = (float) $resumenFinanciero['saldo_pendiente'];
            $hPct = $hTotal > 0 ? min(100, round(($hAbonado / $hTotal) * 100)) : 0;
        @endphp
        <x-ui.modal maxWidth="max-w-2xl" onClose="cerrarHistorialPagos">
            <x-slot:header>
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-ink-primary flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-gold-600 dark:text-gold-400"></i>
                        <span>Pagos del evento</span>
                        <span
                            class="text-gold-700 dark:text-gold-400 text-sm font-semibold">{{ $eventoHistorial->codigo_evento }}</span>
                    </h3>
                    <p class="text-xs text-ink-secondary truncate">
                        {{ $eventoHistorial->cliente->nombre_completo ?? 'Cliente N/A' }}
                        @if ($resumenFinanciero['tiene_contrato'])
                            - Contrato {{ $resumenFinanciero['numero_contrato'] }}
                        @else
                            - <span class="text-amber-600 dark:text-amber-400">Sin contrato</span>
                        @endif
                    </p>
                </div>
            </x-slot:header>

            <div class="px-5 py-4 space-y-3">
                {{-- Resumen en una sola franja --}}
                <div class="rounded-lg border border-outline bg-surface-card overflow-hidden">
                    <div class="grid grid-cols-3 divide-x divide-outline text-center">
                        <div class="px-2 py-2.5">
                            <div class="text-xs text-ink-secondary">Contratado</div>
                            <div class="text-base font-bold text-ink-primary tabular-nums">Bs
                                {{ number_format($hTotal, 2) }}</div>
                        </div>
                        <div class="px-2 py-2.5">
                            <div class="text-xs text-ink-secondary">Abonado</div>
                            <div class="text-base font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">Bs
                                {{ number_format($hAbonado, 2) }}</div>
                        </div>
                        <div class="px-2 py-2.5">
                            <div class="text-xs text-ink-secondary">Saldo</div>
                            <div
                                class="text-base font-bold tabular-nums {{ $hSaldo > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Bs {{ number_format($hSaldo, 2) }}</div>
                        </div>
                    </div>
                    @if ($hTotal > 0)
                        <div class="h-1 bg-surface-sunken">
                            <div class="h-full bg-emerald-500" style="width: {{ $hPct }}%"></div>
                        </div>
                    @endif
                </div>

                {{-- Lista de pagos --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-sm font-semibold text-ink-primary">Cobros registrados
                            ({{ count($pagosHistorial) }})</span>
                        @if ($hTotal > 0)
                            <span class="text-xs text-ink-secondary">{{ $hPct }}% pagado</span>
                        @endif
                    </div>

                    <div class="rounded-lg border border-outline divide-y divide-outline overflow-hidden">
                        @forelse ($pagosHistorial as $pago)
                            @php $anulado = $pago->estado !== 'Valido'; @endphp
                            <div class="flex items-center gap-3 px-3 py-2 {{ $anulado ? 'opacity-60' : '' }}">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span
                                            class="font-semibold text-gold-700 dark:text-gold-400">{{ $pago->numero_recibo }}</span>
                                        <span
                                            class="text-xs text-ink-secondary tabular-nums">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : '-' }}</span>
                                        @if ($anulado)
                                            <span
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                                <span
                                                    class="w-2 h-2 rounded-full bg-rose-500"></span>{{ $pago->estado }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-ink-secondary truncate"
                                        title="{{ $pago->observaciones }}">
                                        {{ $pago->metodo_pago }} - {{ $pago->user->name ?? 'Sistema' }}
                                        @if ($pago->observaciones)
                                            - {{ $pago->observaciones }}
                                        @endif
                                    </div>
                                </div>
                                <span
                                    class="text-sm font-bold tabular-nums shrink-0 {{ $anulado ? 'text-ink-muted line-through' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    Bs {{ number_format($pago->monto, 2) }}
                                </span>
                                <div class="flex items-center gap-1 shrink-0">
                                    <x-ui.icon-button variant="primary"
                                        href="{{ route('comercial.pagos.recibo', $pago->codigo_verificacion) }}"
                                        target="_blank" title="Ver / imprimir recibo">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </x-ui.icon-button>
                                    <x-ui.icon-button
                                        href="{{ route('comercial.pagos.pdf', $pago->codigo_verificacion) }}"
                                        target="_blank" title="Descargar PDF">
                                        <i class="fa-solid fa-file-pdf text-xs"></i>
                                    </x-ui.icon-button>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-sm text-ink-secondary">
                                <i class="fa-solid fa-receipt text-2xl text-ink-muted block mb-1.5"></i>
                                Todavía no hay cobros registrados.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <div class="mr-auto">
                    @if (
                        !empty($resumenFinanciero['tiene_contrato']) &&
                            !empty($resumenFinanciero['contrato_id']) &&
                            $eventoHistorial->cliente_id)
                        @php
                            $cToken = \App\Services\Security\DocumentToken::encodeId(
                                $eventoHistorial->cliente_id,
                                'cliente',
                            );
                            $ctrToken = \App\Services\Security\DocumentToken::encodeId(
                                $resumenFinanciero['contrato_id'],
                                'contrato',
                            );
                        @endphp
                        <x-ui.button variant="secondary" size="sm"
                            href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $cToken, 'contrato' => $ctrToken, 'tipo' => 'contrato']) }}"
                            target="_blank" title="Extracto de pagos del contrato en PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Extracto PDF</span>
                        </x-ui.button>
                    @endif
                </div>
                <x-ui.button size="sm" wire:click="cerrarHistorialPagos">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cerrar</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>