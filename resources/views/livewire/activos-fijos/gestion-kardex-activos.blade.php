<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Kardex Valorizado (Precio Promedio Ponderado - PPP)</h1>
            <p class="text-xs text-slate-400">Historial cronológico de movimientos de entradas, salidas, saldos y valoración monetaria.</p>
        </div>
        <button wire:click="resetFiltros" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-filter-circle-xmark"></i>
            <span>Limpiar Filtros</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Artículo</label>
                <select wire:model.live="asset_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="">Todos los Artículos</option>
                    @foreach($articulos as $art)
                        <option value="{{ $art->id }}">[{{ $art->codigo }}] {{ $art->nombre }}</option>
                    @endforeach
                </select>
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
