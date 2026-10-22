<div class="space-y-6">
    <!-- Header Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-brand-card via-slate-900 to-slate-950 border border-brand-border flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 border border-gold-500/30 text-gold-400 flex items-center justify-center text-2xl flex-shrink-0 shadow-lg shadow-gold-500/10">
                <i class="fa-solid fa-inbox"></i>
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gold-500/10 text-gold-400 border border-gold-500/20 mb-1">
                    <i class="fa-solid fa-globe"></i> Portal Público Web
                </div>
                <h1 class="text-xl md:text-2xl font-extrabold text-white">Solicitudes de Cotización & Contactos Web</h1>
                <p class="text-xs text-slate-400 mt-0.5">Control, atención y seguimiento de prospectos generados desde el sitio público.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('web.contacto') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-right-from-square text-gold-400 text-xs"></i>
                <span>Ver Formulario Web</span>
            </a>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if(session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 flex items-center justify-between gap-3 text-xs font-medium animate-fade-in shadow-lg">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif
    @if(session()->has('info'))
        <div class="p-4 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-300 flex items-center justify-between gap-3 text-xs font-medium animate-fade-in shadow-lg">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-info text-cyan-400 text-base"></i>
                <span>{{ session('info') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-cyan-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    <!-- KPI Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Total -->
        <div class="p-4 rounded-2xl bg-brand-card border border-brand-border flex items-center gap-3 hover:border-gold-500/40 transition-all cursor-pointer" wire:click="$set('filtro_estado', '')">
            <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Recibidas</p>
                <p class="text-xl font-black text-white mt-0.5">{{ $totalTodas }}</p>
            </div>
        </div>

        <!-- Nuevas -->
        <div class="p-4 rounded-2xl bg-brand-card border {{ $totalNuevas > 0 ? 'border-amber-500/60 bg-amber-500/5' : 'border-brand-border' }} flex items-center gap-3 hover:border-amber-500 transition-all cursor-pointer" wire:click="$set('filtro_estado', 'nuevo')">
            <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-lg flex-shrink-0 relative">
                <i class="fa-solid fa-bell"></i>
                @if($totalNuevas > 0)
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-amber-400 rounded-full animate-ping"></span>
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">Nuevas / Pendientes</p>
                <p class="text-xl font-black text-white mt-0.5">{{ $totalNuevas }}</p>
            </div>
        </div>

        <!-- Atendidas -->
        <div class="p-4 rounded-2xl bg-brand-card border border-brand-border flex items-center gap-3 hover:border-emerald-500/40 transition-all cursor-pointer" wire:click="$set('filtro_estado', 'atendido')">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">En Atención</p>
                <p class="text-xl font-black text-white mt-0.5">{{ $totalAtendidas }}</p>
            </div>
        </div>

        <!-- Cotizadas -->
        <div class="p-4 rounded-2xl bg-brand-card border border-brand-border flex items-center gap-3 hover:border-purple-500/40 transition-all cursor-pointer" wire:click="$set('filtro_estado', 'cotizado')">
            <div class="w-10 h-10 rounded-xl bg-purple-500/15 text-purple-400 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-purple-400 uppercase tracking-wider">Cotizadas</p>
                <p class="text-xl font-black text-white mt-0.5">{{ $totalCotizadas }}</p>
            </div>
        </div>

        <!-- Descartadas -->
        <div class="p-4 rounded-2xl bg-brand-card border border-brand-border flex items-center gap-3 hover:border-slate-600 transition-all cursor-pointer" wire:click="$set('filtro_estado', 'descartado')">
            <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Descartadas</p>
                <p class="text-xl font-black text-white mt-0.5">{{ $totalDescartadas }}</p>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="p-5 rounded-2xl bg-brand-card border border-brand-border space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Buscar -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">Buscar por Cliente, Teléfono o Mensaje</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500"><i class="fa-solid fa-magnifying-glass text-xs"></i></span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Escribe para buscar..." class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                </div>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">Estado</label>
                <select wire:model.live="filtro_estado" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="">Todos los Estados</option>
                    <option value="nuevo">🟡 Nuevo / Pendiente</option>
                    <option value="atendido">🟢 Atendido / Contactado</option>
                    <option value="cotizado">🟣 Cotizado / Registrado</option>
                    <option value="descartado">⚪ Descartado</option>
                </select>
            </div>

            <!-- Servicio -->
            <div>
                <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">Servicio Específico</label>
                <select wire:model.live="filtro_servicio" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="">Todos los Servicios</option>
                    @foreach($servicios as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Fechas o Limpiar -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">Desde</label>
                    <input type="date" wire:model.live="fecha_desde" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                </div>
                <button type="button" wire:click="limpiarFiltros" title="Limpiar todos los filtros" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition-all flex items-center justify-center">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla Principal de Solicitudes -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden shadow-xl">
        <div class="p-4 border-b border-brand-border flex items-center justify-between gap-4">
            <h3 class="font-bold text-sm text-white flex items-center gap-2">
                <i class="fa-solid fa-list-ul text-gold-400"></i>
                <span>Listado de Solicitudes Web ({{ $solicitudes->total() }})</span>
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Mostrar</span>
                <select wire:model.live="perPage" class="px-2 py-1 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:border-gold-500">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-brand-border/80">
                    <tr>
                        <th class="py-3.5 px-4">Fecha & Estado</th>
                        <th class="py-3.5 px-4">Cliente & Contacto</th>
                        <th class="py-3.5 px-4">Servicio / Celebración</th>
                        <th class="py-3.5 px-4">Mensaje / Detalle</th>
                        <th class="py-3.5 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($solicitudes as $sol)
                        @php
                            $telLimpio = preg_replace('/[^0-9]/', '', $sol->telefono);
                            if (strlen($telLimpio) === 8) {
                                $telLimpio = '591' . $telLimpio;
                            }
                            $waMsg = urlencode("Hola {$sol->nombre}, un cordial saludo de Mariachi León Guanajuato. Recibimos tu solicitud de cotización enviada desde nuestra página web y con gusto te atendemos.");
                            $waUrl = "https://api.whatsapp.com/send?phone={$telLimpio}&text={$waMsg}";
                        @endphp
                        <tr class="hover:bg-slate-900/50 transition-colors {{ $sol->estado === 'nuevo' ? 'bg-amber-500/5' : '' }}">
                            <!-- Fecha & Estado -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="font-bold text-white text-xs flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-slate-400"></i>
                                    <span>{{ $sol->created_at->format('d/m/Y') }}</span>
                                    <span class="text-slate-400 font-normal">{{ $sol->created_at->format('H:i') }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $sol->created_at->diffForHumans() }}</span>

                                <div class="mt-2">
                                    @if($sol->estado === 'nuevo')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            NUEVO
                                        </span>
                                    @elseif($sol->estado === 'atendido')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                            <i class="fa-solid fa-check text-[9px]"></i>
                                            ATENDIDO
                                        </span>
                                    @elseif($sol->estado === 'cotizado')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/40">
                                            <i class="fa-solid fa-file-invoice-dollar text-[9px]"></i>
                                            COTIZADO
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                            DESCARTADO
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Cliente & Contacto -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-white text-sm flex items-center gap-1.5">
                                    <i class="fa-solid fa-user text-gold-400 text-xs"></i>
                                    <span>{{ $sol->nombre }}</span>
                                </div>

                                <div class="space-y-1 mt-1.5">
                                    @if($sol->telefono)
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all text-[11px] font-bold" title="Abrir chat en WhatsApp">
                                                <i class="fa-brands fa-whatsapp"></i>
                                                <span>{{ $sol->telefono }}</span>
                                            </a>
                                        </div>
                                    @endif

                                    @if($sol->email)
                                        <a href="mailto:{{ $sol->email }}" class="text-[11px] text-slate-400 hover:text-gold-400 flex items-center gap-1 transition-colors">
                                            <i class="fa-regular fa-envelope text-[10px]"></i>
                                            <span class="truncate max-w-[180px]">{{ $sol->email }}</span>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- Servicio / Celebración -->
                            <td class="py-4 px-4">
                                @if($sol->servicio)
                                    <div class="font-bold text-gold-400 text-xs flex items-center gap-1.5">
                                        <i class="fa-solid fa-guitar text-[11px]"></i>
                                        <span>{{ $sol->servicio->nombre }}</span>
                                    </div>
                                    <span class="text-[11px] text-slate-300 font-semibold block mt-0.5">
                                        {{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($sol->servicio->precio_base, 2) }}
                                        <span class="text-slate-400 font-normal">({{ $sol->servicio->duracion_minutos }} min)</span>
                                    </span>
                                @elseif($sol->paquete)
                                    <div class="font-bold text-amber-400 text-xs flex items-center gap-1.5">
                                        <i class="fa-solid fa-box-open text-[11px]"></i>
                                        <span>{{ $sol->paquete->nombre }}</span>
                                    </div>
                                    <span class="text-[11px] text-slate-300 font-semibold block mt-0.5">
                                        {{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($sol->paquete->precio_paquete, 2) }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Consulta General</span>
                                @endif

                                <div class="mt-1 space-y-0.5 text-[11px] text-slate-400">
                                    @if($sol->tipoEvento)
                                        <p><i class="fa-solid fa-champagne-glasses text-[10px] mr-1 text-slate-400"></i>{{ $sol->tipoEvento->nombre }}</p>
                                    @endif
                                    @if($sol->fecha_estimada)
                                        <p class="text-gold-400 font-medium">
                                            <i class="fa-regular fa-calendar-check text-[10px] mr-1"></i>Para: {{ $sol->fecha_estimada->format('d/m/Y') }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <!-- Mensaje / Detalle -->
                            <td class="py-4 px-4 max-w-xs">
                                <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed">{{ $sol->mensaje }}</p>
                                <button type="button" wire:click="verDetalle({{ $sol->id }})" class="text-[11px] font-bold text-gold-400 hover:text-gold-300 mt-1 inline-flex items-center gap-1">
                                    <span>Ver mensaje completo</span>
                                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                                </button>
                            </td>

                            <!-- Acciones -->
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Ver Detalle -->
                                    <button type="button" wire:click="verDetalle({{ $sol->id }})" title="Ver detalles y gestionar" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-gold-500 hover:text-slate-950 text-slate-300 transition-all flex items-center justify-center">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    <!-- Abrir WhatsApp -->
                                    @if($sol->telefono)
                                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" title="Contactar por WhatsApp" class="w-8 h-8 rounded-lg bg-emerald-500/15 hover:bg-emerald-500 hover:text-white text-emerald-400 border border-emerald-500/30 transition-all flex items-center justify-center">
                                            <i class="fa-brands fa-whatsapp text-sm"></i>
                                        </a>
                                    @endif

                                    <!-- Convertir en Cliente -->
                                    <button type="button" wire:click="convertirEnCliente({{ $sol->id }})" title="Registrar como Cliente Oficial" class="w-8 h-8 rounded-lg bg-purple-500/15 hover:bg-purple-600 hover:text-white text-purple-400 border border-purple-500/30 transition-all flex items-center justify-center">
                                        <i class="fa-solid fa-user-plus text-xs"></i>
                                    </button>

                                    <!-- Eliminar -->
                                    <button type="button" wire:click="eliminar({{ $sol->id }})" wire:confirm="¿Seguro que deseas eliminar esta solicitud web?" title="Eliminar registro" class="w-8 h-8 rounded-lg bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 border border-rose-500/20 transition-all flex items-center justify-center">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>

                                <!-- Selector Rápido de Estado -->
                                <div class="mt-2">
                                    <select onchange="@this.call('cambiarEstado', {{ $sol->id }}, this.value)" class="px-2 py-1 bg-slate-950 border border-slate-800 rounded-lg text-[10px] text-slate-300 focus:border-gold-500">
                                        <option value="nuevo" {{ $sol->estado === 'nuevo' ? 'selected' : '' }}>🟡 Nuevo</option>
                                        <option value="atendido" {{ $sol->estado === 'atendido' ? 'selected' : '' }}>🟢 Atendido</option>
                                        <option value="cotizado" {{ $sol->estado === 'cotizado' ? 'selected' : '' }}>🟣 Cotizado</option>
                                        <option value="descartado" {{ $sol->estado === 'descartado' ? 'selected' : '' }}>⚪ Descartar</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-xl text-slate-500 mx-auto mb-3">
                                    <i class="fa-solid fa-inbox"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No se encontraron solicitudes web</p>
                                <p class="text-xs text-slate-500 mt-1">Cuando los usuarios envíen cotizaciones desde el sitio público, aparecerán aquí en tiempo real.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($solicitudes->hasPages())
            <div class="p-4 border-t border-brand-border">
                {{ $solicitudes->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Detalle Completo -->
    @if($modalDetalleOpen && $solicitudSeleccionada)
        @php
            $telLimpioModal = preg_replace('/[^0-9]/', '', $solicitudSeleccionada->telefono);
            if (strlen($telLimpioModal) === 8) {
                $telLimpioModal = '591' . $telLimpioModal;
            }
            $waMsgModal = urlencode("Hola {$solicitudSeleccionada->nombre}, un cordial saludo de Mariachi León Guanajuato. Recibimos tu solicitud de cotización para tu evento y con gusto te brindamos toda la información.");
            $waUrlModal = "https://api.whatsapp.com/send?phone={$telLimpioModal}&text={$waMsgModal}";
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-fade-in">
            <div class="bg-slate-900 border border-brand-border rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="p-6 border-b border-brand-border bg-slate-950/60 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gold-500/15 text-gold-400 flex items-center justify-center text-lg flex-shrink-0">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-white">Detalle de la Solicitud Web #{{ $solicitudSeleccionada->id }}</h3>
                            <p class="text-xs text-slate-400">Recibida el {{ $solicitudSeleccionada->created_at->format('d/m/Y \a \l\a\s H:i') }} ({{ $solicitudSeleccionada->created_at->diffForHumans() }})</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModal" class="text-slate-400 hover:text-white text-lg p-1 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto space-y-6 scrollbar-thin">
                    <!-- Ficha del Cliente -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-brand-border/70 space-y-3">
                        <span class="text-[10px] uppercase font-bold text-gold-400 tracking-wider">Datos del Cliente Solicitante</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <span class="text-[11px] text-slate-400 block">Nombre Completo</span>
                                <span class="font-bold text-white text-sm">{{ $solicitudSeleccionada->nombre }}</span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 block">Teléfono / WhatsApp</span>
                                <span class="font-bold text-emerald-400 text-sm flex items-center gap-1.5 mt-0.5">
                                    <i class="fa-brands fa-whatsapp"></i>
                                    <span>{{ $solicitudSeleccionada->telefono }}</span>
                                </span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 block">Correo Electrónico</span>
                                <span class="font-medium text-slate-200 text-xs">{{ $solicitudSeleccionada->email ?: 'No proporcionado' }}</span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 block">Estado Actual</span>
                                <div class="mt-1">
                                    <select wire:change="cambiarEstado({{ $solicitudSeleccionada->id }}, $event.target.value)" class="px-3 py-1 bg-slate-900 border border-slate-700 rounded-lg text-xs font-bold text-white focus:border-gold-500">
                                        <option value="nuevo" {{ $solicitudSeleccionada->estado === 'nuevo' ? 'selected' : '' }}>🟡 Nuevo</option>
                                        <option value="atendido" {{ $solicitudSeleccionada->estado === 'atendido' ? 'selected' : '' }}>🟢 Atendido</option>
                                        <option value="cotizado" {{ $solicitudSeleccionada->estado === 'cotizado' ? 'selected' : '' }}>🟣 Cotizado</option>
                                        <option value="descartado" {{ $solicitudSeleccionada->estado === 'descartado' ? 'selected' : '' }}>⚪ Descartado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Evento / Servicio -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-brand-border/70 space-y-3">
                        <span class="text-[10px] uppercase font-bold text-gold-400 tracking-wider">Detalles de la Cotización Solicitada</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <span class="text-[11px] text-slate-400 block">Servicio Solicitado</span>
                                @if($solicitudSeleccionada->servicio)
                                    <span class="font-bold text-gold-400 text-sm block mt-0.5">{{ $solicitudSeleccionada->servicio->nombre }}</span>
                                    <span class="text-xs text-slate-300 font-semibold">
                                        {{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($solicitudSeleccionada->servicio->precio_base, 2) }}
                                        <span class="text-slate-400 font-normal">({{ $solicitudSeleccionada->servicio->duracion_minutos }} min)</span>
                                    </span>
                                @elseif($solicitudSeleccionada->paquete)
                                    <span class="font-bold text-amber-400 text-sm block mt-0.5">{{ $solicitudSeleccionada->paquete->nombre }}</span>
                                    <span class="text-xs text-slate-300 font-semibold">{{ $empresa->moneda_simbolo ?? 'Bs.' }} {{ number_format($solicitudSeleccionada->paquete->precio_paquete, 2) }}</span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Sin servicio específico</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 block">Tipo de Celebración</span>
                                <span class="font-medium text-white text-xs mt-0.5 block">
                                    {{ $solicitudSeleccionada->tipoEvento->nombre ?? 'No especificado' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-400 block">Fecha Prevista</span>
                                <span class="font-bold text-gold-400 text-xs mt-0.5 block">
                                    {{ $solicitudSeleccionada->fecha_estimada ? $solicitudSeleccionada->fecha_estimada->format('d/m/Y') : 'Por coordinar' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje Completo -->
                    <div class="space-y-2">
                        <span class="text-[11px] uppercase font-bold text-slate-300 tracking-wider block">Mensaje / Requerimientos del Cliente</span>
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-slate-200 text-xs leading-relaxed whitespace-pre-wrap font-mono select-text">
                            {{ $solicitudSeleccionada->mensaje }}
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-5 border-t border-brand-border bg-slate-950/60 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        @if($solicitudSeleccionada->telefono)
                            <a href="{{ $waUrlModal }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2.5 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-500 text-white text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition-all">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                <span>Chatear por WhatsApp</span>
                            </a>
                        @endif

                        <button type="button" wire:click="convertirEnCliente({{ $solicitudSeleccionada->id }})" class="px-4 py-2.5 rounded-xl font-bold bg-purple-600 hover:bg-purple-500 text-white text-xs flex items-center gap-2 shadow-lg shadow-purple-600/20 transition-all">
                            <i class="fa-solid fa-user-plus text-xs"></i>
                            <span>Registrar como Cliente</span>
                        </button>
                    </div>

                    <button type="button" wire:click="cerrarModal" class="px-4 py-2.5 rounded-xl font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
