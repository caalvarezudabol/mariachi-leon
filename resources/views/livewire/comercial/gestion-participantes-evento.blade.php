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
        <div class="flex items-center gap-3 flex-wrap">
            @php
                $tokenEventoPart = \App\Services\Security\DocumentToken::encodeId($evento->id, 'evento');
            @endphp
            <a href="{{ route('admin.eventos.comisiones.pdf', $tokenEventoPart) }}" target="_blank"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-gold-500 to-amber-500 hover:from-gold-400 hover:to-amber-400 text-slate-950 text-sm font-bold shadow-lg shadow-gold-500/20 transition-all flex items-center gap-2"
                title="Descargar Planilla Oficial de Comisiones y Liquidación a Músicos y Personal en PDF con QR">
                <i class="fa-solid fa-file-pdf text-base"></i>
                <span>Planilla Comisiones (PDF)</span>
            </a>
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

    <!-- Financial KPI Summary Cards Grid (6 Indicadores Oficiales) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- 1. MONTO CONTRATADO -->
        <div class="p-5 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-gold-500/30 transition-all shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Monto Contratado</span>
                <div class="w-9 h-9 rounded-xl bg-gold-500/10 text-gold-400 flex items-center justify-center">
                    <i class="fa-solid fa-file-signature text-base"></i>
                </div>
            </div>
            <div>
                @if ($tieneContrato && $montoContratado !== null)
                    <div class="text-xl font-extrabold text-white">
                        Bs. {{ number_format($montoContratado, 2) }}
                    </div>
                    <p class="text-[11px] text-slate-400 truncate">Contrato: {{ $contrato->codigo_contrato ?? ('#' . $contrato->id) }}</p>
                @else
                    <div class="text-base font-extrabold text-amber-400">
                        Sin contrato
                    </div>
                    <p class="text-[11px] text-slate-500">No formalizado con cliente</p>
                @endif
            </div>
        </div>

        <!-- 2. MONTO COBRADO (CLIENTE) -->
        <div class="p-5 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-emerald-500/30 transition-all shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cobrado al Cliente</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-money-bill-wave text-base"></i>
                </div>
            </div>
            <div>
                @if ($tieneContrato && $totalCobradoCliente !== null)
                    <div class="text-xl font-extrabold text-emerald-400">
                        Bs. {{ number_format($totalCobradoCliente, 2) }}
                    </div>
                    <p class="text-[11px] text-slate-400">Cobros confirmados y vigentes</p>
                @else
                    <div class="text-base font-extrabold text-slate-500">
                        No disponible
                    </div>
                    <p class="text-[11px] text-slate-500">Sin contrato asociado</p>
                @endif
            </div>
        </div>

        <!-- 3. SALDO PENDIENTE DEL CLIENTE -->
        <div class="p-5 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-amber-500/30 transition-all shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Saldo Pendiente (Cliente)</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-base"></i>
                </div>
            </div>
            <div>
                @if ($tieneContrato && $saldoPendienteCliente !== null)
                    @if ($saldoPendienteCliente < 0)
                        <div class="text-xl font-extrabold text-rose-400">
                            Bs. {{ number_format($saldoPendienteCliente, 2) }}
                        </div>
                        <p class="text-[11px] text-rose-400 font-semibold truncate" title="Inconsistencia detectada: cobro acumulado superior al contrato">
                            <i class="fa-solid fa-triangle-exclamation"></i> Cobro excedido
                        </p>
                    @else
                        <div class="text-xl font-extrabold {{ $saldoPendienteCliente == 0 ? 'text-emerald-400' : 'text-amber-400' }}">
                            Bs. {{ number_format($saldoPendienteCliente, 2) }}
                        </div>
                        <p class="text-[11px] text-slate-400 truncate">
                            {{ $saldoPendienteCliente == 0 ? 'Contrato cancelado al 100%' : 'Por cobrar al cliente' }}
                        </p>
                    @endif
                @else
                    <div class="text-base font-extrabold text-slate-500">
                        No disponible
                    </div>
                    <p class="text-[11px] text-slate-500">Sin contrato asociado</p>
                @endif
            </div>
        </div>

        <!-- 4. TOTAL ASIGNADO A PARTICIPANTES -->
        <div class="p-5 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-blue-500/30 transition-all shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Asignado</span>
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <i class="fa-solid fa-users-gear text-base"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-blue-400">
                    Bs. {{ number_format($totalAsignadoParticipantes, 2) }}
                </div>
                <p class="text-[11px] text-slate-400 truncate">{{ $evento->participantes->count() }} participante(s) asignados</p>
            </div>
        </div>

        <!-- 5. TOTAL PAGADO A PARTICIPANTES -->
        <div class="p-5 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-teal-500/30 transition-all shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pagado a Personal</span>
                <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center">
                    <i class="fa-solid fa-hand-holding-dollar text-base"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-teal-400">
                    Bs. {{ number_format($totalPagadoParticipantes, 2) }}
                </div>
                <p class="text-[11px] text-slate-400 truncate">Por pagar: Bs. {{ number_format($saldoPorPagarParticipantes, 2) }}</p>
            </div>
        </div>

        <!-- 6. BALANCE BRUTO DEL EVENTO -->
        <div class="p-5 rounded-2xl bg-brand-card border border-brand-border space-y-2 hover:border-purple-500/30 transition-all shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Balance Bruto</span>
                <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <i class="fa-solid fa-scale-balanced text-base"></i>
                </div>
            </div>
            <div>
                @if ($balanceBruto !== null)
                    <div class="text-xl font-extrabold {{ $balanceBruto >= 0 ? 'text-purple-400' : 'text-rose-400' }}">
                        Bs. {{ number_format($balanceBruto, 2) }}
                    </div>
                    <p class="text-[11px] text-slate-400 truncate" title="Cobrado al cliente - Total asignado a participantes">
                        Cobrado - Asignado
                    </p>
                @else
                    <div class="text-base font-extrabold text-slate-500">
                        Provisional
                    </div>
                    <p class="text-[11px] text-slate-500">No disponible sin contrato</p>
                @endif
            </div>
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
                        <span>{{ $sug->nombre_completo ?? ($sug->nombre . ' ' . $sug->apellido) }}</span>
                        <span class="px-1.5 py-0.5 rounded-md bg-slate-900 text-[10px] text-slate-400">{{ $sug->tipo }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Main Content Grid: Form + List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Column: Add Participant -->
        <div class="lg:col-span-1 p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 shadow-xl h-fit">
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
                            <option value="{{ $per->id }}">{{ $per->nombre_completo ?? ($per->nombre . ' ' . $per->apellido) }} ({{ $per->tipo }})</option>
                        @endforeach
                    </select>
                    @error('persona_id') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
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
                    @error('funcion') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
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
                    @error('concepto_pago') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <!-- Monto Asignado -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Monto Asignado (Bs.)</label>
                    <input type="number" step="0.01" wire:model="monto_asignado" placeholder="0.00" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-gold-500 focus:outline-none transition-all">
                    @error('monto_asignado') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <!-- Observaciones -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Observaciones (Opcional)</label>
                    <textarea wire:model="observaciones" rows="2" placeholder="Notas sobre instrumentos, transporte, etc..." class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-gold-500 focus:outline-none transition-all"></textarea>
                    @error('observaciones') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-gold-500 to-amber-600 hover:from-gold-400 hover:to-amber-500 text-slate-950 font-bold text-sm shadow-lg hover:shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus-circle text-base"></i>
                    <span>Asignar al Evento</span>
                </button>
            </form>
        </div>

        <!-- Right Column: Tables -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 1. Lista de Participantes Asignados -->
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
                                <th class="p-3 text-right">Asignado</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/40">
                            @forelse ($evento->participantes as $part)
                                <tr class="hover:bg-slate-900/50 transition-colors">
                                    <td class="p-3">
                                        <div class="font-bold text-white">{{ $part->persona->nombre_completo ?? ($part->persona->nombre ?? 'N/A') }}</div>
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
                                    <td class="p-3 text-right font-extrabold text-blue-400">
                                        Bs. {{ number_format($part->monto_asignado, 2) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        @php
                                            $stCalculado = $part->estado_calculado;
                                        @endphp
                                        @if ($stCalculado === 'PAGADO')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <i class="fa-solid fa-check-double mr-1"></i> PAGADO
                                            </span>
                                        @elseif ($stCalculado === 'PAGO PARCIAL')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-400 border border-sky-500/20">
                                                <i class="fa-solid fa-circle-half-stroke mr-1"></i> PAGO PARCIAL
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                <i class="fa-solid fa-clock mr-1"></i> PENDIENTE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center space-x-1">
                                        <button type="button" wire:click="editarParticipante({{ $part->id }})" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-gold-400 transition-all" title="Editar Asignación">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" wire:click="eliminarParticipante({{ $part->id }})" wire:confirm="¿Estás seguro de remover a este participante del evento?" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-950 text-rose-400 transition-all" title="Remover Asignación">
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

            <!-- 2. Registro de Distribución Económica (Columnas Oficiales) -->
            <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-gold-400"></i>
                        <span>Registro de Distribución Económica</span>
                    </h3>
                    <span class="text-xs text-slate-400">Pagos en Efectivo o QR con registro individual</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-900/80 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-brand-border">
                            <tr>
                                <th class="p-3">Receptor / Persona</th>
                                <th class="p-3">Concepto</th>
                                <th class="p-3 text-right">Monto Total</th>
                                <th class="p-3 text-right">Monto Pagado</th>
                                <th class="p-3 text-right">Saldo a Pagar</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/40">
                            @forelse ($evento->participantes as $part)
                                @php
                                    $montoTotalAsig = (float)$part->monto_asignado;
                                    $montoPagadoPart = $part->total_pagado;
                                    $saldoPart = $part->saldo_pendiente;
                                    $estadoPart = $part->estado_calculado;
                                @endphp
                                <tr class="hover:bg-slate-900/50 transition-colors">
                                    <td class="p-3">
                                        <div class="font-bold text-white">{{ $part->persona->nombre_completo ?? ($part->persona->nombre ?? 'N/A') }}</div>
                                        <div class="text-xs text-slate-400 flex items-center gap-1.5 mt-0.5">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[11px] text-slate-300 font-medium">{{ $part->funcion }}</span>
                                            @if($part->persona && $part->persona->telefono)
                                                <span><i class="fa-solid fa-phone text-[10px] text-slate-500"></i> {{ $part->persona->telefono }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3 text-xs text-slate-300">
                                        {{ $part->concepto_pago }}
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold text-white">
                                        Bs. {{ number_format($montoTotalAsig, 2) }}
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold text-emerald-400">
                                        Bs. {{ number_format($montoPagadoPart, 2) }}
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold {{ $saldoPart > 0 ? 'text-amber-400' : 'text-slate-500' }}">
                                        Bs. {{ number_format($saldoPart, 2) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        @if ($estadoPart === 'PAGADO')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 whitespace-nowrap">
                                                <i class="fa-solid fa-check-double mr-1"></i> PAGADO
                                            </span>
                                        @elseif ($estadoPart === 'PAGO PARCIAL')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-400 border border-sky-500/20 whitespace-nowrap">
                                                <i class="fa-solid fa-circle-half-stroke mr-1"></i> PAGO PARCIAL
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 whitespace-nowrap">
                                                <i class="fa-solid fa-clock mr-1"></i> PENDIENTE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5 flex-nowrap">
                                            <!-- Botón Registrar Pago -->
                                            @if ($saldoPart > 0)
                                                <button type="button" wire:click="abrirModalPago({{ $part->id }})" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow inline-flex items-center gap-1.5" title="Registrar pago (Efectivo o QR)">
                                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                                    <span>Pagar</span>
                                                </button>
                                            @else
                                                <button type="button" disabled class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-500 text-xs font-bold cursor-not-allowed inline-flex items-center gap-1" title="Asignación completamente cancelada">
                                                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                                                    <span>Listo</span>
                                                </button>
                                            @endif

                                            <!-- Botón Ver Historial -->
                                            <button type="button" wire:click="abrirHistorialPagos({{ $part->id }})" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-gold-400 border border-slate-700 text-xs transition-all" title="Ver historial de pagos de esta persona">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                            </button>

                                            <!-- Botón Editar Asignación -->
                                            <button type="button" wire:click="editarParticipante({{ $part->id }})" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs transition-all" title="Editar asignación">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-500">
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

    <!-- MODAL 1: Registrar Pago a Participante -->
    @if ($mostrarModalPago && $pago_participante)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg p-6 rounded-3xl bg-brand-card border border-emerald-500/40 space-y-5 shadow-2xl animate-fade-in">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i class="fa-solid fa-money-bill-transfer text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Registrar Pago a Participante</h3>
                            <p class="text-xs text-slate-400">{{ $pago_participante->persona->nombre_completo ?? 'Participante' }} ({{ $pago_participante->funcion }})</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModalPago" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Financial Snapshot Box -->
                <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 grid grid-cols-3 gap-3 text-center">
                    <div>
                        <div class="text-[11px] text-slate-400 font-bold uppercase">Total Asignado</div>
                        <div class="text-sm font-extrabold text-white mt-0.5">Bs. {{ number_format($pago_participante->monto_asignado, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400 font-bold uppercase">Ya Pagado</div>
                        <div class="text-sm font-extrabold text-emerald-400 mt-0.5">Bs. {{ number_format($pago_participante->total_pagado, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-amber-400 font-bold uppercase">Saldo Pendiente</div>
                        <div class="text-sm font-extrabold text-amber-400 mt-0.5">Bs. {{ number_format($pago_participante->saldo_pendiente, 2) }}</div>
                    </div>
                </div>

                <form wire:submit.prevent="guardarPagoParticipante" class="space-y-4">
                    <!-- Monto a Pagar -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Monto a Pagar en esta Operación (Bs.) *</label>
                            <button type="button" wire:click="$set('pago_monto', {{ $pago_participante->saldo_pendiente }})" class="text-xs text-emerald-400 hover:underline font-semibold">
                                Pagar Saldo Completo
                            </button>
                        </div>
                        <input type="number" step="0.01" wire:model="pago_monto" max="{{ $pago_participante->saldo_pendiente }}" placeholder="0.00" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-base font-bold focus:border-emerald-500 focus:outline-none transition-all">
                        @error('pago_monto') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <!-- Método de Pago: Efectivo o QR -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Método de Pago *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" wire:model="pago_metodo" value="Efectivo" class="peer sr-only">
                                <div class="p-3 rounded-xl border border-slate-800 bg-slate-900 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400 text-slate-300 flex items-center justify-center gap-2 text-sm font-bold transition-all">
                                    <i class="fa-solid fa-money-bill-1-wave"></i>
                                    <span>Efectivo</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" wire:model="pago_metodo" value="QR" class="peer sr-only">
                                <div class="p-3 rounded-xl border border-slate-800 bg-slate-900 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400 text-slate-300 flex items-center justify-center gap-2 text-sm font-bold transition-all">
                                    <i class="fa-solid fa-qrcode"></i>
                                    <span>QR Bancario</span>
                                </div>
                            </label>
                        </div>
                        @error('pago_metodo') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <!-- Glosa del Pago -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Glosa o Detalle del Pago *</label>
                        <input type="text" wire:model="pago_glosa" placeholder="Ej: Pago parcial por presentación mariachi" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-emerald-500 focus:outline-none transition-all">
                        @error('pago_glosa') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <!-- Observaciones Opcionales -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Observaciones (Opcional)</label>
                        <textarea wire:model="pago_observaciones" rows="2" placeholder="Detalles adicionales, número de transacción, etc..." class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-emerald-500 focus:outline-none transition-all"></textarea>
                    </div>

                    <!-- Metadatos automáticos de auditoría -->
                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 text-xs text-slate-400 flex flex-wrap items-center justify-between gap-2">
                        <span><i class="fa-solid fa-user-check text-emerald-400 mr-1"></i> Responsable: <strong>{{ Auth::user()->name ?? 'Administrador' }}</strong></span>
                        <span><i class="fa-solid fa-clock text-slate-400 mr-1"></i> Fecha: <strong>{{ now()->format('d/m/Y H:i') }}</strong></span>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-brand-border/60">
                        <button type="button" wire:click="cerrarModalPago" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition-all">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold shadow-lg shadow-emerald-600/20 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            <span wire:loading.remove wire:target="guardarPagoParticipante">Confirmar Pago</span>
                            <span wire:loading wire:target="guardarPagoParticipante">Procesando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: Historial de Pagos de un Participante -->
    @if ($mostrarModalHistorial && $historial_participante)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
            <div class="w-full max-w-3xl p-6 rounded-3xl bg-brand-card border border-gold-500/40 space-y-4 shadow-2xl animate-fade-in max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gold-500/10 text-gold-400 flex items-center justify-center">
                            <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Historial de Pagos del Participante</h3>
                            <p class="text-xs text-slate-400">
                                <strong>{{ $historial_participante->persona->nombre_completo ?? 'Participante' }}</strong> | {{ $historial_participante->funcion }} - {{ $historial_participante->concepto_pago }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarHistorialPagos" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Financial Snapshot Bar -->
                <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 grid grid-cols-4 gap-3 text-center">
                    <div>
                        <div class="text-[11px] text-slate-400 font-bold uppercase">Monto Total</div>
                        <div class="text-sm font-extrabold text-white mt-0.5">Bs. {{ number_format($historial_participante->monto_asignado, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400 font-bold uppercase">Pagado Vigente</div>
                        <div class="text-sm font-extrabold text-emerald-400 mt-0.5">Bs. {{ number_format($historial_participante->total_pagado, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-amber-400 font-bold uppercase">Saldo a Pagar</div>
                        <div class="text-sm font-extrabold text-amber-400 mt-0.5">Bs. {{ number_format($historial_participante->saldo_pendiente, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400 font-bold uppercase">Estado Actual</div>
                        <div class="text-xs font-bold mt-1">
                            @if ($historial_participante->estado_calculado === 'PAGADO')
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">PAGADO</span>
                            @elseif ($historial_participante->estado_calculado === 'PAGO PARCIAL')
                                <span class="px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20">PAGO PARCIAL</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">PENDIENTE</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Pagos History Table -->
                <div class="space-y-2">
                    <div class="text-xs font-bold text-slate-300 uppercase tracking-wider">Detalle Cronológico de Movimientos</div>
                    <div class="overflow-x-auto rounded-xl border border-brand-border">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-900/90 text-slate-400 uppercase font-bold border-b border-brand-border">
                                <tr>
                                    <th class="p-2.5">Fecha y Hora</th>
                                    <th class="p-2.5">Método</th>
                                    <th class="p-2.5">Glosa & Detalle</th>
                                    <th class="p-2.5 text-right">Importe</th>
                                    <th class="p-2.5">Registrado Por</th>
                                    <th class="p-2.5 text-center">Estado</th>
                                    <th class="p-2.5 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-border/40">
                                @forelse ($historial_participante->pagos->sortByDesc('created_at') as $pago)
                                    <tr class="hover:bg-slate-900/40 transition-colors {{ $pago->estado === 'Revertido' ? 'opacity-60 bg-rose-950/10' : '' }}">
                                        <td class="p-2.5 font-mono text-[11px] whitespace-nowrap">
                                            {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : $pago->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="p-2.5">
                                            @if ($pago->metodo_pago === 'QR')
                                                <span class="px-2 py-0.5 rounded bg-purple-500/15 text-purple-400 border border-purple-500/30 font-semibold inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-qrcode text-[10px]"></i> QR
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-money-bill text-[10px]"></i> Efectivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-2.5 max-w-[200px]">
                                            <div class="font-bold text-white truncate" title="{{ $pago->glosa }}">{{ $pago->glosa }}</div>
                                            @if ($pago->observaciones)
                                                <div class="text-[10px] text-slate-400 truncate" title="{{ $pago->observaciones }}">{{ $pago->observaciones }}</div>
                                            @endif
                                            @if ($pago->estado === 'Revertido' && $pago->motivo_reversion)
                                                <div class="text-[10px] text-rose-400 mt-0.5 font-semibold">
                                                    <i class="fa-solid fa-ban"></i> Motivo: {{ $pago->motivo_reversion }}
                                                    @if($pago->revertidoPor) (por {{ $pago->revertidoPor->name }}) @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="p-2.5 text-right font-mono font-bold text-sm {{ $pago->estado === 'Revertido' ? 'line-through text-slate-500' : 'text-emerald-400' }}">
                                            Bs. {{ number_format($pago->monto, 2) }}
                                        </td>
                                        <td class="p-2.5 text-slate-300">
                                            {{ $pago->user->name ?? 'Sistema' }}
                                        </td>
                                        <td class="p-2.5 text-center">
                                            @if ($pago->estado === 'Valido')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                    VÁLIDO
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                                    REVERTIDO
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-2.5 text-center">
                                            @if ($pago->estado === 'Valido')
                                                <button type="button" wire:click="abrirModalRevertir({{ $pago->id }})" class="px-2 py-1 rounded bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 border border-rose-500/20 text-[11px] font-bold transition-all inline-flex items-center gap-1" title="Revertir este pago">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                    <span>Revertir</span>
                                                </button>
                                            @else
                                                <span class="text-[10px] text-slate-500 italic">Anulado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-6 text-center text-slate-500">
                                            <i class="fa-solid fa-receipt text-2xl mb-1"></i>
                                            <div>No se registran pagos previos para este participante en este evento.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-2 border-t border-brand-border/60">
                    <button type="button" wire:click="cerrarHistorialPagos" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold">
                        Cerrar Historial
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: Confirmar Reversión de Pago -->
    @if ($mostrarModalRevertir && $pago_a_revertir)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
            <div class="w-full max-w-md p-6 rounded-3xl bg-brand-card border border-rose-500/40 space-y-4 shadow-2xl animate-fade-in">
                <div class="flex items-center gap-3 border-b border-brand-border/60 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Confirmar Reversión de Pago</h3>
                        <p class="text-xs text-slate-400">Pago ID #{{ $pago_a_revertir->id }} - Bs. {{ number_format($pago_a_revertir->monto, 2) }}</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-300 space-y-1">
                    <p class="font-bold">¿Está seguro de revertir este movimiento?</p>
                    <p class="text-slate-400">
                        El importe de <strong>Bs. {{ number_format($pago_a_revertir->monto, 2) }}</strong> volverá a sumarse al saldo pendiente de <strong>{{ $pago_a_revertir->participante->persona->nombre_completo ?? 'Participante' }}</strong>. El movimiento quedará registrado en el historial como Revertido y se asentará en la Bitácora de Auditoría.
                    </p>
                </div>

                <form wire:submit.prevent="confirmarReversionPago" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Motivo de la Reversión *</label>
                        <textarea wire:model="motivo_reversion" rows="2" placeholder="Explique brevemente por qué se revierte este pago (ej: error en monto, duplicidad, etc.)..." class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm focus:border-rose-500 focus:outline-none transition-all"></textarea>
                        @error('motivo_reversion') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="cancelarReversion" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-bold shadow-lg shadow-rose-600/20 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span wire:loading.remove wire:target="confirmarReversionPago">Confirmar Reversión</span>
                            <span wire:loading wire:target="confirmarReversionPago">Procesando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 4: Edit Participant Inline Modal -->
    @if ($editingParticipanteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
            <div class="w-full max-w-md p-6 rounded-3xl bg-brand-card border border-gold-500/40 space-y-4 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-gold-400"></i>
                        <span>Editar Asignación de Participante</span>
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
                        <label class="text-xs font-bold text-slate-300 uppercase">Monto Asignado (Bs.) *</label>
                        <input type="number" step="0.01" wire:model="edit_monto_asignado" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm font-bold focus:border-gold-500">
                        <p class="text-[11px] text-slate-400">Nota: No puede ser inferior al total que ya se le pagó a este participante.</p>
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

    <!-- MODAL 5: Quick Create Person Modal -->
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
                            @error('nuevo_nombre') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300 uppercase">Apellidos *</label>
                            <input type="text" wire:model="nuevo_apellido" placeholder="Ej: Mamani" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-brand-border text-white text-sm">
                            @error('nuevo_apellido') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
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
