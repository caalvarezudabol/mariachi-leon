<div class="space-y-6">
    <!-- Header Banner -->
    <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-r from-brand-card via-slate-900 to-slate-950 border border-brand-border flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-xl">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.eventos') }}" class="w-12 h-12 rounded-2xl bg-slate-950/80 hover:bg-slate-800 border border-gold-500/30 flex items-center justify-center text-gold-400 transition-all shadow-lg flex-shrink-0">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-gold-500/10 text-gold-400 border border-gold-500/20">
                    <i class="fa-solid fa-users-gear text-gold-400"></i> Evento #{{ $evento->id }} - {{ $evento->codigo_evento }}
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white">
                    Participantes & Distribución Económica
                </h1>
                <p class="text-xs md:text-sm text-slate-400 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span><i class="fa-solid fa-user text-gold-400/70 mr-1"></i> Cliente: <strong>{{ $evento->cliente->nombre_completo ?? 'N/A' }}</strong></span>
                    <span><i class="fa-solid fa-music text-gold-400/70 mr-1"></i> Servicio: <strong>{{ $evento->servicio->nombre ?? 'N/A' }}</strong></span>
                    <span><i class="fa-solid fa-calendar text-gold-400/70 mr-1"></i> Fecha: <strong>{{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($evento->hora_inicio)->format('H:i') }}</strong></span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.eventos') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold border border-slate-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-list text-slate-400"></i>
                <span>Volver a Eventos</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session()->has('message'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3 shadow-lg">
            <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-3 shadow-lg">
            <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Financial KPI Summary Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-gold-500/30 transition-all shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Monto Cobrado (Cliente)</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-money-bill-wave text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-white">
                Bs. {{ number_format($montoEvento, 2) }}
            </div>
            <p class="text-xs text-slate-400">Total contratado con cliente</p>
        </div>

        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-gold-500/30 transition-all shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total a Participantes</span>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <i class="fa-solid fa-hand-holding-dollar text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-blue-400">
                Bs. {{ number_format($totalAsignadoParticipantes, 2) }}
            </div>
            <p class="text-xs text-slate-400">Suma de pagos asignados</p>
        </div>

        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-gold-500/30 transition-all shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Balance / Fondo Grupo</span>
                <div class="w-10 h-10 rounded-xl bg-gold-500/10 text-gold-400 flex items-center justify-center">
                    <i class="fa-solid fa-vault text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold {{ $balanceMargen >= 0 ? 'text-gold-400' : 'text-rose-400' }}">
                Bs. {{ number_format($balanceMargen, 2) }}
            </div>
            <p class="text-xs text-slate-400">Margen bruto para Mariachi León</p>
        </div>

        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-gold-500/30 transition-all shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Equipo Confirmado</span>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <i class="fa-solid fa-user-group text-lg"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-white">
                {{ $evento->participantes->count() }} <span class="text-sm font-normal text-slate-400">integrante(s)</span>
            </div>
            <p class="text-xs text-slate-400">Asignados a este evento</p>
        </div>
    </div>

    <!-- Smart Suggestions Bar -->
    @if (!empty($personasSugeridas) && count($personasSugeridas) > 0)
        <div class="p-5 rounded-2xl bg-slate-900/90 border border-gold-500/30 space-y-3 shadow-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-gold-400 text-sm font-bold">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Sugerencias Inteligentes (Basado en historial para "{{ $evento->servicio->nombre ?? 'Servicio' }}")</span>
                </div>
                <span class="text-xs text-slate-400">Haz clic en una persona para cargarla rápido</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($personasSugeridas as $sug)
                    <button type="button" wire:click="seleccionarSugerido({{ $sug->id }})" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-gold-500/20 hover:border-gold-500/50 border border-slate-700 text-xs font-medium text-slate-200 hover:text-gold-300 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle text-gold-400"></i>
                        <span>{{ $sug->nombres }} {{ $sug->apellidos }}</span>
                        <span class="px-1.5 py-0.5 rounded-md bg-slate-900 text-[10px] text-slate-400">{{ $sug->cargo }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Main Content Grid: Form + List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Column: Add Participant -->
        <div class="lg:col-span-1 p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-gold-400"></i>
                    <span>Asignar Integrante</span>
                </h3>
                <button type="button" wire:click="abrirModalPersona" class="text-xs font-bold text-gold-400 hover:text-gold-300 underline flex items-center gap-1">
                    <i class="fa-solid fa-circle-plus"></i> Nueva Persona
                </button>
            </div>

            <form wire:submit.prevent="agregarParticipante" class="space-y-4">
                <!-- Persona Dropdown -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Persona / Músico / Personal</label>
                    <select wire:model.live="persona_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-gold-500 focus:outline-none transition-all">
                        <option value="">-- Seleccionar Persona --</option>
                        @foreach ($todasPersonas as $per)
                            <option value="{{ $per->id }}">{{ $per->nombres }} {{ $per->apellidos }} ({{ $per->cargo }})</option>
                        @endforeach
                    </select>
                    @error('persona_id') <span class="text-xs text-rose-400">{{ $message }}</span> @errorEnd
                </div>

                <!-- Función/Rol -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Función en este Evento</label>
                    <select wire:model="funcion" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-gold-500 focus:outline-none transition-all">
                        <option value="Músico">Músico</option>
                        <option value="Técnico">Técnico de Sonido</option>
                        <option value="Chofer">Chofer / Transporte</option>
                        <option value="Personal de apoyo">Personal de apoyo</option>
                        <option value="Coordinador">Coordinador de Evento</option>
                    </select>
                    @error('funcion') <span class="text-xs text-rose-400">{{ $message }}</span> @errorEnd
                </div>

                <!-- Concepto Pago -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Concepto de Pago</label>
                    <select wire:model="concepto_pago" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-gold-500 focus:outline-none transition-all">
                        <option value="Comisión por Evento">Comisión por Evento</option>
                        <option value="Pago Fijo">Pago Fijo</option>
                        <option value="Viáticos">Viáticos / Alimentación</option>
                        <option value="Bono Especial">Bono Especial</option>
                        <option value="Apoyo Logístico">Apoyo Logístico</option>
                    </select>
                    @error('concepto_pago') <span class="text-xs text-rose-400">{{ $message }}</span> @errorEnd
                </div>

                <!-- Monto Asignado -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Monto Asignado (Bs.)</label>
                    <input type="number" step="0.01" wire:model="monto_asignado" placeholder="0.00" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-gold-500 focus:outline-none transition-all">
                    @error('monto_asignado') <span class="text-xs text-rose-400">{{ $message }}</span> @errorEnd
                </div>

                <!-- Observaciones -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Observaciones (Opcional)</label>
                    <textarea wire:model="observaciones" rows="2" placeholder="Notas sobre instrumentos, transporte, etc..." class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-gold-500 focus:outline-none transition-all"></textarea>
                    @error('observaciones') <span class="text-xs text-rose-400">{{ $message }}</span> @errorEnd
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-gold-500 to-amber-600 hover:from-gold-400 hover:to-amber-500 text-slate-950 font-bold text-sm shadow-lg hover:shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus-circle text-base"></i>
                    <span>Asignar al Evento</span>
                </button>
            </form>
        </div>

        <!-- Participants List Table Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-users text-gold-400"></i>
                        <span>Lista de Participantes Asignados ({{ $evento->participantes->count() }})</span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-900/80 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-brand-border">
                            <tr>
                                <th class="p-3">Integrante</th>
                                <th class="p-3">Función</th>
                                <th class="p-3">Concepto</th>
                                <th class="p-3 text-right">Monto (Bs.)</th>
                                <th class="p-3 text-center">Distribución</th>
                                <th class="p-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/40">
                            @forelse ($evento->participantes as $part)
                                <tr class="hover:bg-slate-900/50 transition-colors">
                                    <td class="p-3">
                                        <div class="font-bold text-white">{{ $part->persona->nombres ?? 'N/A' }} {{ $part->persona->apellidos ?? '' }}</div>
                                        <div class="text-xs text-slate-400"><i class="fa-solid fa-id-card text-gold-400/60 mr-1"></i> CI: {{ $part->persona->ci_nit ?? 'Sin CI' }} | Tel: {{ $part->persona->telefono ?? 'S/N' }}</div>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-800 text-gold-300 border border-slate-700">
                                            {{ $part->funcion }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs text-slate-300">
                                        {{ $part->concepto_pago }}
                                    </td>
                                    <td class="p-3 text-right font-extrabold text-emerald-400">
                                        Bs. {{ number_format($part->monto_asignado, 2) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        @if ($part->estado_distribucion === 'Distribuido')
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <i class="fa-solid fa-check-double mr-1"></i> Distribuido
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                <i class="fa-solid fa-clock mr-1"></i> Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center space-x-1">
                                        <button type="button" wire:click="editarParticipante({{ $part->id }})" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-gold-400 transition-all" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" wire:click="eliminarParticipante({{ $part->id }})" wire:confirm="¿Estás seguro de remover a este participante del evento?" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-950 text-rose-400 transition-all" title="Remover">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-500">
                                        <i class="fa-solid fa-user-slash text-2xl mb-2"></i>
                                        <div>No se han asignado participantes a este evento aún.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Economic Distribution Table -->
            <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-gold-400"></i>
                        <span>Registro de Distribución Económica</span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-900/80 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-brand-border">
                            <tr>
                                <th class="p-3">Receptor / Persona</th>
                                <th class="p-3">Concepto</th>
                                <th class="p-3 text-right">Monto (Bs.)</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/40">
                            @forelse ($evento->distribuciones as $dist)
                                <tr class="hover:bg-slate-900/50 transition-colors">
                                    <td class="p-3">
                                        <div class="font-bold text-white">{{ $dist->persona->nombres ?? 'N/A' }} {{ $dist->persona->apellidos ?? '' }}</div>
                                        <div class="text-xs text-slate-400">{{ $dist->funcion }}</div>
                                    </td>
                                    <td class="p-3 text-xs text-slate-300">
                                        {{ $dist->concepto }}
                                    </td>
                                    <td class="p-3 text-right font-extrabold text-gold-400">
                                        Bs. {{ number_format($dist->monto, 2) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        @if ($dist->estado === 'Distribuido')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                PAGADO / DISTRIBUIDO
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                PENDIENTE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        @if ($dist->estado !== 'Distribuido')
                                            <button type="button" wire:click="marcarDistribucionPagada({{ $dist->id }})" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow">
                                                <i class="fa-solid fa-check mr-1"></i> Marcar Pagado
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-500"><i class="fa-solid fa-circle-check text-emerald-400"></i> Listo</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-500">
                                        No hay registros de distribución económica aún.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Participant Inline Modal -->
    @if ($editingParticipanteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
            <div class="w-full max-w-md p-6 rounded-3xl bg-brand-card border border-gold-500/40 space-y-4 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-gold-400"></i>
                        <span>Editar Participante</span>
                    </h3>
                    <button type="button" wire:click="cancelarEdicion" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase">Función</label>
                        <select wire:model="edit_funcion" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                            <option value="Músico">Músico</option>
                            <option value="Técnico">Técnico de Sonido</option>
                            <option value="Chofer">Chofer / Transporte</option>
                            <option value="Personal de apoyo">Personal de apoyo</option>
                            <option value="Coordinador">Coordinador de Evento</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase">Concepto Pago</label>
                        <select wire:model="edit_concepto_pago" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                            <option value="Comisión por Evento">Comisión por Evento</option>
                            <option value="Pago Fijo">Pago Fijo</option>
                            <option value="Viáticos">Viáticos / Alimentación</option>
                            <option value="Bono Especial">Bono Especial</option>
                            <option value="Apoyo Logístico">Apoyo Logístico</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase">Monto Asignado (Bs.)</label>
                        <input type="number" step="0.01" wire:model="edit_monto_asignado" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase">Observaciones</label>
                        <textarea wire:model="edit_observaciones" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" wire:click="cancelarEdicion" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold">
                        Cancelar
                    </button>
                    <button type="button" wire:click="actualizarParticipante" class="px-4 py-2 rounded-xl bg-gold-500 hover:bg-gold-400 text-slate-950 text-sm font-bold shadow">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Create Person Modal -->
    @if ($mostrarModalPersona)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg p-6 rounded-3xl bg-brand-card border border-gold-500/40 space-y-4 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-gold-400"></i>
                        <span>Registrar Persona en Catálogo</span>
                    </h3>
                    <button type="button" wire:click="cerrarModalPersona" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardarNuevaPersona" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300 uppercase">Nombres *</label>
                            <input type="text" wire:model="nuevo_nombre" placeholder="Ej: Carlos" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                            @error('nuevo_nombre') <span class="text-xs text-rose-400">{{ $message }}</span> @errorEnd
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300 uppercase">Apellidos *</label>
                            <input type="text" wire:model="nuevo_apellido" placeholder="Ej: Mamani" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                            @error('nuevo_apellido') <span class="text-xs text-rose-400">{{ $message }}</span> @errorEnd
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300 uppercase">C.I. / NIT</label>
                            <input type="text" wire:model="nuevo_ci_nit" placeholder="Ej: 8934123" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300 uppercase">Teléfono / WhatsApp</label>
                            <input type="text" wire:model="nuevo_telefono" placeholder="Ej: 71234567" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300 uppercase">Cargo / Especialidad *</label>
                            <select wire:model="nuevo_cargo" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                                <option value="Músico">Músico</option>
                                <option value="Chofer">Chofer</option>
                                <option value="Técnico">Técnico</option>
                                <option value="Personal de apoyo">Personal de apoyo</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300 uppercase">Correo Electrónico</label>
                            <input type="email" wire:model="nuevo_email" placeholder="correo@ejemplo.com" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase">Dirección</label>
                        <input type="text" wire:model="nueva_direccion" placeholder="Dirección de residencia" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-brand-border/60">
                        <button type="button" wire:click="cerrarModalPersona" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-gold-500 hover:bg-gold-400 text-slate-950 text-sm font-bold shadow">
                            Guardar Persona & Seleccionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
