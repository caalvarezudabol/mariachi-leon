<div class="space-y-6">
    <!-- Header de la Pantalla -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-brand-card p-6 rounded-3xl border border-brand-border shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-400 text-2xl shadow-inner">
                <i class="fa-solid fa-file-contract"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Reporte de Eventos & Contratos</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700 font-mono">
                        Comercial
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Consulta integral de presentaciones, contratos, importes pactados y cobranzas.
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
            <a href="{{ route('admin.reportes.eventos-contratos.pdf', [
                    'desde' => $fecha_desde,
                    'hasta' => $fecha_hasta,
                    'campo_fecha' => $campo_fecha,
                    'cliente_id' => $cliente_id,
                    'servicio_id' => $servicio_id,
                    'estado_contrato' => $estado_contrato,
                    'estado_evento' => $estado_evento,
                    'search_lugar' => $search_lugar,
                ]) }}" 
               target="_blank"
               class="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-500 to-amber-500 hover:from-gold-400 hover:to-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-gold-500/20 transition-all">
                <i class="fa-solid fa-file-pdf text-sm"></i>
                <span>Exportar PDF</span>
            </a>

            <!-- Exportar Excel -->
            <a href="{{ route('admin.reportes.eventos-contratos.excel', [
                    'desde' => $fecha_desde,
                    'hasta' => $fecha_hasta,
                    'campo_fecha' => $campo_fecha,
                    'cliente_id' => $cliente_id,
                    'servicio_id' => $servicio_id,
                    'estado_contrato' => $estado_contrato,
                    'estado_evento' => $estado_evento,
                    'search_lugar' => $search_lugar,
                ]) }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-file-excel text-emerald-400"></i>
                <span>Excel</span>
            </a>
        </div>
    </div>

    <!-- Tarjetas de Resumen Global (Conforme a Filtros) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Registros -->
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Eventos / Contratos</span>
                <span class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-list-check"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-white font-mono">
                {{ number_format($resumen['total_registros']) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Registros que cumplen con los filtros</p>
        </div>

        <!-- Total Contratado -->
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Total Contratado</span>
                <span class="w-8 h-8 rounded-xl bg-gold-500/10 text-gold-400 border border-gold-500/20 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-handshake"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-gold-400 font-mono">
                Bs. {{ number_format($resumen['total_contratado'], 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Suma total pactada en contratos</p>
        </div>

        <!-- Total Cobrado -->
        <div class="bg-brand-card p-5 rounded-3xl border border-emerald-500/20 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Total Cobrado</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-300 font-mono">
                Bs. {{ number_format($resumen['total_cobrado'], 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Pagos y anticipos confirmados</p>
        </div>

        <!-- Total Saldo Pendiente -->
        <div class="bg-brand-card p-5 rounded-3xl border border-rose-500/20 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-400">Saldo por Cobrar</span>
                <span class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-clock"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-rose-400 font-mono">
                Bs. {{ number_format($resumen['total_saldo_pendiente'], 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Pendiente de cobro a clientes</p>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-4 shadow-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 text-xs">
            <!-- Buscar general -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Buscar</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Cód. evento, contrato, cliente..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-500 text-xs"></i>
                </div>
            </div>

            <!-- Criterio de Fecha -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha Por</label>
                <select wire:model.live="campo_fecha" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="fecha_evento">Fecha de Evento</option>
                    <option value="fecha_contrato">Fecha de Contrato</option>
                </select>
            </div>

            <!-- Desde -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Desde</label>
                <input type="date" wire:model.live="fecha_desde" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>

            <!-- Hasta -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hasta</label>
                <input type="date" wire:model.live="fecha_hasta" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>

            <!-- Cliente -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cliente</label>
                <select wire:model.live="cliente_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Servicio -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Servicio</label>
                <select wire:model.live="servicio_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos</option>
                    @foreach($servicios as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Estado Contrato -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Estado Contrato</label>
                <select wire:model.live="estado_contrato" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos</option>
                    <option value="Confirmado">Confirmado</option>
                    <option value="Borrador">Borrador</option>
                    <option value="En Ejecucion">En Ejecución</option>
                    <option value="Finalizado">Finalizado</option>
                    <option value="Cancelado">Cancelado</option>
                    <option value="sin_contrato">Sin Contrato</option>
                </select>
            </div>
        </div>

        <!-- Segunda Fila de Filtros -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs pt-1 border-t border-brand-border/60">
            <!-- Estado Evento -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Estado Evento</label>
                <select wire:model.live="estado_evento" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los Estados</option>
                    <option value="Programado">Programado</option>
                    <option value="Confirmado">Confirmado</option>
                    <option value="En Proceso">En Proceso</option>
                    <option value="Finalizado">Finalizado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>

            <!-- Lugar / Ciudad -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Ubicación / Dirección / Municipio</label>
                <input type="text" wire:model.live.debounce.300ms="search_lugar" 
                       placeholder="Filtrar por dirección, salón, zona..." 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>
        </div>
    </div>

    <!-- Tabla de Eventos y Contratos -->
    <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                    <tr>
                        <th class="py-3.5 px-4">Contrato</th>
                        <th class="py-3.5 px-4">Evento</th>
                        <th class="py-3.5 px-4">Fecha & Hora</th>
                        <th class="py-3.5 px-4">Cliente</th>
                        <th class="py-3.5 px-4">Servicio</th>
                        <th class="py-3.5 px-4">Ubicación</th>
                        <th class="py-3.5 px-4 text-center">Estado Contrato</th>
                        <th class="py-3.5 px-4 text-center">Estado Evento</th>
                        <th class="py-3.5 px-4 text-right">Contratado</th>
                        <th class="py-3.5 px-4 text-right">Cobrado</th>
                        <th class="py-3.5 px-4 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($eventos as $ev)
                        @php
                            $montoContratado = $ev->monto_contratado;
                            $totalCobrado = $ev->total_abonado;
                            $saldoPendiente = $ev->saldo_pendiente;
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <!-- Contrato -->
                            <td class="py-3 px-4 font-mono whitespace-nowrap">
                                @if($ev->contrato)
                                    <span class="font-bold text-gold-400">{{ $ev->contrato->numero_contrato }}</span>
                                @else
                                    <span class="text-slate-500 italic">Sin contrato</span>
                                @endif
                            </td>

                            <!-- Evento -->
                            <td class="py-3 px-4 font-mono font-bold text-white whitespace-nowrap">
                                {{ $ev->codigo_evento }}
                            </td>

                            <!-- Fecha & Hora -->
                            <td class="py-3 px-4 font-mono text-slate-300 whitespace-nowrap">
                                <div>{{ \Carbon\Carbon::parse($ev->fecha_evento)->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-500">{{ substr($ev->hora_evento, 0, 5) }}</div>
                            </td>

                            <!-- Cliente -->
                            <td class="py-3 px-4 max-w-[160px]">
                                <div class="font-bold text-white truncate" title="{{ $ev->cliente->nombre_completo ?? '' }}">
                                    {{ $ev->cliente->nombre_completo ?? 'Cliente no asignado' }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $ev->telefono_contacto ?: ($ev->cliente->telefono ?? '') }}
                                </div>
                            </td>

                            <!-- Servicio -->
                            <td class="py-3 px-4 max-w-[130px] truncate text-slate-300">
                                {{ $ev->servicio->nombre ?? 'Servicio Estándar' }}
                            </td>

                            <!-- Ubicación -->
                            <td class="py-3 px-4 max-w-[150px] truncate text-slate-400" title="{{ $ev->direccion_evento }}">
                                {{ $ev->direccion_evento ?: 'No especificada' }}
                            </td>

                            <!-- Estado Contrato -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($ev->contrato)
                                    @php
                                        $colorContrato = match($ev->contrato->estado) {
                                            'Confirmado' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                            'Finalizado' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                            'En Ejecucion' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                            'Cancelado' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                            default => 'bg-slate-800 text-slate-400 border-slate-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $colorContrato }}">
                                        {{ $ev->contrato->estado }}
                                    </span>
                                @else
                                    <span class="text-slate-600 text-[10px] italic">—</span>
                                @endif
                            </td>

                            <!-- Estado Evento -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @php
                                    $colorEvento = match($ev->estado) {
                                        'Confirmado' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                        'Finalizado' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                        'En Proceso' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                        'Cancelado' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                        default => 'bg-slate-800 text-slate-300 border-slate-700',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $colorEvento }}">
                                    {{ $ev->estado }}
                                </span>
                            </td>

                            <!-- Importe Contratado -->
                            <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold text-white">
                                Bs. {{ number_format($montoContratado, 2) }}
                            </td>

                            <!-- Total Cobrado -->
                            <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold text-emerald-400">
                                Bs. {{ number_format($totalCobrado, 2) }}
                            </td>

                            <!-- Saldo Pendiente -->
                            <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold {{ $saldoPendiente > 0 ? 'text-rose-400' : 'text-slate-500' }}">
                                Bs. {{ number_format($saldoPendiente, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 text-xl mx-auto mb-3">
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No se encontraron eventos o contratos</p>
                                <p class="text-xs text-slate-500 mt-1">Intente cambiar los filtros de fecha, cliente o estado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($eventos->hasPages())
            <div class="p-4 border-t border-brand-border bg-slate-950/40">
                {{ $eventos->links() }}
            </div>
        @endif
    </div>
</div>
