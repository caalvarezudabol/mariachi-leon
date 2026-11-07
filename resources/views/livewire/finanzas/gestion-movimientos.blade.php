@php
    $totalMovs = $contTipos->sum();
    $campoFiltro =
        'h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors';
    $esIngreso = $tipo === 'INGRESO';
    $paramsExport = [
        'desde' => $fecha_desde,
        'hasta' => $fecha_hasta,
        'tipo' => $filtro_tipo,
        'clasificacion' => $filtro_clasificacion,
        'estado' => $filtro_estado,
    ];

    // Etiqueta del tipo de movimiento: Ingreso extra / Gasto operativo / Gasto de evento
    $etiquetaTipo = function ($mov) {
        if ($mov->tipo === 'INGRESO') {
            return 'Ingreso extra';
        }
        return $mov->clasificacion === 'EVENTO' ? 'Gasto de evento' : 'Gasto operativo';
    };
@endphp

<div class="space-y-3">

    <x-ui.caja-resumen />

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

    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-money-bill-transfer text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Ingresos / Egresos</h1>
                <x-ui.badge variant="neutral">{{ $totalMovs }}</x-ui.badge>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="abrirModalCrear('INGRESO', 'OPERATIVO')"
                    class="h-8 px-3 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span class="hidden sm:inline">Ingreso extra</span>
                    <span class="sm:hidden">Ingreso</span>
                </button>
                <button type="button" wire:click="abrirModalCrear('EGRESO', 'OPERATIVO')"
                    class="h-8 px-3 inline-flex items-center gap-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span class="hidden sm:inline">Nuevo gasto</span>
                    <span class="sm:hidden">Gasto</span>
                </button>
                <x-ui.action-menu>
                    <x-ui.action-menu-item icon="fa-solid fa-file-pdf" target="_blank"
                        href="{{ route('admin.reportes.gastos.pdf', $paramsExport) }}">Exportar
                        PDF</x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-file-excel"
                        href="{{ route('admin.finanzas.movimientos.excel', $paramsExport) }}">Exportar
                        Excel</x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-ban"
                        wire:click="$set('filtro_estado', '{{ $filtro_estado === 'Anulado' ? 'Valido' : 'Anulado' }}')">{{ $filtro_estado === 'Anulado' ? 'Ver vigentes' : 'Ver anulados' }}</x-ui.action-menu-item>
                </x-ui.action-menu>
            </div>
        </x-slot:header>

        {{-- Resumen + filtros --}}
        <x-slot:filters>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-3">
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Total egresos</span>
                        <i class="fa-solid fa-arrow-trend-down text-rose-600 dark:text-rose-400"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-rose-700 dark:text-rose-400">Bs
                        {{ number_format($totalesConsulta['egresos'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Operativos + de eventos</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Gastos operativos</span>
                        <i class="fa-solid fa-building-columns text-ink-muted"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-ink-primary">Bs
                        {{ number_format($totalesConsulta['operativos'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Alquiler, servicios, mantenimiento</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Gastos de eventos</span>
                        <i class="fa-solid fa-car-side text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-amber-700 dark:text-amber-400">Bs
                        {{ number_format($totalesConsulta['eventos'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Combustible, viáticos, refrigerios</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-ink-secondary">
                        <span>Ingresos extra</span>
                        <i class="fa-solid fa-receipt text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div class="text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">Bs
                        {{ number_format($totalesConsulta['ingresos'], 2) }}</div>
                    <div class="text-xs text-ink-secondary">Fuera de contratos regulares</div>
                </div>
            </div>

            <div class="flex flex-wrap lg:flex-nowrap items-center gap-2">
                <div class="relative flex-1 min-w-[180px]">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <input type="date" wire:model.live="fecha_desde" title="Desde"
                    class="{{ $campoFiltro }} w-[132px]">
                <input type="date" wire:model.live="fecha_hasta" title="Hasta"
                    class="{{ $campoFiltro }} w-[132px]">
                <x-ui.filter-chips>
                    <x-ui.filter-chip wire:click="$set('filtro_tipo', '')" :active="$filtro_tipo === ''">
                        Todos <span class="opacity-70">{{ $totalMovs }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('filtro_tipo', 'EGRESO')" :active="$filtro_tipo === 'EGRESO'">
                        Gastos <span class="opacity-70">{{ $contTipos['EGRESO'] ?? 0 }}</span>
                    </x-ui.filter-chip>
                    <x-ui.filter-chip wire:click="$set('filtro_tipo', 'INGRESO')" :active="$filtro_tipo === 'INGRESO'">
                        Ingresos <span class="opacity-70">{{ $contTipos['INGRESO'] ?? 0 }}</span>
                    </x-ui.filter-chip>
                </x-ui.filter-chips>
                <x-ui.icon-button wire:click="limpiarFiltros" title="Restablecer filtros">
                    <i class="fa-solid fa-rotate-left"></i>
                </x-ui.icon-button>
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
                    <th class="px-3 py-2 border-r border-outline">Evento</th>
                    <th class="px-3 py-2 border-r border-outline">Proveedor</th>
                    <th class="px-3 py-2 border-r border-outline text-right">Monto</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($movimientos as $mov)
                    @php
                        $anulado = $mov->estado === 'Anulado';
                        $ingreso = $mov->tipo === 'INGRESO';
                    @endphp
                    <tr
                        class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $anulado ? 'opacity-70' : '' }}">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                            {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}<span class="text-ink-secondary">
                                - {{ $mov->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                <span
                                    class="w-2 h-2 rounded-full {{ $ingreso ? 'bg-emerald-500' : ($mov->clasificacion === 'EVENTO' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>{{ $etiquetaTipo($mov) }}
                            </span>
                            <div class="text-xs text-ink-secondary">
                                {{ strtoupper($mov->metodo_pago) === 'EFECTIVO' ? 'Efectivo' : (str_contains(strtoupper($mov->metodo_pago), 'QR') ? 'QR' : $mov->metodo_pago) }}
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-xs">
                            <div class="font-semibold text-ink-primary truncate {{ $anulado ? 'line-through' : '' }}"
                                title="{{ $mov->concepto }}">{{ $mov->concepto }}</div>
                            <div class="text-xs text-gold-700 dark:text-gold-400 truncate">{{ $mov->categoria }}</div>
                            @if ($mov->descripcion)
                                <div class="text-xs text-ink-secondary truncate" title="{{ $mov->descripcion }}">
                                    {{ $mov->descripcion }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            @if ($mov->evento)
                                <div class="font-semibold text-ink-primary">{{ $mov->evento->codigo_evento }}</div>
                                <div class="text-xs text-ink-secondary truncate max-w-[160px]">
                                    {{ $mov->evento->cliente->nombre_completo ?? 'Cliente S/N' }}</div>
                            @else
                                <span class="text-ink-muted">General</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-[160px]">
                            <div class="text-ink-primary truncate">{{ $mov->proveedor ?: '-' }}</div>
                            @if ($mov->numero_comprobante)
                                <div class="text-xs text-ink-secondary truncate tabular-nums">N°
                                    {{ $mov->numero_comprobante }}</div>
                            @endif
                        </td>
                        <td
                            class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : ($ingreso ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400') }}">
                            {{ $ingreso ? '+' : '-' }}Bs {{ number_format($mov->monto, 2) }}
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            @if ($anulado)
                                <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary cursor-help"
                                    title="Motivo: {{ $mov->motivo_anulacion ?: 'Sin motivo' }} - Anulado por: {{ $mov->anuladoPor->name ?? 'Admin' }}">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>Anulado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-sm text-ink-primary">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>Válido
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            @if (!$anulado)
                                <x-ui.icon-button variant="danger" wire:click="abrirModalAnular({{ $mov->id }})"
                                    title="Anular movimiento">
                                    <i class="fa-solid fa-ban"></i>
                                </x-ui.icon-button>
                            @else
                                <span class="text-ink-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-sm text-ink-secondary">
                            <i class="fa-solid fa-inbox text-2xl text-ink-muted block mb-2"></i>
                            No se encontraron movimientos. Ajusta los filtros o registra un gasto o ingreso.
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
                    $ingreso = $mov->tipo === 'INGRESO';
                @endphp
                <div class="flex items-center gap-3 px-4 py-2.5 {{ $anulado ? 'opacity-70' : '' }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-xs text-ink-secondary">
                                <span
                                    class="w-2 h-2 rounded-full {{ $ingreso ? 'bg-emerald-500' : ($mov->clasificacion === 'EVENTO' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>{{ $etiquetaTipo($mov) }}
                            </span>
                            @if ($anulado)
                                <span class="text-xs text-rose-700 dark:text-rose-400">Anulado</span>
                            @endif
                        </div>
                        <div
                            class="text-sm font-semibold text-ink-primary truncate {{ $anulado ? 'line-through' : '' }}">
                            {{ $mov->concepto }}</div>
                        <div class="text-xs text-ink-secondary truncate">
                            {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }} - {{ $mov->categoria }}
                            @if ($mov->evento)
                                - {{ $mov->evento->codigo_evento }}
                            @endif
                        </div>
                        <div
                            class="text-sm tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : ($ingreso ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400') }}">
                            {{ $ingreso ? '+' : '-' }}Bs {{ number_format($mov->monto, 2) }}
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
                <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron movimientos.</div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $movimientos->firstItem() ?? 0 }}–{{ $movimientos->lastItem() ?? 0 }} de
                {{ $movimientos->total() }}</span>
            <div>{{ $movimientos->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal Registrar movimiento --}}
    @if ($modalOpen)
        @php
            $lbl = 'block text-sm font-semibold text-ink-primary mb-1';
            $campo =
                'w-full h-10 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors';
            $btnOn = 'border-gold-500 bg-gold-500/10 text-ink-primary';
            $btnOff = 'border-outline bg-surface-card text-ink-secondary hover:border-gold-500/50';
        @endphp
        <form wire:submit.prevent="guardarMovimiento">
            <x-ui.modal :icon="$esIngreso ? 'fa-solid fa-arrow-down-long' : 'fa-solid fa-receipt'" :title="$esIngreso ? 'Ingreso extra' : 'Nuevo gasto'" maxWidth="max-w-lg" onClose="cerrarModal">

                <div class="px-5 py-4 space-y-3.5">

                    {{-- Monto + método --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $lbl }}">Monto: <span
                                    class="text-rose-500 font-black text-base leading-none">*</span></label>
                            <input type="number" step="0.01" inputmode="decimal" wire:model="monto"
                                placeholder="0.00" class="{{ $campo }} font-semibold text-base tabular-nums">
                            @error('monto')
                                <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="{{ $lbl }}">Método:</label>
                            <div class="grid grid-cols-2 gap-1.5">
                                <button type="button" wire:click="$set('metodo_pago','EFECTIVO')"
                                    class="h-10 rounded-lg border text-sm font-semibold transition-colors {{ $metodo_pago === 'EFECTIVO' ? $btnOn : $btnOff }}">Efectivo</button>
                                <button type="button" wire:click="$set('metodo_pago','QR')"
                                    class="h-10 rounded-lg border text-sm font-semibold transition-colors {{ $metodo_pago === 'QR' ? $btnOn : $btnOff }}">QR</button>
                            </div>
                        </div>
                    </div>

                    {{-- Gasto general o de un show --}}
                    @if (!$esIngreso)
                        <div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <button type="button" wire:click="$set('clasificacion','OPERATIVO')"
                                    class="h-9 rounded-lg border text-sm font-semibold transition-colors {{ $clasificacion === 'OPERATIVO' ? $btnOn : $btnOff }}">General</button>
                                <button type="button" wire:click="$set('clasificacion','EVENTO')"
                                    class="h-9 rounded-lg border text-sm font-semibold transition-colors {{ $clasificacion === 'EVENTO' ? $btnOn : $btnOff }}">De
                                    un show</button>
                            </div>
                            @if ($clasificacion === 'EVENTO')
                                <div class="mt-2">
                                    <x-ui.select-search name="evento_id" :options="$eventosOptions"
                                        placeholder="Buscar show por código, cliente o fecha..." />
                                    @error('evento_id')
                                        <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Categoría: buscar o escribir una nueva --}}
                    <div wire:key="cat-{{ $tipo }}-{{ $clasificacion }}" class="relative"
                        x-data="{
                            q: $wire.entangle('categoria'),
                            open: false,
                            opts: @js($categoriasModal),
                            get lista() { const t = (this.q || '').toLowerCase().trim(); return t ? this.opts.filter(o => o.toLowerCase().includes(t)) : this.opts },
                            get nueva() { const t = (this.q || '').trim(); return t !== '' && !this.opts.some(o => o.toLowerCase() === t.toLowerCase()) }
                        }" @click.outside="open = false">
                        <label class="{{ $lbl }}">Categoría: <span
                                class="text-rose-500 font-black text-base leading-none">*</span></label>
                        <div class="relative">
                            <i
                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                            <input type="text" x-model="q" @focus="open = true" @input="open = true"
                                @keydown.escape="open = false" @keydown.enter.prevent="open = false"
                                autocomplete="off" placeholder="Buscar o escribir una nueva..."
                                class="{{ $campo }} !pl-8">
                        </div>
                        <div x-show="open" x-cloak
                            class="mt-1 max-h-44 overflow-y-auto rounded-lg border border-outline bg-surface-card divide-y divide-outline">
                            <template x-for="o in lista" :key="o">
                                <button type="button" @click="q = o; open = false"
                                    class="w-full text-left px-3 py-2 text-sm text-ink-primary hover:bg-surface-hover"
                                    x-text="o"></button>
                            </template>
                            <button type="button" x-show="nueva" @click="open = false"
                                class="w-full text-left px-3 py-2 text-sm font-semibold text-gold-700 dark:text-gold-400 hover:bg-surface-hover border-t border-outline">
                                <i class="fa-solid fa-plus text-xs"></i> Crear "<span x-text="q"></span>"
                            </button>
                            <div x-show="lista.length === 0 && !nueva" class="px-3 py-2 text-sm text-ink-secondary">
                                Escribe para crear una categoría</div>
                        </div>
                        @error('categoria')
                            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Detalle + fecha --}}
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                        <div class="sm:col-span-3">
                            <label class="{{ $lbl }}">Detalle:</label>
                            <input type="text" wire:model="concepto"
                                placeholder="Opcional. Ej: Gasolina van a Warnes" class="{{ $campo }}">
                            @error('concepto')
                                <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $lbl }}">Fecha: <span
                                    class="text-rose-500 font-black text-base leading-none">*</span></label>
                            <input type="date" wire:model="fecha" class="{{ $campo }}">
                            @error('fecha')
                                <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Más datos --}}
                    <div x-data="{ mas: false }">
                        <button type="button" @click="mas = !mas"
                            class="text-sm font-semibold text-gold-700 dark:text-gold-400 hover:underline">
                            <i class="fa-solid" :class="mas ? 'fa-chevron-up' : 'fa-chevron-down'"></i> Más datos
                        </button>
                        <div x-show="mas" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                            <div>
                                <label class="{{ $lbl }}">Proveedor:</label>
                                <input type="text" wire:model="proveedor" placeholder="Ej: Estación de servicio"
                                    class="{{ $campo }}">
                            </div>
                            <div>
                                <label class="{{ $lbl }}">Comprobante:</label>
                                <input type="text" wire:model="numero_comprobante" placeholder="Ej: FAC-10293"
                                    class="{{ $campo }}">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="{{ $lbl }}">Observaciones:</label>
                                <input type="text" wire:model="observaciones" placeholder="Notas"
                                    class="{{ $campo }}">
                            </div>
                        </div>
                    </div>
                </div>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" wire:click="cerrarModal">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </x-ui.button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="guardarMovimiento"
                        class="h-9 px-4 inline-flex items-center gap-2 rounded-lg text-white text-sm font-semibold transition-colors {{ $esIngreso ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }}">
                        <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardarMovimiento"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardarMovimiento"></i>
                        <span>{{ $esIngreso ? 'Guardar ingreso' : 'Guardar gasto' }}</span>
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
                            <h3 class="text-base font-bold text-ink-primary leading-tight">Anular movimiento financiero
                            </h3>
                            <p class="text-xs text-ink-secondary">
                                {{ $movimiento_a_anular->tipo === 'INGRESO' ? 'Ingreso' : 'Gasto' }}
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
                            <p>El monto se excluirá de los balances y quedará registrado en la bitácora de auditoría.
                            </p>
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
                            <span class="text-ink-secondary">Fecha - categoría:</span>
                            <span
                                class="text-ink-primary text-right">{{ \Carbon\Carbon::parse($movimiento_a_anular->fecha)->format('d/m/Y') }}
                                - {{ $movimiento_a_anular->categoria }}</span>
                        </div>
                    </div>

                    <x-ui.textarea label="Motivo de la anulación" required name="motivo_anulacion"
                        wire:model="motivo_anulacion" rows="1" class="!min-h-[72px]"
                        placeholder="Explica por qué se anula este registro contable" />
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