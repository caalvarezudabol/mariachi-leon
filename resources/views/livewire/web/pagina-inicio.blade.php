<div>
    <!-- Hero (compacto) -->
    <section class="relative min-h-[60vh] sm:min-h-[70vh] flex items-center justify-center overflow-hidden bg-slate-950"
        x-data="{
            activeSlide: 0,
            slides: {{ json_encode($banners) }},
            startTimer() {
                if (this.slides.length > 1) {
                    setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.slides.length;
                    }, 4500);
                }
            }
        }" x-init="startTimer()">

        <template x-for="(slide, index) in slides" :key="index">
            <div x-show="activeSlide === index" x-transition:enter="transition transform ease-out duration-700"
                x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition transform ease-in duration-700"
                x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/75 to-slate-950/40 z-10">
                </div>
                <img :src="slide.imagen_url" :alt="slide.titulo" class="w-full h-full object-cover kenburns">
            </div>
        </template>

        <div class="max-w-4xl mx-auto text-center space-y-5 relative z-20 px-4 py-12">
            <div
                class="hero-in inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gold-500/10 border border-gold-500/30 text-gold-400 font-semibold text-[11px] tracking-wider uppercase backdrop-blur-md">
                <i class="fa-solid fa-star"></i> {{ $empresa->slogan ?? 'Tradición & Gala' }}
            </div>

            <h1 class="hero-in-2 font-serif text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                <span x-text="slides[activeSlide] ? slides[activeSlide].titulo : 'El Mejor Mariachi de Gala'"></span>
            </h1>

            <p class="text-sm sm:text-lg text-slate-300 max-w-2xl mx-auto font-light leading-relaxed">
                <span x-text="slides[activeSlide] ? slides[activeSlide].subtitulo : ''"></span>
            </p>

            <div class="hero-in-3 flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                @if ($empresa->whatsapp_numero_limpio)
                    <a href="https://wa.me/{{ $empresa->whatsapp_numero_limpio }}?text={{ urlencode('Hola, quiero cotizar un show de mariachi') }}"
                        target="_blank" rel="noopener noreferrer"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 transition-all text-sm flex items-center justify-center gap-2">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span>Cotizar por WhatsApp</span>
                    </a>
                @endif
                <a :href="slides[activeSlide] ? slides[activeSlide].boton_link : '#cotizar'"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/25 transition-all text-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-music"></i>
                    <span x-text="slides[activeSlide] ? slides[activeSlide].boton_texto : 'Ver paquetes'"></span>
                </a>
            </div>

            <div class="flex items-center justify-center gap-2 pt-3" x-show="slides.length > 1">
                <template x-for="(slide, i) in slides" :key="i">
                    <button @click="activeSlide = i"
                        :class="activeSlide === i ? 'w-8 bg-gold-400' : 'w-2.5 bg-slate-600 hover:bg-slate-400'"
                        class="h-2.5 rounded-full transition-all duration-300"></button>
                </template>
            </div>
        </div>
    </section>

    <!-- Franja de confianza -->
    <section class="bg-slate-50 dark:bg-slate-900 border-y border-slate-200 dark:border-slate-800/80 py-5">
        <div class="max-w-5xl mx-auto px-4 grid grid-cols-3 gap-3 text-center">
            <div>
                <i class="fa-solid fa-clock text-gold-700 dark:text-gold-400 text-xl"></i>
                <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm mt-1">Puntualidad</h4>
                <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400">Llegamos con anticipación.</p>
            </div>
            <div>
                <i class="fa-solid fa-vest text-gold-700 dark:text-gold-400 text-xl"></i>
                <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm mt-1">Trajes de gala</h4>
                <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400">Presentación impecable.</p>
            </div>
            <div>
                <i class="fa-solid fa-music text-gold-700 dark:text-gold-400 text-xl"></i>
                <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm mt-1">Repertorio variado</h4>
                <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400">Clásicos y tradicionales.</p>
            </div>
        </div>
    </section>

    <!-- Servicios -->
    <section class="py-12 px-4 max-w-7xl mx-auto space-y-8">
        <div class="text-center space-y-1">
            <h2 class="font-serif font-bold text-2xl sm:text-3xl text-slate-900 dark:text-white">Nuestros servicios</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Elige el que va con tu celebración.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($serviciosDestacados as $serv)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 hover:border-gold-500/40 transition-all flex flex-col justify-between gap-4 hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
                    data-reveal>
                    <div class="space-y-2">
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ $serv->nombre }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">
                            {{ $serv->descripcion }}</p>
                    </div>
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400"><i
                                    class="fa-regular fa-clock text-gold-700 dark:text-gold-400 mr-1"></i>{{ $serv->duracion_minutos }}
                                min</span>
                            <span
                                class="font-bold text-lg text-gold-700 dark:text-gold-400">{{ $empresa->moneda_simbolo ?? 'Bs.' }}
                                {{ number_format($serv->precio_base, 2) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('web.contacto', ['servicio_id' => $serv->id]) }}"
                                class="flex-1 py-2.5 px-3 rounded-xl font-bold bg-gold-500/15 text-gold-700 dark:text-gold-400 hover:bg-gold-500 hover:text-slate-950 border border-gold-500/30 text-xs text-center transition-all">
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
    </section>

    <!-- Cotizar: un solo formulario (en Contacto) -->
    <section id="cotizar"
        class="py-12 px-4 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-800/80">
        <div class="max-w-2xl mx-auto text-center space-y-4" data-reveal>
            <h3 class="font-serif font-bold text-2xl sm:text-3xl text-slate-900 dark:text-white">¿Listo para tu evento?
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-300">Mira qué fechas tenemos libres y pide tu cotización en
                un minuto.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-1">
                <a href="{{ route('web.contacto') }}"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/25 transition-all text-sm flex items-center justify-center gap-2">
                    <i class="fa-regular fa-calendar-check"></i>
                    <span>Ver fechas y cotizar</span>
                </a>
                @if ($empresa->whatsapp_numero_limpio)
                    <a href="https://wa.me/{{ $empresa->whatsapp_numero_limpio }}?text={{ urlencode('Hola, quiero cotizar un show de mariachi') }}"
                        target="_blank" rel="noopener noreferrer"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition-all text-sm flex items-center justify-center gap-2">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span>Escribir por WhatsApp</span>
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>