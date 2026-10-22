<div class="py-16 px-4 max-w-7xl mx-auto space-y-12">
    <!-- Header -->
    <div class="text-center space-y-4 max-w-3xl mx-auto">
        <span class="px-4 py-1.5 rounded-full bg-gold-500/10 text-gold-400 font-bold text-xs uppercase tracking-widest border border-gold-500/20">Cotización Inmediata y Atención Personalizada</span>
        <h1 class="font-serif font-bold text-4xl sm:text-5xl text-white">Solicita tu Cotización & Contáctanos</h1>
        <p class="text-base text-slate-300">
            Estamos disponibles para responder tus consultas, preparar cotizaciones a medida y agendar la fecha de tu evento especial.
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
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-serif font-bold text-2xl text-white">Formulario de Solicitud de Cotización</h3>
                    <p class="text-xs text-slate-400 mt-1">Completa los campos a continuación para recibir tu propuesta económica personalizada.</p>
                </div>
                <div class="hidden sm:block text-gold-400 text-2xl">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>

            @if($enviadoExito)
                <div class="p-6 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-center space-y-2">
                    <i class="fa-solid fa-circle-check text-3xl text-emerald-400"></i>
                    <h4 class="font-bold text-lg text-white">¡Solicitud de Cotización Enviada Correctamente!</h4>
                    <p class="text-sm text-emerald-200">Hemos recibido tus datos con éxito. Nuestro equipo se pondrá en contacto contigo a la brevedad para brindarte toda la información y disponibilidad.</p>
                </div>
            @endif

            <!-- Banner Destacado cuando se selecciona un servicio o paquete -->
            @if($servicioSeleccionado)
                <div class="p-5 rounded-2xl bg-gradient-to-r from-gold-500/15 to-gold-600/5 border border-gold-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-fade-in shadow-lg">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gold-500/20 text-gold-400 flex items-center justify-center text-xl flex-shrink-0 shadow-inner">
                            <i class="fa-solid fa-guitar"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-gold-500 text-slate-950 font-black text-[10px] uppercase tracking-wider">Servicio Seleccionado</span>
                                <span class="text-xs text-slate-400"><i class="fa-regular fa-clock mr-1 text-gold-400"></i>{{ $servicioSeleccionado->duracion_minutos }} min</span>
                            </div>
                            <h4 class="font-serif font-bold text-lg text-white mt-0.5">{{ $servicioSeleccionado->nombre }}</h4>
                            <p class="text-xs text-slate-300 mt-0.5">
                                Tarifa de referencia: <strong class="text-gold-400 font-bold text-sm">{{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($servicioSeleccionado->precio_base, 2) }}</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="quitarSeleccion" class="px-3 py-1.5 rounded-lg bg-slate-950/80 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-slate-800 hover:border-rose-500/30 text-xs font-semibold flex items-center gap-1.5 transition-colors self-end sm:self-auto">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cambiar / Quitar</span>
                    </button>
                </div>
            @elseif($paqueteSeleccionado)
                <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/15 to-gold-600/5 border border-amber-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-fade-in shadow-lg">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl flex-shrink-0 shadow-inner">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[10px] uppercase tracking-wider">Paquete Seleccionado</span>
                            </div>
                            <h4 class="font-serif font-bold text-lg text-white mt-0.5">{{ $paqueteSeleccionado->nombre }}</h4>
                            <p class="text-xs text-slate-300 mt-0.5">
                                Precio Paquete: <strong class="text-gold-400 font-bold text-sm">{{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($paqueteSeleccionado->precio_paquete, 2) }}</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="quitarSeleccion" class="px-3 py-1.5 rounded-lg bg-slate-950/80 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-slate-800 hover:border-rose-500/30 text-xs font-semibold flex items-center gap-1.5 transition-colors self-end sm:self-auto">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cambiar / Quitar</span>
                    </button>
                </div>
            @endif

            <form wire:submit.prevent="enviarMensaje" class="space-y-6">
                <!-- Selector de Servicio o Paquete de Interés -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-list-check text-gold-400 mr-1.5"></i>Servicio o Paquete a Cotizar
                    </label>
                    <select wire:model.live="item_id" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        <option value="">-- Sin servicio específico / Consulta general --</option>
                        <optgroup label="Servicios por Hora y Modalidad">
                            @foreach($servicios as $s)
                                <option value="servicio_{{ $s->id }}">
                                    🎵 {{ $s->nombre }} — {{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($s->precio_base, 2) }} ({{ $s->duracion_minutos }} min)
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Paquetes Combinados">
                            @foreach($paquetes as $p)
                                <option value="paquete_{{ $p->id }}">
                                    🎁 {{ $p->nombre }} — {{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($p->precio_paquete, 2) }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <!-- Datos de Contacto -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre Completo *</label>
                        <input type="text" wire:model="nombre" required placeholder="Ej: Juan Pérez" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('nombre') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Teléfono o WhatsApp *</label>
                        <input type="text" wire:model="telefono" required placeholder="Ej: 71234567" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('telefono') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Correo, Tipo de Evento y Fecha Estimada -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Correo (Opcional)</label>
                        <input type="email" wire:model="email" placeholder="cliente@correo.com" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('email') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Tipo de Celebración</label>
                        <select wire:model="tipo_evento_id" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione tipo...</option>
                            @foreach($tiposEvento as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                        @error('tipo_evento_id') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fecha Prevista Evento</label>
                        <input type="date" wire:model="fecha_estimada" min="{{ date('Y-m-d') }}" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('fecha_estimada') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Mensaje y Requerimientos -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Detalles del Evento / Mensaje *</label>
                    <textarea wire:model="mensaje" required rows="4" placeholder="Indícanos la dirección aproximada, hora prevista, canciones especiales o requerimientos que tengas..." class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
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
                        <input type="number" wire:model="captcha_respuesta" required placeholder="Ingresa la suma" class="flex-1 w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>
                    @error('captcha_respuesta') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" class="w-full py-4 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 text-base transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane" wire:loading.remove></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading></i>
                    <span wire:loading.remove>Enviar Solicitud de Cotización</span>
                    <span wire:loading>Enviando solicitud...</span>
                </button>
            </form>
        </div>
    </div>
</div>
