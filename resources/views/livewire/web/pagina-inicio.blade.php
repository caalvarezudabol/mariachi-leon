<div>
    <!-- Hero Carousel Section (Banners Dinámicos desplegándose hacia la izquierda) -->
    <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden bg-slate-950" 
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
        
        <!-- Slider Images (Desplazamiento fluido hacia la izquierda) -->
        <template x-for="(slide, index) in slides" :key="index">
            <div x-show="activeSlide === index" 
                 x-transition:enter="transition transform ease-out duration-700" 
                 x-transition:enter-start="translate-x-full opacity-0" 
                 x-transition:enter-end="translate-x-0 opacity-100" 
                 x-transition:leave="transition transform ease-in duration-700" 
                 x-transition:leave-start="translate-x-0 opacity-100" 
                 x-transition:leave-end="-translate-x-full opacity-0" 
                 class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/75 to-slate-950/40 z-10"></div>
                <img :src="slide.imagen_url" :alt="slide.titulo" class="w-full h-full object-cover">
            </div>
        </template>

        <!-- Hero Content Overlay -->
        <div class="max-w-5xl mx-auto text-center space-y-8 relative z-20 px-4 py-20">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gold-500/10 border border-gold-500/30 text-gold-400 font-semibold text-xs tracking-wider uppercase backdrop-blur-md">
                <i class="fa-solid fa-star"></i> Mariachi León Guanajuato &bull; Tradición & Gala
            </div>

            <h1 class="font-serif text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white leading-tight">
                <span x-text="slides[activeSlide] ? slides[activeSlide].titulo : 'El Mejor Mariachi de Gala'"></span>
            </h1>

            <p class="text-base sm:text-xl text-slate-300 max-w-3xl mx-auto font-light leading-relaxed">
                <span x-text="slides[activeSlide] ? slides[activeSlide].subtitulo : ''"></span>
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <a :href="slides[activeSlide] ? slides[activeSlide].boton_link : '#cotizar'" class="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-xl shadow-gold-500/25 transition-all text-base hover:scale-105 flex items-center justify-center gap-3">
                    <i class="fa-solid fa-calculator"></i>
                    <span x-text="slides[activeSlide] ? slides[activeSlide].boton_texto : 'Cotizar Tu Evento'"></span>
                </a>
                
                <!-- Linktree Direct Social Media Button -->
                <a href="https://linktr.ee/mariachileonguanajuato" target="_blank" class="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold bg-emerald-600/90 hover:bg-emerald-500 text-white border border-emerald-400/40 transition-all text-base flex items-center justify-center gap-3 shadow-lg shadow-emerald-600/20 hover:scale-105">
                    <i class="fa-solid fa-tree"></i>
                    <span>Redes Sociales (Linktree)</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                </a>
            </div>

            <!-- Slide Indicators -->
            <div class="flex items-center justify-center gap-2 pt-8" x-show="slides.length > 1">
                <template x-for="(slide, i) in slides" :key="i">
                    <button @click="activeSlide = i" 
                            :class="activeSlide === i ? 'w-8 bg-gold-400' : 'w-2.5 bg-slate-600 hover:bg-slate-400'" 
                            class="h-2.5 rounded-full transition-all duration-300"></button>
                </template>
            </div>
        </div>
    </section>

    <!-- Features Bar -->
    <section class="bg-slate-900 border-y border-slate-800/80 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-3 gap-8 text-center sm:text-left">
            <div class="flex items-center gap-4 justify-center sm:justify-start">
                <div class="w-14 h-14 rounded-2xl bg-gold-500/10 border border-gold-500/20 text-gold-400 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-base">Puntualidad Garantizada</h4>
                    <p class="text-xs text-slate-400">Llegamos con anticipación a la cita de tu evento.</p>
                </div>
            </div>
            <div class="flex items-center gap-4 justify-center sm:justify-start">
                <div class="w-14 h-14 rounded-2xl bg-gold-500/10 border border-gold-500/20 text-gold-400 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class="fa-solid fa-vest"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-base">Trajes de Gala Impecables</h4>
                    <p class="text-xs text-slate-400">Presentación distinguida y profesional.</p>
                </div>
            </div>
            <div class="flex items-center gap-4 justify-center sm:justify-start">
                <div class="w-14 h-14 rounded-2xl bg-gold-500/10 border border-gold-500/20 text-gold-400 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class="fa-solid fa-music"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-base">Repertorio Variado</h4>
                    <p class="text-xs text-slate-400">Canciones tradicionales y clásicos complacientes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Preview -->
    <section class="py-24 px-4 max-w-7xl mx-auto space-y-12">
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <h2 class="font-serif font-bold text-3xl sm:text-4xl text-white">Nuestros Servicios Principales</h2>
            <p class="text-sm text-slate-400">Ofrecemos opciones adaptadas a la magnitud y tipo de tu celebración.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($serviciosDestacados as $serv)
                <div class="p-8 rounded-3xl bg-slate-900/80 border border-slate-800 space-y-6 hover:border-gold-500/40 transition-all flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-gold-500/10 text-gold-400 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-compact-disc"></i>
                        </div>
                        <h3 class="font-bold text-xl text-white">{{ $serv->nombre }}</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ $serv->descripcion }}</p>
                    </div>
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-400"><i class="fa-regular fa-clock text-gold-400 mr-1"></i>{{ $serv->duracion_minutos }} min</span>
                        <span class="font-bold text-lg text-gold-400">Bs. {{ number_format($serv->precio_base, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Quote Form Section -->
    <section id="cotizar" class="py-20 px-4 bg-slate-900/60 border-t border-slate-800/80">
        <div class="max-w-4xl mx-auto bg-slate-950 p-8 sm:p-12 rounded-3xl border border-slate-800 shadow-2xl space-y-8">
            <div class="text-center space-y-2">
                <h3 class="font-serif font-bold text-2xl sm:text-3xl text-white">Solicita una Cotización Inmediata</h3>
                <p class="text-xs sm:text-sm text-slate-400">Completa tus datos y nos pondremos en contacto contigo a la brevedad.</p>
            </div>

            @if($enviadoExito)
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-center space-y-2">
                    <i class="fa-solid fa-circle-check text-2xl text-emerald-400"></i>
                    <h4 class="font-bold text-base">¡Solicitud Enviada con Éxito!</h4>
                    <p class="text-xs">Un encargado del Mariachi León se comunicará contigo pronto.</p>
                </div>
            @endif

            <form wire:submit.prevent="enviarSolicitud" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre Completo *</label>
                        <input type="text" wire:model.defer="nombre" required placeholder="Ej. Juan Pérez" class="w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('nombre') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Teléfono de Contacto (WhatsApp) *</label>
                        <input type="text" wire:model.defer="telefono" required placeholder="Ej. 477 123 4567" class="w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('telefono') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Tipo de Evento</label>
                        <select wire:model.defer="tipo_evento_id" class="w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione el tipo de evento...</option>
                            @foreach($tiposEvento as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fecha Estimada</label>
                        <input type="date" wire:model.defer="fecha_estimada" class="w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Detalles del Evento o Mensaje *</label>
                    <textarea wire:model.defer="mensaje" required rows="4" placeholder="Indica la ubicación, horario deseado o requerimientos especiales..." class="w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                    @error('mensaje') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- CAPTCHA Anti-Bot -->
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Verificación Anti-Bot (CAPTCHA) *</label>
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-950 border border-gold-500/30 rounded-xl text-gold-400 font-bold text-sm select-none">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>¿Cuánto es {{ $captcha_num1 }} + {{ $captcha_num2 }}?</span>
                            <button type="button" wire:click="generarCaptcha" title="Cambiar suma" class="ml-2 text-slate-500 hover:text-gold-400">
                                <i class="fa-solid fa-rotate-right"></i>
                            </button>
                        </div>
                        <input type="number" wire:model.defer="captcha_respuesta" required placeholder="Ingresa la suma" class="flex-1 w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>
                    @error('captcha_respuesta') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" class="w-full py-4 rounded-2xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all text-base flex items-center justify-center gap-2">
                    <span wire:loading.remove>Enviar Solicitud de Cotización</span>
                    <span wire:loading><i class="fa-solid fa-spinner animate-spin"></i> Enviando...</span>
                </button>
            </form>
        </div>
    </section>
</div>
