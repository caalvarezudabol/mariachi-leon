<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-gold-400"></i>
                <span>Gestionar Agenda de Eventos</span>
            </h1>
            <p class="text-xs text-slate-400">Control calendarizado de presentaciones, traslados y detección de conflictos de horario.</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Selector de Vistas -->
            <div class="p-1 bg-slate-950 border border-slate-800 rounded-2xl flex items-center gap-1 shadow-inner">
                <button wire:click="cambiarVista('dia')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $vistaModo === 'dia' ? 'bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 shadow-md shadow-gold-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="fa-solid fa-calendar-day text-xs"></i>
                    <span>Día</span>
                </button>
                <button wire:click="cambiarVista('semana')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $vistaModo === 'semana' ? 'bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 shadow-md shadow-gold-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="fa-solid fa-calendar-week text-xs"></i>
                    <span>Semana</span>
                </button>
                <button wire:click="cambiarVista('mes')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $vistaModo === 'mes' ? 'bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 shadow-md shadow-gold-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="fa-solid fa-calendar-days text-xs"></i>
                    <span>Mes</span>
                </button>
            </div>

            <!-- Botón Nuevo Evento -->
            <a href="{{ route('admin.eventos') }}" 
               class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition-all flex items-center gap-2 shadow-lg">
                <i class="fa-solid fa-plus text-gold-400"></i>
                <span class="hidden sm:inline">Nuevo Evento</span>
            </a>
        </div>
    </div>

    <!-- Navigation Bar & Controls -->
    <div class="bg-brand-card p-4 rounded-2xl border border-brand-border flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center gap-2">
            <button wire:click="fechaAnterior" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-gold-400 hover:border-gold-500/50 transition-all">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <button wire:click="fechaHoy" class="px-4 py-2 rounded-xl bg-slate-950 border border-slate-800 text-gold-400 hover:bg-slate-900 font-extrabold text-xs tracking-wider transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-crosshairs"></i>
                <span>HOY</span>
            </button>
            <button wire:click="fechaSiguiente" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-gold-400 hover:border-gold-500/50 transition-all">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>

        <div class="text-base font-black text-white uppercase tracking-widest font-mono text-center">
            @if($vistaModo === 'dia')
                {{ $fechaActual->translatedFormat('l, d \d\e F \d\e Y') }}
            @elseif($vistaModo === 'semana')
                Semana del {{ $inicioPeriodo->translatedFormat('d \d\e M') }} al {{ $finPeriodo->translatedFormat('d \d\e M \d\e Y') }}
            @else
                {{ $fechaActual->translatedFormat('F Y') }}
            @endif
        </div>

        <!-- Leyenda de Estados y Conflictos -->
        <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-400 flex-wrap justify-center">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Confirmado</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Reservado</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Pendiente</span>
            @if(count($conflictos) > 0)
                <span class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/40 font-bold animate-pulse">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ count($conflictos) }} Conflicto(s)
                </span>
            @endif
        </div>
    </div>

    <!-- Advertencia de Conflictos de Horario -->
    @if(count($conflictos) > 0)
        <div class="p-4 bg-rose-950/40 border border-rose-500/40 rounded-2xl text-rose-300 text-xs flex items-center justify-between gap-3 shadow-lg shadow-rose-950/20">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-xl text-rose-400 animate-bounce"></i>
                <div>
                    <strong class="font-extrabold text-white uppercase tracking-wide">¡Alerta de Solapamiento de Horarios!</strong>
                    <p class="text-rose-300/90 mt-0.5">Existen eventos cruzados a la misma hora en la agenda. Revisa los eventos marcados con la insignia roja en el calendario.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- VISTA 1: CALENDARIO MENSUAL (CUADRÍCULA 7x5) -->
    <!-- ========================================== -->
    @if($vistaModo === 'mes')
        <div class="bg-brand-card border border-brand-border rounded-3xl overflow-hidden shadow-2xl p-4 space-y-3">
            <!-- Encabezado Días de la Semana -->
            <div class="grid grid-cols-7 gap-1 sm:gap-2 text-center text-xs font-black uppercase text-slate-400 tracking-wider pb-2 border-b border-brand-border">
                <div class="py-1"><span class="hidden sm:inline">LUNES</span><span class="sm:hidden">LUN</span></div>
                <div class="py-1"><span class="hidden sm:inline">MARTES</span><span class="sm:hidden">MAR</span></div>
                <div class="py-1"><span class="hidden sm:inline">MIÉRCOLES</span><span class="sm:hidden">MIÉ</span></div>
                <div class="py-1"><span class="hidden sm:inline">JUEVES</span><span class="sm:hidden">JUE</span></div>
                <div class="py-1"><span class="hidden sm:inline">VIERNES</span><span class="sm:hidden">VIE</span></div>
                <div class="py-1"><span class="hidden sm:inline">SÁBADO</span><span class="sm:hidden">SÁB</span></div>
                <div class="py-1 text-gold-400"><span class="hidden sm:inline">DOMINGO</span><span class="sm:hidden">DOM</span></div>
            </div>

            <!-- Cuadrícula del Mes (Grid de Cajas de Días) -->
            <div class="grid grid-cols-7 gap-1 sm:gap-2">
                @foreach($diasCalendario as $dia)
                    <div wire:key="dia-{{ $dia['dateKey'] }}" 
                         class="min-h-[120px] sm:min-h-[140px] p-2 rounded-2xl border transition-all flex flex-col justify-between relative group
                         {{ $dia['esHoy'] ? 'bg-gold-500/10 border-gold-500 shadow-lg shadow-gold-500/20 ring-1 ring-gold-500/50' : ($dia['esMesActual'] ? 'bg-slate-950/70 border-slate-800/80 hover:border-slate-700' : 'bg-slate-950/20 border-slate-900/40 opacity-40') }}">
                        
                        <!-- Top Bar del Día: Número de Día e Indicadores -->
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <button type="button" 
                                    wire:click="irADia('{{ $dia['dateKey'] }}')"
                                    title="Ver agenda detallada del {{ $dia['dateKey'] }}"
                                    class="h-7 w-7 rounded-xl flex items-center justify-center font-mono text-xs font-black transition-transform hover:scale-110 cursor-pointer
                                           {{ $dia['esHoy'] ? 'bg-gold-500 text-slate-950 shadow-md font-extrabold' : ($dia['esMesActual'] ? 'text-slate-200 hover:bg-slate-800' : 'text-slate-600') }}">
                                {{ $dia['diaNumero'] }}
                            </button>

                            @if($dia['eventos']->count() > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-900 border border-slate-800 text-gold-400">
                                    {{ $dia['eventos']->count() }} {{ $dia['eventos']->count() === 1 ? 'evt' : 'evts' }}
                                </span>
                            @endif
                        </div>

                        <!-- Lista de Tarjetas de Evento en la Celda -->
                        <div class="space-y-1.5 flex-1 overflow-y-auto max-h-[100px] sm:max-h-[120px] custom-scrollbar pr-0.5">
                            @foreach($dia['eventos'] as $ev)
                                <div wire:click="verDetalleEvento({{ $ev->id }})" 
                                     wire:key="ev-chip-{{ $ev->id }}"
                                     class="p-1.5 rounded-xl border text-[11px] leading-tight cursor-pointer transition-all hover:scale-[1.02] shadow-md flex flex-col gap-0.5
                                     @if(isset($conflictos[$ev->id])) bg-rose-950/80 border-rose-500 text-rose-200 ring-1 ring-rose-500
                                     @elseif($ev->estado === 'Confirmado' || $ev->estado === 'Realizado' || $ev->estado === 'Finalizado') bg-emerald-950/60 border-emerald-500/40 text-emerald-300 hover:border-emerald-400
                                     @elseif($ev->estado === 'Reservado' || $ev->estado === 'Cotizado') bg-amber-950/60 border-amber-500/40 text-amber-300 hover:border-amber-400
                                     @elseif($ev->estado === 'Cancelado') bg-rose-950/40 border-rose-800 text-rose-400 line-through opacity-70
                                     @else bg-sky-950/60 border-sky-500/40 text-sky-300 hover:border-sky-400 @endif">
                                    
                                    <div class="flex items-center justify-between gap-1 font-bold">
                                        <span class="font-mono text-[10px] tracking-tight truncate flex items-center gap-1">
                                            <i class="fa-regular fa-clock text-[9px] opacity-80"></i>
                                            <span>{{ substr($ev->hora_evento, 0, 5) }}</span>
                                        </span>
                                        @if(isset($conflictos[$ev->id]))
                                            <i class="fa-solid fa-triangle-exclamation text-rose-400 text-[10px] animate-ping"></i>
                                        @endif
                                    </div>

                                    <div class="font-bold truncate text-[10px] text-white">
                                        {{ $ev->cliente->nombre_completo ?? $ev->codigo_evento }}
                                    </div>

                                    <div class="text-[9px] text-slate-300/80 truncate">
                                        {{ $ev->servicio->nombre ?? 'Servicio' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- VISTA 2: CALENDARIO SEMANAL (7 COLUMNAS)   -->
    <!-- ========================================== -->
    @if($vistaModo === 'semana')
        <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
            @foreach($diasCalendario as $dia)
                <div class="bg-brand-card border rounded-2xl overflow-hidden flex flex-col justify-between shadow-xl {{ $dia['esHoy'] ? 'border-gold-500/80 ring-1 ring-gold-500/40' : 'border-brand-border' }}">
                    <!-- Header del Día en la Semana -->
                    <div class="p-3 border-b text-center {{ $dia['esHoy'] ? 'bg-gold-500/10 border-gold-500/40' : 'bg-slate-950/80 border-brand-border' }}">
                        <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            {{ $dia['fecha']->translatedFormat('l') }}
                        </div>
                        <div class="text-base font-black font-mono {{ $dia['esHoy'] ? 'text-gold-400' : 'text-white' }}">
                            {{ $dia['fecha']->translatedFormat('d M') }}
                        </div>
                    </div>

                    <!-- Lista de Eventos del Día -->
                    <div class="p-3 space-y-3 min-h-[300px] flex-1 bg-slate-950/40">
                        @forelse($dia['eventos'] as $ev)
                            <div wire:click="verDetalleEvento({{ $ev->id }})" 
                                 class="p-3 rounded-xl border text-xs cursor-pointer transition-all hover:scale-[1.02] shadow-lg space-y-1.5
                                 @if(isset($conflictos[$ev->id])) bg-rose-950/80 border-rose-500 text-white
                                 @elseif($ev->estado === 'Confirmado' || $ev->estado === 'Realizado' || $ev->estado === 'Finalizado') bg-emerald-950/40 border-emerald-500/40 text-emerald-200
                                 @elseif($ev->estado === 'Reservado' || $ev->estado === 'Cotizado') bg-amber-950/40 border-amber-500/40 text-amber-200
                                 @else bg-sky-950/40 border-sky-500/40 text-sky-200 @endif">
                                
                                <div class="flex items-center justify-between gap-1 border-b border-slate-800/60 pb-1 font-mono font-bold text-[11px]">
                                    <span class="text-gold-400"><i class="fa-regular fa-clock"></i> {{ substr($ev->hora_evento, 0, 5) }}</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-900 text-[10px] text-slate-300">{{ $ev->codigo_evento }}</span>
                                </div>

                                <div class="font-bold text-white leading-tight">
                                    {{ $ev->cliente->nombre_completo ?? 'N/A' }}
                                </div>

                                <div class="text-[11px] text-slate-300">
                                    <i class="fa-solid fa-music text-gold-400 text-[10px]"></i> {{ $ev->servicio->nombre ?? 'Servicio' }}
                                </div>

                                <div class="text-[10px] text-slate-400 truncate flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-rose-400"></i>
                                    <span>{{ $ev->direccion_evento }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex items-center justify-center text-center p-4 text-slate-600 text-xs italic">
                                Sin eventos
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- ========================================== -->
    <!-- VISTA 3: AGENDA DIARIA (DETALLADA)         -->
    <!-- ========================================== -->
    @if($vistaModo === 'dia')
        <div class="bg-brand-card border border-brand-border rounded-3xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-brand-border pb-4">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-calendar-day text-gold-400"></i>
                        <span>Programación del Día</span>
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">{{ $fechaActual->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                </div>
                <span class="px-3 py-1 rounded-xl bg-slate-950 border border-slate-800 text-gold-400 font-bold text-xs font-mono">
                    Total: {{ $eventos->count() }} Evento(s)
                </span>
            </div>

            <div class="space-y-4">
                @forelse($eventos as $ev)
                    <div class="p-5 rounded-2xl bg-slate-950 border transition-all flex flex-col md:flex-row items-start md:items-center justify-between gap-4 {{ isset($conflictos[$ev->id]) ? 'border-rose-500 bg-rose-950/30 shadow-xl shadow-rose-950/20' : 'border-slate-800 hover:border-gold-500/40' }}">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col items-center justify-center text-center shrink-0">
                                <i class="fa-regular fa-clock text-gold-400 text-xs mb-0.5"></i>
                                <span class="text-sm font-black text-white font-mono leading-none">{{ substr($ev->hora_evento, 0, 5) }}</span>
                                <span class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $ev->duracion_horas }}h</span>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-bold text-gold-400 text-xs">{{ $ev->codigo_evento }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                        @if($ev->estado === 'Confirmado' || $ev->estado === 'Realizado' || $ev->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @elseif($ev->estado === 'Reservado' || $ev->estado === 'Cotizado') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                        @elseif($ev->estado === 'Cancelado') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                        @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                        {{ $ev->estado }}
                                    </span>
                                    @if(isset($conflictos[$ev->id]))
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white animate-bounce flex items-center gap-1">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Conflicto de Horario
                                        </span>
                                    @endif
                                </div>

                                <div class="text-base font-bold text-white">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-300">Servicio: <span class="text-gold-400 font-bold">{{ $ev->servicio->nombre ?? 'N/A' }}</span></div>

                                <div class="text-xs text-slate-400 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-location-dot text-rose-400"></i>
                                    <span>{{ $ev->direccion_evento }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 w-full md:w-auto justify-end border-t md:border-t-0 border-slate-800 pt-3 md:pt-0">
                            <button wire:click="verDetalleEvento({{ $ev->id }})" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-1.5 border border-slate-700">
                                <i class="fa-solid fa-eye text-gold-400"></i>
                                <span>Ver Detalle</span>
                            </button>
                            <a href="{{ route('admin.eventos.participantes', $ev->id) }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-1.5 border border-slate-700">
                                <i class="fa-solid fa-people-group text-gold-400"></i>
                                <span>Participantes</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-12 bg-slate-950 rounded-2xl border border-slate-800 text-center text-slate-500 text-sm">
                        No hay presentaciones ni servicios programados para este día.
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL VER DETALLE DEL EVENTO               -->
    <!-- ========================================== -->
    @if($modalDetalleOpen && $eventoDetalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl space-y-6 p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gold-500/10 border border-gold-500/30 flex items-center justify-center text-gold-400">
                            <i class="fa-solid fa-calendar-check text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span>Evento: {{ $eventoDetalle->codigo_evento }}</span>
                            </h3>
                            <div class="text-xs text-slate-400">Detalles completos de la reservación comercial</div>
                        </div>
                    </div>

                    <button wire:click="cerrarModalDetalle" class="text-slate-400 hover:text-white text-lg p-2 rounded-xl hover:bg-slate-800">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Modal Body Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Cliente & Contacto -->
                    <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-1">
                        <div class="text-[10px] font-bold text-gold-400 uppercase tracking-wider">Cliente</div>
                        <div class="text-sm font-bold text-white">{{ $eventoDetalle->cliente->nombre_completo ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-400">Teléfono: <span class="text-slate-200 font-mono">{{ $eventoDetalle->telefono_contacto ?? ($eventoDetalle->cliente->telefono ?? 'N/A') }}</span></div>
                    </div>

                    <!-- Estado & Servicio -->
                    <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-1">
                        <div class="text-[10px] font-bold text-gold-400 uppercase tracking-wider">Servicio & Estado</div>
                        <div class="text-sm font-bold text-white">{{ $eventoDetalle->servicio->nombre ?? 'N/A' }}</div>
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                @if($eventoDetalle->estado === 'Confirmado' || $eventoDetalle->estado === 'Realizado' || $eventoDetalle->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                @elseif($eventoDetalle->estado === 'Reservado' || $eventoDetalle->estado === 'Cotizado') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                @elseif($eventoDetalle->estado === 'Cancelado') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                {{ $eventoDetalle->estado }}
                            </span>
                        </div>
                    </div>

                    <!-- Fecha, Hora y Duración -->
                    <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-1">
                        <div class="text-[10px] font-bold text-gold-400 uppercase tracking-wider">Fecha & Programación</div>
                        <div class="text-xs text-white font-mono font-bold">
                            <i class="fa-solid fa-calendar-day text-gold-400 mr-1"></i>
                            {{ $eventoDetalle->fecha_evento->format('d/m/Y') }} &bull; {{ $eventoDetalle->hora_evento }}
                        </div>
                        <div class="text-xs text-slate-400">Duración: <span class="text-slate-200 font-bold">{{ $eventoDetalle->duracion_horas }} Hora(s)</span></div>
                    </div>

                    <!-- Ubicación Exacta -->
                    <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-1">
                        <div class="text-[10px] font-bold text-gold-400 uppercase tracking-wider">Lugar / Dirección</div>
                        <div class="text-xs text-slate-200 font-semibold leading-relaxed">
                            <i class="fa-solid fa-location-dot text-rose-400 mr-1"></i>
                            {{ $eventoDetalle->direccion_evento }}
                        </div>
                    </div>
                </div>

                @if($eventoDetalle->observaciones)
                    <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 text-xs text-slate-300 space-y-1">
                        <strong class="text-gold-400 uppercase text-[10px]">Observaciones Especiales:</strong>
                        <p class="italic text-slate-400">{{ $eventoDetalle->observaciones }}</p>
                    </div>
                @endif

                <!-- Modal Actions Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-brand-border pt-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        @php
                            $telDetail = preg_replace('/[^0-9]/', '', $eventoDetalle->telefono_contacto ?: ($eventoDetalle->cliente->telefono ?? $eventoDetalle->cliente->whatsapp ?? ''));
                            if ($telDetail && strlen($telDetail) === 8) { $telDetail = '591' . $telDetail; }
                        @endphp
                        @if($telDetail)
                            <a href="https://api.whatsapp.com/send?phone={{ $telDetail }}&text={{ urlencode('Hola ' . ($eventoDetalle->cliente->nombre_completo ?? '') . ', le escribimos del Mariachi León Guanajuato sobre el evento ' . $eventoDetalle->codigo_evento) }}" 
                               target="_blank" 
                               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-md">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                <span>WhatsApp Cliente</span>
                            </a>
                        @endif

                        @if($eventoDetalle->latitud && $eventoDetalle->longitud)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $eventoDetalle->latitud }},{{ $eventoDetalle->longitud }}" 
                               target="_blank" 
                               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-gold-400 text-xs font-bold flex items-center gap-1.5 border border-slate-700">
                                <i class="fa-solid fa-route"></i>
                                <span>Mapa</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.eventos.participantes', $eventoDetalle->id) }}" 
                           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-1.5 border border-slate-700">
                            <i class="fa-solid fa-people-group text-gold-400"></i>
                            <span>Participantes</span>
                        </a>
                    </div>

                    <button wire:click="cerrarModalDetalle" class="w-full sm:w-auto px-5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
