<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestionar Eventos</h1>
            <p class="text-xs text-slate-400">Programación de presentaciones musicales, alquileres y servicios técnicos.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-calendar-plus"></i>
            <span>Nuevo Evento</span>
        </button>
    </div>

    <!-- Mensajes de Notificación -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por código, cliente, dirección..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
            </div>

            <div>
                <select wire:model.live="cliente_filtro" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Clientes</option>
                    @foreach($clientes as $cli)
                        <option value="{{ $cli->id }}">{{ $cli->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="estado_filtro" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Cotizado">Cotizado</option>
                    <option value="Reservado">Reservado</option>
                    <option value="Confirmado">Confirmado</option>
                    <option value="Realizado">Realizado</option>
                    <option value="Cancelado">Cancelado</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-brand-card rounded-2xl border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-brand-border text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3.5 px-4 font-bold">Código / Evento</th>
                        <th class="py-3.5 px-4 font-bold">Cliente / Contacto</th>
                        <th class="py-3.5 px-4 font-bold">Fecha & Hora</th>
                        <th class="py-3.5 px-4 font-bold">Servicio</th>
                        <th class="py-3.5 px-4 font-bold">Ubicación</th>
                        <th class="py-3.5 px-4 font-bold text-center">Estado</th>
                        <th class="py-3.5 px-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/50 text-slate-300">
                    @forelse($eventos as $ev)
                        <tr class="hover:bg-slate-800/30 transition-all">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-extrabold text-gold-400">{{ $ev->codigo_evento }}</span>
                                <div class="text-xs text-slate-400">{{ $ev->duracion_horas }}h de duración</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</div>
                                @if($ev->contacto_evento && $ev->contacto_evento !== ($ev->cliente->nombre_completo ?? ''))
                                    <div class="text-xs text-slate-400">Contacto: {{ $ev->contacto_evento }}</div>
                                @endif
                                @if($ev->telefono_contacto)
                                    <div class="text-xs text-emerald-400 font-mono">{{ $ev->telefono_contacto }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <div class="font-bold text-white">{{ $ev->fecha_evento->format('d/m/Y') }}</div>
                                <div class="text-xs text-gold-400">{{ $ev->hora_evento }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-200 border border-slate-700">
                                    {{ $ev->servicio->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-xs text-slate-300 max-w-xs truncate" title="{{ $ev->direccion_evento }}">
                                    <i class="fa-solid fa-location-dot text-rose-400"></i>
                                    <span>{{ $ev->direccion_evento }}</span>
                                </div>
                                @if($ev->latitud && $ev->longitud)
                                    <button wire:click="verMapa({{ $ev->id }})" class="inline-flex items-center gap-1 text-[11px] font-bold text-gold-400 hover:underline mt-1">
                                        <i class="fa-solid fa-map-location-dot"></i>
                                        <span>Ver Ubicación (Mapa)</span>
                                    </button>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold 
                                    @if($ev->estado === 'Confirmado' || $ev->estado === 'Realizado' || $ev->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($ev->estado === 'Reservado' || $ev->estado === 'Cotizado') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($ev->estado === 'Cancelado') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                    {{ $ev->estado }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('admin.eventos.participantes', $ev->id) }}" class="p-2 rounded-lg bg-slate-800 text-gold-400 hover:text-gold-300 hover:bg-slate-700 transition-all inline-block" title="Gestionar Participantes">
                                    <i class="fa-solid fa-people-group"></i>
                                </a>
                                <button wire:click="editar({{ $ev->id }})" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all" title="Editar Evento">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500 text-sm">
                                No se encontraron eventos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $eventos->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar Evento -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-3xl overflow-hidden shadow-2xl space-y-6 p-6">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-gold-400"></i>
                        <span>{{ $isEdit ? 'Editar Evento ' . $codigo_evento : 'Nuevo Evento' }}</span>
                    </h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Código Evento *</label>
                        <input type="text" wire:model="codigo_evento" readonly class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-gold-400 font-mono font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Cliente *</label>
                        <div class="relative" x-data="{
                            open: false,
                            search: '',
                            selectedId: @entangle('cliente_id').live,
                            selectedName: '',
                            clients: @js($clientes->map(fn($c) => ['id' => $c->id, 'nombre_completo' => $c->nombre_completo, 'ci_nit' => $c->ci_nit, 'telefono' => $c->telefono])),
                            init() {
                                this.updateSelectedName();
                                this.$watch('selectedId', () => this.updateSelectedName());
                            },
                            updateSelectedName() {
                                const item = this.clients.find(c => c.id == this.selectedId);
                                this.selectedName = item ? item.nombre_completo : '';
                            },
                            get filteredClients() {
                                if (!this.search) return this.clients;
                                const s = this.search.toLowerCase();
                                return this.clients.filter(c => 
                                    c.nombre_completo.toLowerCase().includes(s) || 
                                    (c.ci_nit && c.ci_nit.toLowerCase().includes(s)) ||
                                    (c.telefono && c.telefono.includes(s))
                                );
                            },
                            select(id) {
                                this.selectedId = id;
                                this.updateSelectedName();
                                this.open = false;
                                this.search = '';
                                $wire.seleccionarCliente(id);
                            },
                            clear() {
                                this.selectedId = '';
                                this.selectedName = '';
                                this.search = '';
                                $wire.set('cliente_id', '');
                            }
                        }" @click.outside="open = false">
                            <template x-if="!selectedId || open">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" 
                                           x-model="search" 
                                           @focus="open = true" 
                                           placeholder="🔍 Buscar cliente en la lista..." 
                                           class="w-full pl-9 pr-8 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 focus:outline-none">
                                </div>
                            </template>

                            <template x-if="selectedId && !open">
                                <button type="button" 
                                        @click="open = true; search = ''" 
                                        class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm text-left flex items-center justify-between hover:border-gold-500/60 transition-all">
                                    <span class="font-bold text-gold-400 truncate" x-text="selectedName"></span>
                                    <div class="flex items-center gap-2">
                                        <span @click.stop="clear()" class="text-slate-400 hover:text-rose-400 p-1 text-xs" title="Cambiar Cliente">
                                            <i class="fa-solid fa-xmark"></i>
                                        </span>
                                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                                    </div>
                                </button>
                            </template>

                            <!-- Dropdown Lista de Clientes -->
                            <div x-show="open" 
                                 x-cloak 
                                 x-transition 
                                 class="absolute left-0 right-0 top-full mt-1 z-50 max-h-56 overflow-y-auto bg-slate-900 border border-gold-500/40 rounded-xl shadow-2xl divide-y divide-slate-800">
                                <template x-for="c in filteredClients" :key="c.id">
                                    <div @click="select(c.id)" 
                                         class="p-2.5 hover:bg-slate-800 cursor-pointer transition-colors flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-bold text-white" x-text="c.nombre_completo"></div>
                                            <div class="text-xs text-slate-400">
                                                <span x-text="c.ci_nit ? 'CI: ' + c.ci_nit : ''"></span>
                                                <span x-text="c.telefono ? ' | Tel: ' + c.telefono : ''"></span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-check text-gold-400 text-xs" x-show="c.id == selectedId"></i>
                                    </div>
                                </template>
                                <template x-if="filteredClients.length === 0">
                                    <div class="p-3 text-xs text-slate-500 text-center">
                                        No se encontraron clientes en la lista.
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('cliente_id') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Servicio Contratado *</label>
                        <select wire:model="servicio_id" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione Servicio</option>
                            @foreach($servicios as $srv)
                                <option value="{{ $srv->id }}">{{ $srv->nombre }} (Bs {{ number_format($srv->precio_base, 2) }})</option>
                            @endforeach
                        </select>
                        @error('servicio_id') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Contacto del Evento</label>
                        <input type="text" wire:model="contacto_evento" placeholder="Nombre persona a recibir" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Teléfono de Contacto</label>
                        <input type="text" wire:model="telefono_contacto" placeholder="ej. 70000000" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Estado del Evento</label>
                        <select wire:model="estado" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="Pendiente">Pendiente</option>
                            <option value="Cotizado">Cotizado</option>
                            <option value="Reservado">Reservado</option>
                            <option value="Confirmado">Confirmado</option>
                            <option value="Realizado">Realizado</option>
                            <option value="Cancelado">Cancelado</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>

                    <!-- Calendario Interactivo Desplegable para Fecha de Evento -->
                    <div class="relative" x-data="{
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
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Fecha del Evento *</label>

                        <div class="relative">
                            <button type="button" 
                                    @click="open = !open" 
                                    class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm text-left flex items-center justify-between hover:border-gold-500/60 transition-all cursor-pointer">
                                <span class="font-mono font-bold" :class="selectedDate ? 'text-gold-400' : 'text-slate-400'" x-text="formattedDisplay"></span>
                                <i class="fa-solid fa-calendar-days text-gold-400 text-sm"></i>
                            </button>

                            <!-- Desplegable de Calendario Visual -->
                            <div x-show="open" 
                                 x-cloak 
                                 x-transition 
                                 class="absolute left-0 top-full mt-1 z-50 w-72 p-3 bg-slate-900 border border-gold-500/50 rounded-2xl shadow-2xl space-y-3">
                                
                                <!-- Navegación de Mes y Año -->
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <button type="button" @click="prevMonth()" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                    </button>
                                    <div class="text-xs font-bold text-white flex items-center gap-1">
                                        <span x-text="months[currentMonth]"></span>
                                        <span class="text-gold-400" x-text="currentYear"></span>
                                    </div>
                                    <button type="button" @click="nextMonth()" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </button>
                                </div>

                                <!-- Encabezado Días de la Semana -->
                                <div class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 uppercase">
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
                                        <button type="button" 
                                                @click="selectDay(d)" 
                                                :class="{
                                                    'bg-gold-500 text-slate-950 font-extrabold shadow-md': isSelected(d),
                                                    'border border-gold-500/40 text-gold-300 font-bold': isToday(d) && !isSelected(d),
                                                    'text-slate-200 hover:bg-slate-800 hover:text-white': !isSelected(d) && !isToday(d)
                                                }" 
                                                class="h-7 w-7 mx-auto rounded-lg flex items-center justify-center transition-all">
                                            <span x-text="d"></span>
                                        </button>
                                    </template>
                                </div>

                                <!-- Acciones Rápidas -->
                                <div class="flex items-center justify-between border-t border-slate-800 pt-2 text-[11px]">
                                    <button type="button" @click="selectToday()" class="text-gold-400 hover:underline font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-calendar-day"></i> Hoy
                                    </button>
                                    <button type="button" @click="open = false" class="text-slate-400 hover:text-white">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                        @error('fecha_evento') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Hora del Evento *</label>
                        <input type="time" wire:model="hora_evento" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                        @error('hora_evento') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Duración (Horas) *</label>
                        <input type="number" step="0.5" wire:model="duracion_horas" placeholder="1.0" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                        @error('duracion_horas') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="lg:col-span-3 sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Dirección del Evento (Lugar Exacto) *</label>
                        <input type="text" wire:model="direccion_evento" placeholder="ej. Salón de Eventos Las Palmas, Av. Banzer 4to Anillo" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('direccion_evento') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Selección de Ubicación en Mapa Interactivo (Google Maps incorporado) -->
                    <div class="lg:col-span-3 sm:col-span-2 bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-3"
                        x-data="{
                            map: null,
                            marker: null,
                            queryBusqueda: '',
                            buscando: false,
                            lat: @entangle('latitud'),
                            lng: @entangle('longitud'),
                            initPicker() {
                                this.$nextTick(() => {
                                    const container = this.$refs.mapContainer;
                                    if (!container) return;

                                    let pollCount = 0;
                                    let checkExist = setInterval(() => {
                                        pollCount++;
                                        if (container.offsetHeight > 0 || pollCount > 30) {
                                            clearInterval(checkExist);

                                            let initialLat = this.lat ? parseFloat(this.lat) : -17.7833;
                                            let initialLng = this.lng ? parseFloat(this.lng) : -63.1821;

                                            if (this.map) {
                                                this.map.remove();
                                                this.map = null;
                                                this.marker = null;
                                            }

                                            // Inicializar Leaflet con Tiles de Google Maps (Roadmap)
                                            this.map = L.map(container).setView([initialLat, initialLng], 15);

                                            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                                                maxZoom: 20,
                                                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                                                attribution: 'Google Maps'
                                            }).addTo(this.map);

                                            // Marcador rojo arrastrable
                                            this.marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(this.map);
                                            this.bindMarkerEvents();

                                            this.map.on('click', (e) => {
                                                this.updateCoords(e.latlng.lat, e.latlng.lng);
                                            });

                                            setTimeout(() => {
                                                if (this.map) this.map.invalidateSize();
                                            }, 200);
                                        }
                                    }, 100);
                                });
                            },
                            updateCoords(latVal, lngVal) {
                                let formattedLat = parseFloat(latVal).toFixed(6);
                                let formattedLng = parseFloat(lngVal).toFixed(6);

                                this.lat = formattedLat;
                                this.lng = formattedLng;
                                $wire.set('latitud', formattedLat);
                                $wire.set('longitud', formattedLng);

                                if (this.marker) {
                                    this.marker.setLatLng([latVal, lngVal]);
                                } else {
                                    this.marker = L.marker([latVal, lngVal], { draggable: true }).addTo(this.map);
                                    this.bindMarkerEvents();
                                }

                                // Geocodificación inversa para sugerir dirección
                                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latVal}&lon=${lngVal}`)
                                    .then(r => r.json())
                                    .then(d => {
                                        if (d && d.display_name && !$wire.get('direccion_evento')) {
                                            $wire.set('direccion_evento', d.display_name);
                                        }
                                    }).catch(() => {});
                            },
                            bindMarkerEvents() {
                                if (this.marker) {
                                    this.marker.on('dragend', (e) => {
                                        let pos = e.target.getLatLng();
                                        this.updateCoords(pos.lat, pos.lng);
                                    });
                                }
                            },
                            buscarEnMapa() {
                                let q = this.queryBusqueda || $wire.get('direccion_evento');
                                if (!q || q.length < 2) return;
                                this.buscando = true;
                                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q))
                                    .then(r => r.json())
                                    .then(data => {
                                        this.buscando = false;
                                        if (data && data.length > 0) {
                                            let resLat = parseFloat(data[0].lat);
                                            let resLng = parseFloat(data[0].lon);
                                            if (this.map) {
                                                this.map.setView([resLat, resLng], 16);
                                                this.updateCoords(resLat, resLng);
                                            }
                                        } else {
                                            alert('No se encontraron resultados en el mapa para: ' + q);
                                        }
                                    })
                                    .catch(() => { this.buscando = false; });
                            }
                        }"
                        x-init="initPicker()">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-gold-400"></i>
                                <span class="text-xs font-bold text-white uppercase tracking-wider">Seleccionar Ubicación Exacta en Mapa (Google Maps)</span>
                            </div>
                            <div class="text-[11px] text-slate-400">
                                Haz clic o arrastra el marcador rojo en el mapa
                            </div>
                        </div>

                        <!-- Buscador en Mapa -->
                        <div class="flex items-center gap-2">
                            <input type="text" 
                                   x-model="queryBusqueda" 
                                   @keydown.enter.prevent="buscarEnMapa()"
                                   placeholder="Escribe un lugar (ej. La Ramada, Banzer 4to anillo) y presiona Buscar..." 
                                   class="flex-1 px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                            <button type="button" 
                                    @click="buscarEnMapa()" 
                                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-gold-400 text-xs font-bold transition-all flex items-center gap-1">
                                <i class="fa-solid fa-magnifying-glass" x-show="!buscando"></i>
                                <i class="fa-solid fa-spinner animate-spin" x-show="buscando" x-cloak></i>
                                <span>Buscar</span>
                            </button>
                        </div>

                        <!-- Map Canvas (Google Maps Layer) -->
                        <div class="relative w-full h-72 rounded-xl overflow-hidden border border-slate-800 z-10 bg-slate-900">
                            <div x-ref="mapContainer" class="w-full h-full min-h-[280px]"></div>
                        </div>

                        <!-- Footer Coordenadas -->
                        <div class="flex items-center justify-between gap-2 pt-1 text-xs font-mono">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Coordenadas Seleccionadas:</span>
                                <span class="px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-gold-400 font-bold" x-text="lat && lng ? lat + ', ' + lng : 'Haz clic en el mapa para ubicar'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-3 sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Observaciones</label>
                        <textarea wire:model="observaciones" rows="2" placeholder="Indicaciones especiales para la llegada del mariachi, vestimenta, canciones requeridas..." class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-brand-border pt-4">
                    <button wire:click="$set('modalOpen', false)" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500">
                        {{ $isEdit ? 'Actualizar Evento' : 'Guardar Evento' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Ubicación en Mapa -->
    @if($modalMapOpen && $eventoMapa)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl space-y-6 p-6">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-gold-400"></i>
                        <span>Ubicación Exacta: {{ $eventoMapa->codigo_evento }}</span>
                    </h3>
                    <button wire:click="$set('modalMapOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-1">
                        <div class="text-xs font-bold text-gold-400 uppercase tracking-wider">Dirección Registrada:</div>
                        <div class="text-sm font-semibold text-white">{{ $eventoMapa->direccion_evento }}</div>
                        <div class="text-xs text-slate-400">Cliente: <span class="text-slate-200 font-bold">{{ $eventoMapa->cliente->nombre_completo ?? 'N/A' }}</span> &bull; Fecha: <span class="text-slate-200 font-bold">{{ $eventoMapa->fecha_evento->format('d/m/Y') }} {{ $eventoMapa->hora_evento }}</span></div>
                    </div>

                    @if($eventoMapa->latitud && $eventoMapa->longitud)
                        <div class="w-full h-64 rounded-2xl overflow-hidden border border-slate-800 relative bg-slate-950 flex flex-col items-center justify-center p-4 text-center">
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                scrolling="no" 
                                marginheight="0" 
                                marginwidth="0" 
                                src="https://maps.google.com/maps?q={{ $eventoMapa->latitud }},{{ $eventoMapa->longitud }}&hl=es&z=15&output=embed">
                            </iframe>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-xs font-mono text-slate-400">
                                Coordenadas: <span class="text-gold-400">{{ $eventoMapa->latitud }}, {{ $eventoMapa->longitud }}</span>
                            </div>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $eventoMapa->latitud }},{{ $eventoMapa->longitud }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-2">
                                <i class="fa-solid fa-route"></i>
                                <span>Abrir Navegación en Google Maps</span>
                            </a>
                        </div>
                    @else
                        <div class="p-8 text-center text-slate-500 text-sm">
                            Este evento no cuenta con coordenadas de latitud y longitud registradas.
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end border-t border-brand-border pt-4">
                    <button wire:click="$set('modalMapOpen', false)" class="px-5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
