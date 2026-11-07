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
                    <span wire:loading>Enviando...</span>
                </button>
            </form>
        </div>
    </div>
</div>