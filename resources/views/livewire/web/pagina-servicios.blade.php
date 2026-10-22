<div class="py-16 px-4 max-w-7xl mx-auto space-y-16">
    <!-- Header -->
    <div class="text-center space-y-4 max-w-3xl mx-auto">
        <span class="px-4 py-1.5 rounded-full bg-gold-500/10 text-gold-400 font-bold text-xs uppercase tracking-widest border border-gold-500/20">Tarifas Transparentes</span>
        <h1 class="font-serif font-bold text-4xl sm:text-5xl text-white">Servicios & Paquetes de Mariachi</h1>
        <p class="text-base text-slate-300">
            Conoce nuestras opciones individuales y combos con tarifas preferenciales para tu evento. Elige el servicio de tu agrado para solicitar tu cotización inmediata.
        </p>
    </div>

    <!-- Servicios Grid -->
    <div class="space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 pb-4 gap-2">
            <div>
                <h3 class="font-serif font-bold text-2xl text-white">Servicios por Hora y Modalidad</h3>
                <p class="text-xs text-slate-400 mt-1">Haz clic en <strong>"Cotizar este Servicio"</strong> para calcular y reservar tu fecha.</p>
            </div>
            <span class="text-xs font-semibold text-gold-400 bg-gold-500/10 px-3 py-1 rounded-lg border border-gold-500/20 self-start sm:self-auto">
                {{ count($servicios) }} opciones disponibles
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($servicios as $serv)
                <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6 flex flex-col justify-between hover:border-gold-500/50 hover:shadow-2xl hover:shadow-gold-500/10 transition-all group">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 text-gold-400 flex items-center justify-center text-xl font-bold group-hover:bg-gold-500 group-hover:text-slate-950 transition-all">
                                <i class="fa-solid fa-guitar"></i>
                            </div>
                            <span class="text-xs text-slate-400 font-medium flex items-center gap-1.5 bg-slate-950/80 px-3 py-1 rounded-full border border-slate-800">
                                <i class="fa-regular fa-clock text-gold-400"></i>{{ $serv->duracion_minutos }} min
                            </span>
                        </div>
                        <div>
                            <h4 class="font-bold text-xl text-white group-hover:text-gold-400 transition-colors">{{ $serv->nombre }}</h4>
                            <p class="text-xs text-slate-400 leading-relaxed mt-2">{{ $serv->descripcion }}</p>
                        </div>
                    </div>

                    <div class="pt-5 border-t border-slate-800/80 space-y-4">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs text-slate-400">Tarifa base</span>
                            <span class="font-bold text-2xl text-gold-400">{{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($serv->precio_base, 2) }}</span>
                        </div>

                        <a href="{{ route('web.contacto', ['servicio_id' => $serv->id]) }}" 
                           class="w-full py-3 px-4 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-md shadow-gold-500/20 text-xs text-center flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98]">
                            <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
                            <span>Cotizar este Servicio</span>
                            <i class="fa-solid fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Paquetes Grid -->
    @if(count($paquetes) > 0)
    <div class="space-y-8 pt-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 pb-4 gap-2">
            <div>
                <h3 class="font-serif font-bold text-2xl text-white">Paquetes Combinados Recomendados</h3>
                <p class="text-xs text-slate-400 mt-1">Combos con varios servicios incluidos y precios especiales.</p>
            </div>
            <span class="text-xs font-semibold text-gold-400 bg-gold-500/10 px-3 py-1 rounded-lg border border-gold-500/20 self-start sm:self-auto">
                Tarifas de Ahorro
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($paquetes as $paq)
                <div class="p-8 rounded-3xl bg-gradient-to-b from-slate-900 to-slate-950 border border-gold-500/30 space-y-6 flex flex-col justify-between shadow-xl hover:border-gold-500/60 transition-all group">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="font-serif font-bold text-2xl text-white group-hover:text-gold-400 transition-colors">{{ $paq->nombre }}</h4>
                            @if($paq->destacado)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gold-500 text-slate-950 shadow-md shadow-gold-500/30">MÁS POPULAR</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ $paq->descripcion }}</p>

                        <div class="space-y-2 pt-2">
                            <p class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-layer-group text-gold-400"></i>
                                <span>Incluye:</span>
                            </p>
                            <ul class="space-y-2">
                                @foreach($paq->servicios as $s)
                                    <li class="flex items-center gap-2.5 text-xs text-slate-300 bg-slate-950/60 px-3 py-2 rounded-xl border border-slate-800/80">
                                        <i class="fa-solid fa-circle-check text-gold-400 text-sm flex-shrink-0"></i>
                                        <span class="font-medium text-white">{{ $s->nombre }}</span>
                                        <span class="text-[11px] text-slate-400 ml-auto">({{ $s->duracion_minutos }} min)</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Precio Total Paquete</span>
                            <span class="font-bold text-2xl text-gold-400">{{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($paq->precio_paquete, 2) }}</span>
                        </div>
                        <a href="{{ route('web.contacto', ['paquete_id' => $paq->id]) }}" 
                           class="px-6 py-3.5 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/25 text-sm transition-all flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98]">
                            <i class="fa-solid fa-calendar-check text-sm"></i>
                            <span>Reservar / Cotizar Paquete</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
