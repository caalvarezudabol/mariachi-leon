<div class="space-y-6">
    <!-- Header de la Pantalla -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-brand-card p-6 rounded-3xl border border-brand-border shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-400 text-2xl shadow-inner">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Distribución Económica a Músicos</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700 font-mono">
                        Personal
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Control de asignaciones, liquidaciones de comisiones, pagos efectuados y saldos pendientes a integrantes.
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
            <a href="{{ route('admin.reportes.distribucion.pdf', [
                    'modalidad' => $modalidad,
                    'evento_id' => $evento_id,
                    'persona_id' => $persona_id,
                    'desde' => $fecha_desde,
                    'hasta' => $fecha_hasta,
                ]) }}" 
               target="_blank"
               class="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-500 to-amber-500 hover:from-gold-400 hover:to-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-gold-500/20 transition-all">
                <i class="fa-solid fa-file-pdf text-sm"></i>
                <span>Exportar PDF</span>
            </a>

            <!-- Exportar Excel -->
            <a href="{{ route('admin.reportes.distribucion.excel', [
                    'modalidad' => $modalidad,
                    'evento_id' => $evento_id,
                    'persona_id' => $persona_id,
                    'desde' => $fecha_desde,
                    'hasta' => $fecha_hasta,
                ]) }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-file-excel text-emerald-400"></i>
                <span>Excel</span>
            </a>
        </div>
    </div>

    <!-- Pestañas de Modalidad -->
    <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-brand-border/60">
            <div class="flex items-center bg-slate-950 p-1 rounded-2xl border border-slate-800 flex-wrap gap-1">
                <button type="button" wire:click="$set('modalidad', 'consolidado')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $modalidad === 'consolidado' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-table-list"></i>
                    <span>Consolidado por Período</span>
                </button>
                <button type="button" wire:click="$set('modalidad', 'por_evento')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $modalidad === 'por_evento' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-calendar-day"></i>
                    <span>Por Evento Específico</span>
                </button>
                <button type="button" wire:click="$set('modalidad', 'por_participante')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $modalidad === 'por_participante' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-user-music"></i>
                    <span>Por Integrante Individual</span>
                </button>
            </div>

            <span class="text-xs text-slate-400">
                Vista: <strong class="text-white">
                    @if($modalidad === 'consolidado') Consolidado de Todos los Integrantes
                    @elseif($modalidad === 'por_evento') Detalle de un Evento Específico
                    @else Historial de un Integrante
                    @endif
                </strong>
            </span>
        </div>

        <!-- Filtros según Modalidad -->
        @if($modalidad === 'consolidado')
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Buscar Integrante</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search" 
                               placeholder="Nombre del músico, CI, teléfono..." 
                               class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-500 text-xs"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Desde</label>
                    <input type="date" wire:model.live="fecha_desde" 
                           class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hasta</label>
                    <input type="date" wire:model.live="fecha_hasta" 
                           class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                </div>
            </div>

        @elseif($modalidad === 'por_evento')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Seleccionar Evento <span class="text-rose-400">*</span>
                    </label>
                    <select wire:model.live="evento_id" 
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                        <option value="">Seleccione un evento...</option>
                        @foreach($eventosLista as $ev)
                            <option value="{{ $ev->id }}">
                                {{ $ev->codigo_evento }} | {{ \Carbon\Carbon::parse($ev->fecha_evento)->format('d/m/Y') }} | {{ $ev->cliente->nombre_completo ?? 'Cliente S/N' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        @else
            <!-- Por Participante Individual -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Seleccionar Integrante <span class="text-rose-400">*</span>
                    </label>
                    <select wire:model.live="persona_id" 
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                        <option value="">Seleccione un integrante...</option>
                        @foreach($musicosLista as $m)
                            <option value="{{ $m->id }}">{{ $m->nombre_completo }} ({{ $m->tipo }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Desde</label>
                    <input type="date" wire:model.live="fecha_desde" 
                           class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hasta</label>
                    <input type="date" wire:model.live="fecha_hasta" 
                           class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                </div>
            </div>
        @endif
    </div>

    <!-- ============================================================= -->
    <!-- VISTA 1: CONSOLIDADO POR PERÍODO -->
    <!-- ============================================================= -->
    @if($modalidad === 'consolidado')
        <!-- Tarjetas de Resumen Global -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Integrantes</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-users"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-white font-mono">
                    {{ number_format($resumenGlobal['total_musicos']) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-2">Músicos y personal activo</p>
            </div>

            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Total Asignado</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-800 text-gold-400 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-white font-mono">
                    Bs. {{ number_format($resumenGlobal['total_asignado'], 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-2">Comisiones totales comprometidas</p>
            </div>

            <div class="bg-brand-card p-5 rounded-3xl border border-emerald-500/30 shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Total Pagado</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-300 font-mono">
                    Bs. {{ number_format($resumenGlobal['total_pagado'], 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-2">Efectivo o QR transferido</p>
            </div>

            <div class="bg-brand-card p-5 rounded-3xl border {{ $resumenGlobal['saldo_pendiente'] > 0 ? 'border-rose-500/40' : 'border-emerald-500/40' }} shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $resumenGlobal['saldo_pendiente'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">Saldo Pendiente</span>
                    <span class="w-8 h-8 rounded-xl {{ $resumenGlobal['saldo_pendiente'] > 0 ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' }} border flex items-center justify-center text-sm">
                        <i class="fa-solid fa-clock"></i>
                    </span>
                </div>
                <div class="text-2xl font-black font-mono {{ $resumenGlobal['saldo_pendiente'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    Bs. {{ number_format($resumenGlobal['saldo_pendiente'], 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-2">Por liquidar a integrantes</p>
            </div>
        </div>

        <!-- Tabla Consolidada -->
        <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                        <tr>
                            <th class="py-3.5 px-4">Integrante</th>
                            <th class="py-3.5 px-4">Tipo</th>
                            <th class="py-3.5 px-4">Teléfono</th>
                            <th class="py-3.5 px-4 text-center">Eventos</th>
                            <th class="py-3.5 px-4 text-right">Total Asignado</th>
                            <th class="py-3.5 px-4 text-right">Total Pagado</th>
                            <th class="py-3.5 px-4 text-right">Saldo Pendiente</th>
                            <th class="py-3.5 px-4 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/60">
                        @forelse($filasConsolidadas as $fc)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-white whitespace-nowrap">
                                    {{ $fc['nombre_completo'] }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $fc['tipo'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400 font-mono">
                                    {{ $fc['telefono'] ?: '—' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-white">
                                    {{ $fc['total_eventos'] }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-white whitespace-nowrap">
                                    Bs. {{ number_format($fc['total_asignado'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400 whitespace-nowrap">
                                    Bs. {{ number_format($fc['total_pagado'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $fc['saldo_pendiente'] > 0 ? 'text-rose-400' : 'text-slate-500' }}">
                                    Bs. {{ number_format($fc['saldo_pendiente'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($fc['saldo_pendiente'] <= 0.01)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            Al Día
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500">
                                    <p class="text-sm font-semibold text-slate-400">No hay registros de integrantes para este período</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- ============================================================= -->
    <!-- VISTA 2: POR EVENTO ESPECÍFICO -->
    <!-- ============================================================= -->
    @elseif($modalidad === 'por_evento')
        @if($evento)
            <!-- Tarjeta Informativa del Evento -->
            <div class="bg-brand-card p-6 rounded-3xl border border-brand-border space-y-4 shadow-xl">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-brand-border/60 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-gold-500/10 text-gold-400 border border-gold-500/20 font-mono font-bold">
                                {{ $evento->codigo_evento }}
                            </span>
                            <h3 class="text-lg font-bold text-white">{{ $evento->cliente->nombre_completo ?? 'Cliente S/N' }}</h3>
                        </div>
                        <p class="text-xs text-slate-400">
                            Fecha: <span class="text-white font-mono">{{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }}</span> |
                            Hora: <span class="text-white font-mono">{{ substr($evento->hora_evento, 0, 5) }}</span> |
                            Servicio: <span class="text-amber-300">{{ $evento->servicio->nombre ?? 'Estándar' }}</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-4 text-xs">
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 uppercase tracking-wider block">Contratado Cliente</span>
                            <span class="text-base font-bold text-white font-mono">Bs. {{ number_format($evento->monto_contratado, 2) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-emerald-400 uppercase tracking-wider block">Cobrado Cliente</span>
                            <span class="text-base font-bold text-emerald-400 font-mono">Bs. {{ number_format($evento->total_abonado, 2) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-rose-400 uppercase tracking-wider block">Saldo Cliente</span>
                            <span class="text-base font-bold text-rose-400 font-mono">Bs. {{ number_format($evento->saldo_pendiente, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tarjetas de Resumen del Evento -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Total Comisiones Asignadas</span>
                        <span class="text-xl font-black text-white font-mono mt-1 block">Bs. {{ number_format($resumenEvento['total_asignado'], 2) }}</span>
                        <span class="text-[10px] text-slate-500">{{ $resumenEvento['cantidad_participantes'] }} integrantes asignados</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-950 border border-emerald-500/30">
                        <span class="text-[11px] text-emerald-400 font-bold uppercase tracking-wider block">Total Pagado a Músicos</span>
                        <span class="text-xl font-black text-emerald-300 font-mono mt-1 block">Bs. {{ number_format($resumenEvento['total_pagado'], 2) }}</span>
                        <span class="text-[10px] text-slate-500">Distribuciones ya pagadas</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-950 border border-rose-500/30">
                        <span class="text-[11px] text-rose-400 font-bold uppercase tracking-wider block">Saldo Pendiente a Músicos</span>
                        <span class="text-xl font-black text-rose-400 font-mono mt-1 block">Bs. {{ number_format($resumenEvento['saldo_pendiente'], 2) }}</span>
                        <span class="text-[10px] text-slate-500">Por liquidar a integrantes de este evento</span>
                    </div>
                </div>
            </div>

            <!-- Tabla de Participantes del Evento -->
            <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                            <tr>
                                <th class="py-3.5 px-4">Integrante</th>
                                <th class="py-3.5 px-4">Instrumento / Función</th>
                                <th class="py-3.5 px-4">Concepto</th>
                                <th class="py-3.5 px-4 text-right">Asignado</th>
                                <th class="py-3.5 px-4 text-right">Pagado</th>
                                <th class="py-3.5 px-4 text-right">Saldo</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/60">
                            @forelse($participantes as $p)
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="py-3 px-4 font-bold text-white whitespace-nowrap">
                                        {{ $p->persona->nombre_completo ?? 'No asignado' }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-300">
                                        {{ $p->funcion }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-400">
                                        {{ $p->concepto_pago }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-white whitespace-nowrap">
                                        Bs. {{ number_format($p->monto_asignado, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400 whitespace-nowrap">
                                        Bs. {{ number_format($p->total_pagado, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $p->saldo_pendiente > 0 ? 'text-rose-400' : 'text-slate-500' }}">
                                        Bs. {{ number_format($p->saldo_pendiente, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if($p->estado_calculado === 'PAGADO')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                Pagado
                                            </span>
                                        @elseif($p->estado_calculado === 'PAGO PARCIAL')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                                Pago Parcial
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-500">
                                        <p class="text-sm font-semibold text-slate-400">No hay participantes asignados a este evento</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-brand-card p-12 rounded-3xl border border-brand-border text-center">
                <p class="text-slate-400">Seleccione un evento para consultar su distribución económica.</p>
            </div>
        @endif

    <!-- ============================================================= -->
    <!-- VISTA 3: POR INTEGRANTE INDIVIDUAL -->
    <!-- ============================================================= -->
    @else
        @if($persona)
            <!-- Tarjetas de Resumen del Integrante -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Eventos Asignados</span>
                    <span class="text-2xl font-black text-white font-mono">{{ $resumenParticipante['total_eventos'] }}</span>
                    <p class="text-[11px] text-slate-500 mt-2">En el período consultado</p>
                </div>

                <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300 block mb-1">Total Comisiones Asignadas</span>
                    <span class="text-2xl font-black text-white font-mono">Bs. {{ number_format($resumenParticipante['total_asignado'], 2) }}</span>
                    <p class="text-[11px] text-slate-500 mt-2">Monto pactado total</p>
                </div>

                <div class="bg-brand-card p-5 rounded-3xl border border-emerald-500/30 shadow-lg">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Total Cobrado</span>
                    <span class="text-2xl font-black text-emerald-300 font-mono">Bs. {{ number_format($resumenParticipante['total_pagado'], 2) }}</span>
                    <p class="text-[11px] text-slate-500 mt-2">Pagos efectivos recibidos</p>
                </div>

                <div class="bg-brand-card p-5 rounded-3xl border {{ $resumenParticipante['saldo_pendiente'] > 0 ? 'border-rose-500/40' : 'border-emerald-500/40' }} shadow-lg">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $resumenParticipante['saldo_pendiente'] > 0 ? 'text-rose-400' : 'text-emerald-400' }} block mb-1">Saldo por Cobrar</span>
                    <span class="text-2xl font-black font-mono {{ $resumenParticipante['saldo_pendiente'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">Bs. {{ number_format($resumenParticipante['saldo_pendiente'], 2) }}</span>
                    <p class="text-[11px] text-slate-500 mt-2">Pendiente de desembolso</p>
                </div>
            </div>

            <!-- Tabla Histórica de Participaciones -->
            <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden shadow-xl">
                <div class="p-5 border-b border-brand-border flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-gold-400"></i>
                        <span>Historial de Participaciones de {{ $persona->nombre_completo }}</span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                            <tr>
                                <th class="py-3.5 px-4">Fecha Evento</th>
                                <th class="py-3.5 px-4">Cód. Evento</th>
                                <th class="py-3.5 px-4">Cliente</th>
                                <th class="py-3.5 px-4">Función / Instrumento</th>
                                <th class="py-3.5 px-4 text-right">Asignado</th>
                                <th class="py-3.5 px-4 text-right">Pagado</th>
                                <th class="py-3.5 px-4 text-right">Saldo</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/60">
                            @forelse($participaciones as $part)
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="py-3 px-4 font-mono text-slate-300 whitespace-nowrap">
                                        {{ $part->evento ? \Carbon\Carbon::parse($part->evento->fecha_evento)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-gold-400 whitespace-nowrap">
                                        {{ $part->evento->codigo_evento ?? 'S/E' }}
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate text-white">
                                        {{ $part->evento->cliente->nombre_completo ?? 'Cliente S/N' }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-300">
                                        {{ $part->funcion }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-white whitespace-nowrap">
                                        Bs. {{ number_format($part->monto_asignado, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400 whitespace-nowrap">
                                        Bs. {{ number_format($part->total_pagado, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $part->saldo_pendiente > 0 ? 'text-rose-400' : 'text-slate-500' }}">
                                        Bs. {{ number_format($part->saldo_pendiente, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if($part->estado_calculado === 'PAGADO')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                Pagado
                                            </span>
                                        @elseif($part->estado_calculado === 'PAGO PARCIAL')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                                Pago Parcial
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-500">
                                        <p class="text-sm font-semibold text-slate-400">No se encontraron participaciones para este integrante en el rango de fechas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-brand-card p-12 rounded-3xl border border-brand-border text-center">
                <p class="text-slate-400">Seleccione un integrante para consultar su historial de distribución económica.</p>
            </div>
        @endif
    @endif
</div>
