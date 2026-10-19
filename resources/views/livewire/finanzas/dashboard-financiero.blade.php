<div class="space-y-6">
    <!-- Header de la Pantalla -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-brand-card p-6 rounded-3xl border border-brand-border shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-400 text-2xl shadow-inner">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Dashboard Financiero</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-gold-500/10 text-gold-400 border border-gold-500/20 font-mono">
                        Sprint 3
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Control consolidado de ingresos, egresos, contratos, comisiones de músicos y fondo común.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.finanzas.movimientos') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-money-bill-transfer text-gold-400"></i>
                <span>Movimientos & Gastos</span>
            </a>

            <a href="{{ route('admin.finanzas.fondo-comun') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-vault text-emerald-400"></i>
                <span>Fondo Común</span>
            </a>

            <a href="{{ route('admin.reportes.financieros.pdf', ['inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'cliente_id' => $cliente_id, 'evento_id' => $evento_id]) }}" 
               target="_blank"
               class="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-500 to-amber-500 hover:from-gold-400 hover:to-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-gold-500/20 transition-all">
                <i class="fa-solid fa-file-pdf text-sm"></i>
                <span>Reporte Ejecutivo (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros Dinámicos -->
    <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-4 shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 border-b border-brand-border/60 pb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-filter text-gold-400 text-sm"></i>
                <span class="text-xs font-bold text-slate-200 uppercase tracking-wider">Filtros del Período</span>
            </div>

            <!-- Presets de Período Rápido -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" wire:click="aplicarPreset('este_mes')" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $periodo_preset === 'este_mes' ? 'bg-gold-500 text-slate-950 shadow-md' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Este Mes
                </button>
                <button type="button" wire:click="aplicarPreset('mes_anterior')" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $periodo_preset === 'mes_anterior' ? 'bg-gold-500 text-slate-950 shadow-md' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Mes Anterior
                </button>
                <button type="button" wire:click="aplicarPreset('este_ano')" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $periodo_preset === 'este_ano' ? 'bg-gold-500 text-slate-950 shadow-md' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Este Año
                </button>
                <button type="button" wire:click="aplicarPreset('todo')" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $periodo_preset === 'todo' ? 'bg-gold-500 text-slate-950 shadow-md' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Todo el Histórico
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5 text-xs">
            <!-- Fecha Inicio -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Desde</label>
                <input type="date" wire:model.live="fecha_inicio" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>

            <!-- Fecha Fin -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hasta</label>
                <input type="date" wire:model.live="fecha_fin" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>

            <!-- Cliente -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cliente</label>
                <select wire:model.live="cliente_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los Clientes</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Evento -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Evento</label>
                <select wire:model.live="evento_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los Eventos</option>
                    @foreach($eventos as $ev)
                        <option value="{{ $ev->id }}">{{ $ev->codigo_evento }} ({{ $ev->cliente->nombre_completo ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Servicio -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Servicio</label>
                <select wire:model.live="servicio_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los Servicios</option>
                    @foreach($servicios as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Botón Limpiar -->
            <div class="flex items-end">
                <button type="button" wire:click="limpiarFiltros" 
                        class="w-full px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition-all flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Restablecer</span>
                </button>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 1: Tarjetas Principales de Clientes y Contratos -->
    <div>
        <div class="flex items-center gap-2 mb-3">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Gestión Contractual y Cobros a Clientes</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Total Contratado -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Total Contratado</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-white">
                    Bs. {{ number_format($kpis['total_contratado'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-handshake text-blue-400"></i>
                    <span>{{ $kpis['count_contratos'] ?? 0 }} contrato(s) formalizado(s)</span>
                </div>
            </div>

            <!-- Total Cobrado a Clientes -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Total Cobrado (Clientes)</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-400">
                    Bs. {{ number_format($kpis['total_cobrado'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-receipt text-emerald-400"></i>
                    <span>Pagos confirmados en caja</span>
                </div>
            </div>

            <!-- Saldo Pendiente de Cobro -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Saldo Pendiente de Cobro</span>
                    <div class="w-9 h-9 rounded-xl {{ ($kpis['saldo_pendiente_clientes'] ?? 0) > 0 ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' }} flex items-center justify-center">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono {{ ($kpis['saldo_pendiente_clientes'] ?? 0) > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                    Bs. {{ number_format($kpis['saldo_pendiente_clientes'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-clock text-amber-400"></i>
                    <span>Cuentas por cobrar a clientes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: Tarjetas de Participantes y Músicos -->
    <div>
        <div class="flex items-center gap-2 mb-3">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
            <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Distribución Económica y Comisiones a Músicos</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Total Asignado a Participantes -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Total Asignado a Músicos</span>
                    <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-purple-400">
                    Bs. {{ number_format($kpis['total_asignado_participantes'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Obligación económica total por servicios
                </div>
            </div>

            <!-- Total Pagado a Participantes -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Total Pagado a Músicos</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-400">
                    Bs. {{ number_format($kpis['total_pagado_participantes'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Comisiones efectivamente liquidadas
                </div>
            </div>

            <!-- Saldo Pendiente a Participantes -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Saldo Pendiente a Músicos</span>
                    <div class="w-9 h-9 rounded-xl {{ ($kpis['saldo_pendiente_participantes'] ?? 0) > 0 ? 'bg-rose-500/10 border-rose-500/20 text-rose-400' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' }} flex items-center justify-center">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono {{ ($kpis['saldo_pendiente_participantes'] ?? 0) > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    Bs. {{ number_format($kpis['saldo_pendiente_participantes'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Por liquidar a integrantes del mariachi
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: Gastos Operativos, Gastos de Eventos y Fondo Común -->
    <div>
        <div class="flex items-center gap-2 mb-3">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Gastos Operativos, Gastos por Evento y Fondo Común</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Gastos Operativos Generales -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-4 shadow-lg">
                <div class="flex items-center justify-between text-slate-400 mb-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Gastos Operativos</span>
                    <i class="fa-solid fa-building text-rose-400 text-xs"></i>
                </div>
                <div class="text-xl font-extrabold font-mono text-rose-400">
                    Bs. {{ number_format($kpis['total_gastos_operativos'] ?? 0, 2) }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Alquiler, servicios, insumos</div>
            </div>

            <!-- Gastos por Evento -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-4 shadow-lg">
                <div class="flex items-center justify-between text-slate-400 mb-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Gastos por Evento</span>
                    <i class="fa-solid fa-van-shuttle text-amber-400 text-xs"></i>
                </div>
                <div class="text-xl font-extrabold font-mono text-amber-400">
                    Bs. {{ number_format($kpis['total_gastos_eventos'] ?? 0, 2) }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Transporte, combustible, viáticos</div>
            </div>

            <!-- Total Egresos Registrados -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-4 shadow-lg">
                <div class="flex items-center justify-between text-slate-400 mb-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Total Egresos</span>
                    <i class="fa-solid fa-arrow-trend-down text-red-400 text-xs"></i>
                </div>
                <div class="text-xl font-extrabold font-mono text-red-400">
                    Bs. {{ number_format($kpis['total_egresos'] ?? 0, 2) }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Gastos operativos + eventos</div>
            </div>

            <!-- Saldo Fondo Común -->
            <div class="bg-brand-card border border-brand-border rounded-3xl p-4 shadow-lg">
                <div class="flex items-center justify-between text-slate-400 mb-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Saldo Fondo Común</span>
                    <i class="fa-solid fa-vault text-gold-400 text-xs"></i>
                </div>
                <div class="text-xl font-extrabold font-mono text-gold-400">
                    Bs. {{ number_format($kpis['saldo_fondo_comun'] ?? 0, 2) }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Disponible en caja/fondo común</div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 4: Indicadores de Rentabilidad y Balances -->
    <div class="bg-slate-950/80 p-6 rounded-3xl border border-gold-500/30 shadow-2xl space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-gold-400"></i>
                    <span>Balance y Rendimiento Operativo</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Cálculos exactos según las fórmulas oficiales de Sprint 3.
                </p>
            </div>
            <span class="text-[11px] font-mono text-slate-400 bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-800">
                Fórmulas contables verificadas
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Ingresos Consolidados -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Ingresos Consolidados
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-400">
                    Bs. {{ number_format($kpis['total_ingresos_consolidados'] ?? 0, 2) }}
                </div>
                <p class="text-[11px] text-slate-400">
                    Total cobrado a clientes (Bs. {{ number_format($kpis['total_cobrado'] ?? 0, 2) }}) + Ingresos adicionales (Bs. {{ number_format($kpis['total_ingresos_adicionales'] ?? 0, 2) }}).
                </p>
            </div>

            <!-- Balance Bruto de Eventos -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Balance Bruto de Eventos
                </div>
                <div class="text-2xl font-extrabold font-mono {{ ($kpis['balance_bruto_eventos'] ?? 0) >= 0 ? 'text-sky-400' : 'text-rose-400' }}">
                    Bs. {{ number_format($kpis['balance_bruto_eventos'] ?? 0, 2) }}
                </div>
                <p class="text-[11px] text-slate-400">
                    <strong>Fórmula:</strong> Total cobrado a clientes menos total asignado a participantes. (No constituye utilidad neta).
                </p>
            </div>

            <!-- Resultado Operativo Estimado -->
            <div class="bg-slate-900/90 border border-gold-500/30 rounded-2xl p-4 space-y-2 bg-gradient-to-br from-slate-900 to-gold-950/15">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gold-400">
                    Resultado Operativo Estimado
                </div>
                <div class="text-2xl font-extrabold font-mono {{ ($kpis['resultado_operativo_estimado'] ?? 0) >= 0 ? 'text-gold-400' : 'text-rose-400' }}">
                    Bs. {{ number_format($kpis['resultado_operativo_estimado'] ?? 0, 2) }}
                </div>
                <p class="text-[11px] text-slate-400">
                    <strong>Fórmula:</strong> Ingresos totales menos (comisiones asignadas + gastos de eventos + gastos operativos).
                </p>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 5: Gráficos y Distribución Analítica -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Gráfico 1: Evolución Mensual de Ingresos y Egresos (2 Columnas) -->
        <div class="lg:col-span-2 bg-brand-card p-6 rounded-3xl border border-brand-border space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-column text-gold-400 text-sm"></i>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Evolución Mensual (Últimos 6 Meses)</h3>
                </div>
                <span class="text-[11px] text-slate-400">Ingresos vs Egresos</span>
            </div>

            <!-- Visual Bar Chart -->
            <div class="space-y-4 pt-2">
                @php
                    $maxVal = 1;
                    foreach($graficos['meses'] ?? [] as $m) {
                        $maxVal = max($maxVal, $m['ingresos'], $m['egresos']);
                    }
                @endphp

                <div class="grid grid-cols-6 gap-2 sm:gap-4 items-end h-52 pt-4 px-2 border-b border-slate-800">
                    @forelse($graficos['meses'] ?? [] as $m)
                        @php
                            $hIng = min(100, max(4, ($m['ingresos'] / $maxVal) * 100));
                            $hEgr = min(100, max(4, ($m['egresos'] / $maxVal) * 100));
                        @endphp
                        <div class="flex flex-col items-center gap-1.5 h-full justify-end group">
                            <!-- Barras comparativas -->
                            <div class="w-full flex items-end justify-center gap-1 sm:gap-1.5 h-40">
                                <!-- Barra Ingreso -->
                                <div class="w-3 sm:w-5 bg-gradient-to-t from-emerald-600 to-emerald-400 rounded-t-md transition-all group-hover:brightness-110 shadow-sm"
                                     style="height: {{ $hIng }}%"
                                     title="Ingresos: Bs. {{ number_format($m['ingresos'], 2) }}">
                                </div>
                                <!-- Barra Egreso -->
                                <div class="w-3 sm:w-5 bg-gradient-to-t from-rose-600 to-rose-400 rounded-t-md transition-all group-hover:brightness-110 shadow-sm"
                                     style="height: {{ $hEgr }}%"
                                     title="Egresos: Bs. {{ number_format($m['egresos'], 2) }}">
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 text-center truncate w-full">
                                {{ $m['label'] }}
                            </span>
                        </div>
                    @empty
                        <div class="col-span-6 text-center py-10 text-slate-500 text-xs">
                            No se registran movimientos en el período consultado.
                        </div>
                    @endforelse
                </div>

                <!-- Leyenda del Gráfico -->
                <div class="flex items-center justify-center gap-6 text-xs pt-1">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded bg-emerald-500"></span>
                        <span class="text-slate-300">Ingresos Totales</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded bg-rose-500"></span>
                        <span class="text-slate-300">Egresos & Liquidaciones</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico 2: Distribución por Servicio y Gastos por Categoría -->
        <div class="bg-brand-card p-6 rounded-3xl border border-brand-border space-y-6 shadow-xl flex flex-col justify-between">
            <!-- Ingresos por Servicio -->
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-2">
                    <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-music text-gold-400"></i>
                        <span>Ingresos por Servicio</span>
                    </h4>
                </div>
                <div class="space-y-2">
                    @php
                        $maxServ = $graficos['ingresos_por_servicio']->max('total') ?: 1;
                    @endphp
                    @forelse($graficos['ingresos_por_servicio'] ?? [] as $serv)
                        @php
                            $pctServ = min(100, round(($serv->total / $maxServ) * 100));
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300 truncate font-medium">{{ $serv->servicio }}</span>
                                <span class="font-mono font-bold text-emerald-400 text-[11px]">Bs. {{ number_format($serv->total, 2) }}</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-slate-900 overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pctServ }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic py-2">Sin datos de servicios en el período.</p>
                    @endforelse
                </div>
            </div>

            <!-- Gastos por Categoría -->
            <div class="space-y-3 border-t border-brand-border/60 pt-4">
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-2">
                    <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-tags text-rose-400"></i>
                        <span>Gastos por Categoría</span>
                    </h4>
                </div>
                <div class="space-y-2">
                    @php
                        $maxCat = $graficos['gastos_por_categoria']->max('total') ?: 1;
                    @endphp
                    @forelse($graficos['gastos_por_categoria'] ?? [] as $cat)
                        @php
                            $pctCat = min(100, round(($cat->total / $maxCat) * 100));
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300 truncate font-medium">{{ $cat->categoria }}</span>
                                <span class="font-mono font-bold text-rose-400 text-[11px]">Bs. {{ number_format($cat->total, 2) }}</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-slate-900 overflow-hidden">
                                <div class="h-full bg-rose-500 rounded-full" style="width: {{ $pctCat }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic py-2">Sin gastos registrados en el período.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
