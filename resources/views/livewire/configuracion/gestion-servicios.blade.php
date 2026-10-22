<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Catálogo de Servicios</h1>
            <p class="text-xs text-slate-400">Servicios musicales (Serenatas, Bodas, Cumpleaños) y complementarios (Alquiler de sonido, Técnico, Transporte) con plantilla económica de participantes.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo Servicio</span>
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
            <div class="relative lg:col-span-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, tipo de servicio o descripción..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
            </div>

            <div>
                <select wire:model.live="tipo_filtro" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Tipos</option>
                    <option value="Musical">Musical (Mariachi / Serenatas)</option>
                    <option value="Alquiler Sonido">Alquiler de Sonido</option>
                    <option value="Técnico">Servicio Técnico</option>
                    <option value="Transporte">Transporte</option>
                    <option value="Otro">Otros Servicios</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-brand-card rounded-2xl border border-brand-border overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-brand-border text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3.5 px-4 font-bold">Servicio / Descripción</th>
                        <th class="py-3.5 px-4 font-bold">Tipo</th>
                        <th class="py-3.5 px-4 font-bold text-right">Precio Base (Bs)</th>
                        <th class="py-3.5 px-4 font-bold text-center">Duración Est.</th>
                        <th class="py-3.5 px-4 font-bold text-center">Plantilla Económica</th>
                        <th class="py-3.5 px-4 font-bold text-center">Estado</th>
                        <th class="py-3.5 px-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/50 text-slate-300">
                    @forelse($servicios as $s)
                        <tr class="hover:bg-slate-800/30 transition-all">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white">{{ $s->nombre }}</div>
                                @if($s->descripcion)
                                    <div class="text-xs text-slate-400 line-clamp-1">{{ $s->descripcion }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800 text-gold-400 border border-slate-700">
                                    {{ $s->tipo_servicio ?: 'Musical' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                                Bs {{ number_format($s->precio_base, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-xs text-slate-300">
                                {{ $s->duracion_minutos }} min
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button wire:click="abrirPlantilla({{ $s->id }})" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold transition-all {{ $s->participantes_activos_count > 0 ? 'bg-gold-500/10 text-gold-400 border border-gold-500/30 hover:bg-gold-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:text-white' }}" title="Configurar plantilla predeterminada de participantes y comisiones">
                                    <i class="fa-solid fa-users-gear text-xs"></i>
                                    <span>{{ $s->participantes_activos_count }} Activos</span>
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button wire:click="cambiarEstado({{ $s->id }})" class="px-3 py-1 rounded-full text-xs font-bold transition-all {{ $s->activo ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20 hover:bg-rose-500/20' }}">
                                    {{ $s->activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5">
                                <button wire:click="abrirPlantilla({{ $s->id }})" class="p-2 rounded-lg bg-gold-500/10 text-gold-400 hover:bg-gold-500/20 transition-all" title="Configurar Plantilla de Participantes">
                                    <i class="fa-solid fa-users-gear"></i>
                                </button>
                                <button wire:click="editar({{ $s->id }})" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all" title="Editar Servicio">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button wire:click="eliminar({{ $s->id }})" wire:confirm="¿Está seguro de eliminar este servicio?" class="p-2 rounded-lg bg-slate-800 text-rose-400 hover:text-rose-300 hover:bg-rose-950/50 transition-all" title="Eliminar">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500 text-sm">
                                No se encontraron servicios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $servicios->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar Servicio -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-xl max-h-[92vh] flex flex-col overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border px-6 py-4 shrink-0">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-music text-gold-400"></i>
                        <span>{{ $isEdit ? 'Editar Servicio' : 'Nuevo Servicio' }}</span>
                    </h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="overflow-y-auto px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Nombre del Servicio *</label>
                            <input type="text" wire:model="nombre" placeholder="ej. Serenata Completa 1 Hora" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('nombre') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Tipo de Servicio *</label>
                            <select wire:model="tipo_servicio" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="Musical">Musical (Mariachi / Serenatas)</option>
                                <option value="Alquiler Sonido">Alquiler de Sonido</option>
                                <option value="Técnico">Servicio Técnico</option>
                                <option value="Transporte">Transporte</option>
                                <option value="Otro">Otros Servicios</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Precio Base (Bs) *</label>
                            <input type="number" step="0.50" wire:model="precio_base" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('precio_base') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Duración Estimada (Minutos) *</label>
                            <input type="number" wire:model="duracion_minutos" placeholder="60" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('duracion_minutos') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Estado</label>
                            <select wire:model="activo" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Descripción del Servicio</label>
                            <textarea wire:model="descripcion" rows="2" placeholder="Detalles de repertorio, número de canciones o equipamiento incluido..." class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Observaciones</label>
                            <textarea wire:model="observaciones" rows="2" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                        </div>

                        <!-- Sección: Configuración de participantes y distribución económica -->
                        @if($isEdit)
                            <div class="sm:col-span-2 pt-3 border-t border-brand-border">
                                <div class="p-4 bg-slate-950/70 border border-gold-500/30 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3 shadow-inner">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold uppercase tracking-wider text-gold-400 flex items-center gap-2">
                                            <i class="fa-solid fa-users-gear"></i>
                                            <span>Configuración de participantes y distribución económica</span>
                                        </div>
                                        <p class="text-xs text-slate-400">
                                            Configura los integrantes predeterminados y comisiones para este servicio, que se sugerirán automáticamente en cada nuevo evento.
                                        </p>
                                    </div>
                                    <button type="button" wire:click="abrirPlantilla({{ $servicio_id }})" class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-md shadow-gold-500/20 shrink-0 flex items-center gap-2">
                                        <i class="fa-solid fa-sliders"></i>
                                        <span>Configurar Plantilla</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-brand-border px-6 py-4 shrink-0">
                    <button wire:click="$set('modalOpen', false)" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500">
                        {{ $isEdit ? 'Actualizar Servicio' : 'Guardar Servicio' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL PRINCIPAL: CONFIGURACIÓN DE PLANTILLA DE PARTICIPANTES POR SERVICIO -->
    <!-- ========================================================================= -->
    @if($modalPlantillaOpen && $servicioPlantilla)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-md animate-fade-in">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden shadow-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-brand-border px-6 py-4 shrink-0 bg-slate-950/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gold-500/10 border border-gold-500/30 flex items-center justify-center text-gold-400 text-lg">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-bold text-white">Plantilla Económica de Participantes</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-800 text-gold-400 border border-slate-700">
                                    {{ $servicioPlantilla->tipo_servicio ?: 'Musical' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400">
                                Servicio: <span class="text-white font-semibold">{{ $servicioPlantilla->nombre }}</span> | Precio Base: <span class="text-emerald-400 font-mono font-bold">Bs {{ number_format($servicioPlantilla->precio_base, 2) }}</span>
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarPlantilla" class="text-slate-400 hover:text-white text-lg p-1.5 rounded-lg hover:bg-slate-800 transition-all">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Notificaciones de la Plantilla -->
                <div class="px-6 pt-4 space-y-2 shrink-0">
                    @if (session()->has('plantilla_success'))
                        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-sm"></i>
                            <span>{{ session('plantilla_success') }}</span>
                        </div>
                    @endif
                    @if (session()->has('plantilla_error'))
                        <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-400 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-sm"></i>
                            <span>{{ session('plantilla_error') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Body -->
                <div class="overflow-y-auto px-6 py-4 space-y-5 flex-1">
                    <!-- Barra de Acción y Explicación -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-slate-950/70 border border-brand-border rounded-2xl">
                        <div class="text-xs text-slate-400">
                            <span class="font-bold text-slate-200">¿Cómo funciona?</span> Las personas activas en esta lista se sugerirán con sus montos al crear un nuevo evento con este servicio. Podrás modificarlos o quitarlos libremente en cada evento sin alterar esta plantilla.
                        </div>
                        @if(!$mostrarFormParticipante)
                            <button wire:click="abrirFormParticipante" class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-md shadow-gold-500/20 shrink-0 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-user-plus"></i>
                                <span>Agregar Participante</span>
                            </button>
                        @endif
                    </div>

                    <!-- Subformulario Agregar / Editar Participante -->
                    @if($mostrarFormParticipante)
                        <div class="p-5 bg-slate-900 border border-gold-500/40 rounded-2xl space-y-4 shadow-xl">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                <h4 class="text-sm font-bold text-gold-400 flex items-center gap-2">
                                    <i class="fa-solid {{ $editingPlantillaId ? 'fa-user-pen' : 'fa-user-plus' }}"></i>
                                    <span>{{ $editingPlantillaId ? 'Editar Participante en la Plantilla' : 'Agregar Participante a la Plantilla' }}</span>
                                </h4>
                                <button wire:click="cerrarFormParticipante" class="text-slate-400 hover:text-white text-xs flex items-center gap-1">
                                    <i class="fa-solid fa-xmark"></i>
                                    <span>Cancelar</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Selección de Persona -->
                                <div class="lg:col-span-2">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Participante (Persona) *</label>
                                        <button type="button" wire:click="abrirModalNuevaPersona" class="text-[11px] font-bold text-gold-400 hover:text-gold-300 flex items-center gap-1">
                                            <i class="fa-solid fa-plus-circle"></i>
                                            <span>Registrar nueva persona</span>
                                        </button>
                                    </div>
                                    <select wire:model.live="plantilla_persona_id" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                        <option value="">-- Seleccionar Persona del Catálogo --</option>
                                        @foreach($personasDisponibles as $persona)
                                            <option value="{{ $persona->id }}">
                                                {{ $persona->nombre_completo }} {{ $persona->ci_nit ? '(' . $persona->ci_nit . ')' : '' }} - {{ $persona->tipo ?: 'Músico' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plantilla_persona_id') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Función -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Función *</label>
                                    <input type="text" wire:model="plantilla_funcion" placeholder="ej. Músico, Técnico, Chofer..." list="funciones_sugeridas" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                    <datalist id="funciones_sugeridas">
                                        <option value="Músico">
                                        <option value="Coordinador">
                                        <option value="Técnico de Sonido">
                                        <option value="Chofer / Transporte">
                                        <option value="Personal de Apoyo">
                                        <option value="Fotógrafo / Video">
                                    </datalist>
                                    @error('plantilla_funcion') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Concepto Económico -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Concepto Económico *</label>
                                    <input type="text" wire:model="plantilla_concepto" placeholder="ej. Comisión por Evento" list="conceptos_sugeridos" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                    <datalist id="conceptos_sugeridos">
                                        <option value="Comisión por Evento">
                                        <option value="Pago por Servicio">
                                        <option value="Jornada">
                                        <option value="Transporte">
                                        <option value="Viático">
                                        <option value="Honorarios">
                                    </datalist>
                                    @error('plantilla_concepto') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Tipo de Cálculo -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Tipo de Cálculo *</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" wire:click="$set('plantilla_tipo_calculo', 'monto_fijo')" class="px-3 py-2 rounded-xl text-xs font-bold border transition-all text-center {{ $plantilla_tipo_calculo === 'monto_fijo' ? 'bg-gold-500 text-slate-950 border-gold-500' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700' }}">
                                            <i class="fa-solid fa-coins mr-1"></i> Monto Fijo (Bs)
                                        </button>
                                        <button type="button" wire:click="$set('plantilla_tipo_calculo', 'porcentaje')" class="px-3 py-2 rounded-xl text-xs font-bold border transition-all text-center {{ $plantilla_tipo_calculo === 'porcentaje' ? 'bg-gold-500 text-slate-950 border-gold-500' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700' }}">
                                            <i class="fa-solid fa-percent mr-1"></i> Porcentaje (%)
                                        </button>
                                    </div>
                                </div>

                                <!-- Valor Configurado -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">
                                        {{ $plantilla_tipo_calculo === 'porcentaje' ? 'Porcentaje (%) *' : 'Monto Fijo (Bs) *' }}
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-xs text-slate-400">
                                            {{ $plantilla_tipo_calculo === 'porcentaje' ? '%' : 'Bs' }}
                                        </span>
                                        <input type="number" step="0.50" wire:model.live.debounce.200ms="plantilla_valor" class="w-full pl-9 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono font-bold">
                                    </div>
                                    @error('plantilla_valor') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Vista Previa de Cálculo Dinámica -->
                                <div class="lg:col-span-3 sm:col-span-2 p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs flex flex-col sm:flex-row items-center justify-between gap-2">
                                    @if($plantilla_tipo_calculo === 'porcentaje')
                                        @php
                                            $base = (float)$servicioPlantilla->precio_base;
                                            $pct = (float)$plantilla_valor;
                                            $calculado = round(($base * $pct) / 100, 2);
                                        @endphp
                                        <div class="text-slate-300">
                                            <span class="text-gold-400 font-bold"><i class="fa-solid fa-calculator mr-1"></i> Regla porcentual:</span>
                                            Base de cálculo: <strong class="text-white font-mono">Bs {{ number_format($base, 2) }}</strong> (Precio Base del Servicio) &times; <strong class="text-gold-400 font-mono">{{ $pct }}%</strong>
                                        </div>
                                        <div class="font-mono font-bold text-emerald-400 text-sm">
                                            Importe Resultante: Bs {{ number_format($calculado, 2) }}
                                        </div>
                                    @else
                                        <div class="text-slate-300">
                                            <span class="text-gold-400 font-bold"><i class="fa-solid fa-coins mr-1"></i> Regla de monto fijo:</span>
                                            Se asignará directamente el monto establecido por evento.
                                        </div>
                                        <div class="font-mono font-bold text-emerald-400 text-sm">
                                            Importe Asignado: Bs {{ number_format((float)$plantilla_valor, 2) }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Estado y Observaciones -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Estado en Plantilla</label>
                                    <select wire:model="plantilla_activo" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                        <option value="1">Activo (Se sugiere automáticamente)</option>
                                        <option value="0">Inactivo (No se sugiere)</option>
                                    </select>
                                </div>

                                <div class="lg:col-span-2">
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Observaciones Opcionales</label>
                                    <input type="text" wire:model="plantilla_observaciones" placeholder="ej. Incluye transporte propio, requerimiento especial..." class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                                <button type="button" wire:click="cerrarFormParticipante" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="guardarParticipantePlantilla" class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-md shadow-gold-500/20">
                                    <i class="fa-solid fa-check mr-1"></i>
                                    <span>{{ $editingPlantillaId ? 'Actualizar en Plantilla' : 'Guardar en Plantilla' }}</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Tabla de Participantes en la Plantilla -->
                    <div class="bg-slate-950/90 rounded-2xl border border-brand-border overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                                <thead>
                                    <tr class="bg-slate-900 border-b border-brand-border text-slate-400 text-[11px] uppercase tracking-wider">
                                        <th class="py-3 px-4 font-bold">Participante</th>
                                        <th class="py-3 px-4 font-bold">Función</th>
                                        <th class="py-3 px-4 font-bold">Concepto</th>
                                        <th class="py-3 px-4 font-bold text-center">Tipo Cálculo</th>
                                        <th class="py-3 px-4 font-bold text-right">Valor Configurado</th>
                                        <th class="py-3 px-4 font-bold text-right">Importe Sugerido</th>
                                        <th class="py-3 px-4 font-bold text-center">Estado</th>
                                        <th class="py-3 px-4 font-bold text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-border/40 text-slate-300">
                                    @php
                                        $totalSugerido = 0;
                                        $totalFijos = 0;
                                        $totalPorcentajes = 0;
                                        $countActivos = 0;
                                    @endphp
                                    @forelse($servicioPlantilla->plantillaParticipantes as $sp)
                                        @php
                                            $base = (float)$servicioPlantilla->precio_base;
                                            $montoCalc = $sp->calcularMontoSugerido($base);
                                            if ($sp->activo) {
                                                $countActivos++;
                                                $totalSugerido += $montoCalc;
                                                if ($sp->tipo_calculo === 'porcentaje') {
                                                    $totalPorcentajes += $montoCalc;
                                                } else {
                                                    $totalFijos += $montoCalc;
                                                }
                                            }
                                        @endphp
                                        <tr class="hover:bg-slate-800/30 transition-all {{ !$sp->activo ? 'opacity-50 bg-slate-950/50' : '' }}">
                                            <td class="py-3 px-4">
                                                <div class="font-bold text-white flex items-center gap-2">
                                                    <i class="fa-solid fa-user text-gold-400 text-xs"></i>
                                                    <span>{{ $sp->persona->nombre_completo ?? 'N/A' }}</span>
                                                </div>
                                                @if($sp->persona && $sp->persona->ci_nit)
                                                    <div class="text-[11px] text-slate-400 font-mono">CI: {{ $sp->persona->ci_nit }}</div>
                                                @endif
                                                @if($sp->observaciones)
                                                    <div class="text-[11px] text-slate-500 italic mt-0.5 line-clamp-1">{{ $sp->observaciones }}</div>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-200 border border-slate-700">
                                                    {{ $sp->funcion }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-xs text-slate-300">
                                                {{ $sp->concepto_pago }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                @if($sp->tipo_calculo === 'porcentaje')
                                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                                        Porcentaje (%)
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-sky-500/10 text-sky-400 border border-sky-500/30">
                                                        Monto Fijo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-200">
                                                @if($sp->tipo_calculo === 'porcentaje')
                                                    {{ number_format($sp->valor, 2) }}%
                                                @else
                                                    Bs {{ number_format($sp->valor, 2) }}
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400">
                                                Bs {{ number_format($montoCalc, 2) }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <button wire:click="toggleActivoParticipantePlantilla({{ $sp->id }})" class="px-2.5 py-1 rounded-full text-[11px] font-bold transition-all {{ $sp->activo ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:text-white' }}" title="Clic para alternar estado activo/inactivo">
                                                    {{ $sp->activo ? 'Activo' : 'Inactivo' }}
                                                </button>
                                            </td>
                                            <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                                <button wire:click="abrirFormParticipante({{ $sp->id }})" class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all" title="Editar en Plantilla">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button wire:click="eliminarParticipantePlantilla({{ $sp->id }})" wire:confirm="¿Remover a este participante de la plantilla del servicio? Esto no alterará eventos históricos." class="p-1.5 rounded-lg bg-slate-800 text-rose-400 hover:text-rose-300 hover:bg-rose-950/50 transition-all" title="Remover de la Plantilla">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-8 text-slate-500">
                                                <i class="fa-solid fa-users-slash text-2xl mb-2 text-slate-600 block"></i>
                                                No hay participantes configurados en esta plantilla.<br>
                                                <button wire:click="abrirFormParticipante" class="mt-2 text-xs text-gold-400 hover:underline font-bold">
                                                    + Haz clic aquí para agregar el primer participante
                                                </button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Resumen Económico de la Plantilla -->
                    @if($servicioPlantilla->plantillaParticipantes->count() > 0)
                        @php
                            $precioBase = (float)$servicioPlantilla->precio_base;
                            $margenEstimado = $precioBase - $totalSugerido;
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-slate-950/80 border border-brand-border rounded-2xl">
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Integrantes Activos</div>
                                <div class="text-lg font-bold text-white">{{ $countActivos }} de {{ $servicioPlantilla->plantillaParticipantes->count() }}</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Fijos</div>
                                <div class="text-base font-bold font-mono text-sky-400">Bs {{ number_format($totalFijos, 2) }}</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Porcentajes</div>
                                <div class="text-base font-bold font-mono text-amber-400">Bs {{ number_format($totalPorcentajes, 2) }}</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Sugerido Evento</div>
                                <div class="text-lg font-bold font-mono text-emerald-400">Bs {{ number_format($totalSugerido, 2) }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between border-t border-brand-border px-6 py-4 shrink-0 bg-slate-950/60">
                    <span class="text-xs text-slate-500 italic">Los cambios se aplican automáticamente a futuros eventos.</span>
                    <button wire:click="cerrarPlantilla" class="px-5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cerrar Ventana
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL SECUNDARIO: REGISTRO RÁPIDO DE PERSONA / MÚSICO                      -->
    <!-- ========================================================================= -->
    @if($mostrarModalNuevaPersona)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md animate-fade-in">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl space-y-4 p-6">
                <div class="flex items-center justify-between border-b border-brand-border pb-3">
                    <h4 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-gold-400"></i>
                        <span>Registrar Nueva Persona en Catálogo</span>
                    </h4>
                    <button wire:click="cerrarModalNuevaPersona" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Nombres *</label>
                        <input type="text" wire:model="nueva_persona_nombre" placeholder="ej. Carlos" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500">
                        @error('nueva_persona_nombre') <span class="text-rose-400">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Apellidos *</label>
                        <input type="text" wire:model="nueva_persona_apellido" placeholder="ej. Rodríguez" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500">
                        @error('nueva_persona_apellido') <span class="text-rose-400">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">CI / NIT</label>
                        <input type="text" wire:model="nueva_persona_ci_nit" placeholder="ej. 8392102 LP" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Tipo / Especialidad *</label>
                        <select wire:model="nueva_persona_tipo" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500">
                            <option value="Músico">Músico</option>
                            <option value="Técnico">Técnico</option>
                            <option value="Chofer">Chofer</option>
                            <option value="Coordinador">Coordinador</option>
                            <option value="Personal Apoyo">Personal Apoyo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Teléfono / WhatsApp</label>
                        <input type="text" wire:model="nueva_persona_telefono" placeholder="ej. 77012345" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Email</label>
                        <input type="email" wire:model="nueva_persona_email" placeholder="ej. persona@correo.com" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-border">
                    <button wire:click="cerrarModalNuevaPersona" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cancelar
                    </button>
                    <button wire:click="guardarNuevaPersona" class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500">
                        Crear y Seleccionar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
