<!-- Modal rápido: Nuevo Cliente (se abre encima del formulario de Evento) -->
@if ($modalClienteOpen)
    <x-ui.modal title="Nuevo Cliente" icon="fa-solid fa-user-plus" :nested="true" onClose="cerrarModalNuevoCliente">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
            <x-ui.input class="sm:col-span-2" label="Nombre Completo" required name="nc_nombre_completo"
                wire:model="nc_nombre_completo" wire:keydown.enter="guardarNuevoCliente" autofocus />

            <x-ui.input label="C.I. / NIT" name="nc_ci_nit" wire:model="nc_ci_nit" placeholder="ej. 10293847" />

            <x-ui.input label="Teléfono Principal" wire:model="nc_telefono" placeholder="ej. 70000000" />

            <x-ui.input label="WhatsApp Comercial" wire:model="nc_whatsapp" placeholder="ej. +591 70000000" />

            <x-ui.input type="email" label="Correo Electrónico" name="nc_email" wire:model="nc_email"
                placeholder="cliente@ejemplo.com" />

            <x-ui.input class="sm:col-span-2" label="Dirección de Domicilio / Oficina" wire:model="nc_direccion"
                placeholder="Av. Principal #123" />
        </div>

        <x-slot:footer>
            <x-ui.button variant="secondary" size="sm" wire:click="cerrarModalNuevoCliente">
                <i class="fa-solid fa-xmark"></i>
                <span>Cancelar</span>
            </x-ui.button>
            <x-ui.button variant="primary" size="sm" wire:click="guardarNuevoCliente" wire:loading.attr="disabled"
                wire:target="guardarNuevoCliente">
                <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardarNuevoCliente"></i>
                <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardarNuevoCliente"></i>
                <span>Guardar y Seleccionar</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
@endif

<!-- Modal Crear/Editar Evento -->
@if ($modalOpen)
    <form wire:submit.prevent="guardar" id="formEvento" x-data
        x-on:evento-error-validacion.window="document.getElementById('modalEventoBody')?.scrollTo({top: 0, behavior: 'smooth'})">
        <x-ui.modal maxWidth="max-w-3xl" bodyId="modalEventoBody" onClose="$set('modalOpen', false)">
            <x-slot:header>
                <h3 class="text-base font-bold text-ink-primary flex items-center gap-2 truncate">
                    <i class="fa-solid fa-calendar-check text-gold-600 dark:text-gold-400"></i>
                    <span class="truncate">{{ $isEdit ? 'Editar evento' : 'Nuevo evento' }} - <span
                            class="text-gold-600 dark:text-gold-400">{{ $codigo_evento }}</span></span>
                </h3>
            </x-slot:header>

            <div class="px-5 py-4 space-y-4">
                <!-- Banner Alerta Superior de Errores de Validación -->
                @if ($errors->any() || session()->has('modal_error') || session()->has('participante_error'))
                    <div
                        class="p-4 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 space-y-2">
                        <div class="flex items-center gap-2 font-semibold text-sm">
                            <i class="fa-solid fa-triangle-exclamation text-base"></i>
                            <span>Por favor revise los siguientes datos requeridos:</span>
                        </div>
                        @if (session()->has('modal_error'))
                            <div class="text-xs">{{ session('modal_error') }}</div>
                        @endif
                        @if (session()->has('participante_error'))
                            <div class="text-xs">{{ session('participante_error') }}</div>
                        @endif
                        @if ($errors->any())
                            <ul class="list-disc list-inside text-xs space-y-1 pl-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                    <div class="min-w-0 sm:col-span-3">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-semibold text-ink-primary">Cliente: <span
                                    class="text-rose-500 font-black text-base leading-none">*</span></label>
                            <button type="button" wire:click="abrirModalNuevoCliente"
                                class="text-[11px] font-semibold text-gold-600 dark:text-gold-400 hover:underline flex items-center gap-1 transition-colors"
                                title="Registrar un cliente nuevo sin salir de este formulario">
                                <i class="fa-solid fa-user-plus"></i>
                                <span>Nuevo cliente</span>
                            </button>
                        </div>
                        @php
                            $opcionesClientesEvento = $clientes
                                ->map(
                                    fn($c) => [
                                        'id' => $c->id,
                                        'label' => $c->nombre_completo,
                                        'meta' => trim(
                                            ($c->ci_nit ? 'CI: ' . $c->ci_nit : '') .
                                                ($c->telefono ? ' - Tel: ' . $c->telefono : ''),
                                        ),
                                        'search' => mb_strtolower(
                                            $c->nombre_completo . ' ' . $c->ci_nit . ' ' . $c->telefono,
                                        ),
                                    ],
                                )
                                ->values();
                        @endphp
                        <x-ui.select-search name="cliente_id" :options="$opcionesClientesEvento" placeholder="Buscar cliente..."
                            push-event="cliente-creado" on-select="seleccionarCliente(option.id)" />
                        @error('cliente_id')
                            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="min-w-0 sm:col-span-3">
                        <label class="block text-sm font-semibold text-ink-primary mb-1">Servicio: <span
                                class="text-rose-500 font-black text-base leading-none">*</span></label>
                        @php
                            $opcionesServiciosEvento = $servicios
                                ->map(
                                    fn($sv) => [
                                        'id' => $sv->id,
                                        'label' => $sv->nombre,
                                        'meta' => $sv->por_hora
                                            ? 'Bs ' . number_format($sv->precio_base, 0) . ' por hora'
                                            : 'Bs ' .
                                                number_format($sv->precio_base, 2) .
                                                ($sv->duracion_minutos ? ' - ' . $sv->duracion_minutos . ' min' : ''),
                                        'search' => mb_strtolower($sv->nombre),
                                    ],
                                )
                                ->values();
                        @endphp
                        <x-ui.select-search name="servicio_id" :options="$opcionesServiciosEvento" placeholder="Buscar servicio..."
                            on-select="seleccionarServicio(option.id)" />
                        @error('servicio_id')
                            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Calendario Interactivo Desplegable para Fecha de Evento -->
                    <div class="relative min-w-0 sm:col-span-2" x-data="{
                        open: false,
                        selectedDate: @entangle('fecha_evento'),
                        currentYear: new Date().getFullYear(),
                        currentMonth: new Date().getMonth(),
                        months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                        weekdays: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                        init() {
                            this.syncFromSelected();
                            this.$watch('selectedDate', () => this.syncFromSelected());
                        },
                        syncFromSelected() {
                            if (this.selectedDate) {
                                const parts = this.selectedDate.split('-');
                                if (parts.length === 3) {
                                    this.currentYear = parseInt(parts[0]);
                                    this.currentMonth = parseInt(parts[1]) - 1;
                                }
                            }
                        },
                        get daysInMonth() {
                            return new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                        },
                        get firstDayOfWeek() {
                            return new Date(this.currentYear, this.currentMonth, 1).getDay();
                        },
                        get formattedDisplay() {
                            if (!this.selectedDate) return 'Seleccionar Fecha...';
                            const parts = this.selectedDate.split('-');
                            if (parts.length === 3) {
                                return `${parts[2]}/${parts[1]}/${parts[0]}`;
                            }
                            return this.selectedDate;
                        },
                        selectDay(day) {
                            const m = String(this.currentMonth + 1).padStart(2, '0');
                            const d = String(day).padStart(2, '0');
                            const full = `${this.currentYear}-${m}-${d}`;
                            this.selectedDate = full;
                            $wire.set('fecha_evento', full);
                            this.open = false;
                        },
                        prevMonth() {
                            if (this.currentMonth === 0) {
                                this.currentMonth = 11;
                                this.currentYear--;
                            } else {
                                this.currentMonth--;
                            }
                        },
                        nextMonth() {
                            if (this.currentMonth === 11) {
                                this.currentMonth = 0;
                                this.currentYear++;
                            } else {
                                this.currentMonth++;
                            }
                        },
                        selectToday() {
                            const today = new Date();
                            this.currentYear = today.getFullYear();
                            this.currentMonth = today.getMonth();
                            this.selectDay(today.getDate());
                        },
                        isToday(day) {
                            const today = new Date();
                            return today.getFullYear() === this.currentYear &&
                                today.getMonth() === this.currentMonth &&
                                today.getDate() === day;
                        },
                        isSelected(day) {
                            if (!this.selectedDate) return false;
                            const m = String(this.currentMonth + 1).padStart(2, '0');
                            const d = String(day).padStart(2, '0');
                            return this.selectedDate === `${this.currentYear}-${m}-${d}`;
                        }
                    }" @click.outside="open = false">
                        <label class="block text-sm font-semibold text-ink-primary mb-1">Fecha del evento: <span
                                class="text-rose-500 font-black text-base leading-none">*</span></label>

                        <div class="relative">
                            <button type="button" @click="open = !open"
                                class="w-full h-9 px-3.5 bg-surface-sunken border border-outline rounded-lg text-sm text-left flex items-center justify-between hover:border-gold-500/60 transition-colors cursor-pointer">
                                <span class="font-semibold"
                                    :class="selectedDate ? 'text-gold-600 dark:text-gold-400' : 'text-ink-muted'"
                                    x-text="formattedDisplay"></span>
                                <i class="fa-solid fa-calendar-days text-gold-600 dark:text-gold-400 text-sm"></i>
                            </button>

                            <!-- Desplegable de Calendario Visual -->
                            <div x-show="open" x-cloak x-transition
                                class="absolute left-0 top-full mt-1 z-50 w-72 p-3 bg-surface-card border border-gold-500/50 rounded-xl shadow-2xl space-y-3">

                                <!-- Navegación de Mes y Año -->
                                <div class="flex items-center justify-between border-b border-outline pb-2">
                                    <button type="button" @click="prevMonth()"
                                        class="p-1 text-ink-secondary hover:text-ink-primary rounded-lg hover:bg-surface-hover">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                    </button>
                                    <div class="text-xs font-semibold text-ink-primary flex items-center gap-1">
                                        <span x-text="months[currentMonth]"></span>
                                        <span class="text-gold-600 dark:text-gold-400" x-text="currentYear"></span>
                                    </div>
                                    <button type="button" @click="nextMonth()"
                                        class="p-1 text-ink-secondary hover:text-ink-primary rounded-lg hover:bg-surface-hover">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </button>
                                </div>

                                <!-- Encabezado Días de la Semana -->
                                <div class="grid grid-cols-7 text-center text-[10px] font-semibold text-ink-muted">
                                    <template x-for="w in weekdays" :key="w">
                                        <div x-text="w"></div>
                                    </template>
                                </div>

                                <!-- Cuadrícula de Días -->
                                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                    <!-- Espacios vacíos al inicio del mes -->
                                    <template x-for="blank in firstDayOfWeek" :key="'b' + blank">
                                        <div></div>
                                    </template>

                                    <!-- Días del mes -->
                                    <template x-for="d in daysInMonth" :key="d">
                                        <button type="button" @click="selectDay(d)"
                                            :class="{
                                                'bg-gold-500 text-slate-950 font-bold': isSelected(
                                                    d),
                                                'border border-gold-500/40 text-gold-600 dark:text-gold-400 font-semibold': isToday(
                                                        d) && !
                                                    isSelected(d),
                                                'text-ink-secondary hover:bg-surface-hover hover:text-ink-primary': !
                                                    isSelected(
                                                        d) && !isToday(d)
                                            }"
                                            class="h-7 w-7 mx-auto rounded-lg flex items-center justify-center transition-colors">
                                            <span x-text="d"></span>
                                        </button>
                                    </template>
                                </div>

                                <!-- Acciones Rápidas -->
                                <div
                                    class="flex items-center justify-between border-t border-outline pt-2 text-[11px]">
                                    <button type="button" @click="selectToday()"
                                        class="text-gold-600 dark:text-gold-400 hover:underline font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-calendar-day"></i> Hoy
                                    </button>
                                    <button type="button" @click="open = false"
                                        class="text-ink-muted hover:text-ink-primary">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                        @error('fecha_evento')
                            <span class="text-xs text-rose-400 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="min-w-0 sm:col-span-2">
                        <x-ui.input type="time" label="Hora del evento" required name="hora_evento"
                            wire:model="hora_evento" />
                    </div>

                    <div class="min-w-0 sm:col-span-2">
                        <x-ui.input type="number" step="1" min="1" inputmode="numeric"
                            label="Duración (min)" required name="duracion_horas"
                            wire:model.live.debounce.400ms="duracion_min" placeholder="60" />
                        @php $servicioSeleccionado = $servicios->firstWhere('id', $servicio_id); @endphp
                        @if ($servicioSeleccionado && $servicioSeleccionado->por_hora)
                            <p class="text-xs text-ink-muted mt-1">
                                Bs {{ number_format($servicioSeleccionado->precio_base, 0) }} por hora
                                @if ($servicioSeleccionado->horas_minimas)
                                    - mínimo
                                    {{ rtrim(rtrim(number_format($servicioSeleccionado->horas_minimas, 1), '0'), '.') }}
                                    h
                                    ({{ (int) round($servicioSeleccionado->horas_minimas * 60) }} min)
                                @endif
                            </p>
                        @elseif ($servicioSeleccionado)
                            <p class="text-xs text-ink-muted mt-1">
                                Del servicio: {{ $servicioSeleccionado->duracion_minutos }} min
                            </p>
                        @endif
                    </div>

                    <div class="sm:col-span-4 min-w-0">
                        @php
                            $dirClienteEvento = $cliente_id
                                ? optional($clientes->firstWhere('id', $cliente_id))->direccion
                                : null;
                            $telModal = preg_replace('/[^0-9]/', '', $telefono_contacto ?: '');
                            if ($telModal && strlen($telModal) === 8) {
                                $telModal = '591' . $telModal;
                            }
                        @endphp
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-semibold text-ink-primary">Dirección del evento: <span
                                    class="text-rose-500 font-black text-base leading-none">*</span></label>
                            <div class="flex items-center gap-3 shrink-0">
                                @if ($dirClienteEvento && trim($direccion_evento) === '')
                                    <button type="button" wire:click="usarDireccionCliente"
                                        class="text-[11px] font-semibold text-gold-600 dark:text-gold-400 hover:underline flex items-center gap-1"
                                        title="{{ $dirClienteEvento }}">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <span>Usar la del cliente</span>
                                    </button>
                                @endif
                                @if ($telModal)
                                    <a href="https://api.whatsapp.com/send?phone={{ $telModal }}&text={{ urlencode('Hola, le escribimos del Mariachi León Guanajuato sobre su evento ' . $codigo_evento . '. Por favor compártenos la ubicación o referencias de la presentación.') }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-600 text-white hover:bg-emerald-500 transition-colors shrink-0"
                                        title="Abrir WhatsApp del cliente">
                                        <i class="fa-brands fa-whatsapp"></i>
                                        <span>WhatsApp</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <input type="text" wire:model="direccion_evento"
                            placeholder="Lugar exacto, ej. Salón Las Palmas, Av. Banzer 4to Anillo"
                            class="w-full h-9 px-3.5 bg-surface-sunken border @error('direccion_evento') border-rose-500 ring-1 ring-rose-500 @else border-outline @enderror rounded-lg text-ink-primary text-sm placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                        @error('direccion_evento')
                            <span class="text-xs text-rose-400 mt-1 block font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sm:col-span-2 min-w-0">
                        <x-ui.select label="Estado del evento" wire:model="estado">
                            <option value="Pendiente">Pendiente</option>
                            <option value="Cotizado">Cotizado</option>
                            <option value="Reservado">Reservado</option>
                            <option value="Confirmado">Confirmado</option>
                            <option value="Realizado">Realizado</option>
                            <option value="Cancelado">Cancelado</option>
                            <option value="Finalizado">Finalizado</option>
                        </x-ui.select>
                    </div>

                    <!-- Datos secundarios (plegados): el contacto y teléfono se toman del cliente -->
                    <div class="sm:col-span-6 min-w-0" x-data="{ mas: false }">
                        <button type="button" @click="mas = !mas"
                            class="text-xs font-semibold text-ink-secondary hover:text-ink-primary flex items-center gap-1.5">
                            <i class="fa-solid fa-chevron-right text-[10px] transition-transform"
                                :class="mas && 'rotate-90'"></i>
                            <span>Más datos - contacto y teléfono</span>
                        </button>
                        <div x-show="mas" x-cloak class="grid grid-cols-1 sm:grid-cols-6 gap-3 mt-2">
                            <div class="min-w-0 sm:col-span-3">
                                <x-ui.input label="Contacto del evento" wire:model="contacto_evento"
                                    placeholder="Nombre de quien recibe" />
                            </div>

                            <div class="min-w-0 sm:col-span-3">
                                <x-ui.input label="Teléfono de contacto" wire:model="telefono_contacto"
                                    placeholder="ej. 70000000" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: Equipo del evento (personas del catálogo "Músicos y personal") -->
                @php
                    // Icono y color según el tipo de persona
                    $estiloTipo = function ($tipo) {
                        $t = mb_strtolower((string) $tipo);
                        if (str_contains($t, 'transp') || str_contains($t, 'bus') || str_contains($t, 'chofer')) {
                            return ['fa-bus', 'bg-sky-500/15 text-sky-600 dark:text-sky-400', 'transporte'];
                        }
                        if (str_contains($t, 'sonid')) {
                            return [
                                'fa-volume-high',
                                'bg-violet-500/15 text-violet-600 dark:text-violet-400',
                                'sonido',
                            ];
                        }
                        if (
                            $t === '' ||
                            str_contains($t, 'músic') ||
                            str_contains($t, 'music') ||
                            str_contains($t, 'cantan')
                        ) {
                            return ['fa-music', 'bg-amber-500/15 text-amber-600 dark:text-amber-400', 'musico'];
                        }
                        return ['fa-user', 'bg-slate-500/15 text-slate-600 dark:text-slate-300', 'otro'];
                    };
                    $incluidosEquipo = collect($participantesSugeridos)->filter(fn($pe) => !empty($pe['incluido']));
                    $idsEnEquipo = $incluidosEquipo->pluck('persona_id')->all();
                    $disponiblesEquipo = $personasDisponibles->reject(fn($pp) => in_array($pp->id, $idsEnEquipo));
                    $nombresCategoria = [
                        'musico' => 'Músicos',
                        'transporte' => 'Transporte',
                        'sonido' => 'Sonido',
                        'otro' => 'Otros',
                    ];
                    $conteoCategorias = $disponiblesEquipo->groupBy(fn($pp) => $estiloTipo($pp->tipo)[2])->map->count();
                    $totalAsignadoEquipo = (float) $incluidosEquipo->sum(
                        fn($pe) => (float) ($pe['monto_asignado'] ?? 0),
                    );
                    $restoEvento = (float) $precioReferencia - $totalAsignadoEquipo;
                    $nMusicosTarifa = $incluidosEquipo
                        ->filter(
                            fn($pe) => $estiloTipo($pe['funcion'] ?? '')[2] === 'musico' &&
                                ($pe['modalidad'] ?? 'por_evento') !== 'mensual',
                        )
                        ->count();
                @endphp
                <div class="pt-3 border-t border-outline space-y-2" x-data="{ q: '', cat: 'todos' }">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-bold text-ink-primary flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-users text-gold-600 dark:text-gold-400"></i>
                            <span>Equipo</span>
                            <x-ui.badge variant="neutral">{{ $incluidosEquipo->count() }}</x-ui.badge>
                        </h4>
                        @if ($conteoCategorias->count() > 1)
                            <div
                                class="flex gap-1.5 overflow-x-auto min-w-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                <button type="button" @click="cat = 'todos'"
                                    :class="cat === 'todos' ? 'bg-ink-primary text-surface-card border-ink-primary' :
                                        'bg-surface-card text-ink-secondary border-outline hover:border-gold-500'"
                                    class="shrink-0 h-7 px-2.5 rounded-full border text-[13px] font-semibold transition-colors">
                                    Todos <span class="opacity-70">{{ $disponiblesEquipo->count() }}</span>
                                </button>
                                @foreach ($nombresCategoria as $keyCat => $labelCat)
                                    @if (($conteoCategorias[$keyCat] ?? 0) > 0)
                                        <button type="button" @click="cat = '{{ $keyCat }}'"
                                            :class="cat === '{{ $keyCat }}' ?
                                                'bg-ink-primary text-surface-card border-ink-primary' :
                                                'bg-surface-card text-ink-secondary border-outline hover:border-gold-500'"
                                            class="shrink-0 h-7 px-2.5 rounded-full border text-[13px] font-semibold transition-colors">
                                            {{ $labelCat }} <span
                                                class="opacity-70">{{ $conteoCategorias[$keyCat] }}</span>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if (session()->has('participante_error'))
                        <div
                            class="p-2.5 bg-rose-500/10 border border-rose-500/20 rounded-lg text-rose-700 dark:text-rose-400 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-sm"></i>
                            <span>{{ session('participante_error') }}</span>
                        </div>
                    @endif

                    @error('participantes')
                        <div
                            class="p-2.5 bg-rose-500/10 border border-rose-500/20 rounded-lg text-rose-700 dark:text-rose-400 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-sm"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <!-- Tira de personas disponibles: un toque la agrega -->
                    @if ($personasDisponibles->count() === 0)
                        <p class="text-xs text-ink-muted">No hay músicos ni personal registrados. Se crean en Músicos y
                            personal.</p>
                    @elseif ($disponiblesEquipo->count() > 0)
                        <div class="space-y-1.5">
                            <div class="relative">
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                                <input type="text" x-model="q" placeholder="Buscar músico o personal..."
                                    class="w-full h-8 pl-9 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                            </div>
                            <div class="relative">
                                <button type="button"
                                    @click="$refs.tira.scrollBy({ left: -260, behavior: 'smooth' })"
                                    class="hidden sm:flex absolute -left-3 top-[calc(50%-4px)] -translate-y-1/2 z-10 w-7 h-7 rounded-full bg-surface-card border border-outline shadow items-center justify-center text-ink-secondary hover:text-ink-primary hover:border-gold-500"
                                    title="Anteriores">
                                    <i class="fa-solid fa-chevron-left text-xs"></i>
                                </button>
                                <button type="button" @click="$refs.tira.scrollBy({ left: 260, behavior: 'smooth' })"
                                    class="hidden sm:flex absolute -right-3 top-[calc(50%-4px)] -translate-y-1/2 z-10 w-7 h-7 rounded-full bg-surface-card border border-outline shadow items-center justify-center text-ink-secondary hover:text-ink-primary hover:border-gold-500"
                                    title="Siguientes">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </button>
                                <div x-ref="tira" class="flex gap-2 overflow-x-auto pb-2 [scrollbar-width:thin]">
                                    @foreach ($disponiblesEquipo as $pp)
                                        @php
                                            [$icoPp, $clsPp, $catPp] = $estiloTipo($pp->tipo);
                                            $mensualPp = ($pp->modalidad_pago ?? 'por_evento') === 'mensual';
                                            $metaPp =
                                                ($pp->tipo ?: 'Músico') .
                                                ' - ' .
                                                ($mensualPp
                                                    ? 'Mensual'
                                                    : ($pp->tarifa_evento
                                                        ? 'Bs ' . number_format($pp->tarifa_evento, 0)
                                                        : 'Sin tarifa'));
                                            $buscaPp = mb_strtolower($pp->nombre_completo . ' ' . $pp->tipo);
                                        @endphp
                                        <button type="button" wire:key="disp-{{ $pp->id }}"
                                            wire:click="agregarPersona({{ $pp->id }})"
                                            x-show="(cat === 'todos' || cat === '{{ $catPp }}') && (!q || @js($buscaPp).includes(q.toLowerCase()))"
                                            class="flex-none inline-flex items-center gap-2 pl-1.5 pr-2 py-1.5 bg-surface-card border border-outline rounded-xl text-left hover:border-gold-500 active:scale-[0.98] transition">
                                            <span
                                                class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $clsPp }}">
                                                <i class="fa-solid {{ $icoPp }} text-sm"></i>
                                            </span>
                                            <span class="flex flex-col leading-tight">
                                                <span
                                                    class="text-sm font-semibold text-ink-primary whitespace-nowrap">{{ $pp->nombre_completo }}</span>
                                                <span
                                                    class="text-xs text-ink-muted whitespace-nowrap">{{ $metaPp }}</span>
                                            </span>
                                            <span
                                                class="w-6 h-6 rounded-full bg-gold-500/15 text-gold-600 dark:text-gold-400 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-plus text-[11px]"></i>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Misma tarifa para todos los músicos -->
                    @if ($nMusicosTarifa > 1)
                        <div class="flex flex-wrap items-center gap-2 text-sm" x-data="{ t: '' }">
                            <span class="text-ink-secondary">Misma tarifa a los {{ $nMusicosTarifa }} músicos:</span>
                            <div class="relative w-24">
                                <span
                                    class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] font-bold text-ink-muted">Bs</span>
                                <input type="number" step="0.50" min="0" inputmode="decimal"
                                    x-model="t" @keydown.enter.prevent="$wire.aplicarTarifaMusicos(t)"
                                    placeholder="350"
                                    class="w-full h-8 pl-6 pr-2 bg-surface-sunken border border-outline rounded-lg text-ink-primary font-bold text-[14px] text-right tabular-nums focus:border-gold-500 focus:outline-none">
                            </div>
                            <button type="button" @click="$wire.aplicarTarifaMusicos(t)"
                                :disabled="t === ''"
                                class="h-8 px-3 rounded-lg border border-gold-500 bg-gold-500/10 text-ink-primary text-xs font-semibold hover:bg-gold-500/20 disabled:opacity-40 transition-colors">
                                Aplicar a músicos
                            </button>
                            <span class="text-xs text-ink-muted hidden sm:inline">No cambia transporte, sonido ni
                                mensuales</span>
                        </div>
                    @endif

                    <!-- Equipo del evento (2 columnas en pantalla ancha) -->
                    <div
                        class="border border-outline rounded-xl bg-surface-card overflow-hidden grid grid-cols-1 sm:grid-cols-2">
                        @foreach ($participantesSugeridos as $idx => $pe)
                            @if (empty($pe['incluido']))
                                @continue
                            @endif
                            @php
                                [$icoPe, $clsPe] = array_slice($estiloTipo($pe['funcion'] ?? ''), 0, 2);
                                $esMensual = ($pe['modalidad'] ?? 'por_evento') === 'mensual';
                                $sinTarifa = !$esMensual && (float) ($pe['monto_asignado'] ?? 0) <= 0;
                            @endphp
                            <div class="flex items-center gap-2 pl-2.5 pr-1 py-1.5 border-b border-outline sm:[&:nth-child(odd)]:border-r"
                                wire:key="equipo-{{ $pe['persona_id'] }}">
                                <span
                                    class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 {{ $clsPe }}">
                                    <i class="fa-solid {{ $icoPe }} text-[11px]"></i>
                                </span>
                                <div class="min-w-0 flex-1 flex items-baseline gap-x-1.5 overflow-hidden">
                                    <span
                                        class="text-[14.5px] font-semibold text-ink-primary truncate">{{ $pe['nombre_completo'] }}</span>
                                    <span class="text-[13px] text-ink-muted whitespace-nowrap shrink-0">-
                                        {{ $pe['funcion'] }}</span>
                                    @if ($esMensual)
                                        <span
                                            class="text-[12px] text-sky-600 dark:text-sky-400 whitespace-nowrap shrink-0 hidden sm:inline">Mensual</span>
                                    @endif
                                    @if ($sinTarifa)
                                        <i class="fa-solid fa-triangle-exclamation text-[11px] text-amber-500 shrink-0"
                                            title="Sin tarifa: ingrese el monto"></i>
                                    @endif
                                    @if (!empty($pe['tiene_pagos']))
                                        <i class="fa-solid fa-lock text-[11px] text-emerald-600 dark:text-emerald-400 shrink-0"
                                            title="Ya tiene pagos por Bs {{ number_format($pe['total_pagado'], 2) }}"></i>
                                    @endif
                                </div>
                                <div class="relative w-20 shrink-0">
                                    <span
                                        class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] font-bold text-ink-muted">Bs</span>
                                    <input type="number" step="0.50" min="0" inputmode="decimal"
                                        wire:model.live.debounce.400ms="participantesSugeridos.{{ $idx }}.monto_asignado"
                                        class="w-full h-8 pl-6 pr-2 bg-surface-sunken border border-outline rounded-lg text-ink-primary font-bold text-[14px] text-right tabular-nums focus:border-gold-500 focus:outline-none">
                                </div>
                                <button type="button" wire:click="quitarParticipante({{ $idx }})"
                                    class="w-7 h-8 shrink-0 flex items-center justify-center text-ink-muted hover:text-rose-600 transition-colors"
                                    title="{{ !empty($pe['tiene_pagos']) ? 'Tiene pagos registrados' : 'Quitar del evento' }}">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        @endforeach

                        @if ($incluidosEquipo->count() === 0)
                            <div class="sm:col-span-2 px-3 py-2.5 text-center text-[13px] text-ink-muted">
                                Toca una persona para agregarla al evento.
                            </div>
                        @else
                            <div
                                class="sm:col-span-2 flex flex-wrap items-center justify-between gap-x-3 gap-y-0.5 px-3 py-2 bg-surface-sunken text-sm text-ink-secondary">
                                <span>
                                    Pagarás <strong class="text-ink-primary tabular-nums">Bs
                                        {{ number_format($totalAsignadoEquipo, 0) }}</strong>
                                    @if ($precioReferencia > 0)
                                        de <span class="tabular-nums">{{ number_format($precioReferencia, 0) }}</span>
                                    @endif
                                </span>
                                @if ($precioReferencia > 0)
                                    <span
                                        class="font-bold tabular-nums {{ $restoEvento < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        {{ $restoEvento < 0 ? 'Pasa por' : 'Quedan' }} Bs
                                        {{ number_format(abs($restoEvento), 0) }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- SECCIÓN: Contrato y adelanto (solo al crear un evento nuevo) -->
                @if (!$isEdit)
                    @php
                        $montoCt = is_numeric($monto_contrato) ? (float) $monto_contrato : 0;
                        $adelantoCt = is_numeric($adelanto) ? (float) $adelanto : 0;
                        $saldoCt = $montoCt - $adelantoCt;
                    @endphp
                    <div class="min-w-0 border-t border-outline pt-3 space-y-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-semibold text-ink-primary">Contrato:</span>
                            <select wire:model.live="contrato_opcion"
                                class="h-9 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors min-w-[14rem]">
                                <option value="si">Sí, generar contrato</option>
                                <option value="no">No, solo reservar</option>
                            </select>
                            @if ($contrato_opcion === 'no')
                                <span class="text-xs text-ink-secondary">Solo se crea el evento. El contrato y los
                                    pagos se hacen después, desde Contratos.</span>
                            @endif
                        </div>

                        @if ($contrato_opcion === 'si')
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div class="min-w-0">
                                    <x-ui.input label="Adelanto (Bs)" type="number" step="0.01"
                                        inputmode="decimal" name="adelanto" wire:model.live.debounce.300ms="adelanto"
                                        placeholder="0.00" />
                                </div>
                                <div class="min-w-0">
                                    <x-ui.input label="Monto total (Bs)" required type="number" step="0.01"
                                        inputmode="decimal" name="monto_contrato"
                                        wire:model.live.debounce.300ms="monto_contrato" placeholder="0.00" />
                                </div>
                                <div class="min-w-0 col-span-2 sm:col-span-1">
                                    <label class="block text-sm font-semibold text-ink-primary mb-1">Método:</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach (['Efectivo' => 'fa-money-bill-wave', 'QR' => 'fa-qrcode'] as $m => $ic)
                                            <button type="button"
                                                wire:click="$set('metodo_adelanto', '{{ $m }}')"
                                                class="h-9 rounded-lg border text-sm font-semibold flex items-center justify-center gap-1.5 transition-colors
                                                        {{ $metodo_adelanto === $m ? 'border-gold-500 bg-gold-500/10 text-ink-primary' : 'border-outline bg-surface-card text-ink-secondary' }}
                                                        {{ $adelantoCt <= 0 ? 'opacity-50' : '' }}">
                                                <i class="fa-solid {{ $ic }} text-xs"></i>
                                                {{ $m }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @error('adelanto')
                                <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                            @enderror

                            <div
                                class="flex flex-wrap items-center justify-between gap-x-3 gap-y-0.5 px-3 py-2 rounded-lg bg-surface-sunken text-sm text-ink-secondary">
                                <span>
                                    @if ($adelantoCt > 0)
                                        Adelanto <strong class="text-ink-primary tabular-nums">Bs
                                            {{ number_format($adelantoCt, 2) }}</strong>
                                        ({{ $metodo_adelanto }}) - el evento queda <strong
                                            class="text-ink-primary">Confirmado</strong>
                                    @else
                                        Sin adelanto - el evento queda <strong
                                            class="text-ink-primary">Reservado</strong>
                                    @endif
                                </span>
                                <span
                                    class="font-bold tabular-nums {{ $saldoCt < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-ink-primary' }}">
                                    Saldo del cliente Bs {{ number_format(max(0, $saldoCt), 2) }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Observaciones (al final) -->
                <div class="min-w-0">
                    <x-ui.textarea label="Observaciones" wire:model="observaciones" rows="1"
                        placeholder="Vestimenta, canciones, indicaciones..." />
                </div>

                <!-- Banner Inferior de Advertencia Visible cuando se está abajo -->
                @if ($errors->any() || session()->has('participante_error') || session()->has('modal_error'))
                    <div
                        class="px-4 py-3 rounded-lg bg-rose-500/10 border border-rose-500/30 flex items-center justify-between text-xs text-rose-700 dark:text-rose-400">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-sm shrink-0"></i>
                            <span class="font-medium">
                                @if (session()->has('participante_error'))
                                    {{ session('participante_error') }}
                                @elseif(session()->has('modal_error'))
                                    {{ session('modal_error') }}
                                @else
                                    Existen datos obligatorios incompletos o inválidos en el formulario.
                                @endif
                            </span>
                        </div>
                        <button type="button"
                            @click="document.getElementById('modalEventoBody')?.scrollTo({top: 0, behavior: 'smooth'})"
                            class="text-xs hover:underline font-semibold shrink-0 ml-3 flex items-center gap-1">
                            <span>Ver campos faltantes</span>
                            <i class="fa-solid fa-arrow-up"></i>
                        </button>
                    </div>
                @endif

            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button"
                    wire:click="$set('modalOpen', false)">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" type="submit" wire:loading.attr="disabled"
                    wire:target="guardar">
                    <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardar"></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardar"></i>
                    <span>{{ $isEdit ? 'Actualizar' : 'Guardar' }}</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </form>
@endif