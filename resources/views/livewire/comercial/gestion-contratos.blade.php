@php
    $estadosMap = [
        'Borrador' => 'Borrador',
        'Pendiente de Confirmacion' => 'Pendiente de confirmación',
        'Confirmado' => 'Confirmado',
        'En Ejecucion' => 'En ejecución',
        'Realizado' => 'Realizado',
        'Cancelado' => 'Cancelado',
        'Finalizado' => 'Finalizado',
    ];
    $estadoPunto = function ($estado) {
        return match (true) {
            in_array($estado, ['Confirmado', 'Realizado', 'Finalizado']) => 'bg-emerald-500',
            in_array($estado, ['Pendiente de Confirmacion', 'En Ejecucion']) => 'bg-amber-500',
            $estado === 'Cancelado' => 'bg-rose-500',
            default => 'bg-slate-400',
        };
    };
@endphp

<div class="space-y-3">

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
                <i class="fa-solid fa-file-contract text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Contratos</h1>
                <x-ui.badge variant="neutral">{{ $estados->sum() }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nuevo contrato</span>
                <span class="sm:hidden">Nuevo</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Filtros: una sola fila --}}
        <x-slot:filters>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar N° contrato, cliente o evento..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                        Todos <span class="opacity-70">{{ $estados->sum() }}</span>
                    </x-ui.filter-chip>
                    @foreach ($estadosMap as $valor => $texto)
                        @if (($estados[$valor] ?? 0) > 0 || $estado_filtro === $valor)
                            <x-ui.filter-chip wire:click="$set('estado_filtro', '{{ $valor }}')"
                                :active="$estado_filtro === $valor">
                                {{ $texto }} <span class="opacity-70">{{ $estados[$valor] ?? 0 }}</span>
                            </x-ui.filter-chip>
                        @endif
                    @endforeach
                </x-ui.filter-chips>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline">Contrato</th>
                    <th class="px-3 py-2 border-r border-outline">Cliente</th>
                    <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Monto</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Saldo</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($contratos as $ctr)
                    @php
                        $clienteTokenCtr = $ctr->cliente_id
                            ? \App\Services\Security\DocumentToken::encodeId($ctr->cliente_id, 'cliente')
                            : null;
                        $ctrTokenCtr = \App\Services\Security\DocumentToken::encodeId($ctr->id, 'contrato');
                        $ctrEvTok = $ctr->evento
                            ? \App\Services\Security\DocumentToken::encodeId($ctr->evento->id, 'evento')
                            : null;
                        $saldoPagado = $ctr->saldo_pendiente <= 0;
                        $horaCtr = $ctr->hora_contrato
                            ? \Illuminate\Support\Str::substr($ctr->hora_contrato, 0, 5)
                            : null;
                    @endphp
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <a href="{{ route('admin.contratos.pdf', $ctr->codigo_verificacion) }}" target="_blank"
                                class="font-semibold text-gold-700 dark:text-gold-400 hover:underline"
                                title="Ver contrato oficial en PDF con QR">{{ $ctr->numero_contrato }}</a>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="flex items-baseline gap-1.5 min-w-0">
                                @if ($clienteTokenCtr)
                                    <a href="{{ route('admin.clientes.historial-contratos.pdf', $clienteTokenCtr) }}"
                                        target="_blank"
                                        class="font-semibold text-ink-primary hover:text-gold-700 dark:hover:text-gold-400 whitespace-nowrap"
                                        title="Historial de contratos del cliente en PDF">{{ $ctr->cliente->nombre_completo ?? 'N/A' }}</a>
                                @else
                                    <span
                                        class="font-semibold text-ink-primary whitespace-nowrap">{{ $ctr->cliente->nombre_completo ?? 'N/A' }}</span>
                                @endif
                                <span class="text-xs text-ink-secondary truncate max-w-[220px]">
                                    -
                                    @if ($clienteTokenCtr && $ctr->evento)
                                        <a href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenCtr, 'contrato' => $ctrTokenCtr, 'tipo' => 'contrato']) }}"
                                            target="_blank"
                                            class="hover:text-gold-700 dark:hover:text-gold-400 hover:underline"
                                            title="Pagos de este evento en PDF">{{ $ctr->evento->codigo_evento }}</a>
                                    @else
                                        {{ $ctr->evento->codigo_evento ?? '' }}
                                    @endif
                                    - {{ $ctr->servicio->nombre ?? '' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                            {{ $ctr->fecha_contrato?->format('d/m/Y') }}@if ($horaCtr)
                                <span class="text-ink-secondary"> - {{ $horaCtr }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums">
                            <div class="font-semibold text-ink-primary">Bs {{ number_format($ctr->monto_total, 2) }}
                            </div>
                            <div class="text-xs text-ink-secondary">Abonado Bs
                                {{ number_format($ctr->total_pagado, 2) }}</div>
                        </td>
                        <td
                            class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-bold {{ $saldoPagado ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                            Bs {{ number_format($ctr->saldo_pendiente, 2) }}
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <span
                                    class="w-2 h-2 rounded-full {{ $estadoPunto($ctr->estado) }}"></span>{{ $estadosMap[$ctr->estado] ?? $ctr->estado }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            <div class="inline-flex items-center gap-1">
                                <x-ui.icon-button variant="primary"
                                    wire:click="abrirHistorialPagos({{ $ctr->id }})" title="Pagos del contrato">
                                    <i class="fa-solid fa-receipt"></i>
                                </x-ui.icon-button>
                                <x-ui.icon-button wire:click="editar({{ $ctr->id }})" title="Editar contrato">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="fa-solid fa-file-contract"
                                        href="{{ route('admin.contratos.pdf', $ctr->codigo_verificacion) }}"
                                        target="_blank">Contrato PDF</x-ui.action-menu-item>
                                    {{-- Comisiones: oculto por ahora (el pago al personal se hace en Finanzas > Pagos a personal)
                                    @if ($ctr->evento)
                                        <x-ui.action-menu-item icon="fa-solid fa-users-viewfinder" wire:click="abrirComisiones({{ $ctr->id }})">Comisiones ({{ $ctr->evento->participantes->count() }})</x-ui.action-menu-item>
                                        <x-ui.action-menu-item icon="fa-solid fa-file-invoice-dollar" href="{{ route('admin.eventos.comisiones.pdf', $ctrEvTok) }}" target="_blank">Planilla de comisiones PDF</x-ui.action-menu-item>
                                    @endif
                                    --}}
                                    @if ($clienteTokenCtr)
                                        <x-ui.action-menu-item icon="fa-solid fa-file-pdf"
                                            href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenCtr, 'contrato' => $ctrTokenCtr, 'tipo' => 'contrato']) }}"
                                            target="_blank">Extracto de pagos PDF</x-ui.action-menu-item>
                                        <x-ui.action-menu-item icon="fa-solid fa-clock-rotate-left"
                                            href="{{ route('admin.clientes.historial-contratos.pdf', $clienteTokenCtr) }}"
                                            target="_blank">Historial del cliente PDF</x-ui.action-menu-item>
                                    @endif
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-sm text-ink-secondary">No se encontraron
                                contratos registrados.</td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </div>

            {{-- Lista compacta (móvil) --}}
            <div class="md:hidden divide-y divide-outline">
                @forelse ($contratos as $ctr)
                    @php
                        $clienteTokenCtr = $ctr->cliente_id
                            ? \App\Services\Security\DocumentToken::encodeId($ctr->cliente_id, 'cliente')
                            : null;
                        $ctrTokenCtr = \App\Services\Security\DocumentToken::encodeId($ctr->id, 'contrato');
                        $ctrEvTok = $ctr->evento
                            ? \App\Services\Security\DocumentToken::encodeId($ctr->evento->id, 'evento')
                            : null;
                        $saldoPagado = $ctr->saldo_pendiente <= 0;
                    @endphp
                    <div class="flex items-center gap-3 px-4 py-2.5">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.contratos.pdf', $ctr->codigo_verificacion) }}" target="_blank"
                                    class="font-semibold text-gold-700 dark:text-gold-400 text-sm">{{ $ctr->numero_contrato }}</a>
                                <span class="inline-flex items-center gap-1 text-xs text-ink-secondary">
                                    <span
                                        class="w-2 h-2 rounded-full {{ $estadoPunto($ctr->estado) }}"></span>{{ $estadosMap[$ctr->estado] ?? $ctr->estado }}
                                </span>
                            </div>
                            <div class="text-sm font-semibold text-ink-primary truncate">
                                {{ $ctr->cliente->nombre_completo ?? 'N/A' }}</div>
                            <div class="text-xs text-ink-secondary truncate">{{ $ctr->evento->codigo_evento ?? '' }} -
                                {{ $ctr->servicio->nombre ?? '' }}</div>
                            <div class="text-xs tabular-nums mt-0.5">
                                <span class="text-ink-primary font-semibold">Bs
                                    {{ number_format($ctr->monto_total, 2) }}</span>
                                <span class="text-ink-secondary"> - Saldo </span>
                                <span
                                    class="font-bold {{ $saldoPagado ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">Bs
                                    {{ number_format($ctr->saldo_pendiente, 2) }}</span>
                            </div>
                        </div>
                        <x-ui.action-menu>
                            <x-ui.action-menu-item icon="fa-solid fa-receipt"
                                wire:click="abrirHistorialPagos({{ $ctr->id }})">Pagos del
                                contrato</x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-pen-to-square"
                                wire:click="editar({{ $ctr->id }})">Editar</x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-file-contract"
                                href="{{ route('admin.contratos.pdf', $ctr->codigo_verificacion) }}"
                                target="_blank">Contrato PDF</x-ui.action-menu-item>
                            {{-- Comisiones: oculto por ahora (el pago al personal se hace en Finanzas > Pagos a personal)
                        @if ($ctr->evento)
                            <x-ui.action-menu-item icon="fa-solid fa-users-viewfinder" wire:click="abrirComisiones({{ $ctr->id }})">Comisiones</x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-file-invoice-dollar" href="{{ route('admin.eventos.comisiones.pdf', $ctrEvTok) }}" target="_blank">Planilla de comisiones PDF</x-ui.action-menu-item>
                        @endif
                        --}}
                            @if ($clienteTokenCtr)
                                <x-ui.action-menu-item icon="fa-solid fa-file-pdf"
                                    href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenCtr, 'contrato' => $ctrTokenCtr, 'tipo' => 'contrato']) }}"
                                    target="_blank">Extracto de pagos PDF</x-ui.action-menu-item>
                                <x-ui.action-menu-item icon="fa-solid fa-clock-rotate-left"
                                    href="{{ route('admin.clientes.historial-contratos.pdf', $clienteTokenCtr) }}"
                                    target="_blank">Historial del cliente PDF</x-ui.action-menu-item>
                            @endif
                        </x-ui.action-menu>
                    </div>
                @empty
                    <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron contratos registrados.</div>
                @endforelse
            </div>

            <x-slot:footer>
                <span>Mostrando {{ $contratos->firstItem() ?? 0 }}–{{ $contratos->lastItem() ?? 0 }} de
                    {{ $contratos->total() }}</span>
                <div>{{ $contratos->links() }}</div>
            </x-slot:footer>
        </x-ui.panel>

        {{-- Modal Crear/Editar Contrato --}}
        @if ($modalOpen)
            <form wire:submit.prevent="guardar">
                <x-ui.modal icon="fa-solid fa-file-contract" :title="$isEdit ? 'Editar Contrato ' . $numero_contrato : 'Nuevo Contrato'" onClose="$set('modalOpen', false)">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
                        <x-ui.input label="N° Contrato" required readonly type="text" name="numero_contrato"
                            wire:model="numero_contrato" class="!bg-surface-hover font-semibold cursor-default" />

                        <x-ui.input label="Fecha del contrato" required type="date" name="fecha_contrato"
                            wire:model="fecha_contrato" />

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-ink-primary mb-1">
                                Evento relacionado:
                                <span class="text-rose-500 font-black text-base leading-none">*</span>
                            </label>
                            <x-ui.select-search name="evento_id" :options="$eventosOptions"
                                placeholder="Buscar por código, cliente o servicio..." />
                            @error('evento_id')
                                <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <x-ui.input label="Monto total del contrato (Bs)" required type="number" step="0.50"
                            name="monto_total" wire:model.live="monto_total" class="font-semibold" />

                        <div>
                            <x-ui.input label="Pago inicial / seña (Bs)" required type="number" step="0.50"
                                name="pago_inicial" wire:model.live="pago_inicial" class="font-semibold" />
                            <p class="text-xs text-ink-secondary mt-1">Puede ser parcial o el 100% del total.</p>
                        </div>

                        <x-ui.input label="Total abonado (Bs)" readonly type="number" step="0.50"
                            name="total_pagado" wire:model="total_pagado"
                            class="!bg-surface-hover font-bold text-emerald-700 dark:text-emerald-400 cursor-default" />

                        <x-ui.input label="Saldo pendiente (Bs)" readonly type="number" step="0.50"
                            name="saldo_pendiente" wire:model="saldo_pendiente"
                            class="!bg-surface-hover font-bold text-rose-700 dark:text-rose-400 cursor-default" />

                        <x-ui.select label="Estado del contrato" name="estado" wire:model="estado">
                            @foreach ($estadosMap as $valor => $texto)
                                <option value="{{ $valor }}">{{ $texto }}</option>
                            @endforeach
                        </x-ui.select>

                        <div>
                            <x-ui.textarea label="Observaciones" name="observaciones" rows="1"
                                wire:model="observaciones" />
                        </div>
                    </div>

                    <x-slot:footer>
                        <x-ui.button variant="secondary" wire:click="$set('modalOpen', false)">
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

        {{-- Modal Pagos del Contrato --}}
        @if ($modalHistorialPagosOpen && $contratoHistorial)
            @php
                $cTokenModal = $contratoHistorial->cliente_id
                    ? \App\Services\Security\DocumentToken::encodeId($contratoHistorial->cliente_id, 'cliente')
                    : null;
                $ctrTokenModal = \App\Services\Security\DocumentToken::encodeId($contratoHistorial->id, 'contrato');
                $evHist = $contratoHistorial->evento;
            @endphp
            <x-ui.modal maxWidth="max-w-4xl" onClose="cerrarHistorialPagos">
                <x-slot:header>
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-9 h-9 rounded-lg bg-gold-500/10 border border-gold-500/30 flex items-center justify-center text-gold-700 dark:text-gold-400 shrink-0">
                            <i class="fa-solid fa-file-contract"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold text-ink-primary">Pagos del contrato</h3>
                                <x-ui.badge variant="gold">{{ $contratoHistorial->numero_contrato }}</x-ui.badge>
                                @if ($evHist)
                                    <x-ui.badge variant="neutral">{{ $evHist->codigo_evento }}</x-ui.badge>
                                @endif
                                <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                    <span
                                        class="w-2 h-2 rounded-full {{ $estadoPunto($contratoHistorial->estado) }}"></span>{{ $estadosMap[$contratoHistorial->estado] ?? $contratoHistorial->estado }}
                                </span>
                            </div>
                            <p class="text-xs text-ink-secondary truncate">
                                {{ $contratoHistorial->cliente->nombre_completo ?? 'Cliente N/A' }}
                                - {{ $contratoHistorial->servicio->nombre ?? 'Servicio general' }}
                                @if ($evHist && $evHist->lugar)
                                    - {{ $evHist->lugar }}
                                @endif
                            </p>
                        </div>
                    </div>
                </x-slot:header>

                <div class="px-6 py-5 space-y-4">
                    {{-- Resumen --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-surface-sunken border border-outline rounded-lg p-3">
                            <div class="text-xs text-ink-secondary">Monto contratado</div>
                            <div class="text-lg font-bold text-ink-primary tabular-nums">Bs
                                {{ number_format($resumenFinanciero['monto_total'], 2) }}</div>
                        </div>
                        <div class="bg-surface-sunken border border-outline rounded-lg p-3">
                            <div class="text-xs text-ink-secondary">Total abonado</div>
                            <div class="text-lg font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">Bs
                                {{ number_format($resumenFinanciero['total_abonado'], 2) }}</div>
                        </div>
                        <div class="bg-surface-sunken border border-outline rounded-lg p-3">
                            <div class="text-xs text-ink-secondary">Saldo pendiente</div>
                            <div
                                class="text-lg font-bold tabular-nums {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                Bs {{ number_format($resumenFinanciero['saldo_pendiente'], 2) }}</div>
                        </div>
                    </div>

                    {{-- Datos del evento --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div>
                            <div class="text-xs text-ink-secondary">Fecha del evento:</div>
                            <div class="font-semibold text-ink-primary tabular-nums">
                                @if ($evHist)
                                    {{ $evHist->fecha_evento ? $evHist->fecha_evento->format('d/m/Y') : '-' }}
                                    @if ($evHist->hora_inicio)
                                        - {{ \Carbon\Carbon::parse($evHist->hora_inicio)->format('H:i') }}
                                    @endif
                                @else
                                    <span class="text-ink-secondary font-normal">Sin evento asignado</span>
                                @endif
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-ink-secondary">Lugar:</div>
                            <div class="font-semibold text-ink-primary truncate" title="{{ $evHist->lugar ?? '' }}">
                                {{ $evHist->lugar ?? 'No especificado' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-ink-secondary">Firma del contrato:</div>
                            <div class="font-semibold text-ink-primary tabular-nums">
                                {{ $contratoHistorial->fecha_contrato ? $contratoHistorial->fecha_contrato->format('d/m/Y') : '-' }}
                                @if ($contratoHistorial->hora_contrato)
                                    - {{ \Illuminate\Support\Str::substr($contratoHistorial->hora_contrato, 0, 5) }}
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de pagos --}}
                    <div class="border border-outline rounded-lg overflow-hidden">
                        <div
                            class="px-3 py-2 border-b border-outline bg-surface-card flex items-center justify-between gap-2 flex-wrap">
                            <h4 class="text-sm font-semibold text-ink-primary flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-gold-600 dark:text-gold-400"></i>
                                Pagos registrados
                                <x-ui.badge variant="neutral">{{ count($pagosHistorial) }}</x-ui.badge>
                            </h4>
                            <span class="text-xs text-ink-secondary">Los pagos anulados no suman al saldo</span>
                        </div>
                        <x-ui.table>
                            <x-slot:header>
                                <th class="px-3 py-2 border-r border-outline">Recibo</th>
                                <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                                <th class="px-3 py-2 border-r border-outline">Concepto</th>
                                <th class="px-3 py-2 border-r border-outline text-right">Importe</th>
                                <th class="px-3 py-2 border-r border-outline">Método</th>
                                <th class="px-3 py-2 border-r border-outline">Estado</th>
                                <th class="px-3 py-2 w-px whitespace-nowrap text-right">Recibo</th>
                            </x-slot:header>
                            @forelse ($pagosHistorial as $pago)
                                <tr
                                    class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $pago->estado === 'Anulado' ? 'opacity-60' : '' }}">
                                    <td
                                        class="px-3 py-1.5 border-r border-outline whitespace-nowrap font-semibold text-gold-700 dark:text-gold-400">
                                        {{ $pago->numero_recibo }}</td>
                                    <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                                        {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : '-' }}
                                        @if ($pago->fecha_pago)
                                            <span class="text-ink-secondary"> -
                                                {{ $pago->fecha_pago->format('H:i') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-1.5 border-r border-outline max-w-[220px] truncate"
                                        title="{{ $pago->observaciones }}">
                                        {{ $pago->observaciones ?: 'Cobranza / abono del servicio' }}
                                    </td>
                                    <td
                                        class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold {{ $pago->estado === 'Anulado' ? 'text-ink-muted line-through' : 'text-emerald-700 dark:text-emerald-400' }}">
                                        Bs {{ number_format($pago->monto, 2) }}
                                    </td>
                                    <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                                        <div class="text-ink-primary">{{ $pago->metodo_pago }}</div>
                                        <div class="text-xs text-ink-secondary">{{ $pago->user->name ?? 'Sistema' }}</div>
                                    </td>
                                    <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                            <span
                                                class="w-2 h-2 rounded-full {{ $pago->estado === 'Valido' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $pago->estado === 'Valido' ? 'Válido' : $pago->estado }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <x-ui.icon-button variant="primary" class="!w-8 !h-8"
                                                href="{{ route('comercial.pagos.recibo', $pago->codigo_verificacion) }}"
                                                target="_blank" title="Ver / imprimir recibo">
                                                <i class="fa-solid fa-print"></i>
                                            </x-ui.icon-button>
                                            <x-ui.icon-button variant="danger" class="!w-8 !h-8"
                                                href="{{ route('comercial.pagos.pdf', $pago->codigo_verificacion) }}"
                                                target="_blank" title="Descargar recibo en PDF">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </x-ui.icon-button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-sm text-ink-secondary">
                                        <i class="fa-solid fa-receipt text-2xl text-ink-muted mb-2 block"></i>
                                        No se registran pagos asociados a este contrato o evento.
                                    </td>
                                </tr>
                            @endforelse
                        </x-ui.table>
                    </div>
                </div>

                <x-slot:footer>
                    <div class="flex items-center gap-2 flex-wrap mr-auto">
                        <x-ui.button size="sm" variant="secondary"
                            href="{{ route('admin.contratos.pdf', $contratoHistorial->codigo_verificacion) }}"
                            target="_blank">
                            <i class="fa-solid fa-file-contract"></i><span>Contrato PDF</span>
                        </x-ui.button>
                        @if ($cTokenModal)
                            <x-ui.button size="sm" variant="secondary"
                                href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $cTokenModal, 'contrato' => $ctrTokenModal, 'tipo' => 'contrato']) }}"
                                target="_blank">
                                <i class="fa-solid fa-file-pdf"></i><span>Extracto PDF</span>
                            </x-ui.button>
                            <x-ui.button size="sm" variant="secondary"
                                href="{{ route('admin.clientes.historial-contratos.pdf', $cTokenModal) }}"
                                target="_blank">
                                <i class="fa-solid fa-clock-rotate-left"></i><span>Historial del cliente</span>
                            </x-ui.button>
                            <x-ui.button size="sm" variant="secondary"
                                href="{{ route('admin.clientes.estado-cuenta', ['token' => $cTokenModal, 'contrato' => $ctrTokenModal]) }}">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Estado de cuenta web</span>
                            </x-ui.button>
                            {{-- Comisiones PDF: oculto por ahora
                        @if ($evHist)
                            <x-ui.button size="sm" variant="secondary" href="{{ route('admin.eventos.comisiones.pdf', \App\Services\Security\DocumentToken::encodeId($evHist->id, 'evento')) }}" target="_blank">
                                <i class="fa-solid fa-users-viewfinder"></i><span>Comisiones PDF</span>
                            </x-ui.button>
                        @endif
                        --}}
                        @endif
                    </div>
                    <x-ui.button variant="secondary" wire:click="cerrarHistorialPagos">
                        <i class="fa-solid fa-xmark"></i><span>Cerrar</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        @endif

        {{-- Comisiones y desmarcar pago: oculto por ahora. El pago al personal se hace en Finanzas > Pagos a personal.
         Para reactivar: cambiar false por true en la linea siguiente. --}}
        @if (false)
            {{-- Modal Comisiones a Músicos y Personal --}}
            @if ($modalComisionesOpen && $eventoComisiones)
                @php
                    $tokenEvComCtr =
                        $resumenComisiones['token'] ??
                        \App\Services\Security\DocumentToken::encodeId($eventoComisiones->id, 'evento');
                @endphp
                <x-ui.modal maxWidth="max-w-4xl" onClose="cerrarComisiones">
                    <x-slot:header>
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-700 dark:text-amber-400 shrink-0">
                                <i class="fa-solid fa-users-viewfinder"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-bold text-ink-primary">Comisiones a músicos y personal</h3>
                                    <x-ui.badge variant="gold">{{ $eventoComisiones->codigo_evento }}</x-ui.badge>
                                    @if ($eventoComisiones->contrato)
                                        <x-ui.badge
                                            variant="neutral">{{ $eventoComisiones->contrato->numero_contrato }}</x-ui.badge>
                                    @else
                                        <span class="text-xs italic text-amber-700 dark:text-amber-400">Sin contrato
                                            formal</span>
                                    @endif
                                    <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                        <span
                                            class="w-2 h-2 rounded-full {{ in_array($eventoComisiones->estado, ['Confirmado', 'Realizado']) ? 'bg-emerald-500' : ($eventoComisiones->estado === 'Cancelado' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>{{ $eventoComisiones->estado }}
                                    </span>
                                </div>
                                <p class="text-xs text-ink-secondary truncate">
                                    {{ $eventoComisiones->cliente->nombre_completo ?? 'N/A' }}
                                    - {{ $eventoComisiones->servicio->nombre ?? 'Servicio estándar' }}
                                    -
                                    {{ $eventoComisiones->fecha_evento ? $eventoComisiones->fecha_evento->format('d/m/Y') : '-' }}
                                    {{ $eventoComisiones->hora_evento }}
                                </p>
                            </div>
                        </div>
                    </x-slot:header>

                    <div class="px-6 py-5 space-y-4">
                        @if (session()->has('comision_success'))
                            <div
                                class="flex items-center gap-2 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
                                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                                <span>{{ session('comision_success') }}</span>
                            </div>
                        @endif

                        {{-- Resumen --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="bg-surface-sunken border border-outline rounded-lg p-3">
                                <div class="text-xs text-ink-secondary">Total del evento</div>
                                <div class="text-base font-bold text-ink-primary tabular-nums">Bs
                                    {{ number_format($resumenComisiones['monto_evento'] ?? 0, 2) }}</div>
                                <div class="text-xs text-ink-secondary">Cobrado al cliente</div>
                            </div>
                            <div class="bg-surface-sunken border border-outline rounded-lg p-3">
                                <div class="text-xs text-ink-secondary">Total comisiones</div>
                                <div class="text-base font-bold text-sky-700 dark:text-sky-400 tabular-nums">Bs
                                    {{ number_format($resumenComisiones['total_comisiones'] ?? 0, 2) }}</div>
                                <div class="text-xs text-ink-secondary">
                                    {{ $resumenComisiones['count_participantes'] ?? 0 }} integrante(s)</div>
                            </div>
                            <div class="bg-surface-sunken border border-outline rounded-lg p-3">
                                <div class="text-xs text-ink-secondary">Comisiones pagadas</div>
                                <div class="text-base font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">Bs
                                    {{ number_format($resumenComisiones['total_pagado'] ?? 0, 2) }}</div>
                                <div class="text-xs text-ink-secondary">{{ $resumenComisiones['count_pagados'] ?? 0 }}
                                    liquidado(s)</div>
                            </div>
                            <div class="bg-surface-sunken border border-outline rounded-lg p-3">
                                <div class="text-xs text-ink-secondary">Margen del grupo</div>
                                <div
                                    class="text-base font-bold tabular-nums {{ ($resumenComisiones['margen_grupo'] ?? 0) >= 0 ? 'text-gold-700 dark:text-gold-400' : 'text-rose-700 dark:text-rose-400' }}">
                                    Bs {{ number_format($resumenComisiones['margen_grupo'] ?? 0, 2) }}</div>
                                <div class="text-xs text-ink-secondary">Utilidad Mariachi León</div>
                            </div>
                        </div>

                        {{-- Integrantes --}}
                        <div class="border border-outline rounded-lg overflow-hidden">
                            <div
                                class="px-3 py-2 border-b border-outline bg-surface-card flex items-center justify-between gap-2 flex-wrap">
                                <h4 class="text-sm font-semibold text-ink-primary flex items-center gap-2">
                                    <i class="fa-solid fa-users-gear text-gold-600 dark:text-gold-400"></i>
                                    Integrantes y liquidación
                                    <x-ui.badge
                                        variant="neutral">{{ $eventoComisiones->participantes->count() }}</x-ui.badge>
                                </h4>
                                <span class="text-xs text-ink-secondary">Usa el botón de cada fila para marcar como pagado
                                    o pendiente</span>
                            </div>
                            <x-ui.table>
                                <x-slot:header>
                                    <th class="px-3 py-2 border-r border-outline">Integrante</th>
                                    <th class="px-3 py-2 border-r border-outline">Función</th>
                                    <th class="px-3 py-2 border-r border-outline text-right">Comisión</th>
                                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acción</th>
                                </x-slot:header>
                                @forelse ($eventoComisiones->participantes as $part)
                                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                                        <td class="px-3 py-1.5 border-r border-outline">
                                            <div class="font-semibold text-ink-primary">
                                                {{ $part->persona->nombre_completo ?? ($part->persona->nombre ?? 'N/A') }}
                                            </div>
                                            <div class="text-xs text-ink-secondary">CI:
                                                {{ $part->persona->ci_nit ?? '-' }} - Tel:
                                                {{ $part->persona->telefono ?? '-' }}</div>
                                        </td>
                                        <td class="px-3 py-1.5 border-r border-outline">
                                            <div class="text-ink-primary">{{ $part->funcion }}</div>
                                            <div class="text-xs text-ink-secondary">
                                                {{ $part->concepto_pago ?: 'Comisión por evento' }}</div>
                                        </td>
                                        <td
                                            class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold text-emerald-700 dark:text-emerald-400">
                                            Bs {{ number_format($part->monto_asignado, 2) }}
                                        </td>
                                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                                <span
                                                    class="w-2 h-2 rounded-full {{ $part->estado_distribucion === 'Distribuido' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>{{ $part->estado_distribucion === 'Distribuido' ? 'Pagado' : 'Pendiente' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                                            @if ($part->estado_distribucion === 'Distribuido')
                                                <x-ui.button size="sm" variant="secondary"
                                                    wire:click="solicitarDesmarcarComision({{ $part->id }})"
                                                    wire:loading.attr="disabled"
                                                    title="Desmarcar comisión y revertir pago con observación">
                                                    <i class="fa-solid fa-arrow-rotate-left"></i><span>Desmarcar</span>
                                                </x-ui.button>
                                            @else
                                                <x-ui.button size="sm" variant="success"
                                                    wire:click="marcarComisionPagada({{ $part->id }})"
                                                    wire:loading.attr="disabled" title="Marcar como pagado / distribuido">
                                                    <i class="fa-solid fa-check"></i><span>Marcar pagado</span>
                                                </x-ui.button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-8 text-sm text-ink-secondary">
                                            <i class="fa-solid fa-user-slash text-2xl text-ink-muted mb-2 block"></i>
                                            No se han asignado músicos ni personal a este evento.
                                            <div class="mt-2">
                                                <a href="{{ route('admin.eventos.participantes', $eventoComisiones->id) }}"
                                                    class="text-gold-700 dark:text-gold-400 hover:underline font-semibold">
                                                    <i class="fa-solid fa-user-plus mr-1"></i>Asignar integrantes ahora
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </x-ui.table>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center gap-2 flex-wrap mr-auto">
                            <x-ui.button size="sm"
                                href="{{ route('admin.eventos.comisiones.pdf', $tokenEvComCtr) }}" target="_blank">
                                <i class="fa-solid fa-file-pdf"></i><span>Planilla PDF</span>
                            </x-ui.button>
                            <x-ui.button size="sm" variant="secondary"
                                href="{{ route('admin.eventos.participantes', $eventoComisiones->id) }}">
                                <i class="fa-solid fa-users"></i><span>Gestionar integrantes</span>
                            </x-ui.button>
                        </div>
                        <x-ui.button variant="secondary" wire:click="cerrarComisiones">
                            <i class="fa-solid fa-xmark"></i><span>Cerrar</span>
                        </x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>
            @endif

            {{-- Modal Desmarcar comisión y revertir pago --}}
            @if ($modalDesmarcarOpen && $desmarcarParticipante)
                <form wire:submit.prevent="confirmarDesmarcarComision">
                    <x-ui.modal :nested="true" maxWidth="max-w-lg" onClose="cerrarModalDesmarcar">
                        <x-slot:header>
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-9 h-9 rounded-lg bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-700 dark:text-rose-400 shrink-0">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base font-bold text-ink-primary leading-tight">Desmarcar y revertir
                                        pago</h3>
                                    <p class="text-xs text-ink-secondary">Comisión de músico / personal</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="px-6 py-5 space-y-4">
                            <div class="bg-surface-sunken border border-outline rounded-lg p-3 text-sm space-y-1.5">
                                <div class="flex justify-between gap-3">
                                    <span class="text-ink-secondary">Participante:</span>
                                    <span
                                        class="font-semibold text-ink-primary text-right">{{ $desmarcarParticipante->persona->nombre_completo ?? ($desmarcarParticipante->persona->nombre ?? 'N/A') }}</span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span class="text-ink-secondary">Función:</span>
                                    <span class="text-ink-primary text-right">{{ $desmarcarParticipante->funcion }} -
                                        {{ $desmarcarParticipante->concepto_pago ?: 'Comisión por evento' }}</span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span class="text-ink-secondary">Monto de la comisión:</span>
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">Bs
                                        {{ number_format($desmarcarParticipante->monto_asignado, 2) }}</span>
                                </div>
                                @if ($eventoComisiones)
                                    <div class="flex justify-between gap-3">
                                        <span class="text-ink-secondary">Evento:</span>
                                        <span class="text-ink-primary">{{ $eventoComisiones->codigo_evento }}</span>
                                    </div>
                                @endif
                            </div>

                            <div
                                class="flex items-start gap-2.5 p-3 rounded-lg border-l-[3px] border-amber-500 bg-amber-500/10 text-sm text-ink-primary">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 mt-0.5"></i>
                                <p>Al desmarcar, la liquidación vuelve a <strong>Pendiente</strong>, se revierten los
                                    registros de pago y se actualizan los totales de comisiones pagadas. Queda asentado en
                                    la <strong>Bitácora de Auditoría</strong>.</p>
                            </div>

                            <x-ui.textarea label="Motivo / observación de la reversión" required
                                name="observacionDesmarcar" wire:model="observacionDesmarcar" rows="1"
                                class="!min-h-[72px]"
                                placeholder="Ej: error en registro, cambio de integrante, ajuste de liquidación" />
                        </div>

                        <x-slot:footer>
                            <x-ui.button type="button" variant="secondary" wire:click="cerrarModalDesmarcar">
                                <i class="fa-solid fa-xmark"></i><span>Cancelar</span>
                            </x-ui.button>
                            <x-ui.button type="submit" variant="danger" wire:loading.attr="disabled"
                                wire:target="confirmarDesmarcarComision">
                                <i class="fa-solid fa-rotate-left" wire:loading.remove
                                    wire:target="confirmarDesmarcarComision"></i>
                                <i class="fa-solid fa-spinner fa-spin" wire:loading
                                    wire:target="confirmarDesmarcarComision"></i>
                                <span>Confirmar reversión</span>
                            </x-ui.button>
                        </x-slot:footer>
                    </x-ui.modal>
                </form>
            @endif
        @endif
    </div>