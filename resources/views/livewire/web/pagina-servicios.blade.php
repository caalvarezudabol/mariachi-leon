<div class="py-16 px-4 max-w-7xl mx-auto space-y-16">
    <!-- Header -->
    <div class="text-center space-y-4 max-w-3xl mx-auto">
        <span class="px-4 py-1.5 rounded-full bg-gold-500/10 text-gold-400 font-bold text-xs uppercase tracking-widest border border-gold-500/20">Tarifas Transparentes</span>
        <h1 class="font-serif font-bold text-4xl sm:text-5xl text-white">Servicios & Paquetes de Mariachi</h1>
        <p class="text-base text-slate-300">
            Conoce nuestras opciones individuales y combos con tarifas preferenciales para tu evento.
        </p>
    </div>

    <!-- Servicios Grid -->
    <div class="space-y-8">
        <h3 class="font-serif font-bold text-2xl text-white border-b border-slate-800 pb-4">Servicios por Hora y Modalidad</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($servicios as $serv)
                <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6 flex flex-col justify-between hover:border-gold-500/40 transition-all">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-gold-500/10 text-gold-400 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-guitar"></i>
                        </div>
                        <h4 class="font-bold text-xl text-white">{{ $serv->nombre }}</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ $serv->descripcion }}</p>
                    </div>
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-400"><i class="fa-regular fa-clock text-gold-400 mr-1"></i>{{ $serv->duracion_minutos }} min</span>
                        <span class="font-bold text-xl text-gold-400">Bs. {{ number_format($serv->precio_base, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Paquetes Grid -->
    <div class="space-y-8 pt-8">
        <h3 class="font-serif font-bold text-2xl text-white border-b border-slate-800 pb-4">Paquetes Combinados Recomendados</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($paquetes as $paq)
                <div class="p-8 rounded-3xl bg-gradient-to-b from-slate-900 to-slate-950 border border-gold-500/30 space-y-6 flex flex-col justify-between shadow-xl">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="font-serif font-bold text-2xl text-white">{{ $paq->nombre }}</h4>
                            @if($paq->destacado)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gold-500 text-slate-950">MÁS POPULAR</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ $paq->descripcion }}</p>

                        <div class="space-y-2 pt-2">
                            <p class="text-xs font-bold text-slate-300 uppercase tracking-wider">Incluye:</p>
                            <ul class="space-y-1.5">
                                @foreach($paq->servicios as $s)
                                    <li class="flex items-center gap-2 text-xs text-slate-300">
                                        <i class="fa-solid fa-circle-check text-gold-400"></i>
                                        <span>{{ $s->nombre }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800/80 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 block">Precio Total Paquete</span>
                            <span class="font-bold text-2xl text-gold-400">Bs. {{ number_format($paq->precio_paquete, 2) }}</span>
                        </div>
                        <a href="{{ route('web.contacto') }}" class="px-6 py-3 rounded-xl font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20 text-sm transition-all">
                            Reservar Paquete
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
