<div class="space-y-6">
    <!-- Header de la Pantalla -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-brand-card p-6 rounded-3xl border border-brand-border shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-400 text-2xl shadow-inner">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Ingresos & Gastos Financieros</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700 font-mono">
                        Finanzas
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Control de gastos operativos, gastos por evento e ingresos extraordinarios.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.finanzas.dashboard') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-chart-line text-gold-400"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.reportes.gastos.pdf', ['desde' => $fecha_desde, 'hasta' => $fecha_hasta, 'tipo' => $filtro_tipo, 'clasificacion' => $filtro_clasificacion, 'estado' => $filtro_estado]) }}" 
               target="_blank"
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-rose-300 border border-rose-500/30 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-file-pdf text-rose-400"></i>
                <span>Exportar PDF</span>
            </a>

            <a href="{{ route('admin.finanzas.movimientos.excel', ['desde' => $fecha_desde, 'hasta' => $fecha_hasta, 'tipo' => $filtro_tipo, 'clasificacion' => $filtro_clasificacion, 'estado' => $filtro_estado]) }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-file-excel text-emerald-400"></i>
                <span>Excel</span>
            </a>

            <!-- Botones Registrar -->
            <button type="button" wire:click="abrirModalCrear('EGRESO', 'OPERATIVO')" 
                    class="px-3.5 py-2 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/40 font-bold text-xs flex items-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-plus-circle text-rose-400"></i>
                <span>+ Gasto Operativo</span>
            </button>

            <button type="button" wire:click="abrirModalCrear('EGRESO', 'EVENTO')" 
                    class="px-3.5 py-2 rounded-xl bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border border-amber-500/40 font-bold text-xs flex items-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-gas-pump text-amber-400"></i>
                <span>+ Gasto de Evento</span>
            </button>

            <button type="button" wire:click="abrirModalCrear('INGRESO', 'OPERATIVO')" 
                    class="px-3.5 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 font-bold text-xs flex items-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-arrow-down-long text-emerald-400"></i>
                <span>+ Ingreso Extra</span>
            </button>
        </div>
    </div>

    <!-- Mensajes Flash -->
    @if (session()->has('message'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center justify-between gap-3 text-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-300">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 flex items-center justify-between gap-3 text-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-400/60 hover:text-rose-300">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Tarjetas de Resumen de la Consulta Actual -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Egresos -->
        <div class="bg-brand-card p-5 rounded-3xl border border-rose-500/20 shadow-lg relative overflow-hidden group">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-rose-500/5 rounded-full blur-xl group-hover:bg-rose-500/10 transition-all"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-400 block mb-1">Total Egresos</span>
                    <span class="text-2xl font-black text-rose-300">Bs. {{ number_format($totalesConsulta['egresos'], 2) }}</span>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 text-xl">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Gastos operativos + gastos de eventos filtrados</p>
        </div>

        <!-- Gastos Operativos -->
        <div class="bg-brand-card p-5 rounded-3xl border border-slate-700/60 shadow-lg relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300 block mb-1">Gastos Operativos</span>
                    <span class="text-2xl font-black text-white">Bs. {{ number_format($totalesConsulta['operativos'], 2) }}</span>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 text-xl">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Alquiler, servicios, mantenimiento regular</p>
        </div>

        <!-- Gastos de Eventos -->
        <div class="bg-brand-card p-5 rounded-3xl border border-amber-500/20 shadow-lg relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400 block mb-1">Gastos de Eventos</span>
                    <span class="text-2xl font-black text-amber-300">Bs. {{ number_format($totalesConsulta['eventos'], 2) }}</span>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl">
                    <i class="fa-solid fa-car-side"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Combustible, viáticos, refrigerios por evento</p>
        </div>

        <!-- Ingresos Extraordinarios -->
        <div class="bg-brand-card p-5 rounded-3xl border border-emerald-500/20 shadow-lg relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Ingresos Extras</span>
                    <span class="text-2xl font-black text-emerald-300">Bs. {{ number_format($totalesConsulta['ingresos'], 2) }}</span>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Ingresos fuera de contratos regulares</p>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-4 shadow-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3 text-xs">
            <!-- Buscar texto -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Buscar</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Concepto, proveedor, comp..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-500 text-xs"></i>
                </div>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tipo</label>
                <select wire:model.live="filtro_tipo" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los tipos</option>
                    <option value="EGRESO">EGRESO (Gasto)</option>
                    <option value="INGRESO">INGRESO (Extra)</option>
                </select>
            </div>

            <!-- Clasificación -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Clasificación</label>
                <select wire:model.live="filtro_clasificacion" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todas</option>
                    <option value="OPERATIVO">Gasto Operativo</option>
                    <option value="EVENTO">Gasto de Evento</option>
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Estado</label>
                <select wire:model.live="filtro_estado" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="Valido">Válidos (Activos)</option>
                    <option value="Anulado">Anulados</option>
                    <option value="todos">Todos los Estados</option>
                </select>
            </div>

            <!-- Desde -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha Desde</label>
                <input type="date" wire:model.live="fecha_desde" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>

            <!-- Hasta -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha Hasta</label>
                <input type="date" wire:model.live="fecha_hasta" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                    <tr>
                        <th class="py-3.5 px-4">Fecha</th>
                        <th class="py-3.5 px-4">Tipo & Clasificación</th>
                        <th class="py-3.5 px-4">Categoría / Concepto</th>
                        <th class="py-3.5 px-4">Evento Asociado</th>
                        <th class="py-3.5 px-4">Proveedor / Comp.</th>
                        <th class="py-3.5 px-4 text-right">Monto (Bs.)</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($movimientos as $mov)
                        <tr class="hover:bg-slate-800/40 transition-colors {{ $mov->estado === 'Anulado' ? 'opacity-60 bg-rose-950/10' : '' }}">
                            <!-- Fecha -->
                            <td class="py-3 px-4 font-mono text-slate-300 whitespace-nowrap">
                                <div>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-500">{{ $mov->created_at->format('H:i') }}</div>
                            </td>

                            <!-- Tipo & Clasificación -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 mb-1">
                                    @if($mov->tipo === 'EGRESO')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                            EGRESO
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            INGRESO
                                        </span>
                                    @endif

                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $mov->clasificacion }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-credit-card text-[9px] text-slate-500"></i>
                                    <span>{{ $mov->metodo_pago }}</span>
                                </div>
                            </td>

                            <!-- Categoría y Concepto -->
                            <td class="py-3 px-4 max-w-xs">
                                <div class="font-bold text-white truncate" title="{{ $mov->concepto }}">
                                    {{ $mov->concepto }}
                                </div>
                                <div class="text-[11px] text-gold-400/90 font-medium truncate">
                                    {{ $mov->categoria }}
                                </div>
                                @if($mov->descripcion)
                                    <div class="text-[10px] text-slate-400 truncate italic">
                                        {{ $mov->descripcion }}
                                    </div>
                                @endif
                            </td>

                            <!-- Evento Asociado -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($mov->evento)
                                    <div class="font-bold text-amber-300 flex items-center gap-1">
                                        <i class="fa-solid fa-calendar-day text-[10px]"></i>
                                        <span>{{ $mov->evento->codigo_evento }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[140px]">
                                        {{ $mov->evento->cliente->nombre_completo ?? 'Cliente S/N' }}
                                    </div>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">Gasto General / No aplica</span>
                                @endif
                            </td>

                            <!-- Proveedor / Comprobante -->
                            <td class="py-3 px-4 max-w-[140px]">
                                <div class="text-slate-300 truncate">
                                    {{ $mov->proveedor ?: '—' }}
                                </div>
                                @if($mov->numero_comprobante)
                                    <div class="text-[10px] font-mono text-slate-400 truncate">
                                        <i class="fa-solid fa-ticket text-[9px]"></i> {{ $mov->numero_comprobante }}
                                    </div>
                                @endif
                            </td>

                            <!-- Monto -->
                            <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold text-sm {{ $mov->tipo === 'INGRESO' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $mov->tipo === 'INGRESO' ? '+' : '-' }} Bs. {{ number_format($mov->monto, 2) }}
                            </td>

                            <!-- Estado -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($mov->estado === 'Valido')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        Válido
                                    </span>
                                @else
                                    <div>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                            Anulado
                                        </span>
                                        @if($mov->motivo_anulacion)
                                            <div class="text-[9px] text-rose-400/80 truncate max-w-[120px] mt-0.5" title="{{ $mov->motivo_anulacion }}">
                                                {{ $mov->motivo_anulacion }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($mov->estado === 'Valido')
                                    <button type="button" wire:click="abrirModalAnular({{ $mov->id }})" 
                                            title="Anular movimiento"
                                            class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 inline-flex items-center justify-center transition-all">
                                        <i class="fa-solid fa-ban text-xs"></i>
                                    </button>
                                @else
                                    <span class="text-slate-600 text-xs italic">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 text-xl mx-auto mb-3">
                                    <i class="fa-solid fa-inbox"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No se encontraron movimientos registrados</p>
                                <p class="text-xs text-slate-500 mt-1">Ajuste los filtros o registre un nuevo gasto/ingreso.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
            <div class="p-4 border-t border-brand-border bg-slate-950/40">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Registrar Movimiento -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-brand-card border border-brand-border w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="p-5 border-b border-brand-border flex items-center justify-between bg-slate-900/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $tipo === 'INGRESO' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }} border flex items-center justify-center text-lg">
                            <i class="fa-solid {{ $tipo === 'INGRESO' ? 'fa-arrow-down-long' : 'fa-receipt' }}"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">
                                Registrar {{ $tipo === 'INGRESO' ? 'Ingreso Extraordinario' : 'Gasto Financiero' }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                Ingrese los detalles del movimiento contable.
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModal" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit.prevent="guardarMovimiento" class="overflow-y-auto p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tipo de Movimiento -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Tipo de Flujo <span class="text-rose-400">*</span>
                            </label>
                            <select wire:model.live="tipo" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                                <option value="EGRESO">EGRESO (Gasto / Salida de dinero)</option>
                                <option value="INGRESO">INGRESO (Extraordinario / Entrada)</option>
                            </select>
                        </div>

                        <!-- Clasificación -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Clasificación <span class="text-rose-400">*</span>
                            </label>
                            <select wire:model.live="clasificacion" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                                <option value="OPERATIVO">Gasto Operativo (Alquiler, servicios, oficina)</option>
                                <option value="EVENTO">Gasto por Evento (Combustible, viáticos, comidas)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Si es por Evento, seleccionar Evento -->
                    @if($clasificacion === 'EVENTO')
                        <div class="p-3.5 rounded-2xl bg-amber-500/5 border border-amber-500/20 space-y-2">
                            <label class="block text-[11px] font-bold text-amber-300 uppercase tracking-wider">
                                Evento Asociado <span class="text-rose-400">*</span>
                            </label>
                            <select wire:model.live="evento_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                                <option value="">Seleccione el evento...</option>
                                @foreach($eventos as $ev)
                                    <option value="{{ $ev->id }}">
                                        {{ $ev->codigo_evento }} | {{ \Carbon\Carbon::parse($ev->fecha_evento)->format('d/m/Y') }} | {{ $ev->cliente->nombre_completo ?? 'Cliente S/N' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('evento_id') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Categoría -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Categoría <span class="text-rose-400">*</span>
                            </label>
                            <select wire:model="categoria" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                                @if($tipo === 'INGRESO')
                                    @foreach($categoriasIngresos as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                @elseif($clasificacion === 'EVENTO')
                                    @foreach($categoriasEventos as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                @else
                                    @foreach($categoriasOperativas as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('categoria') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Fecha <span class="text-rose-400">*</span>
                            </label>
                            <input type="date" wire:model="fecha" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                            @error('fecha') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Concepto -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Concepto / Título <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" wire:model="concepto" placeholder="Ej. Combustible van para evento en Quillacollo" 
                               class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                        @error('concepto') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Monto -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Monto (Bs.) <span class="text-rose-400">*</span>
                            </label>
                            <input type="number" step="0.01" wire:model="monto" placeholder="0.00" 
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-bold focus:border-gold-500 focus:outline-none">
                            @error('monto') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Método de Pago -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Método de Pago <span class="text-rose-400">*</span>
                            </label>
                            <select wire:model="metodo_pago" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia QR">Transferencia QR</option>
                                <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                <option value="Tarjeta de Débito/Crédito">Tarjeta</option>
                            </select>
                            @error('metodo_pago') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Número Comprobante -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                N° Comprobante / Factura
                            </label>
                            <input type="text" wire:model="numero_comprobante" placeholder="Ej. FAC-10293 / REC-44" 
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Proveedor -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Proveedor / Beneficiario
                            </label>
                            <input type="text" wire:model="proveedor" placeholder="Ej. Estación de Servicio YPFB" 
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                        </div>

                        <!-- Glosa -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Glosa Administrativa
                            </label>
                            <input type="text" wire:model="glosa" placeholder="Resumen contable corto" 
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Observaciones adicionales
                        </label>
                        <textarea wire:model="observaciones" rows="2" placeholder="Detalles de justificación, notas para auditoría..." 
                                  class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none"></textarea>
                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-4 border-t border-brand-border flex items-center justify-end gap-3">
                        <button type="button" wire:click="cerrarModal" 
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-gold-500 hover:bg-gold-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition-all shadow-md">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Guardar Movimiento</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Anular Movimiento -->
    @if($modalAnularOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-brand-card border border-rose-500/40 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <!-- Modal Header -->
                <div class="p-5 border-b border-brand-border flex items-center justify-between bg-rose-950/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-400 text-lg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Anular Movimiento Financiero</h3>
                            <p class="text-xs text-rose-300/80">Esta acción excluirá el monto de los balances y quedará registrada en bitácora.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModalAnular" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 text-xs">
                    @if($movimiento_a_anular)
                        <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 font-bold">{{ $movimiento_a_anular->tipo }} #{{ $movimiento_a_anular->id }}</span>
                                <span class="font-mono font-bold text-rose-400">Bs. {{ number_format($movimiento_a_anular->monto, 2) }}</span>
                            </div>
                            <div class="text-white font-medium">{{ $movimiento_a_anular->concepto }}</div>
                            <div class="text-[11px] text-slate-500">
                                Fecha: {{ \Carbon\Carbon::parse($movimiento_a_anular->fecha)->format('d/m/Y') }} | Categoría: {{ $movimiento_a_anular->categoria }}
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Motivo de Anulación <span class="text-rose-400">*</span>
                        </label>
                        <textarea wire:model="motivo_anulacion" rows="3" placeholder="Explique claramente la razón por la cual se anula este registro contable..." 
                                  class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-rose-500 focus:outline-none"></textarea>
                        @error('motivo_anulacion') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 border-t border-brand-border flex items-center justify-end gap-3">
                        <button type="button" wire:click="cerrarModalAnular" 
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-colors">
                            Cancelar
                        </button>
                        <button type="button" wire:click="confirmarAnulacion" 
                                class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs flex items-center gap-2 transition-all shadow-md">
                            <i class="fa-solid fa-ban"></i>
                            <span>Confirmar Anulación</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
