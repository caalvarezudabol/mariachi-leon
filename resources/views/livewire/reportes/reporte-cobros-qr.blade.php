@php
    $campoFiltro = 'h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors';
    $presets = [
        'este_mes' => 'Este mes',
        'mes_anterior' => 'Mes anterior',
        'este_ano' => 'Este año',
        'todo' => 'Todo el historial',
    ];
    $paramsExport = [
        'desde' => $fecha_desde,
        'hasta' => $fecha_hasta,
        'mes' => $mes,
        'origen' => $origen_filtro,
        'usuario_id' => $usuario_id,
        'estado' => $estado_filtro,
        'search' => $search,
    ];
@endphp

<div class="space-y-3">
    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-qrcode text-lg text-gold-600 dark:text-gold-400"></i>
                <div>
                    <h1 class="text-base font-semibold text-ink-primary truncate">Reporte de Cobros por QR</h1>
                    <p class="text-xs text-ink-muted">Transacciones y cobros automatizados con Banco Económico</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.button size="sm" target="_blank" href="{{ route('admin.reportes.cobros-qr.pdf', $paramsExport) }}">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span class="hidden sm:inline">Exportar PDF</span>
                    <span class="sm:hidden">PDF</span>
                </x-ui.button>
                <x-ui.action-menu>
                    <x-ui.action-menu-item icon="fa-solid fa-receipt" href="{{ route('admin.pagos') }}" wire:navigate>
                        Gestión de Pagos
                    </x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-envelope-open-text" href="{{ route('admin.solicitudes-web') }}" wire:navigate>
                        Contactos Web
                    </x-ui.action-menu-item>
                    <x-ui.action-menu-item icon="fa-solid fa-shield-halved" href="{{ route('admin.auditoria') }}" wire:navigate>
                        Bitácora del Sistema
                    </x-ui.action-menu-item>
                </x-ui.action-menu>
            </div>
        </x-slot:header>

        {{-- Filtros y Controles --}}
        <x-slot:filters>
            <div class="flex flex-col gap-2.5">
                {{-- Fila 1: Presets de tiempo y Estado --}}
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <x-ui.filter-chips>
                        @foreach ($presets as $clave => $texto)
                            <x-ui.filter-chip wire:click="aplicarPreset('{{ $clave }}')" :active="$periodo_preset === $clave">
                                {{ $texto }}
                            </x-ui.filter-chip>
                        @endforeach
                    </x-ui.filter-chips>

                    <x-ui.filter-chips>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', 'PAGADO')" :active="$estado_filtro === 'PAGADO'">
                            <i class="fa-solid fa-circle-check text-emerald-500 mr-1 text-[11px]"></i>Pagados (Cobrados)
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', 'PENDIENTE')" :active="$estado_filtro === 'PENDIENTE'">
                            <i class="fa-solid fa-clock text-amber-500 mr-1 text-[11px]"></i>Pendientes
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                            Todos
                        </x-ui.filter-chip>
                    </x-ui.filter-chips>
                </div>

                {{-- Fila 2: Origen (Web Pública vs Sistema Interno) --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-ink-muted uppercase tracking-wider mr-1">Origen:</span>
                    <x-ui.filter-chips>
                        <x-ui.filter-chip wire:click="$set('origen_filtro', '')" :active="$origen_filtro === ''">
                            <i class="fa-solid fa-globe mr-1"></i>Todos los orígenes
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('origen_filtro', 'web')" :active="$origen_filtro === 'web'">
                            <i class="fa-solid fa-user-tag mr-1 text-sky-500"></i>Web Pública (Clientes)
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('origen_filtro', 'sistema')" :active="$origen_filtro === 'sistema'">
                            <i class="fa-solid fa-desktop mr-1 text-amber-500"></i>Sistema Interno
                        </x-ui.filter-chip>
                    </x-ui.filter-chips>
                </div>

                {{-- Fila 3: Inputs de Fecha, Mes, Usuario y Buscador --}}
                <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-outline/40">
                    <div class="flex items-center gap-1.5 text-xs text-ink-muted">
                        <i class="fa-regular fa-calendar"></i>
                        <span>Rango:</span>
                    </div>
                    <input type="date" wire:model.live="fecha_desde" title="Fecha Desde" class="{{ $campoFiltro }}">
                    <span class="text-ink-muted text-xs">a</span>
                    <input type="date" wire:model.live="fecha_hasta" title="Fecha Hasta" class="{{ $campoFiltro }}">

                    {{-- Selector de Mes Específico --}}
                    <div class="flex items-center gap-1 pl-1">
                        <span class="text-xs text-ink-muted">Mes:</span>
                        <input type="month" wire:model.live="mes" title="Mes específico" class="{{ $campoFiltro }}">
                    </div>

                    {{-- Selector de Usuario del Sistema (si es sistema o todos) --}}
                    @if ($origen_filtro !== 'web')
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-ink-muted">Usuario:</span>
                            <select wire:model.live="usuario_id" class="{{ $campoFiltro }} max-w-[190px]">
                                <option value="">Todos los usuarios</option>
                                @foreach ($usuariosSistema as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Selector de Estado detallado --}}
                    <select wire:model.live="estado_filtro" class="{{ $campoFiltro }} max-w-[150px]">
                        <option value="">Cualquier estado</option>
                        <option value="PAGADO">Solo PAGADO</option>
                        <option value="PENDIENTE">Solo PENDIENTE</option>
                        <option value="EXPIRADO">EXPIRADO</option>
                        <option value="ANULADO">ANULADO</option>
                    </select>

                    {{-- Buscador reactivo con debounce --}}
                    <div class="relative flex-1 min-w-[200px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-ink-muted text-xs pointer-events-none"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por transacción, QR, cliente o teléfono..."
                            class="w-full {{ $campoFiltro }} pl-8 pr-8">
                        @if ($search)
                            <button type="button" wire:click="$set('search', '')"
                                class="absolute right-2 top-2 text-ink-muted hover:text-ink-primary text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @endif
                    </div>

                    @if ($search || $origen_filtro || $usuario_id || $periodo_preset !== 'este_mes' || $estado_filtro !== 'PAGADO')
                        <button type="button" wire:click="limpiarFiltros"
                            class="h-8 px-2.5 text-xs text-ink-muted hover:text-rose-600 transition-colors flex items-center gap-1"
                            title="Restablecer filtros">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span class="hidden md:inline">Restablecer</span>
                        </button>
                    @endif
                </div>
            </div>
        </x-slot:filters>

        {{-- Tarjetas KPI de Resumen --}}
        <div class="p-4 space-y-4">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5">
                {{-- KPI 1: Total Cobrado --}}
                <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-3 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">Total Cobrado QR</span>
                        <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                    </div>
                    <div class="text-xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">
                        Bs {{ number_format($kpis['total_cobrado'], 2) }}
                    </div>
                    <div class="text-[11px] text-ink-muted mt-0.5">
                        {{ $kpis['cantidad_cobrados'] }} {{ $kpis['cantidad_cobrados'] === 1 ? 'cobro confirmado' : 'cobros confirmados' }}
                    </div>
                </div>

                {{-- KPI 2: Cobros Web Pública --}}
                <div class="rounded-lg border border-sky-500/20 bg-sky-500/5 px-3 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-sky-700 dark:text-sky-400 uppercase tracking-wide">Web Pública (Clientes)</span>
                        <i class="fa-solid fa-globe text-sky-500 text-sm"></i>
                    </div>
                    <div class="text-xl font-bold text-sky-700 dark:text-sky-300 mt-1">
                        Bs {{ number_format($kpis['total_web'], 2) }}
                    </div>
                    <div class="text-[11px] text-ink-muted mt-0.5">
                        {{ $kpis['cantidad_web'] }} anticipos recibidos
                    </div>
                </div>

                {{-- KPI 3: Cobros en Sistema --}}
                <div class="rounded-lg border border-amber-500/20 bg-amber-500/5 px-3 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide">Cobros Sistema</span>
                        <i class="fa-solid fa-cash-register text-amber-500 text-sm"></i>
                    </div>
                    <div class="text-xl font-bold text-amber-700 dark:text-amber-300 mt-1">
                        Bs {{ number_format($kpis['total_sistema'], 2) }}
                    </div>
                    <div class="text-[11px] text-ink-muted mt-0.5">
                        {{ $kpis['cantidad_sistema'] }} pagos por contratos
                    </div>
                </div>

                {{-- KPI 4: Registros Totales / Pendientes --}}
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-ink-muted uppercase tracking-wide">QRs Generados</span>
                        <i class="fa-solid fa-list-check text-ink-muted text-sm"></i>
                    </div>
                    <div class="text-xl font-bold text-ink-primary mt-1">
                        {{ $kpis['cantidad_total'] }}
                    </div>
                    <div class="text-[11px] text-ink-muted mt-0.5">
                        @if ($kpis['cantidad_pendientes'] > 0)
                            <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $kpis['cantidad_pendientes'] }} pendientes</span> (Bs {{ number_format($kpis['total_pendientes'], 2) }})
                        @else
                            <span>Sin transacciones pendientes</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tabla de Transacciones QR --}}
            <div class="overflow-x-auto rounded-lg border border-outline">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-outline bg-surface-sunken/60 text-ink-muted font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-2.5 px-3">Fecha y Hora</th>
                            <th class="py-2.5 px-3">Transacción / QR ID</th>
                            <th class="py-2.5 px-3">Canal / Origen</th>
                            <th class="py-2.5 px-3">Cliente / Pagador</th>
                            <th class="py-2.5 px-3">Concepto / Detalle</th>
                            <th class="py-2.5 px-3 text-right">Monto</th>
                            <th class="py-2.5 px-3 text-center">Estado</th>
                            <th class="py-2.5 px-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline/40">
                        @forelse ($transacciones as $tx)
                            <tr class="hover:bg-surface-hover/60 transition-colors">
                                {{-- Fecha y Hora --}}
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <div class="font-medium text-ink-primary">{{ $tx->created_at->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-ink-muted">{{ $tx->created_at->format('H:i:s') }}</div>
                                    @if ($tx->pagado_en)
                                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">
                                            Pagado: {{ $tx->pagado_en->format('H:i') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Transacción / QR ID --}}
                                <td class="py-2.5 px-3">
                                    <div class="font-mono font-semibold text-ink-primary text-[11px]">{{ $tx->transaction_id }}</div>
                                    @if ($tx->qr_id)
                                        <div class="font-mono text-[10px] text-ink-muted truncate max-w-[150px]" title="{{ $tx->qr_id }}">
                                            QR: {{ $tx->qr_id }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Canal / Origen --}}
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    @if ($tx->origen === 'contacto_web')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-500/10 text-sky-700 dark:text-sky-400 border border-sky-500/20">
                                            <i class="fa-solid fa-globe text-[9px]"></i> Web Pública
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                                            <i class="fa-solid fa-desktop text-[9px]"></i> Sistema
                                        </span>
                                        @if ($tx->creador)
                                            <div class="text-[10px] text-ink-muted mt-0.5">{{ $tx->creador->name }}</div>
                                        @endif
                                    @endif
                                </td>

                                {{-- Cliente / Pagador --}}
                                <td class="py-2.5 px-3">
                                    <div class="font-medium text-ink-primary">{{ $tx->nombre_cliente }}</div>
                                    @if ($tx->telefono_cliente)
                                        <div class="text-[10px] text-ink-muted">
                                            <i class="fa-solid fa-phone text-[9px] mr-0.5"></i>{{ $tx->telefono_cliente }}
                                        </div>
                                    @endif
                                    @if (!empty($tx->datos_pago['senderName']) && $tx->datos_pago['senderName'] !== $tx->nombre_cliente)
                                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400">
                                            Banca: {{ $tx->datos_pago['senderName'] }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Concepto / Detalle --}}
                                <td class="py-2.5 px-3 max-w-[240px]">
                                    <div class="truncate text-ink-secondary" title="{{ $tx->descripcion }}">
                                        {{ $tx->descripcion ?: 'Cobro mediante código QR' }}
                                    </div>
                                    @if ($tx->contrato)
                                        <a href="{{ route('admin.reportes.estados-cuenta', ['modalidad' => 'contrato', 'contrato_id' => $tx->contrato_id]) }}"
                                            class="text-[10px] text-gold-600 hover:underline inline-flex items-center gap-1" wire:navigate>
                                            <i class="fa-solid fa-file-contract text-[9px]"></i> Contrato {{ $tx->contrato->numero_contrato }}
                                        </a>
                                    @elseif ($tx->pago)
                                        <span class="text-[10px] text-ink-muted">Recibo N° {{ $tx->pago->numero_recibo }}</span>
                                    @endif
                                </td>

                                {{-- Monto --}}
                                <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                    <span class="font-bold text-sm text-ink-primary">
                                        Bs {{ number_format($tx->monto, 2) }}
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if ($tx->estado === 'PAGADO')
                                        <x-ui.badge variant="success">
                                            <i class="fa-solid fa-check text-[10px]"></i> PAGADO
                                        </x-ui.badge>
                                    @elseif ($tx->estado === 'PENDIENTE')
                                        @if ($tx->estaExpirado())
                                            <x-ui.badge variant="neutral">
                                                <i class="fa-solid fa-hourglass-end text-[10px]"></i> EXPIRADO
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="gold">
                                                <i class="fa-solid fa-clock text-[10px]"></i> PENDIENTE
                                            </x-ui.badge>
                                        @endif
                                    @elseif ($tx->estado === 'EXPIRADO')
                                        <x-ui.badge variant="neutral">
                                            <i class="fa-solid fa-clock-rotate-left text-[10px]"></i> EXPIRADO
                                        </x-ui.badge>
                                    @elseif ($tx->estado === 'ANULADO')
                                        <x-ui.badge variant="danger">
                                            <i class="fa-solid fa-ban text-[10px]"></i> ANULADO
                                        </x-ui.badge>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if ($tx->pago)
                                        <a href="{{ route('comercial.pagos.pdf', $tx->pago->codigo_verificacion) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium text-ink-secondary hover:text-gold-600 bg-surface-sunken hover:bg-gold-500/10 rounded border border-outline transition-colors"
                                            title="Ver Recibo de Pago PDF">
                                            <i class="fa-solid fa-receipt text-[10px]"></i>
                                            <span>Recibo</span>
                                        </a>
                                    @elseif ($tx->contactoWeb)
                                        <a href="{{ route('admin.solicitudes-web') }}" wire:navigate
                                            class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium text-ink-secondary hover:text-sky-600 bg-surface-sunken hover:bg-sky-500/10 rounded border border-outline transition-colors"
                                            title="Ver Solicitudes Web">
                                            <i class="fa-solid fa-eye text-[10px]"></i>
                                            <span>Contacto</span>
                                        </a>
                                    @else
                                        <span class="text-ink-muted text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-ink-muted">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-solid fa-qrcode text-3xl text-ink-muted/40"></i>
                                        <p class="font-medium text-sm">No se encontraron transacciones QR con los filtros seleccionados.</p>
                                        <p class="text-xs text-ink-muted/80">Prueba cambiando el rango de fechas, el origen o el estado.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if ($transacciones->hasPages())
                <div class="pt-2">
                    {{ $transacciones->links() }}
                </div>
            @endif
        </div>
    </x-ui.panel>
</div>
