<div class="space-y-4">
    <!-- Mensajes de Notificación -->
    @if (session()->has('success'))
        <div
            class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
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
                    href="{{ route('admin.eventos.pdf', ['search' => $search, 'cliente_id' => $cliente_filtro, 'estado' => $estado_filtro]) }}"
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
                    <x-ui.select wire:model.live="cliente_filtro" class="sm:w-56 shrink-0">
                        <option value="">Todos los clientes</option>
                        @foreach ($clientes as $cli)
                            <option value="{{ $cli->id }}">{{ $cli->nombre_completo }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.filter-chips>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                            Todos <span class="opacity-70">{{ $totalEventos }}</span>
                        </x-ui.filter-chip>
                        @foreach ($estados as $estado => $count)
                            <x-ui.filter-chip wire:click="$set('estado_filtro', '{{ $estado }}')"
                                :active="$estado_filtro === $estado">
                                {{ $estado }} <span class="opacity-70">{{ $count }}</span>
                            </x-ui.filter-chip>
                        @endforeach
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

        <!-- Tabla (desktop) -->
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="py-1.5 px-3 border-r border-outline">Evento</th>
                    <th class="py-1.5 px-3 border-r border-outline">Fecha y Hora</th>
                    <th class="py-1.5 px-3 border-r border-outline">Servicio</th>
                    <th class="py-1.5 px-3 border-r border-outline text-right">Monto</th>
                    <th class="py-1.5 px-3 border-r border-outline text-right">Saldo</th>
                    <th class="py-1.5 px-3 border-r border-outline">Estado</th>
                    <th class="py-1.5 px-3 text-right w-px whitespace-nowrap">Acciones</th>
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
                    @endphp
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="py-1.5 px-3 border-r border-outline">
                            <div class="text-xs text-gold-700 dark:text-gold-400 font-semibold">
                                {{ $ev->codigo_evento }}</div>
                            <div class="text-sm font-semibold text-ink-primary truncate max-w-[220px]">
                                {{ $ev->cliente->nombre_completo ?? 'N/A' }}</div>
                            @if ($waUrl)
                                <a href="{{ $waUrl }}" target="_blank"
                                    class="text-xs text-emerald-600 dark:text-emerald-400 font-medium hover:underline inline-flex items-center gap-1"
                                    title="Abrir WhatsApp de {{ $ev->cliente->nombre_completo ?? 'Cliente' }}">
                                    <i class="fa-brands fa-whatsapp"></i>
                                    <span>{{ $ev->telefono_contacto ?: $ev->cliente->telefono }}</span>
                                </a>
                            @endif
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-sm text-ink-secondary whitespace-nowrap">
                            {{ $ev->fecha_evento->format('d/m/Y') }} - {{ $ev->hora_evento }}
                            <div class="text-xs text-ink-muted">{{ $ev->duracion_horas }}h</div>
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-sm text-ink-secondary">
                            {{ $ev->servicio->nombre ?? 'N/A' }}
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-right whitespace-nowrap">
                            @if ($ev->contrato)
                                <div class="text-sm font-semibold text-ink-primary tabular-nums">Bs
                                    {{ number_format($ev->monto_contratado, 2) }}</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 tabular-nums">Abonado Bs
                                    {{ number_format($ev->total_abonado, 2) }}</div>
                            @else
                                <span class="text-xs text-ink-muted italic">Sin contrato</span>
                            @endif
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-right whitespace-nowrap">
                            <span
                                class="text-sm font-semibold tabular-nums {{ $ev->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Bs {{ number_format($ev->saldo_pendiente, 2) }}
                            </span>
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline">
                            @php
                                $dotColor = match (true) {
                                    in_array($ev->estado, ['Confirmado', 'Realizado', 'Finalizado'])
                                        => 'bg-emerald-500',
                                    in_array($ev->estado, ['Reservado', 'Cotizado']) => 'bg-amber-500',
                                    $ev->estado === 'Cancelado' => 'bg-rose-500',
                                    default => 'bg-slate-400',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-secondary">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $dotColor }}"></span>
                                {{ $ev->estado }}
                            </span>
                        </td>
                        <td class="py-1.5 px-3 text-right w-px whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button wire:click="abrirHistorialPagos({{ $ev->id }})"
                                    title="Historial de Pagos y Estado Financiero">
                                    <i class="fa-solid fa-receipt text-xs"></i>
                                </x-ui.icon-button>
                                <x-ui.icon-button wire:click="editar({{ $ev->id }})" title="Editar Evento">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
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
                        <td colspan="7" class="text-center py-8 text-ink-muted text-sm">
                            No se encontraron eventos registrados.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        <!-- Filas (móvil) -->
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
                @endphp
                <div class="px-4 py-2.5 space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span
                                class="text-xs text-gold-700 dark:text-gold-400 font-semibold">{{ $ev->codigo_evento }}</span>
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="font-semibold text-sm text-ink-primary truncate">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</span>
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $dotColor }}"></span>
                            </div>
                        </div>
                        <x-ui.action-menu>
                            <x-ui.action-menu-item icon="fa-solid fa-receipt"
                                wire:click="abrirHistorialPagos({{ $ev->id }})">
                                Historial de Pagos
                            </x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-pen-to-square"
                                wire:click="editar({{ $ev->id }})">
                                Editar Evento
                            </x-ui.action-menu-item>
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
                    <div class="text-xs text-ink-muted">
                        {{ $ev->fecha_evento->format('d/m/Y') }} - {{ $ev->hora_evento }} -
                        {{ $ev->servicio->nombre ?? 'N/A' }}
                    </div>
                    @if ($waUrl)
                        <a href="{{ $waUrl }}" target="_blank"
                            class="text-xs text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-1">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span>{{ $ev->telefono_contacto ?: $ev->cliente->telefono }}</span>
                        </a>
                    @endif
                    <div class="flex items-center gap-3 text-xs">
                        @if ($ev->contrato)
                            <span class="text-ink-secondary tabular-nums">Monto Bs
                                {{ number_format($ev->monto_contratado, 2) }}</span>
                        @endif
                        <span
                            class="font-semibold tabular-nums {{ $ev->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            Saldo Bs {{ number_format($ev->saldo_pendiente, 2) }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-center py-8 text-ink-muted text-sm">No se encontraron eventos registrados.</p>
            @endforelse
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