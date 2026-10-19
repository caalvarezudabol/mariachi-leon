<div class="space-y-6">
    <!-- Header de la Pantalla -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-brand-card p-6 rounded-3xl border border-brand-border shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-400 text-2xl shadow-inner">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Reporte Financiero & Balances</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-gold-500/10 text-gold-400 border border-gold-500/20 font-mono">
                        Finanzas
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Balances consolidados de ingresos y egresos, rentabilidad por evento y resultado operativo estimado.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.finanzas.dashboard') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-chart-line text-gold-400"></i>
                <span>Dashboard</span>
            </a>

            <!-- Exportar PDF -->
            <a href="{{ route('admin.reportes.financieros.pdf', [
                    'inicio' => $fecha_inicio,
                    'fin' => $fecha_fin,
                    'cliente_id' => $cliente_id,
                    'evento_id' => $evento_id,
                    'servicio_id' => $servicio_id,
                    'tipo' => $tipo_balance,
                ]) }}" 
               target="_blank"
               class="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-500 to-amber-500 hover:from-gold-400 hover:to-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-gold-500/20 transition-all">
                <i class="fa-solid fa-file-pdf text-sm"></i>
                <span>Reporte Ejecutivo (PDF)</span>
            </a>

            <!-- Exportar Excel -->
            <a href="{{ route('admin.reportes.financieros.excel', [
                    'inicio' => $fecha_inicio,
                    'fin' => $fecha_fin,
                    'cliente_id' => $cliente_id,
                    'evento_id' => $evento_id,
                    'servicio_id' => $servicio_id,
                    'tipo' => $tipo_balance,
                ]) }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-file-excel text-emerald-400"></i>
                <span>Excel</span>
            </a>
        </div>
    </div>

    <!-- Pestañas de Tipo de Balance y Filtros -->
    <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-brand-border/60">
            <!-- Pestañas -->
            <div class="flex items-center bg-slate-950 p-1 rounded-2xl border border-slate-800 flex-wrap gap-1">
                <button type="button" wire:click="$set('tipo_balance', 'consolidado')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $tipo_balance === 'consolidado' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Balance Consolidado</span>
                </button>
                <button type="button" wire:click="$set('tipo_balance', 'por_evento')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $tipo_balance === 'por_evento' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-list-ol"></i>
                    <span>Rentabilidad por Evento</span>
                </button>
                <button type="button" wire:click="$set('tipo_balance', 'gastos_operativos')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $tipo_balance === 'gastos_operativos' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>Gastos Operativos</span>
                </button>
            </div>

            <!-- Presets de Período -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" wire:click="aplicarPreset('este_mes')" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $periodo_preset === 'este_mes' ? 'bg-gold-500 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Este Mes
                </button>
                <button type="button" wire:click="aplicarPreset('mes_anterior')" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $periodo_preset === 'mes_anterior' ? 'bg-gold-500 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Mes Anterior
                </button>
                <button type="button" wire:click="aplicarPreset('este_ano')" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $periodo_preset === 'este_ano' ? 'bg-gold-500 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Este Año
                </button>
                <button type="button" wire:click="aplicarPreset('todo')" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $periodo_preset === 'todo' ? 'bg-gold-500 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Todo
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Desde</label>
                <input type="date" wire:model.live="fecha_inicio" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hasta</label>
                <input type="date" wire:model.live="fecha_fin" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cliente</label>
                <select wire:model.live="cliente_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los clientes</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Evento</label>
                <select wire:model.live="evento_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los eventos</option>
                    @foreach($eventosLista as $ev)
                        <option value="{{ $ev->id }}">{{ $ev->codigo_evento }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Servicio</label>
                <select wire:model.live="servicio_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los servicios</option>
                    @foreach($servicios as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- VISTA 1: BALANCE CONSOLIDADO -->
    <!-- ============================================================= -->
    @if($tipo_balance === 'consolidado')
        <!-- 4 Tarjetas Ejecutivas de Gran Impacto -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Ingresos Consolidados -->
            <div class="bg-brand-card p-6 rounded-3xl border border-emerald-500/30 shadow-xl relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Total Ingresos</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </span>
                </div>
                <div class="text-3xl font-black text-emerald-300 font-mono">
                    Bs. {{ number_format($kpis['total_ingresos_consolidados'], 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-2">
                    Cobrado a clientes + ingresos extra
                </p>
            </div>

            <!-- Costos y Gastos Totales Reconocidos -->
            <div class="bg-brand-card p-6 rounded-3xl border border-rose-500/30 shadow-xl relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-400">Total Costos & Gastos</span>
                    <span class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-arrow-trend-down"></i>
                    </span>
                </div>
                <div class="text-3xl font-black text-rose-400 font-mono">
                    Bs. {{ number_format($kpis['costos_totales_reconocidos'], 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-2">
                    Músicos + gastos eventos + operativos
                </p>
            </div>

            <!-- Balance Bruto de Eventos -->
            <div class="bg-brand-card p-6 rounded-3xl border border-gold-500/30 shadow-xl relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gold-400">Balance Bruto Eventos</span>
                    <span class="w-8 h-8 rounded-xl bg-gold-500/10 text-gold-400 border border-gold-500/20 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </span>
                </div>
                <div class="text-3xl font-black text-white font-mono">
                    Bs. {{ number_format($kpis['balance_bruto_eventos'], 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-2">
                    Cobrado a clientes - Asignado a músicos
                </p>
            </div>

            <!-- Resultado Operativo Estimado -->
            @php
                $resultado = $kpis['resultado_operativo_estimado'];
            @endphp
            <div class="bg-brand-card p-6 rounded-3xl border {{ $resultado >= 0 ? 'border-emerald-500/50' : 'border-rose-500/50' }} shadow-xl relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider {{ $resultado >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">Resultado Operativo</span>
                    <span class="w-8 h-8 rounded-xl {{ $resultado >= 0 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }} border flex items-center justify-center text-sm">
                        <i class="fa-solid {{ $resultado >= 0 ? 'fa-chart-line' : 'fa-triangle-exclamation' }}"></i>
                    </span>
                </div>
                <div class="text-3xl font-black font-mono {{ $resultado >= 0 ? 'text-emerald-300' : 'text-rose-400' }}">
                    {{ $resultado >= 0 ? '+' : '' }}Bs. {{ number_format($resultado, 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-2">
                    Margen neto final del período
                </p>
            </div>
        </div>

        <!-- Matriz Detallada de Balances -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Rubro: Clientes y Contratos -->
            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-3 shadow-lg">
                <h4 class="text-sm font-bold text-white flex items-center gap-2 border-b border-brand-border/60 pb-2">
                    <i class="fa-solid fa-file-contract text-gold-400"></i>
                    <span>Comercial & Cobranzas a Clientes</span>
                </h4>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-slate-400">Total Contratado (Pactado):</span>
                        <span class="font-mono font-bold text-white">Bs. {{ number_format($kpis['total_contratado'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-emerald-400">Total Efectivamente Cobrado:</span>
                        <span class="font-mono font-bold text-emerald-400">Bs. {{ number_format($kpis['total_cobrado'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-rose-400">Saldo Pendiente por Cobrar:</span>
                        <span class="font-mono font-bold text-rose-400">Bs. {{ number_format($kpis['saldo_pendiente_clientes'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-slate-400">Ingresos Extraordinarios:</span>
                        <span class="font-mono font-bold text-slate-300">Bs. {{ number_format($kpis['total_ingresos_adicionales'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Rubro: Personal y Egresos -->
            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-3 shadow-lg">
                <h4 class="text-sm font-bold text-white flex items-center gap-2 border-b border-brand-border/60 pb-2">
                    <i class="fa-solid fa-money-bill-transfer text-rose-400"></i>
                    <span>Músicos, Logística & Operaciones</span>
                </h4>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-slate-400">Total Asignado a Músicos:</span>
                        <span class="font-mono font-bold text-white">Bs. {{ number_format($kpis['total_asignado_participantes'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-slate-400">Total Pagado a Músicos:</span>
                        <span class="font-mono font-bold text-slate-300">Bs. {{ number_format($kpis['total_pagado_participantes'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-rose-400">Saldo Pendiente por Pagar a Músicos:</span>
                        <span class="font-mono font-bold text-rose-400">Bs. {{ number_format($kpis['saldo_pendiente_participantes'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-slate-400">Gastos Directos de Eventos:</span>
                        <span class="font-mono font-bold text-amber-300">Bs. {{ number_format($kpis['total_gastos_eventos'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
                        <span class="text-slate-400">Gastos Operativos Institucionales:</span>
                        <span class="font-mono font-bold text-slate-300">Bs. {{ number_format($kpis['total_gastos_operativos'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

    <!-- ============================================================= -->
    <!-- VISTA 2: RENTABILIDAD POR EVENTO -->
    <!-- ============================================================= -->
    @elseif($tipo_balance === 'por_evento')
        <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden shadow-xl">
            <div class="p-5 border-b border-brand-border flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-gold-400"></i>
                    <span>Balance y Rentabilidad Individual de Presentaciones</span>
                </h3>
                <span class="text-xs text-slate-400">Total evaluados: <strong class="text-white">{{ $balancesEventos->count() }}</strong></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                        <tr>
                            <th class="py-3.5 px-4">Evento & Fecha</th>
                            <th class="py-3.5 px-4">Cliente</th>
                            <th class="py-3.5 px-4 text-right">Contratado</th>
                            <th class="py-3.5 px-4 text-right">Cobrado</th>
                            <th class="py-3.5 px-4 text-right">Asignado Músicos</th>
                            <th class="py-3.5 px-4 text-right">Gastos Evento</th>
                            <th class="py-3.5 px-4 text-right">Balance Bruto</th>
                            <th class="py-3.5 px-4 text-right">Resultado Neto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/60">
                        @forelse($balancesEventos as $be)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-mono whitespace-nowrap">
                                    <div class="font-bold text-gold-400">{{ $be['codigo_evento'] }}</div>
                                    <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($be['fecha_evento'])->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-3 px-4 max-w-xs truncate text-white">
                                    {{ $be['cliente'] }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-white whitespace-nowrap">
                                    Bs. {{ number_format($be['monto_contratado'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400 whitespace-nowrap">
                                    Bs. {{ number_format($be['total_cobrado'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-300 whitespace-nowrap">
                                    Bs. {{ number_format($be['total_asignado'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-amber-300 whitespace-nowrap">
                                    Bs. {{ number_format($be['gastos_evento'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $be['balance_bruto'] >= 0 ? 'text-white' : 'text-rose-400' }}">
                                    Bs. {{ number_format($be['balance_bruto'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $be['balance_neto'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $be['balance_neto'] >= 0 ? '+' : '' }}Bs. {{ number_format($be['balance_neto'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500">
                                    <p class="text-sm font-semibold text-slate-400">No se encontraron eventos en el período consultado</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- ============================================================= -->
    <!-- VISTA 3: GASTOS OPERATIVOS -->
    <!-- ============================================================= -->
    @else
        <!-- Gastos por Categoría -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border md:col-span-1 shadow-lg space-y-3">
                <h4 class="text-sm font-bold text-white border-b border-brand-border/60 pb-2">
                    Resumen por Categoría
                </h4>
                <div class="space-y-2 text-xs">
                    @forelse($gastosPorCategoria as $gpc)
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950/60">
                            <div>
                                <div class="font-bold text-white">{{ $gpc->categoria }}</div>
                                <div class="text-[10px] text-slate-500">{{ $gpc->cantidad }} registro(s)</div>
                            </div>
                            <span class="font-mono font-bold text-rose-400">Bs. {{ number_format($gpc->total, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-slate-500 text-xs">Sin gastos operativos registrados.</p>
                    @endforelse
                </div>
            </div>

            <!-- Tabla de Movimientos Operativos -->
            <div class="bg-brand-card rounded-3xl border border-brand-border md:col-span-2 overflow-hidden shadow-xl">
                <div class="p-5 border-b border-brand-border flex items-center justify-between">
                    <h4 class="text-sm font-bold text-white">Detalle de Gastos Operativos</h4>
                    <span class="text-xs text-slate-400">Total: <strong class="text-rose-400">Bs. {{ number_format($gastosOperativos->sum('monto'), 2) }}</strong></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                            <tr>
                                <th class="py-3.5 px-4">Fecha</th>
                                <th class="py-3.5 px-4">Categoría</th>
                                <th class="py-3.5 px-4">Concepto</th>
                                <th class="py-3.5 px-4">Proveedor</th>
                                <th class="py-3.5 px-4 text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/60">
                            @forelse($gastosOperativos as $go)
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="py-3 px-4 font-mono text-slate-300 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($go->fecha)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-gold-400 font-semibold whitespace-nowrap">
                                        {{ $go->categoria }}
                                    </td>
                                    <td class="py-3 px-4 text-white">
                                        {{ $go->concepto }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-400">
                                        {{ $go->proveedor ?: '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-rose-400 whitespace-nowrap">
                                        Bs. {{ number_format($go->monto, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-500">
                                        <p class="text-sm font-semibold text-slate-400">No se encontraron gastos operativos en este período</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
