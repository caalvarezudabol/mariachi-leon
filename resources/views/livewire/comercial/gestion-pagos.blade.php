@php
    $metodosMap = ['EFECTIVO' => 'Efectivo', 'QR' => 'QR banco', 'TRANSFERENCIA' => 'Transferencia'];
    $usuarioActual = auth()->user();
    $puedeGestionarPagos =
        $usuarioActual && ($usuarioActual->can('gestionar-pagos') || $usuarioActual->hasRole('Administrador'));
@endphp

<div class="space-y-3">

    <x-ui.caja-resumen />

    {{-- Avisos --}}
    @if (session()->has('success_pago'))
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-xl text-emerald-600 dark:text-emerald-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-ink-primary">{{ session('success_pago') }}</p>

                </div>
            </div>
            @if ($ultimoRecibo)
                <div class="flex items-center gap-2 shrink-0">
                    <x-ui.button size="sm"
                        href="{{ route('comercial.pagos.recibo', $ultimoRecibo['codigo_verificacion']) }}"
                        target="_blank">
                        <i class="fa-solid fa-print"></i>
                        <span>Ver recibo</span>
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
                <i class="fa-solid fa-receipt text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Pagos</h1>
                <x-ui.badge variant="neutral">{{ $estados->sum() }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" wire:click="abrirModal">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Nuevo pago</span>
                <span class="sm:hidden">Nuevo</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Filtros: una sola fila --}}
        <x-slot:filters>
            <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar N° recibo, N° contrato, cliente..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select wire:model.live="metodo_filtro"
                        class="h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors">
                        <option value="">Todos los métodos</option>
                        @foreach ($metodosMap as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                    <x-ui.filter-chips>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                            Todos <span class="opacity-70">{{ $estados->sum() }}</span>
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', 'Valido')" :active="$estado_filtro === 'Valido'">
                            Válidos <span class="opacity-70">{{ $estados['Valido'] ?? 0 }}</span>
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', 'Anulado')" :active="$estado_filtro === 'Anulado'">
                            Anulados <span class="opacity-70">{{ $estados['Anulado'] ?? 0 }}</span>
                        </x-ui.filter-chip>
                    </x-ui.filter-chips>
                </div>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline">Recibo</th>
                    <th class="px-3 py-2 border-r border-outline">Cliente</th>
                    <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                    <th class="px-3 py-2 border-r border-outline">Método</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Monto</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($pagos as $p)
                    @php
                        $anulado = $p->estado === 'Anulado';
                        $clienteIdPago = $p->cliente_id ?: ($p->contrato ? $p->contrato->cliente_id : null);
                        $clienteTokenPago = $clienteIdPago
                            ? \App\Services\Security\DocumentToken::encodeId($clienteIdPago, 'cliente')
                            : null;
                        $ctrTokenPago = $p->contrato
                            ? \App\Services\Security\DocumentToken::encodeId($p->contrato->id, 'contrato')
                            : null;
                        $eventoPago = $p->evento ?: ($p->contrato ? $p->contrato->evento : null);
                        $eventoConcluido =
                            $eventoPago && in_array($eventoPago->estado, ['Realizado', 'Finalizado', 'Concluido']);
                        $puedeAnular = $puedeGestionarPagos && !$eventoConcluido;
                    @endphp
                    <tr
                        class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $anulado ? 'opacity-70' : '' }}">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span
                                class="font-semibold {{ $anulado ? 'line-through text-rose-700 dark:text-rose-400' : 'text-gold-700 dark:text-gold-400' }}">{{ $p->numero_recibo }}</span>
                            @if ($p->comprobante_referencia)
                                <div class="text-xs text-ink-secondary">Ref: {{ $p->comprobante_referencia }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="flex items-baseline gap-1.5 min-w-0">
                                @if ($clienteTokenPago)
                                    <a href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenPago, 'tipo' => 'general']) }}"
                                        target="_blank"
                                        class="font-semibold text-ink-primary hover:text-gold-700 dark:hover:text-gold-400 whitespace-nowrap"
                                        title="Historial de pagos del cliente en PDF">{{ $p->cliente->nombre_completo ?? 'N/A' }}</a>
                                @else
                                    <span
                                        class="font-semibold text-ink-primary whitespace-nowrap">{{ $p->cliente->nombre_completo ?? 'N/A' }}</span>
                                @endif
                                <span class="text-xs text-ink-secondary truncate max-w-[220px]">
                                    -
                                    @if ($p->contrato && $clienteTokenPago)
                                        <a href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenPago, 'contrato' => $ctrTokenPago, 'tipo' => 'contrato']) }}"
                                            target="_blank"
                                            class="hover:text-gold-700 dark:hover:text-gold-400 hover:underline"
                                            title="Extracto de pagos de este contrato en PDF">{{ $p->contrato->numero_contrato }}</a>
                                    @else
                                        {{ $p->contrato->numero_contrato ?? 'N/A' }}
                                    @endif
                                    @if ($p->evento)
                                        - {{ $p->evento->codigo_evento }}
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                            {{ $p->fecha_pago->format('d/m/Y') }}<span class="text-ink-secondary"> -
                                {{ $p->fecha_pago->format('H:i') }}</span>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-ink-primary">
                            {{ $metodosMap[$p->metodo_pago] ?? $p->metodo_pago }}</td>
                        <td
                            class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : 'text-emerald-700 dark:text-emerald-400' }}">
                            Bs {{ number_format($p->monto, 2) }}
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            @if ($anulado)
                                <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary cursor-help"
                                    title="Motivo: {{ $p->motivo_anulacion ?: 'Sin motivo' }} - Anulado por: {{ $p->anuladoPor ? $p->anuladoPor->name : 'Admin' }}">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>Anulado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>Válido
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            <div class="inline-flex items-center gap-1">
                                <x-ui.icon-button variant="primary"
                                    href="{{ route('comercial.pagos.recibo', $p->codigo_verificacion) }}"
                                    target="_blank" title="Ver e imprimir recibo oficial">
                                    <i class="fa-solid fa-print"></i>
                                </x-ui.icon-button>
                                @if (!$anulado)
                                    @if ($puedeAnular)
                                        <x-ui.icon-button variant="danger"
                                            wire:click="abrirModalAnular({{ $p->id }})" title="Anular pago">
                                            <i class="fa-solid fa-ban"></i>
                                        </x-ui.icon-button>
                                    @elseif ($eventoConcluido)
                                        <span
                                            class="inline-flex items-center justify-center w-9 h-9 text-ink-muted cursor-not-allowed"
                                            title="No se puede anular: el evento ya concluyó">
                                            <i class="fa-solid fa-lock text-xs"></i>
                                        </span>
                                    @endif
                                @endif
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="fa-solid fa-file-pdf"
                                        href="{{ route('comercial.pagos.pdf', $p->codigo_verificacion) }}"
                                        target="_blank">Recibo en PDF</x-ui.action-menu-item>
                                    @if ($clienteTokenPago)
                                        <x-ui.action-menu-item icon="fa-solid fa-file-invoice-dollar"
                                            href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenPago, 'tipo' => 'general']) }}"
                                            target="_blank">Historial de pagos del cliente</x-ui.action-menu-item>
                                    @endif
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-sm text-ink-secondary">No se encontraron
                            registros de pagos.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($pagos as $p)
                @php
                    $anulado = $p->estado === 'Anulado';
                    $clienteIdPago = $p->cliente_id ?: ($p->contrato ? $p->contrato->cliente_id : null);
                    $clienteTokenPago = $clienteIdPago
                        ? \App\Services\Security\DocumentToken::encodeId($clienteIdPago, 'cliente')
                        : null;
                    $eventoPago = $p->evento ?: ($p->contrato ? $p->contrato->evento : null);
                    $eventoConcluido =
                        $eventoPago && in_array($eventoPago->estado, ['Realizado', 'Finalizado', 'Concluido']);
                    $puedeAnular = $puedeGestionarPagos && !$eventoConcluido;
                @endphp
                <div class="flex items-center gap-3 px-4 py-2.5 {{ $anulado ? 'opacity-70' : '' }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span
                                class="font-semibold text-sm {{ $anulado ? 'line-through text-rose-700 dark:text-rose-400' : 'text-gold-700 dark:text-gold-400' }}">{{ $p->numero_recibo }}</span>
                            <span class="inline-flex items-center gap-1 text-xs text-ink-secondary">
                                <span
                                    class="w-2 h-2 rounded-full {{ $anulado ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>{{ $anulado ? 'Anulado' : 'Válido' }}
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-ink-primary truncate">
                            {{ $p->cliente->nombre_completo ?? 'N/A' }}</div>
                        <div class="text-xs text-ink-secondary truncate">{{ $p->contrato->numero_contrato ?? 'N/A' }}
                            - {{ $metodosMap[$p->metodo_pago] ?? $p->metodo_pago }} -
                            {{ $p->fecha_pago->format('d/m/Y H:i') }}</div>
                        <div
                            class="text-sm tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : 'text-emerald-700 dark:text-emerald-400' }}">
                            Bs {{ number_format($p->monto, 2) }}</div>
                    </div>
                    <x-ui.action-menu>
                        <x-ui.action-menu-item icon="fa-solid fa-print"
                            href="{{ route('comercial.pagos.recibo', $p->codigo_verificacion) }}" target="_blank">Ver
                            e imprimir recibo</x-ui.action-menu-item>
                        <x-ui.action-menu-item icon="fa-solid fa-file-pdf"
                            href="{{ route('comercial.pagos.pdf', $p->codigo_verificacion) }}" target="_blank">Recibo
                            en PDF</x-ui.action-menu-item>
                        @if ($clienteTokenPago)
                            <x-ui.action-menu-item icon="fa-solid fa-file-invoice-dollar"
                                href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenPago, 'tipo' => 'general']) }}"
                                target="_blank">Historial de pagos del cliente</x-ui.action-menu-item>
                        @endif
                        @if (!$anulado && $puedeAnular)
                            <x-ui.action-menu-item variant="danger" icon="fa-solid fa-ban"
                                wire:click="abrirModalAnular({{ $p->id }})">Anular pago</x-ui.action-menu-item>
                        @endif
                    </x-ui.action-menu>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron registros de pagos.</div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $pagos->firstItem() ?? 0 }}–{{ $pagos->lastItem() ?? 0 }} de
                {{ $pagos->total() }}</span>
            <div>{{ $pagos->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Nuevo Pago --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardar" wire:confirm="¿Está seguro de realizar el pago?">
            <x-ui.modal icon="fa-solid fa-receipt" title="Registrar Pago de Cliente" maxWidth="max-w-xl"
                onClose="$set('modalOpen', false)">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 px-5 py-4">
                    <x-ui.input label="N° Recibo" required readonly type="text" name="numero_recibo"
                        wire:model="numero_recibo" class="!bg-surface-hover font-semibold cursor-default" />

                    <x-ui.input label="Fecha y hora del pago" required type="datetime-local" name="fecha_pago"
                        wire:model="fecha_pago" />

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-ink-primary mb-1">
                            Contrato:
                            <span class="text-rose-500 font-black text-base leading-none">*</span>
                        </label>
                        <x-ui.select-search name="contrato_id" :options="$contratosOptions"
                            placeholder="Buscar contrato con saldo pendiente..." />
                        @error('contrato_id')
                            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <x-ui.input label="Monto (Bs)" required type="number" step="0.50" inputmode="decimal"
                        name="monto" wire:model="monto"
                        class="font-semibold text-emerald-700 dark:text-emerald-400" />

                    <div class="min-w-0">
                        <label class="block text-sm font-semibold text-ink-primary mb-1">Método: <span
                                class="text-rose-500 font-black text-base leading-none">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['EFECTIVO' => ['Efectivo', 'fa-money-bill-wave'], 'QR' => ['QR', 'fa-qrcode']] as $valor => [$texto, $ico])
                                <button type="button" wire:click="$set('metodo_pago', '{{ $valor }}')"
                                    class="h-9 rounded-lg border text-sm font-semibold flex items-center justify-center gap-1.5 transition-colors
                                        {{ $metodo_pago === $valor ? 'border-gold-500 bg-gold-500/10 text-ink-primary' : 'border-outline bg-surface-card text-ink-secondary hover:border-gold-500/50' }}">
                                    <i class="fa-solid {{ $ico }} text-xs"></i> {{ $texto }}
                                </button>
                            @endforeach
                        </div>
                        @error('metodo_pago')
                            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.input label="Comprobante" type="text" name="comprobante_referencia"
                            wire:model="comprobante_referencia" placeholder="N° de transacción (opcional)" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.textarea label="Observaciones" name="observaciones" rows="1"
                            wire:model="observaciones" />
                    </div>
                </div>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="$set('modalOpen', false)">
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

    {{-- Modal Anular Pago --}}
    @if ($modalAnularOpen && $pagoParaAnular)
        <form wire:submit.prevent="confirmarAnulacion">
            <x-ui.modal maxWidth="max-w-lg" onClose="$set('modalAnularOpen', false)">
                <x-slot:header>
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-9 h-9 rounded-lg bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-700 dark:text-rose-400 shrink-0">
                            <i class="fa-solid fa-ban"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-ink-primary leading-tight">Anular cobro / recibo</h3>
                            <p class="text-xs text-ink-secondary">Recibo N° {{ $pagoParaAnular->numero_recibo }}</p>
                        </div>
                    </div>
                </x-slot:header>

                <div class="px-6 py-5 space-y-4">
                    <div
                        class="flex items-start gap-2.5 p-3 rounded-lg border-l-[3px] border-rose-500 bg-rose-500/10 text-sm text-ink-primary">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 mt-0.5"></i>
                        <div class="space-y-1">
                            <p class="font-bold">Acción irreversible</p>
                            <p>El monto de <strong>Bs {{ number_format($pagoParaAnular->monto, 2) }}</strong> se
                                descontará del total pagado y volverá al saldo pendiente del contrato
                                <strong>{{ $pagoParaAnular->contrato->numero_contrato ?? 'N/A' }}</strong>.</p>
                            <p class="text-xs text-ink-secondary">Se registrará la fecha, la hora y el usuario que
                                autoriza en la bitácora de auditoría.</p>
                        </div>
                    </div>

                    <div class="bg-surface-sunken border border-outline rounded-lg p-3 text-sm space-y-1.5">
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Cliente:</span>
                            <span
                                class="font-semibold text-ink-primary text-right">{{ $pagoParaAnular->cliente->nombre_completo ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Monto del pago:</span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">Bs
                                {{ number_format($pagoParaAnular->monto, 2) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Fecha original:</span>
                            <span
                                class="text-ink-primary tabular-nums">{{ $pagoParaAnular->fecha_pago->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <x-ui.textarea label="Motivo de la anulación" required name="motivoAnulacion"
                        wire:model="motivoAnulacion" rows="1" class="!min-h-[72px]"
                        placeholder="Ej: error de digitación, pago rebotado, etc." />
                </div>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="$set('modalAnularOpen', false)">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </x-ui.button>
                    <x-ui.button type="submit" variant="danger" wire:loading.attr="disabled"
                        wire:target="confirmarAnulacion">
                        <i class="fa-solid fa-ban" wire:loading.remove wire:target="confirmarAnulacion"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="confirmarAnulacion"></i>
                        <span>Confirmar anulación</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </form>
    @endif
</div>