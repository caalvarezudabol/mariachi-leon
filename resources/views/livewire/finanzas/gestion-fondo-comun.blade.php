@php
    $totalMovs = $contTipos->sum();
    $bs = fn($n) => 'Bs ' . number_format((float) $n, 2);
    $esAporte = $tipo === 'INGRESO';
    $btnOn = 'border-gold-500 bg-gold-500/10 text-ink-primary';
    $btnOff = 'border-outline bg-surface-card text-ink-secondary hover:border-gold-500/50';
    $lbl = 'block text-sm font-semibold text-ink-primary mb-1';
    $campo =
        'w-full h-10 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors';
@endphp

<div class="space-y-3">

    {{-- Avisos --}}
    @if (session()->has('message'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-rose-500/30 bg-rose-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-triangle-exclamation text-lg text-rose-600 dark:text-rose-400"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <x-ui.caja-resumen />

    <x-ui.panel>
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-vault text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Fondo común</h1>
                <x-ui.badge variant="neutral">{{ $totalMovs }}</x-ui.badge>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="abrirModalCrear('EGRESO')"
                    class="h-8 px-3 inline-flex items-center gap-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-minus text-xs"></i><span>Retiro</span>
                </button>
                <button type="button" wire:click="abrirModalCrear('INGRESO')"
                    class="h-8 px-3 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i><span>Aporte</span>
                </button>
                <x-ui.action-menu>
                    <x-ui.action-menu-item icon="fa-solid fa-file-pdf" target="_blank"
                        href="{{ route('admin.reportes.fondo-comun.pdf', ['desde' => now()->startOfYear()->format('Y-m-d'), 'hasta' => now()->format('Y-m-d'), 'tipo' => $filtro_tipo]) }}">Exportar
                        PDF</x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-ban"
                        wire:click="$set('filtro_estado', '{{ $filtro_estado === 'Anulado' ? 'Valido' : 'Anulado' }}')">{{ $filtro_estado === 'Anulado' ? 'Ver vigentes' : 'Ver anulados' }}</x-ui.action-menu-item>
                </x-ui.action-menu>
            </div>
        </x-slot:header>

        <x-slot:filters>
            {{-- Saldo del fondo --}}
            <div class="grid grid-cols-3 gap-2 mb-3">
                <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 min-w-0">
                    <div class="text-xs text-ink-secondary truncate">Fondo total</div>
                    <div
                        class="text-base sm:text-lg font-bold tabular-nums text-emerald-700 dark:text-emerald-400 truncate">
                        {{ $bs($saldoActual) }}</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2 min-w-0">
                    <div class="text-xs text-ink-secondary truncate"><i class="fa-solid fa-money-bill-wave mr-1"></i>En
                        efectivo</div>
                    <div class="text-base sm:text-lg font-bold tabular-nums text-ink-primary truncate">
                        {{ $bs($fondoMetodo['Efectivo']) }}</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2 min-w-0">
                    <div class="text-xs text-ink-secondary truncate"><i class="fa-solid fa-qrcode mr-1"></i>En QR</div>
                    <div class="text-base sm:text-lg font-bold tabular-nums text-ink-primary truncate">
                        {{ $bs($fondoMetodo['QR']) }}</div>
                </div>
            </div>

            {{-- Filtros (una fila) --}}
            <div class="flex flex-wrap lg:flex-nowrap items-center gap-2">
                <div class="relative flex-1 min-w-[180px]">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('filtro_tipo', '')" :active="$filtro_tipo === ''">
                        Todos <span class="opacity-70">{{ $totalMovs }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('filtro_tipo', 'INGRESO')" :active="$filtro_tipo === 'INGRESO'">
                        Aportes <span class="opacity-70">{{ $contTipos['INGRESO'] ?? 0 }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('filtro_tipo', 'EGRESO')" :active="$filtro_tipo === 'EGRESO'">
                        Retiros <span class="opacity-70">{{ $contTipos['EGRESO'] ?? 0 }}</span>
                    </x-ui.filter-chip>
                </x-ui.filter-chips>
            </div>
            @if ($filtro_estado === 'Anulado')
                <div class="mt-2 text-xs text-rose-700 dark:text-rose-400">Mostrando solo movimientos anulados.</div>
            @endif
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                    <th class="px-3 py-2 border-r border-outline">Tipo</th>
                    <th class="px-3 py-2 border-r border-outline">Concepto</th>
                    <th class="px-3 py-2 border-r border-outline">Método</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Monto</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($movimientos as $mov)
                    @php
                        $anulado = $mov->estado === 'Anulado';
                        $aporte = $mov->tipo === 'INGRESO';
                    @endphp
                    <tr
                        class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $anulado ? 'opacity-70' : '' }}">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                            {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <span
                                    class="w-2 h-2 rounded-full {{ $aporte ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $aporte ? 'Aporte' : 'Retiro' }}
                            </span>
                            @if ($anulado)
                                <div class="text-xs text-rose-700 dark:text-rose-400"
                                    title="{{ $mov->motivo_anulacion }}">Anulado</div>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-xs">
                            <div class="font-semibold text-ink-primary truncate {{ $anulado ? 'line-through' : '' }}"
                                title="{{ $mov->concepto }}">{{ $mov->concepto }}</div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-ink-primary">
                            {{ $mov->metodo }}</td>
                        <td
                            class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : ($aporte ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400') }}">
                            {{ $aporte ? '+' : '-' }}{{ $bs($mov->monto) }}
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            @if (!$anulado)
                                <x-ui.icon-button variant="danger" wire:click="abrirModalAnular({{ $mov->id }})"
                                    title="Anular">
                                    <i class="fa-solid fa-ban"></i>
                                </x-ui.icon-button>
                            @else
                                <span class="text-ink-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-sm text-ink-secondary">
                            <i class="fa-solid fa-vault text-2xl text-ink-muted block mb-2"></i>
                            Aún no hay movimientos en el fondo.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($movimientos as $mov)
                @php
                    $anulado = $mov->estado === 'Anulado';
                    $aporte = $mov->tipo === 'INGRESO';
                @endphp
                <div class="flex items-center gap-3 px-4 py-2.5 {{ $anulado ? 'opacity-70' : '' }}">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs text-ink-secondary inline-flex items-center gap-1">
                            <span
                                class="w-2 h-2 rounded-full {{ $aporte ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $aporte ? 'Aporte' : 'Retiro' }}
                            @if ($anulado)
                                - Anulado
                            @endif
                        </div>
                        <div
                            class="text-sm font-semibold text-ink-primary truncate {{ $anulado ? 'line-through' : '' }}">
                            {{ $mov->concepto }}</div>
                        <div class="text-xs text-ink-secondary truncate">
                            {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }} - {{ $mov->metodo }}</div>
                        <div
                            class="text-sm tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : ($aporte ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400') }}">
                            {{ $aporte ? '+' : '-' }}{{ $bs($mov->monto) }}
                        </div>
                    </div>
                    @if (!$anulado)
                        <x-ui.action-menu>
                            <x-ui.action-menu-item variant="danger" icon="fa-solid fa-ban"
                                wire:click="abrirModalAnular({{ $mov->id }})">Anular
                                movimiento</x-ui.action-menu-item>
                        </x-ui.action-menu>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">Aún no hay movimientos en el fondo.</div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $movimientos->firstItem() ?? 0 }}–{{ $movimientos->lastItem() ?? 0 }} de
                {{ $movimientos->total() }}</span>
            <div>{{ $movimientos->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Aporte / Retiro --}}
    @if ($modalOpen)
        <form wire:submit.prevent="guardarMovimiento">
            <x-ui.modal :icon="$esAporte ? 'fa-solid fa-plus' : 'fa-solid fa-minus'" :title="$esAporte ? 'Aporte al fondo' : 'Retiro del fondo'" maxWidth="max-w-md" onClose="cerrarModal">
                <div class="px-5 py-4 space-y-3.5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $lbl }}">Monto: <span
                                    class="text-rose-500 font-black text-base leading-none">*</span></label>
                            <input type="number" step="0.01" inputmode="decimal"
                                wire:model.live.debounce.300ms="monto" placeholder="0.00"
                                class="{{ $campo }} font-semibold text-base tabular-nums">
                            @error('monto')
                                <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="{{ $lbl }}">Método:</label>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach (['Efectivo' => 'fa-money-bill-wave', 'QR' => 'fa-qrcode'] as $m => $ic)
                                    <button type="button" wire:click="$set('metodo', '{{ $m }}')"
                                        class="h-10 rounded-lg border text-sm font-semibold flex items-center justify-center gap-1.5 transition-colors {{ $metodo === $m ? $btnOn : $btnOff }}">
                                        <i class="fa-solid {{ $ic }} text-xs"></i> {{ $m }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-ink-secondary">
                        @if ($esAporte)
                            Sale de lo disponible en {{ $metodo === 'QR' ? 'QR' : 'efectivo' }} y queda reservado.
                        @else
                            Hay {{ $bs($fondoMetodo[$metodo] ?? 0) }} en {{ $metodo === 'QR' ? 'QR' : 'efectivo' }}.
                            Vuelve a lo disponible.
                        @endif
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                        <div class="sm:col-span-3">
                            <label class="{{ $lbl }}">Detalle:</label>
                            <input type="text" wire:model="concepto" placeholder="Opcional"
                                class="{{ $campo }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $lbl }}">Fecha: <span
                                    class="text-rose-500 font-black text-base leading-none">*</span></label>
                            <input type="date" wire:model="fecha" class="{{ $campo }}">
                        </div>
                    </div>
                </div>
                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="cerrarModal">
                        <i class="fa-solid fa-xmark"></i><span>Cancelar</span>
                    </x-ui.button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="guardarMovimiento"
                        class="h-9 px-4 inline-flex items-center gap-2 rounded-lg text-white text-sm font-semibold transition-colors {{ $esAporte ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }}">
                        <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardarMovimiento"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardarMovimiento"></i>
                        <span>{{ $esAporte ? 'Guardar aporte' : 'Guardar retiro' }}</span>
                    </button>
                </x-slot:footer>
            </x-ui.modal>
        </form>
    @endif

    {{-- Modal Anular movimiento --}}
    @if ($modalAnularOpen && $movimiento_a_anular)
        <form wire:submit.prevent="confirmarAnulacion">
            <x-ui.modal maxWidth="max-w-lg" onClose="cerrarModalAnular">
                <x-slot:header>
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-9 h-9 rounded-lg bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-700 dark:text-rose-400 shrink-0">
                            <i class="fa-solid fa-ban"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-ink-primary leading-tight">Anular movimiento del fondo
                            </h3>
                            <p class="text-xs text-ink-secondary">
                                {{ $movimiento_a_anular->tipo === 'INGRESO' ? 'Aporte' : 'Retiro' }}
                                #{{ $movimiento_a_anular->id }}</p>
                        </div>
                    </div>
                </x-slot:header>

                <div class="px-6 py-5 space-y-4">
                    <div
                        class="flex items-start gap-2.5 p-3 rounded-lg border-l-[3px] border-rose-500 bg-rose-500/10 text-sm text-ink-primary">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 mt-0.5"></i>
                        <div class="space-y-1">
                            <p class="font-bold">Acción irreversible</p>
                            <p>Se revertirá el efecto sobre el saldo del fondo y quedará registrado en la bitácora de
                                auditoría.</p>
                        </div>
                    </div>

                    <div class="bg-surface-sunken border border-outline rounded-lg p-3 text-sm space-y-1.5">
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Concepto:</span>
                            <span
                                class="font-semibold text-ink-primary text-right">{{ $movimiento_a_anular->concepto }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Monto:</span>
                            <span
                                class="font-bold tabular-nums {{ $movimiento_a_anular->tipo === 'INGRESO' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">Bs
                                {{ number_format($movimiento_a_anular->monto, 2) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-ink-secondary">Fecha - método:</span>
                            <span
                                class="text-ink-primary tabular-nums">{{ \Carbon\Carbon::parse($movimiento_a_anular->fecha)->format('d/m/Y') }}
                                - {{ $movimiento_a_anular->metodo }}</span>
                        </div>
                    </div>

                    <x-ui.textarea label="Motivo de la anulación" required name="motivo_anulacion"
                        wire:model="motivo_anulacion" rows="1" class="!min-h-[72px]"
                        placeholder="Explica por qué se anula este movimiento" />
                </div>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="cerrarModalAnular">
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