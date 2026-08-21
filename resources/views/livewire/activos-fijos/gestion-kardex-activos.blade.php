<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Kardex Valorizado (Precio Promedio Ponderado - PPP)</h1>
            <p class="text-xs text-slate-400">Historial cronológico de movimientos de entradas, salidas, saldos y valoración monetaria.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="exportarPdf" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-500 shadow-lg shadow-rose-600/20 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-pdf text-sm"></i>
                <span>Exportar PDF</span>
            </button>
            <button wire:click="resetFiltros" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter-circle-xmark"></i>
                <span>Limpiar Filtros</span>
            </button>
        </div>
    </div>

    @if (session()->has('warning'))
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            
            <!-- BUSCADOR CON AUTOCOMPLETADO Y SELECCIÓN DE ARTÍCULO -->
            <div class="relative" x-data="{ dropdownOpen: @entangle('dropdown_open') }">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Buscar / Seleccionar Artículo</label>
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.250ms="search_articulo"
                           wire:focus="abrirDropdown"
                           placeholder="🔍 Código o nombre..." 
                           class="w-full pl-8 pr-8 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500 font-bold {{ $asset_id ? 'border-gold-500/60 text-gold-400' : '' }}">
                    
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-500 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>

                    @if($asset_id || $search_articulo)
                        <button type="button" wire:click="limpiarArticulo" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-rose-400 transition-colors">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    @endif
                </div>

                <!-- Dropdown Result List Overlay -->
                <div x-show="dropdownOpen" 
                     x-cloak 
                     @click.away="dropdownOpen = false" 
                     class="absolute z-50 left-0 right-0 mt-1 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto divide-y divide-slate-800/60 scrollbar-thin min-w-[220px]">
                    <div wire:click="limpiarArticulo" class="px-3 py-2 text-xs font-semibold text-slate-400 hover:bg-slate-800 hover:text-white cursor-pointer transition-colors flex items-center justify-between">
                        <span>-- Todos los Artículos --</span>
                        <i class="fa-solid fa-list-ul text-[10px]"></i>
                    </div>

                    @forelse($articulos as $art)
                        <div wire:click="seleccionarArticulo({{ $art->id }})" 
                             class="px-3 py-2 text-xs hover:bg-gold-500/10 hover:text-gold-300 cursor-pointer transition-colors flex items-center justify-between group {{ $asset_id == $art->id ? 'bg-gold-500/20 text-gold-400 font-bold' : 'text-slate-200' }}">
                            <div>
                                <span class="font-mono text-gold-400 font-bold">[{{ $art->codigo }}]</span>
                                <span>{{ $art->nombre }}</span>
                            </div>
                            <span class="text-[10px] text-slate-500 group-hover:text-gold-400">{{ $art->category->nombre ?? '' }}</span>
                        </div>
                    @empty
                        <div class="px-3 py-3 text-xs text-slate-500 text-center">
                            No se encontraron artículos con "{{ $search_articulo }}"
                        </div>
                    @endforelse
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha Desde</label>
                <input type="date" wire:model.live="fecha_inicio" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha Hasta</label>
                <input type="date" wire:model.live="fecha_fin" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tipo Movimiento</label>
                <select wire:model.live="tipo_movimiento" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="">Entradas y Salidas</option>
                    <option value="entrada">Solo Entradas (+)</option>
                    <option value="salida">Solo Salidas (-)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Motivo</label>
                <select wire:model.live="motivo" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="">Todos los Motivos</option>
                    <option value="compra">Compra</option>
                    <option value="donacion">Donación</option>
                    <option value="devolucion">Devolución</option>
                    <option value="reposicion">Reposición</option>
                    <option value="asignacion">Asignación</option>
                    <option value="prestamo">Préstamo</option>
                    <option value="baja">Baja</option>
                    <option value="perdida">Pérdida</option>
                    <option value="deterioro">Deterioro</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="ajuste_positivo">Ajuste (+)</option>
                    <option value="ajuste_negativo">Ajuste (-)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Usuario Operador</label>
                <select wire:model.live="user_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="">Todos los Usuarios</option>
                    @foreach($usuarios as $usr)
                        <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Artículo / Código</th>
                        <th class="px-4 py-3">Movimiento / Motivo</th>
                        <th class="px-4 py-3 text-center bg-emerald-950/30 text-emerald-400">Entrada</th>
                        <th class="px-4 py-3 text-center bg-emerald-950/30 text-emerald-400">Costo Unit.</th>
                        <th class="px-4 py-3 text-center bg-rose-950/30 text-rose-400">Salida</th>
                        <th class="px-4 py-3 text-center bg-rose-950/30 text-rose-400">Costo Salida</th>
                        <th class="px-4 py-3 text-center bg-slate-900 text-gold-400">Saldo Cant.</th>
                        <th class="px-4 py-3 text-center bg-slate-900 text-gold-400">PPP Saldo</th>
                        <th class="px-4 py-3 text-center bg-slate-900 text-gold-400">Valor Saldo</th>
                        <th class="px-4 py-3">Responsable / Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60 font-mono text-xs">
                    @forelse($movimientos as $mov)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-4 py-3 text-slate-300">
                                {{ $mov->fecha_movimiento->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-sans">
                                <div class="font-bold text-white">{{ $mov->asset->nombre ?? 'N/A' }}</div>
                                <div class="text-[11px] font-mono text-gold-400">{{ $mov->asset->codigo ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 font-sans">
                                @if($mov->tipo_movimiento === 'entrada')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <i class="fa-solid fa-plus text-[9px]"></i> {{ str_replace('_', ' ', $mov->motivo) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <i class="fa-solid fa-minus text-[9px]"></i> {{ str_replace('_', ' ', $mov->motivo) }}
                                    </span>
                                @endif
                            </td>

                            <!-- ENTRADA -->
                            <td class="px-4 py-3 text-center bg-emerald-950/10 text-emerald-400 font-bold">
                                {{ $mov->tipo_movimiento === 'entrada' ? '+' . number_format($mov->cantidad, 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center bg-emerald-950/10 text-slate-300">
                                {{ $mov->tipo_movimiento === 'entrada' ? 'Bs ' . number_format($mov->costo_unitario, 2) : '-' }}
                            </td>

                            <!-- SALIDA -->
                            <td class="px-4 py-3 text-center bg-rose-950/10 text-rose-400 font-bold">
                                {{ $mov->tipo_movimiento === 'salida' ? '-' . number_format($mov->cantidad, 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center bg-rose-950/10 text-slate-300">
                                {{ $mov->tipo_movimiento === 'salida' ? 'Bs ' . number_format($mov->costo_unitario, 2) : '-' }}
                            </td>

                            <!-- SALDO -->
                            <td class="px-4 py-3 text-center bg-slate-950 font-bold text-white">
                                {{ number_format($mov->cantidad_saldo, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center bg-slate-950 font-bold text-gold-400">
                                Bs {{ number_format($mov->costo_ppp_saldo, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center bg-slate-950 font-bold text-gold-300">
                                Bs {{ number_format($mov->valor_total_saldo, 2) }}
                            </td>

                            <td class="px-4 py-3 font-sans text-[11px]">
                                @if($mov->responsable)
                                    <div class="text-slate-200 font-bold"><i class="fa-solid fa-user text-emerald-400 mr-1"></i>{{ $mov->responsable->nombre_completo }}</div>
                                @endif
                                <div class="text-slate-400"><i class="fa-solid fa-user-gear text-slate-500 mr-1"></i>{{ $mov->user->name ?? 'Sistema' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-8 text-center text-slate-500 font-sans">No se encontraron movimientos registrados en el Kardex.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $movimientos->links() }}
        </div>
    </div>
</div>
