<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestionar Agenda de Eventos</h1>
            <p class="text-xs text-slate-400">Control calendarizado de presentaciones, traslados y detección de conflictos de horario.</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="cambiarVista('dia')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $vistaModo === 'dia' ? 'bg-gold-500 text-slate-950 shadow-lg shadow-gold-500/20' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Día
            </button>
            <button wire:click="cambiarVista('semana')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $vistaModo === 'semana' ? 'bg-gold-500 text-slate-950 shadow-lg shadow-gold-500/20' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Semana
            </button>
            <button wire:click="cambiarVista('mes')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $vistaModo === 'mes' ? 'bg-gold-500 text-slate-950 shadow-lg shadow-gold-500/20' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Mes
            </button>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="bg-brand-card p-4 rounded-2xl border border-brand-border flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button wire:click="fechaAnterior" class="p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button wire:click="fechaHoy" class="px-3 py-1.5 rounded-xl bg-slate-800 text-gold-400 hover:bg-slate-700 font-bold text-xs">
                Hoy
            </button>
            <button wire:click="fechaSiguiente" class="p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

        <div class="text-sm font-extrabold text-white uppercase tracking-wider">
            @if($vistaModo === 'dia')
                {{ $fechaActual->translatedFormat('l, d \d\e F \d\e Y') }}
            @elseif($vistaModo === 'semana')
                {{ $inicioPeriodo->format('d/m/Y') }} al {{ $finPeriodo->format('d/m/Y') }}
            @else
                {{ $fechaActual->translatedFormat('F Y') }}
            @endif
        </div>
    </div>

    <!-- Advertencia de Conflictos -->
    @if(count($conflictos) > 0)
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-400 text-xs flex items-center gap-3 animate-pulse">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <div>
                <strong class="font-bold">¡ALERTA DE CONFLICTO DE HORARIO DETECTADA!</strong>
                <p>Se han encontrado eventos superpuestos en la misma fecha y rango de horas. Revisa los eventos marcados con la insignia roja de conflicto.</p>
            </div>
        </div>
    @endif

    <!-- Event List Grid -->
    <div class="space-y-4">
        @forelse($eventos as $ev)
            <div class="p-5 rounded-2xl bg-brand-card border transition-all flex flex-col md:flex-row items-start md:items-center justify-between gap-4 {{ isset($conflictos[$ev->id]) ? 'border-rose-500 bg-rose-950/20 shadow-lg shadow-rose-500/10' : 'border-brand-border hover:border-gold-500/30' }}">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col items-center justify-center flex-shrink-0 text-center">
                        <span class="text-xs font-bold text-gold-400 uppercase font-mono">{{ $ev->fecha_evento->format('M') }}</span>
                        <span class="text-lg font-extrabold text-white font-mono leading-none">{{ $ev->fecha_evento->format('d') }}</span>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono font-bold text-gold-400 text-xs">{{ $ev->codigo_evento }}</span>
                            <span class="text-xs text-slate-300 font-bold">&bull; {{ $ev->hora_evento }} ({{ $ev->duracion_horas }}h)</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                @if($ev->estado === 'Confirmado' || $ev->estado === 'Realizado' || $ev->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                @elseif($ev->estado === 'Reservado' || $ev->estado === 'Cotizado') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                @elseif($ev->estado === 'Cancelado') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                {{ $ev->estado }}
                            </span>
                            @if(isset($conflictos[$ev->id]))
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white animate-bounce">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Conflicto Horario
                                </span>
                            @endif
                        </div>

                        <div class="text-base font-bold text-white">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-400">Servicio: <span class="text-slate-200 font-semibold">{{ $ev->servicio->nombre ?? 'N/A' }}</span></div>

                        <div class="text-xs text-slate-400 flex items-center gap-1 mt-1">
                            <i class="fa-solid fa-location-dot text-rose-400"></i>
                            <span>{{ $ev->direccion_evento }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto justify-end border-t md:border-t-0 border-slate-800 pt-3 md:pt-0">
                    @if($ev->latitud && $ev->longitud)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $ev->latitud }},{{ $ev->longitud }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-gold-400 text-xs font-bold flex items-center gap-1.5 border border-slate-700">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <span>Mapa</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.eventos.participantes', $ev->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-1.5 border border-slate-700">
                        <i class="fa-solid fa-people-group text-gold-400"></i>
                        <span>Participantes</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 bg-brand-card rounded-2xl border border-brand-border text-center text-slate-500 text-sm">
                No hay eventos programados en el período seleccionado.
            </div>
        @endforelse
    </div>
</div>
