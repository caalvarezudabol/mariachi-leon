<div class="space-y-3">

    {{-- Aviso de pago registrado --}}
    @if (session()->has('success_pago'))
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-xl text-emerald-600 dark:text-emerald-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-ink-primary">{{ session('success_pago') }}</p>
                    <p class="text-xs text-ink-secondary">El pago quedó registrado y se actualizaron los saldos del
                        contrato.</p>
                </div>
            </div>
            @if ($ultimoRecibo)
                <div class="flex items-center gap-2 shrink-0">
                    <x-ui.button size="sm"
                        href="{{ route('comercial.pagos.recibo', $ultimoRecibo['codigo_verificacion']) }}"
                        target="_blank">
                        <i class="fa-solid fa-print"></i>
                        <span>Imprimir recibo {{ $ultimoRecibo['numero'] }}</span>
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
                <i class="fa-solid fa-file-signature text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Cotizaciones</h1>
                <x-ui.badge variant="neutral">{{ $estados->sum() }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nueva cotización</span>
                <span class="sm:hidden">Nueva</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Filtros: una sola fila --}}
        <x-slot:filters>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar N° cotización, cliente o evento..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                        Todos <span class="opacity-70">{{ $estados->sum() }}</span>
                    </x-ui.filter-chip>
                    @foreach (['Borrador' => 'Borrador', 'Enviada' => 'Enviada', 'Aceptada' => 'Aceptada', 'Rechazada' => 'Rechazada', 'Convertida en Contrato' => 'Convertida'] as $valor => $texto)
                        <x-ui.filter-chip wire:click="$set('estado_filtro', '{{ $valor }}')" :active="$estado_filtro === $valor">
                            {{ $texto }} <span class="opacity-70">{{ $estados[$valor] ?? 0 }}</span>
                        </x-ui.filter-chip>
                    @endforeach
                </x-ui.filter-chips>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline">Cotización</th>
                    <th class="px-3 py-2 border-r border-outline">Cliente</th>
                    <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Total</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Saldo</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($cotizaciones as $c)
                    @php
                        $contratoAceptado =
                            $c->contrato &&
                            in_array($c->contrato->estado, [
                                'Confirmado',
                                'Aceptado',
                                'En Ejecucion',
                                'Realizado',
                                'Finalizado',
                                'Vigente',
                                'Concluido',
                            ]);
                        $punto = match (true) {
                            in_array($c->estado, ['Aceptada', 'Convertida en Contrato']) => 'bg-emerald-500',
                            $c->estado === 'Enviada' => 'bg-amber-500',
                            $c->estado === 'Rechazada' => 'bg-rose-500',
                            default => 'bg-slate-400',
                        };
                        $saldo = $c->contrato ? $c->contrato->saldo_pendiente : $c->monto_total;
                        $saldoPagado = $c->contrato && $c->contrato->saldo_pendiente <= 0;
                    @endphp
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span
                                class="font-semibold text-gold-700 dark:text-gold-400">{{ $c->numero_cotizacion }}</span>
                            @if ($c->contrato)
                                <span class="text-xs text-ink-secondary"> - <i
                                        class="fa-solid fa-file-contract text-emerald-600 dark:text-emerald-400"></i>
                                    {{ $c->contrato->numero_contrato }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="flex items-baseline gap-1.5 min-w-0">
                                @if ($contratoAceptado)
                                    <a href="{{ route('admin.eventos', ['cliente_id' => $c->cliente_id]) }}"
                                        class="font-semibold text-ink-primary hover:text-gold-700 dark:hover:text-gold-400 whitespace-nowrap"
                                        title="Ir a Gestión de Eventos filtrando por este cliente">{{ $c->cliente->nombre_completo ?? 'N/A' }}</a>
                                @else
                                    <span
                                        class="font-semibold text-ink-primary whitespace-nowrap">{{ $c->cliente->nombre_completo ?? 'N/A' }}</span>
                                @endif
                                <span class="text-xs text-ink-secondary truncate max-w-[220px]">-
                                    {{ $c->evento->codigo_evento ?? '' }} - {{ $c->servicio->nombre ?? '' }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                            {{ $c->fecha_cotizacion->format('d/m/Y') }}</td>
                        <td class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums">
                            <div class="font-semibold text-ink-primary">Bs {{ number_format($c->monto_total, 2) }}
                            </div>
                            @if ($c->contrato)
                                <div class="text-xs text-ink-secondary">Abonado Bs
                                    {{ number_format($c->contrato->total_pagado, 2) }}</div>
                            @endif
                        </td>
                        <td
                            class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-bold {{ $saldoPagado ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                            Bs {{ number_format($saldo, 2) }}
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <span class="w-2 h-2 rounded-full {{ $punto }}"></span>{{ $c->estado }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            @if ($contratoAceptado)
                                <x-ui.icon-button variant="primary"
                                    href="{{ route('admin.eventos', ['cliente_id' => $c->cliente_id]) }}"
                                    title="Ir a Gestión de Eventos de {{ $c->cliente->nombre_completo ?? '' }}">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </x-ui.icon-button>
                            @else
                                <span class="text-ink-muted select-none" title="Sin opciones disponibles">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-sm text-ink-secondary">No se encontraron
                            cotizaciones registradas.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($cotizaciones as $c)
                @php
                    $contratoAceptado =
                        $c->contrato &&
                        in_array($c->contrato->estado, [
                            'Confirmado',
                            'Aceptado',
                            'En Ejecucion',
                            'Realizado',
                            'Finalizado',
                            'Vigente',
                            'Concluido',
                        ]);
                    $punto = match (true) {
                        in_array($c->estado, ['Aceptada', 'Convertida en Contrato']) => 'bg-emerald-500',
                        $c->estado === 'Enviada' => 'bg-amber-500',
                        $c->estado === 'Rechazada' => 'bg-rose-500',
                        default => 'bg-slate-400',
                    };
                    $saldo = $c->contrato ? $c->contrato->saldo_pendiente : $c->monto_total;
                    $saldoPagado = $c->contrato && $c->contrato->saldo_pendiente <= 0;
                @endphp
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span
                                class="font-semibold text-gold-700 dark:text-gold-400 text-sm">{{ $c->numero_cotizacion }}</span>
                            <span class="inline-flex items-center gap-1 text-xs text-ink-secondary">
                                <span class="w-2 h-2 rounded-full {{ $punto }}"></span>{{ $c->estado }}
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-ink-primary truncate">
                            {{ $c->cliente->nombre_completo ?? 'N/A' }}</div>
                        <div class="text-xs text-ink-secondary truncate">{{ $c->evento->codigo_evento ?? '' }} -
                            {{ $c->servicio->nombre ?? '' }}</div>
                        <div class="text-xs tabular-nums mt-0.5">
                            <span class="text-ink-primary font-semibold">Bs
                                {{ number_format($c->monto_total, 2) }}</span>
                            <span class="text-ink-secondary"> - Saldo </span>
                            <span
                                class="font-bold {{ $saldoPagado ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">Bs
                                {{ number_format($saldo, 2) }}</span>
                        </div>
                    </div>
                    @if ($contratoAceptado)
                        <x-ui.icon-button variant="primary"
                            href="{{ route('admin.eventos', ['cliente_id' => $c->cliente_id]) }}"
                            title="Gestión de Eventos">
                            <i class="fa-solid fa-calendar-days"></i>
                        </x-ui.icon-button>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron cotizaciones registradas.
                </div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $cotizaciones->firstItem() ?? 0 }}–{{ $cotizaciones->lastItem() ?? 0 }} de
                {{ $cotizaciones->total() }}</span>
            <div>{{ $cotizaciones->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Pago Rápido --}}
    @if ($modalPagoRapidoOpen && $pagoContrato)
        <x-ui.modal maxWidth="max-w-xl" onClose="$set('modalPagoRapidoOpen', false)">
            <x-slot:header>
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-700 dark:text-amber-400">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-bold text-ink-primary leading-tight">Registrar Pago Rápido</h3>
                        <p class="text-xs text-ink-secondary">Contrato N° {{ $pagoContrato->numero_contrato }}</p>
                    </div>
                </div>
            </x-slot:header>

            <div class="px-6 py-5 space-y-4">
                {{-- Resumen --}}
                <div class="bg-surface-sunken border border-outline rounded-lg p-3 text-sm space-y-2">
                    <div class="flex justify-between gap-3">
                        <span class="text-ink-secondary">Cliente:</span>
                        <span
                            class="font-semibold text-ink-primary text-right">{{ $pagoContrato->cliente->nombre_completo ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-ink-secondary">Evento:</span>
                        <span class="text-ink-primary text-right">{{ $pagoContrato->evento->codigo_evento ?? 'N/A' }}
                            -
                            {{ $pagoContrato->evento ? $pagoContrato->evento->fecha_evento->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-outline text-center">
                        <div class="bg-surface-card border border-outline rounded-lg p-2">
                            <div class="text-xs text-ink-secondary">Total</div>
                            <div class="text-sm font-bold text-ink-primary tabular-nums">Bs
                                {{ number_format($pagoContrato->monto_total, 2) }}</div>
                        </div>
                        <div class="bg-surface-card border border-outline rounded-lg p-2">
                            <div class="text-xs text-ink-secondary">Abonado</div>
                            <div class="text-sm font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">Bs
                                {{ number_format($pagoContrato->total_pagado, 2) }}</div>
                        </div>
                        <div class="bg-surface-card border border-outline rounded-lg p-2">
                            <div class="text-xs text-ink-secondary">Saldo</div>
                            <div class="text-sm font-bold text-rose-700 dark:text-rose-400 tabular-nums">Bs
                                {{ number_format($pagoContrato->saldo_pendiente, 2) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Tipo de pago --}}
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" wire:click="seleccionarTipoPago('acuenta')"
                        class="p-2.5 rounded-lg border text-center transition-colors {{ $tipoPago === 'acuenta' ? 'border-amber-500 bg-amber-500/10 text-amber-700 dark:text-amber-400 font-bold' : 'border-outline bg-surface-card text-ink-secondary hover:bg-surface-hover' }}">
                        <i class="fa-solid fa-hand-holding-dollar block mb-0.5"></i>
                        <span class="text-sm font-semibold">Pago a cuenta</span>
                    </button>
                    <button type="button" wire:click="seleccionarTipoPago('saldo')"
                        class="p-2.5 rounded-lg border text-center transition-colors {{ $tipoPago === 'saldo' ? 'border-emerald-500 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold' : 'border-outline bg-surface-card text-ink-secondary hover:bg-surface-hover' }}">
                        <i class="fa-solid fa-receipt block mb-0.5"></i>
                        <span class="text-sm font-semibold">Saldar total</span>
                        <div class="text-xs tabular-nums font-normal">Bs
                            {{ number_format($pagoContrato->saldo_pendiente, 2) }}</div>
                    </button>
                </div>

                {{-- Formulario --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input label="Monto a cobrar (Bs)" required type="number" step="0.50" name="pagoMonto"
                        wire:model.live="pagoMonto" :readonly="$tipoPago === 'saldo'" class="font-semibold" />

                    <x-ui.select label="Método de pago" required name="pagoMetodo" wire:model="pagoMetodo">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia bancaria</option>
                        <option value="QR">QR / Billetera móvil</option>
                        <option value="Tarjeta">Tarjeta débito/crédito</option>
                        <option value="Otro">Otro</option>
                    </x-ui.select>

                    <x-ui.input label="Fecha y hora de cobro" required type="datetime-local" name="pagoFecha"
                        wire:model="pagoFecha" />

                    <x-ui.input label="N° comprobante / referencia" type="text" name="pagoReferencia"
                        wire:model="pagoReferencia" placeholder="Ej: Transf. 981244" />

                    <div class="sm:col-span-2">
                        <x-ui.input label="Concepto / observaciones" type="text" name="pagoObservaciones"
                            wire:model="pagoObservaciones" placeholder="Ej: Primer abono o cancelación" />
                    </div>
                </div>

                {{-- Proyección --}}
                <div
                    class="flex items-center justify-between p-3 rounded-lg border border-outline bg-surface-sunken text-sm">
                    <span class="text-ink-secondary">Saldo restante proyectado:</span>
                    <span
                        class="font-bold tabular-nums {{ $pagoContrato->saldo_pendiente - (float) $pagoMonto <= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">
                        Bs {{ number_format(max(0, $pagoContrato->saldo_pendiente - (float) $pagoMonto), 2) }}
                    </span>
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" wire:click="$set('modalPagoRapidoOpen', false)">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button wire:click="guardarPagoRapido" wire:confirm="¿Está seguro de realizar el pago?"
                    wire:loading.attr="disabled" wire:target="guardarPagoRapido">
                    <i class="fa-solid fa-receipt" wire:loading.remove wire:target="guardarPagoRapido"></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardarPagoRapido"></i>
                    <span>Confirmar pago y emitir recibo</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Modal Crear/Editar Cotización --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardar">
            <x-ui.modal icon="fa-solid fa-file-signature" :title="$isEdit ? 'Editar Cotización ' . $numero_cotizacion : 'Nueva Cotización'" onClose="$set('modalOpen', false)">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
                    <x-ui.input label="N° Cotización" required readonly type="text" name="numero_cotizacion"
                        wire:model="numero_cotizacion" class="!bg-surface-hover font-semibold cursor-default" />

                    <x-ui.input label="Fecha de cotización" required type="date" name="fecha_cotizacion"
                        wire:model="fecha_cotizacion" />

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

                    <x-ui.input label="Precio base (Bs)" required type="number" step="0.50" name="precio_base"
                        wire:model.live="precio_base" />

                    <x-ui.input label="Descuento (Bs)" type="number" step="0.50" name="descuento"
                        wire:model.live="descuento" />

                    <x-ui.input label="Monto total cotizado (Bs)" readonly type="number" step="0.50"
                        name="monto_total" wire:model="monto_total"
                        class="!bg-surface-hover font-bold text-emerald-700 dark:text-emerald-400 cursor-default" />

                    <x-ui.select label="Estado" name="estado" wire:model="estado">
                        <option value="Borrador">Borrador</option>
                        <option value="Enviada">Enviada</option>
                        <option value="Aceptada">Aceptada</option>
                        <option value="Rechazada">Rechazada</option>
                        <option value="Convertida en Contrato">Convertida en Contrato</option>
                    </x-ui.select>

                    <div class="sm:col-span-2">
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
</div>