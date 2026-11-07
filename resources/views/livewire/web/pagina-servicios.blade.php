<div class="py-10 px-4 max-w-7xl mx-auto space-y-10">
    <div class="text-center space-y-2 max-w-2xl mx-auto">
        <h1 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white">Servicios y paquetes</h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300">Elige el que va con tu evento y cotiza al
            instante.</p>
    </div>

    <!-- Servicios -->
    <div class="space-y-4">
        <h3
            class="font-serif font-bold text-xl text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
            Por hora y modalidad</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($servicios as $serv)
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between gap-4 hover:border-gold-500/50 transition-all hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
                    data-reveal>
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-bold text-lg text-slate-900 dark:text-white">{{ $serv->nombre }}</h4>
                            <span
                                class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1 bg-slate-100 dark:bg-slate-950/80 px-2.5 py-1 rounded-full border border-slate-200 dark:border-slate-800 shrink-0">
                                <i
                                    class="fa-regular fa-clock text-gold-700 dark:text-gold-400"></i>{{ $serv->duracion_minutos }}
                                min
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $serv->descripcion }}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800/80 space-y-3">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Tarifa base</span>
                            <span
                                class="font-bold text-xl text-gold-700 dark:text-gold-400">{{ $empresa->moneda_simbolo ?? 'Bs.' }}
                                {{ number_format($serv->precio_base, 2) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('web.contacto', ['servicio_id' => $serv->id]) }}"
                                class="flex-1 py-2.5 px-4 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 text-xs text-center transition-all">
                                Cotizar
                            </a>
                            @if ($empresa->whatsapp_numero_limpio)
                                <a href="https://wa.me/{{ $empresa->whatsapp_numero_limpio }}?text={{ urlencode('Hola, me interesa el servicio ' . $serv->nombre) }}"
                                    target="_blank" rel="noopener noreferrer" title="Consultar por WhatsApp"
                                    class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center transition-all">
                                    <i class="fa-brands fa-whatsapp text-lg"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Paquetes -->
    @if (count($paquetes) > 0)
        <div class="space-y-4">
            <h3
                class="font-serif font-bold text-xl text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                Paquetes combinados</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                @foreach ($paquetes as $paq)
                    <div class="p-5 rounded-2xl bg-gradient-to-b from-white dark:from-slate-900 to-slate-50 dark:to-slate-950 border border-gold-500/30 flex flex-col justify-between gap-4 hover:border-gold-500/60 transition-all hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
                        data-reveal>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-serif font-bold text-xl text-slate-900 dark:text-white">
                                    {{ $paq->nombre }}</h4>
                                @if ($paq->destacado)
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gold-500 text-slate-950 shrink-0">MÁS
                                        POPULAR</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                {{ $paq->descripcion }}</p>

                            <ul class="space-y-1.5">
                                @foreach ($paq->servicios as $s)
                                    <li class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                                        <i
                                            class="fa-solid fa-circle-check text-gold-700 dark:text-gold-400 shrink-0"></i>
                                        <span
                                            class="font-medium text-slate-900 dark:text-white">{{ $s->nombre }}</span>
                                        <span
                                            class="text-[11px] text-slate-500 dark:text-slate-400 ml-auto">{{ $s->duracion_minutos }}
                                            min</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div
                            class="pt-3 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between gap-3">
                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 block">Precio del paquete</span>
                                <span
                                    class="font-bold text-xl text-gold-700 dark:text-gold-400">{{ $empresa->moneda_simbolo ?? 'Bs.' }}
                                    {{ number_format($paq->precio_paquete, 2) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('web.contacto', ['paquete_id' => $paq->id]) }}"
                                    class="px-5 py-2.5 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 text-xs transition-all">
                                    Reservar
                                </a>
                                @if ($empresa->whatsapp_numero_limpio)
                                    <a href="https://wa.me/{{ $empresa->whatsapp_numero_limpio }}?text={{ urlencode('Hola, me interesa el paquete ' . $paq->nombre) }}"
                                        target="_blank" rel="noopener noreferrer" title="Consultar por WhatsApp"
                                        class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center transition-all">
                                        <i class="fa-brands fa-whatsapp text-lg"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>