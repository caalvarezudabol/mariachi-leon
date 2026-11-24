<div class="py-5 px-4 max-w-6xl mx-auto space-y-4">
    <div class="text-center max-w-2xl mx-auto">
        <h1 class="font-serif font-bold text-2xl sm:text-3xl text-slate-900 dark:text-white">Cotiza tu evento</h1>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">Escríbenos y te preparamos una propuesta a tu
            medida.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Contacto directo (una sola tarjeta) -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3 h-fit hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
            data-reveal>
            @if ($empresa->whatsapp_numero_limpio)
                <a href="{{ $empresa->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                    class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm flex items-center justify-center gap-2 transition-all">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                    <span>Chatear por WhatsApp</span>
                </a>
            @endif

            <div class="space-y-2 text-sm">
                @if ($empresa->telefono_principal)
                    <a href="{{ $empresa->telefono_url }}"
                        class="flex items-center gap-3 text-slate-700 dark:text-slate-200 hover:text-gold-700 dark:hover:text-gold-400 transition-colors">
                        <i class="fa-solid fa-phone w-5 text-center text-gold-700 dark:text-gold-400"></i>
                        <span class="font-semibold">{{ $empresa->telefono_principal }}</span>
                    </a>
                @endif
                @if ($empresa->email_contacto)
                    <a href="mailto:{{ $empresa->email_contacto }}"
                        class="flex items-center gap-3 text-slate-700 dark:text-slate-200 hover:text-gold-700 dark:hover:text-gold-400 transition-colors break-all">
                        <i class="fa-solid fa-envelope w-5 text-center text-gold-700 dark:text-gold-400"></i>
                        <span>{{ $empresa->email_contacto }}</span>
                    </a>
                @endif
                @if ($empresa->direccion_fisica || $empresa->ciudad_pais)
                    <div class="flex items-start gap-3 text-slate-700 dark:text-slate-200">
                        <i class="fa-solid fa-location-dot w-5 text-center text-gold-700 dark:text-gold-400 mt-0.5"></i>
                        <span>
                            {{ $empresa->direccion_fisica }}
                            @if ($empresa->ciudad_pais)
                                <span
                                    class="block text-xs text-slate-500 dark:text-slate-400">{{ $empresa->ciudad_pais }}</span>
                            @endif
                        </span>
                    </div>
                @endif
            </div>

            <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-center gap-2">
                @if ($empresa->redes_instagram)
                    <a href="{{ $empresa->redes_instagram }}" target="_blank" rel="noopener noreferrer"
                        title="Instagram"
                        class="w-9 h-9 rounded-xl bg-pink-500/10 text-pink-600 dark:text-pink-400 hover:bg-pink-500 hover:text-white border border-pink-500/20 flex items-center justify-center transition-all">
                        <i class="fa-brands fa-instagram text-lg"></i>
                    </a>
                @endif
                @if ($empresa->redes_tiktok)
                    <a href="{{ $empresa->redes_tiktok }}" target="_blank" rel="noopener noreferrer" title="TikTok"
                        class="w-9 h-9 rounded-xl bg-white dark:bg-slate-950 text-cyan-700 dark:text-cyan-400 hover:text-pink-400 border border-slate-700 flex items-center justify-center transition-all">
                        <i class="fa-brands fa-tiktok text-lg"></i>
                    </a>
                @endif
                @if ($empresa->redes_youtube)
                    <a href="{{ $empresa->redes_youtube }}" target="_blank" rel="noopener noreferrer" title="YouTube"
                        class="w-9 h-9 rounded-xl bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white border border-red-500/20 flex items-center justify-center transition-all">
                        <i class="fa-brands fa-youtube text-lg"></i>
                    </a>
                @endif
                @if ($empresa->redes_facebook)
                    <a href="{{ $empresa->redes_facebook }}" target="_blank" rel="noopener noreferrer" title="Facebook"
                        class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-[#1877F2] hover:text-white border border-blue-500/20 flex items-center justify-center transition-all">
                        <i class="fa-brands fa-facebook-f text-lg"></i>
                    </a>
                @endif
            </div>
        </div>

        <!-- Formulario -->
        <div class="lg:col-span-3 bg-slate-50 dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-xl hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
            data-reveal>
            <h3 class="font-serif font-bold text-lg text-slate-900 dark:text-white">Solicitud de cotización</h3>

            @if ($enviadoExito)
                <div
                    class="p-5 rounded-xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-700 dark:text-emerald-300 text-center space-y-1">
                    <i class="fa-solid fa-circle-check text-3xl text-emerald-600 dark:text-emerald-400"></i>
                    <h4 class="font-bold text-base text-slate-900 dark:text-white">¡Solicitud enviada!</h4>
                    <p class="text-sm text-emerald-800 dark:text-emerald-200">Nos pondremos en contacto contigo a la
                        brevedad.</p>
                </div>
            @endif

            @if ($servicioSeleccionado)
                <div
                    class="p-3 rounded-xl bg-gold-500/10 border border-gold-500/40 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="font-serif font-bold text-base text-slate-900 dark:text-white truncate">
                            {{ $servicioSeleccionado->nombre }}</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            {{ $servicioSeleccionado->duracion_minutos }} min ·
                            <strong class="text-gold-700 dark:text-gold-400">{{ $empresa->moneda_simbolo ?? 'Bs.' }}
                                {{ number_format($servicioSeleccionado->precio_base, 2) }}</strong>
                        </p>
                    </div>
                    <button type="button" wire:click="quitarSeleccion"
                        class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-950/80 hover:bg-rose-500/20 text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-800 text-xs font-semibold flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Quitar</span>
                    </button>
                </div>
            @elseif($paqueteSeleccionado)
                <div
                    class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/40 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="font-serif font-bold text-base text-slate-900 dark:text-white truncate">
                            {{ $paqueteSeleccionado->nombre }}</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            Paquete ·
                            <strong class="text-gold-700 dark:text-gold-400">{{ $empresa->moneda_simbolo ?? 'Bs.' }}
                                {{ number_format($paqueteSeleccionado->precio_paquete, 2) }}</strong>
                        </p>
                    </div>
                    <button type="button" wire:click="quitarSeleccion"
                        class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-950/80 hover:bg-rose-500/20 text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-800 text-xs font-semibold flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Quitar</span>
                    </button>
                </div>
            @endif

            <form wire:submit.prevent="enviarMensaje" class="space-y-3">
                <div>
                    <label
                        class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">Servicio
                        o paquete</label>
                    @php
                        $mon = $empresa->moneda_simbolo ?? 'Bs.';
                        $itemsServ = collect([['v' => '', 'l' => 'Consulta general']])
                            ->concat(
                                $servicios->map(
                                    fn($x) => [
                                        'v' => 'servicio_' . $x->id,
                                        'l' => $x->nombre,
                                        's' =>
                                            $mon .
                                            ' ' .
                                            number_format($x->precio_base, 0) .
                                            ' · ' .
                                            $x->duracion_minutos .
                                            ' min',
                                        'g' => 'Servicios',
                                    ],
                                ),
                            )
                            ->concat(
                                $paquetes->map(
                                    fn($x) => [
                                        'v' => 'paquete_' . $x->id,
                                        'l' => $x->nombre,
                                        's' => $mon . ' ' . number_format($x->precio_paquete, 0),
                                        'g' => 'Paquetes',
                                    ],
                                ),
                            )
                            ->values();
                    @endphp
                    <div class="relative" x-data="{
                        open: false,
                        q: '',
                        esOtro: false,
                        val: $wire.entangle('item_id').live,
                        items: @js($itemsServ),
                        get lista() {
                            const t = this.q.toLowerCase();
                            return this.items.filter(i => i.l.toLowerCase().includes(t));
                        },
                        get actual() {
                            if (this.esOtro && !this.val) return 'Otro';
                            const f = this.items.find(i => !i.otro && String(i.v) === String(this.val ?? ''));
                            return f ? f.l : '';
                        },
                        pick(i) {
                            this.esOtro = !!i.otro;
                            this.val = i.otro ? '' : i.v;
                            this.open = false;
                            this.q = '';
                        },
                        cabecera(n) { return n === 0 || this.lista[n - 1].g !== this.lista[n].g; }
                    }" @click.outside="open = false"
                        @keydown.escape="open = false">
                        <i
                            class="fa-solid fa-chevron-down absolute right-3 top-3 text-slate-400 text-[10px] pointer-events-none"></i>
                        <input type="text" autocomplete="off" :value="open ? q : actual"
                            @input="q = $event.target.value" @focus="open = true; q = ''; $event.target.select()"
                            placeholder="Consulta general"
                            class="w-full pl-3 pr-8 py-2 bg-white dark:bg-slate-950 border rounded-xl text-slate-900 dark:text-white text-sm placeholder:text-slate-400 focus:border-gold-500 focus:outline-none transition-colors border-slate-200 dark:border-slate-800">

                        <div x-show="open" x-cloak x-transition.opacity
                            class="absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl py-1">
                            <template x-for="(i, n) in lista" :key="n">
                                <div>
                                    <div x-show="i.g && cabecera(n)"
                                        class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400"
                                        x-text="i.g"></div>
                                    <button type="button" @click="pick(i)"
                                        class="w-full text-left px-3 py-1.5 text-sm text-slate-800 dark:text-slate-100 hover:bg-gold-500/10 flex items-center justify-between gap-3">
                                        <span class="truncate" x-text="i.l"></span>
                                        <span x-show="i.s" class="text-xs text-slate-500 dark:text-slate-400 shrink-0"
                                            x-text="i.s"></span>
                                    </button>
                                </div>
                            </template>
                            <div x-show="lista.length === 0" class="px-3 py-2 text-sm text-slate-500">Sin resultados
                            </div>
                        </div>

                        <p x-show="esOtro && !val" x-cloak class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                            <i class="fa-solid fa-circle-info mr-1"></i>Cuéntanos en el mensaje cuál es.
                        </p>
                    </div>

                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">Nombre
                            <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="nombre" placeholder="Ej: Juan Pérez"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-sm focus:border-gold-500 @error('nombre') !border-rose-500 @enderror">
                        @error('nombre')
                            <span class="text-xs text-rose-600 dark:text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">WhatsApp
                            <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="telefono" placeholder="Ej: 71234567"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-sm focus:border-gold-500 @error('telefono') !border-rose-500 @enderror">
                        @error('telefono')
                            <span class="text-xs text-rose-600 dark:text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">Correo
                            (opcional)</label>
                        <input type="email" wire:model="email" placeholder="cliente@correo.com"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-sm focus:border-gold-500">
                        @error('email')
                            <span class="text-xs text-rose-600 dark:text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">Celebración</label>
                        @php
                            $itemsTipo = $tiposEvento
                                ->map(fn($t) => ['v' => $t->id, 'l' => $t->nombre])
                                ->push(['v' => 'otro', 'l' => 'Otro', 'otro' => true])
                                ->values();
                        @endphp
                        <div class="relative" x-data="{
                            open: false,
                            q: '',
                            esOtro: false,
                            val: $wire.entangle('tipo_evento_id'),
                            items: @js($itemsTipo),
                            get lista() {
                                const t = this.q.toLowerCase();
                                return this.items.filter(i => i.l.toLowerCase().includes(t));
                            },
                            get actual() {
                                if (this.esOtro && !this.val) return 'Otro';
                                const f = this.items.find(i => !i.otro && String(i.v) === String(this.val ?? ''));
                                return f ? f.l : '';
                            },
                            pick(i) {
                                this.esOtro = !!i.otro;
                                this.val = i.otro ? '' : i.v;
                                this.open = false;
                                this.q = '';
                            },
                            cabecera(n) { return n === 0 || this.lista[n - 1].g !== this.lista[n].g; }
                        }" @click.outside="open = false"
                            @keydown.escape="open = false">
                            <i
                                class="fa-solid fa-chevron-down absolute right-3 top-3 text-slate-400 text-[10px] pointer-events-none"></i>
                            <input type="text" autocomplete="off" :value="open ? q : actual"
                                @input="q = $event.target.value" @focus="open = true; q = ''; $event.target.select()"
                                placeholder="Buscar o elegir..."
                                class="w-full pl-3 pr-8 py-2 bg-white dark:bg-slate-950 border rounded-xl text-slate-900 dark:text-white text-sm placeholder:text-slate-400 focus:border-gold-500 focus:outline-none transition-colors border-slate-200 dark:border-slate-800">

                            <div x-show="open" x-cloak x-transition.opacity
                                class="absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl py-1">
                                <template x-for="(i, n) in lista" :key="n">
                                    <div>
                                        <div x-show="i.g && cabecera(n)"
                                            class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400"
                                            x-text="i.g"></div>
                                        <button type="button" @click="pick(i)"
                                            class="w-full text-left px-3 py-1.5 text-sm text-slate-800 dark:text-slate-100 hover:bg-gold-500/10 flex items-center justify-between gap-3">
                                            <span class="truncate" x-text="i.l"></span>
                                            <span x-show="i.s"
                                                class="text-xs text-slate-500 dark:text-slate-400 shrink-0"
                                                x-text="i.s"></span>
                                        </button>
                                    </div>
                                </template>
                                <div x-show="lista.length === 0" class="px-3 py-2 text-sm text-slate-500">Sin
                                    resultados</div>
                            </div>

                            <p x-show="esOtro && !val" x-cloak
                                class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                <i class="fa-solid fa-circle-info mr-1"></i>Cuéntanos en el mensaje cuál es.
                            </p>
                        </div>

                        @error('tipo_evento_id')
                            <span class="text-xs text-rose-600 dark:text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">Fecha
                            prevista</label>
                        <x-web.calendario-disponibilidad />
                        @error('fecha_estimada')
                            <span class="text-xs text-rose-600 dark:text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="flex flex-col">
                            <label
                                class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">Mensaje
                                <span class="text-rose-500">*</span></label>
                            <textarea wire:model="mensaje" rows="4" placeholder="Dirección aproximada, hora, canciones especiales..."
                                class="w-full flex-1 min-h-[96px] px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-sm focus:border-gold-500 @error('mensaje') !border-rose-500 @enderror"></textarea>
                            @error('mensaje')
                                <span
                                    class="text-xs text-rose-600 dark:text-rose-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <div
                                class="flex items-center gap-2 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-gold-500/30 rounded-xl text-gold-700 dark:text-gold-400 font-bold text-sm select-none">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>{{ $captcha_num1 }} + {{ $captcha_num2 }} = ?</span>
                                <button type="button" wire:click="generarCaptcha" title="Cambiar suma"
                                    class="ml-1 text-slate-500 hover:text-gold-700 dark:hover:text-gold-400">
                                    <i class="fa-solid fa-rotate-right"></i>
                                </button>
                            </div>
                            <input type="number" wire:model="captcha_respuesta" placeholder="Resultado"
                                class="flex-1 w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-sm focus:border-gold-500 @error('captcha_respuesta') !border-rose-500 @enderror">
                        </div>
                        @error('captcha_respuesta')
                            <span class="text-xs text-rose-600 dark:text-rose-400 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 text-sm transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane" wire:loading.remove></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading></i>
                    <span wire:loading.remove>Enviar solicitud</span>
                    <span wire:loading>Procesando...</span>
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL 1: Pregunta de Anticipo Fijo de Bs. 100 mediante QR -->
    <div x-data="{ open: @entangle('modalAnticipoOpen') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
            @click="$wire.set('modalAnticipoOpen', false)"></div>

        <!-- Contenedor Centrado -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg transform rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-2xl transition-all space-y-4">
                
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gold-500/10 border border-gold-500/30 flex items-center justify-center text-gold-600 dark:text-gold-400 text-2xl shadow-inner">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h3 class="font-serif font-bold text-xl text-slate-900 dark:text-white">
                        ¿Deseas asegurar tu fecha con un anticipo?
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Puedes reservar tu evento en este momento dejando un anticipo fijo de <strong class="text-gold-700 dark:text-gold-400 font-bold">Bs. 100.00</strong> mediante <strong class="text-slate-900 dark:text-white">QR Simple</strong> de la red bancaria nacional. Así tu horario quedará bloqueado con máxima prioridad.
                    </p>
                </div>

                <!-- Tarjeta informativa del anticipo -->
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-gold-500/20 space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span class="text-slate-600 dark:text-slate-400">Monto de reserva inicial:</span>
                        <span class="text-base font-bold text-gold-700 dark:text-gold-400">Bs. 100.00</span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                        <span>Generado de forma segura a través de la API del <strong>Banco Económico</strong>.</span>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="space-y-2 pt-2">
                    <button type="button" wire:click="iniciarPagoQr" wire:loading.attr="disabled"
                        class="w-full py-3 px-4 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-400 hover:to-gold-500 text-slate-950 text-sm shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-qrcode text-base"></i>
                        <span>Pagar anticipo de Bs. 100 con QR</span>
                    </button>

                    <button type="button" wire:click="enviarSinAnticipo" wire:loading.attr="disabled"
                        class="w-full py-2.5 px-4 rounded-xl font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm border border-slate-300 dark:border-slate-700 transition-all">
                        Enviar solo cotización (sin anticipo)
                    </button>

                    <button type="button" @click="$wire.set('modalAnticipoOpen', false)"
                        class="w-full text-center text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors pt-1">
                        Volver al formulario para revisar datos
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL 2: Visor del Código QR con Temporizador de 4 Minutos e Instrucciones -->
    <div x-data="{
            open: @entangle('modalQrOpen'),
            segundos: @entangle('qrSegundosRestantes'),
            interval: null,
            iniciarContador() {
                if (this.interval) clearInterval(this.interval);
                this.interval = setInterval(() => {
                    if (this.segundos > 0) {
                        this.segundos--;
                    } else {
                        clearInterval(this.interval);
                        $wire.tiempoExpirado();
                    }
                }, 1000);
            },
            format(s) {
                const m = Math.floor(Math.max(0, s) / 60);
                const sec = Math.max(0, s) % 60;
                return `${m.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
            }
        }"
        x-init="$watch('open', value => { if (value) iniciarContador(); else if (interval) clearInterval(interval); })"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-3 sm:p-4"
            @if ($modalQrOpen && $qrId && !$pagoConfirmadoExito && !$qrExpirado) wire:poll.3s="verificarEstadoQr" @endif>
            
            <div class="relative w-full max-w-md transform rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-2xl transition-all space-y-4">

                @if ($pagoConfirmadoExito)
                    <!-- ESTADO 1: PAGO CONFIRMADO CON ÉXITO -->
                    <div class="text-center space-y-3 py-4">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-500/10 border-2 border-emerald-500 flex items-center justify-center text-emerald-500 text-3xl animate-bounce">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h3 class="font-serif font-bold text-2xl text-slate-900 dark:text-white">
                            ¡Anticipo confirmado!
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Recibimos tu anticipo de <strong class="text-emerald-600 font-bold">Bs. 100.00</strong> con éxito mediante Pago Simple QR.
                        </p>
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-500/30 text-xs text-emerald-800 dark:text-emerald-200 space-y-1 text-left">
                            <div><strong>Operación:</strong> Anticipo de reserva formal</div>
                            <div><strong>Importe:</strong> Bs. 100.00</div>
                            <div><strong>Estado:</strong> Confirmado en línea por Banco Económico</div>
                        </div>
                        <button type="button" @click="open = false; window.location.href = '{{ route('web.home') }}'"
                            class="w-full py-3 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-500 text-white text-sm shadow-lg shadow-emerald-600/30 transition-all">
                            Finalizar y volver al inicio
                        </button>
                    </div>

                @elseif ($qrExpirado)
                    <!-- ESTADO 2: CÓDIGO QR EXPIRADO (LÍMITE 4 MINUTOS ALCANZADO) -->
                    <div class="text-center space-y-3 py-4">
                        <div class="w-14 h-14 mx-auto rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-500 text-2xl">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                        <h3 class="font-serif font-bold text-xl text-slate-900 dark:text-white">
                            Tiempo de QR agotado
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                            El tiempo máximo de <strong class="text-slate-900 dark:text-white">4 minutos</strong> ha expirado por seguridad bancaria. Puedes generar uno nuevo o enviar tu solicitud sin anticipo.
                        </p>

                        <div class="space-y-2 pt-2">
                            <button type="button" wire:click="generarNuevoQr" wire:loading.attr="disabled"
                                class="w-full py-2.5 rounded-xl font-bold bg-gold-500 hover:bg-gold-400 text-slate-950 text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-rotate-right"></i>
                                <span>Generar nuevo QR (4 minutos)</span>
                            </button>

                            <button type="button" wire:click="enviarSinAnticipo" wire:loading.attr="disabled"
                                class="w-full py-2.5 rounded-xl font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs border border-slate-300 dark:border-slate-700 transition-all">
                                Enviar solicitud sin anticipo
                            </button>
                        </div>
                    </div>

                @elseif ($qrGenerando)
                    <!-- ESTADO 3: GENERANDO QR EN TIEMPO REAL -->
                    <div class="text-center py-12 space-y-3">
                        <i class="fa-solid fa-circle-notch fa-spin text-4xl text-gold-500"></i>
                        <h4 class="font-serif font-bold text-lg text-slate-900 dark:text-white">Generando Código QR Simple...</h4>
                        <p class="text-xs text-slate-500">Conectando de forma segura con la red del Banco Económico...</p>
                    </div>

                @elseif ($qrImageBase64)
                    <!-- ESTADO 4: VISOR DEL QR ACTIVO CON TEMPORIZADOR DE 4 MINUTOS -->
                    <div class="text-center space-y-2">
                        <!-- Cabecera de Monto y Temporizador -->
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                            <div class="text-left">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Importe a pagar</span>
                                <span class="text-xl font-black text-slate-900 dark:text-white font-mono">Bs. 100.00</span>
                            </div>

                            <!-- Temporizador de 4 minutos con alerta visual -->
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border"
                                :class="segundos <= 60 ? 'bg-rose-500/10 border-rose-500/40 text-rose-600 dark:text-rose-400 animate-pulse' : 'bg-gold-500/10 border-gold-500/30 text-gold-700 dark:text-gold-400'">
                                <i class="fa-regular fa-clock text-sm"></i>
                                <span class="font-mono font-bold text-sm tabular-nums" x-text="format(segundos)">04:00</span>
                            </div>
                        </div>

                        <!-- Imagen del Código QR -->
                        <div class="pt-2">
                            <div class="inline-block p-2.5 bg-white rounded-2xl shadow-xl border-2 border-gold-500/30">
                                <img src="data:image/png;base64,{{ $qrImageBase64 }}" alt="QR Pago Simple Banco Económico"
                                    class="w-56 h-56 object-contain block mx-auto">
                            </div>
                        </div>

                        <!-- Indicador pulsante de detección en vivo -->
                        <div class="pt-1 flex items-center justify-center gap-2 text-xs font-semibold text-gold-700 dark:text-gold-400">
                            <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                            <span>Esperando confirmación de tu app bancaria...</span>
                        </div>

                        <!-- Instrucciones oficiales para Banca Boliviana -->
                        <div class="text-left text-xs bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-mobile-screen text-gold-600"></i>
                                <span>Instrucciones para pagar desde tu banco:</span>
                            </div>
                            <ol class="list-decimal list-inside text-[11px] text-slate-600 dark:text-slate-300 space-y-1 leading-relaxed">
                                <li>Abre tu app bancaria (<strong>Banco Unión, BCP, BNB, Banco Económico, Mercantil Santa Cruz, FIE, BISA, Ganadero, Sol</strong>, etc.).</li>
                                <li>Ingresa a la sección <strong>Pago Simple / Cobro con QR</strong> y presiona <strong>Escanear QR</strong>.</li>
                                <li>Verifica el monto de <strong>Bs. 100.00</strong> y confirma la transferencia.</li>
                                <li><strong>No cierres esta ventana:</strong> tu pago se detectará y confirmará automáticamente en unos segundos.</li>
                            </ol>
                        </div>

                        <!-- Botones inferiores de cancelación o auxilio -->
                        <div class="pt-2 flex items-center justify-between gap-3 text-xs">
                            <button type="button" wire:click="cancelarQr"
                                class="text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                                <i class="fa-solid fa-xmark mr-1"></i>Cancelar QR
                            </button>

                            <button type="button" wire:click="enviarSinAnticipo"
                                class="text-gold-700 dark:text-gold-400 font-semibold hover:underline">
                                Enviar sin anticipo &rarr;
                            </button>
                        </div>

                        @if (config('app.debug'))
                            <div class="pt-2 border-t border-dashed border-amber-500/30">
                                <button type="button" wire:click="simularPagoPrueba"
                                    class="w-full py-1.5 px-2 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold text-[11px] border border-amber-500/30 transition-colors flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-flask"></i>
                                    <span>Simular Pago Exitoso (Ambiente de Pruebas Sandbox)</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>