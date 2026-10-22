<div class="py-16 px-4 max-w-7xl mx-auto space-y-12">
    <!-- Header -->
    <div class="text-center space-y-4 max-w-3xl mx-auto">
        <span class="px-4 py-1.5 rounded-full bg-gold-500/10 text-gold-400 font-bold text-xs uppercase tracking-widest border border-gold-500/20">Atención Personalizada</span>
        <h1 class="font-serif font-bold text-4xl sm:text-5xl text-white">Contáctanos</h1>
        <p class="text-base text-slate-300">
            Estamos disponibles para responder todas tus dudas, brindar cotizaciones y agendar tu fecha especial.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Direct Contact Cards -->
        <div class="space-y-6">
            <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-gold-500/30 transition-all">
                <div class="w-12 h-12 rounded-2xl bg-gold-500/10 text-gold-400 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <h3 class="font-bold text-lg text-white">Llámanos o WhatsApp</h3>
                <p class="text-xs text-slate-400">Respuesta rápida y asesoramiento inmediato.</p>
                @if($empresa->telefono_principal)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-gold-400 text-xs"></i>
                        <a href="{{ $empresa->telefono_url }}" class="font-bold text-base text-gold-400 hover:text-gold-300 transition-colors">
                            {{ $empresa->telefono_principal }}
                        </a>
                    </div>
                @endif
                @if($empresa->whatsapp_numero_limpio)
                    <div class="pt-1">
                        <a href="{{ $empresa->whatsapp_url }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all text-xs font-bold">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Chatear por WhatsApp</span>
                        </a>
                    </div>
                @endif
            </div>

            <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-gold-500/30 transition-all">
                <div class="w-12 h-12 rounded-2xl bg-gold-500/10 text-gold-400 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <h3 class="font-bold text-lg text-white">Correo Electrónico</h3>
                <p class="text-xs text-slate-400">Atención comercial y cotizaciones formales en PDF.</p>
                <a href="mailto:{{ $empresa->email_contacto }}" class="font-bold text-base text-gold-400 hover:text-gold-300 transition-colors break-all block">
                    {{ $empresa->email_contacto ?? 'contacto@mariachileonguanajuato.com' }}
                </a>
            </div>

            <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-gold-500/30 transition-all">
                <div class="w-12 h-12 rounded-2xl bg-gold-500/10 text-gold-400 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <h3 class="font-bold text-lg text-white">Ubicación Base</h3>
                <p class="text-xs text-slate-400">Cobertura en eventos locales y viajes acordados.</p>
                <p class="font-bold text-base text-gold-400 leading-snug">
                    {{ $empresa->direccion_fisica ?? 'Santa Cruz, Bolivia' }}
                    @if($empresa->ciudad_pais)
                        <span class="block text-xs text-slate-400 font-normal mt-1">{{ $empresa->ciudad_pais }}</span>
                    @endif
                </p>
            </div>

            <!-- Tarjeta de Redes Sociales Oficiales con Botones Individuales -->
            <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-gold-500/30 transition-all">
                <div class="w-12 h-12 rounded-2xl bg-gold-500/10 text-gold-400 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-share-nodes"></i>
                </div>
                <h3 class="font-bold text-lg text-white">Redes Sociales Oficiales</h3>
                <p class="text-xs text-slate-400">Síguenos, conócenos e interactúa en nuestros canales oficiales:</p>
                
                <div class="space-y-2.5 pt-1">
                    @if($empresa->whatsapp_numero_limpio)
                    <a href="{{ $empresa->whatsapp_url }}" target="_blank" rel="noopener noreferrer" 
                       class="w-full px-4 py-2.5 rounded-xl bg-emerald-600/90 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-between shadow transition-all hover:scale-[1.02] group">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                            <span>WhatsApp Directo</span>
                        </span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-75 group-hover:opacity-100"></i>
                    </a>
                    @endif

                    @if($empresa->redes_instagram)
                    <a href="{{ $empresa->redes_instagram }}" target="_blank" rel="noopener noreferrer" 
                       class="w-full px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#f09433] via-[#dc2743] to-[#bc1888] hover:opacity-95 text-white font-bold text-xs flex items-center justify-between shadow transition-all hover:scale-[1.02] group">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-brands fa-instagram text-lg"></i>
                            <span>Instagram Oficial</span>
                        </span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-75 group-hover:opacity-100"></i>
                    </a>
                    @endif

                    @if($empresa->redes_tiktok)
                    <a href="{{ $empresa->redes_tiktok }}" target="_blank" rel="noopener noreferrer" 
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 hover:bg-black text-white border border-cyan-400/40 hover:border-pink-500/50 font-bold text-xs flex items-center justify-between shadow transition-all hover:scale-[1.02] group">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-brands fa-tiktok text-lg text-cyan-400 group-hover:text-pink-400"></i>
                            <span>TikTok Oficial</span>
                        </span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-75 group-hover:opacity-100"></i>
                    </a>
                    @endif

                    @if($empresa->redes_youtube)
                    <a href="{{ $empresa->redes_youtube }}" target="_blank" rel="noopener noreferrer" 
                       class="w-full px-4 py-2.5 rounded-xl bg-[#FF0000] hover:bg-[#e60000] text-white font-bold text-xs flex items-center justify-between shadow transition-all hover:scale-[1.02] group">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-brands fa-youtube text-lg"></i>
                            <span>Canal de YouTube</span>
                        </span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-75 group-hover:opacity-100"></i>
                    </a>
                    @endif

                    @if($empresa->redes_facebook)
                    <a href="{{ $empresa->redes_facebook }}" target="_blank" rel="noopener noreferrer" 
                       class="w-full px-4 py-2.5 rounded-xl bg-[#1877F2] hover:bg-[#166fe5] text-white font-bold text-xs flex items-center justify-between shadow transition-all hover:scale-[1.02] group">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-brands fa-facebook-f text-lg"></i>
                            <span>Página de Facebook</span>
                        </span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-75 group-hover:opacity-100"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Interactive Form -->
        <div class="lg:col-span-2 bg-slate-900 p-8 sm:p-12 rounded-3xl border border-slate-800 space-y-6 shadow-2xl">
            <h3 class="font-serif font-bold text-2xl text-white">Envíanos un Mensaje</h3>

            @if($enviadoExito)
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-center space-y-2">
                    <i class="fa-solid fa-circle-check text-2xl text-emerald-400"></i>
                    <h4 class="font-bold text-base">¡Mensaje Enviado Correctamente!</h4>
                    <p class="text-xs">Te contactaremos a la brevedad para atender tu consulta.</p>
                </div>
            @endif

            <form wire:submit.prevent="enviarMensaje" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre Completo *</label>
                        <input type="text" wire:model.defer="nombre" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Teléfono *</label>
                        <input type="text" wire:model.defer="telefono" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Correo (Opcional)</label>
                        <input type="email" wire:model.defer="email" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Tipo de Evento</label>
                        <select wire:model.defer="tipo_evento_id" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione evento...</option>
                            @foreach($tiposEvento as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Mensaje / Consulta *</label>
                    <textarea wire:model.defer="mensaje" required rows="4" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                    @error('mensaje') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- CAPTCHA Anti-Bot -->
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Verificación Anti-Bot (CAPTCHA) *</label>
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-900 border border-gold-500/30 rounded-xl text-gold-400 font-bold text-sm select-none">
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

                <button type="submit" wire:loading.attr="disabled" class="w-full py-4 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 text-base transition-all">
                    <span>Enviar Consulta</span>
                </button>
            </form>
        </div>
    </div>
</div>
