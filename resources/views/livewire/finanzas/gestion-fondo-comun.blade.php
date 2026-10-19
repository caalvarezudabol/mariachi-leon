<div class="space-y-6">
    <!-- Header de la Pantalla -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-brand-card p-6 rounded-3xl border border-brand-border shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-2xl shadow-inner">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Fondo Común Operativo</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                        Caja de Reserva
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Administración de aportes institucionales, reservas de eventos y retiros operativos autorizados.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.finanzas.dashboard') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-chart-line text-gold-400"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.finanzas.movimientos') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-money-bill-transfer text-gold-400"></i>
                <span>Gastos & Ingresos</span>
            </a>

            <a href="{{ route('admin.reportes.fondo-comun.pdf', ['desde' => $fecha_desde, 'hasta' => $fecha_hasta, 'tipo' => $filtro_tipo]) }}" 
               target="_blank"
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-file-pdf text-emerald-400"></i>
                <span>Exportar PDF</span>
            </a>

            <!-- Botones de Acción -->
            <button type="button" wire:click="abrirModalCrear('INGRESO')" 
                    class="px-3.5 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 font-bold text-xs flex items-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-circle-plus text-emerald-400"></i>
                <span>+ Registrar Aporte</span>
            </button>

            <button type="button" wire:click="abrirModalCrear('EGRESO')" 
                    class="px-3.5 py-2 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/40 font-bold text-xs flex items-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-circle-minus text-rose-400"></i>
                <span>- Registrar Retiro</span>
            </button>
        </div>
    </div>

    <!-- Mensajes Flash -->
    @if (session()->has('message'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center justify-between gap-3 text-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-300">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 flex items-center justify-between gap-3 text-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-400/60 hover:text-rose-300">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Tarjetas de Saldo y Totales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Saldo Disponible Actual -->
        <div class="bg-gradient-to-br from-emerald-950/40 via-brand-card to-brand-card p-6 rounded-3xl border border-emerald-500/40 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Saldo Disponible</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-sm">
                    <i class="fa-solid fa-wallet"></i>
                </span>
            </div>
            <div class="text-3xl font-black text-emerald-300 font-mono">
                Bs. {{ number_format($saldoActual, 2) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-emerald-400 text-[10px]"></i>
                <span>Fondos reales disponibles en bóveda</span>
            </p>
        </div>

        <!-- Total Aportes Históricos -->
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Total Aportes (Histórico)</span>
                <span class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>
            </div>
            <div class="text-2xl font-bold text-white font-mono">
                Bs. {{ number_format($totalAportesHistorico, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-2">
                En el período: <span class="text-emerald-400 font-bold">Bs. {{ number_format($aportesPeriodo, 2) }}</span>
            </p>
        </div>

        <!-- Total Retiros Históricos -->
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Total Retiros (Histórico)</span>
                <span class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </span>
            </div>
            <div class="text-2xl font-bold text-white font-mono">
                Bs. {{ number_format($totalRetirosHistorico, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-2">
                En el período: <span class="text-rose-400 font-bold">Bs. {{ number_format($retirosPeriodo, 2) }}</span>
            </p>
        </div>

        <!-- Flujo Neto del Período -->
        @php
            $flujoNetoPeriodo = $aportesPeriodo - $retirosPeriodo;
        @endphp
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Flujo Neto Período</span>
                <span class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-scale-balanced"></i>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono {{ $flujoNetoPeriodo >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ $flujoNetoPeriodo >= 0 ? '+' : '' }}Bs. {{ number_format($flujoNetoPeriodo, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Diferencial aportes vs. retiros consultados</p>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-4 shadow-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <!-- Buscar texto -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Buscar</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Concepto, glosa, observaciones..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-500 text-xs"></i>
                </div>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tipo</label>
                <select wire:model.live="filtro_tipo" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Todos los Tipos</option>
                    <option value="INGRESO">Aportes (Ingreso)</option>
                    <option value="EGRESO">Retiros (Egreso)</option>
                </select>
            </div>

            <!-- Desde -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha Desde</label>
                <input type="date" wire:model.live="fecha_desde" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>

            <!-- Hasta -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha Hasta</label>
                <input type="date" wire:model.live="fecha_hasta" 
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos del Fondo Común -->
    <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                    <tr>
                        <th class="py-3.5 px-4">Fecha & Hora</th>
                        <th class="py-3.5 px-4">Tipo</th>
                        <th class="py-3.5 px-4">Concepto / Glosa</th>
                        <th class="py-3.5 px-4">Método</th>
                        <th class="py-3.5 px-4 text-right">Monto</th>
                        <th class="py-3.5 px-4 text-right">Saldo Resultante</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($movimientos as $mov)
                        <tr class="hover:bg-slate-800/40 transition-colors {{ $mov->estado === 'Anulado' ? 'opacity-60 bg-rose-950/10' : '' }}">
                            <!-- Fecha -->
                            <td class="py-3 px-4 font-mono text-slate-300 whitespace-nowrap">
                                <div>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-500">{{ $mov->created_at->format('H:i') }}</div>
                            </td>

                            <!-- Tipo -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($mov->tipo === 'INGRESO')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        <i class="fa-solid fa-arrow-down mr-1"></i> Aporte
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                        <i class="fa-solid fa-arrow-up mr-1"></i> Retiro
                                    </span>
                                @endif
                            </td>

                            <!-- Concepto -->
                            <td class="py-3 px-4 max-w-sm">
                                <div class="font-bold text-white truncate" title="{{ $mov->concepto }}">
                                    {{ $mov->concepto }}
                                </div>
                                @if($mov->observaciones)
                                    <div class="text-[10px] text-slate-400 truncate italic">
                                        {{ $mov->observaciones }}
                                    </div>
                                @endif
                                <div class="text-[9px] text-slate-500 mt-0.5">
                                    Registrado por: {{ $mov->user->name ?? 'Usuario' }}
                                </div>
                            </td>

                            <!-- Método -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $mov->metodo }}
                                </span>
                            </td>

                            <!-- Monto -->
                            <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold text-sm {{ $mov->tipo === 'INGRESO' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $mov->tipo === 'INGRESO' ? '+' : '-' }} Bs. {{ number_format($mov->monto, 2) }}
                            </td>

                            <!-- Saldo Resultante -->
                            <td class="py-3 px-4 text-right whitespace-nowrap font-mono text-slate-300 text-xs">
                                Bs. {{ number_format($mov->saldo_resultante, 2) }}
                            </td>

                            <!-- Estado -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($mov->estado === 'Valido')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        Válido
                                    </span>
                                @else
                                    <div>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                            Anulado
                                        </span>
                                        @if($mov->motivo_anulacion)
                                            <div class="text-[9px] text-rose-400/80 truncate max-w-[120px] mt-0.5" title="{{ $mov->motivo_anulacion }}">
                                                {{ $mov->motivo_anulacion }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($mov->estado === 'Valido')
                                    <button type="button" wire:click="abrirModalAnular({{ $mov->id }})" 
                                            title="Anular movimiento"
                                            class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 inline-flex items-center justify-center transition-all">
                                        <i class="fa-solid fa-ban text-xs"></i>
                                    </button>
                                @else
                                    <span class="text-slate-600 text-xs italic">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 text-xl mx-auto mb-3">
                                    <i class="fa-solid fa-vault"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No hay movimientos en el fondo común para este período</p>
                                <p class="text-xs text-slate-500 mt-1">Utilice los botones superiores para registrar un aporte o retiro.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
            <div class="p-4 border-t border-brand-border bg-slate-950/40">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Registrar Aporte / Retiro -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-brand-card border border-brand-border w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="p-5 border-b border-brand-border flex items-center justify-between bg-slate-900/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $tipo === 'INGRESO' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }} border flex items-center justify-center text-lg">
                            <i class="fa-solid {{ $tipo === 'INGRESO' ? 'fa-circle-plus' : 'fa-circle-minus' }}"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">
                                Registrar {{ $tipo === 'INGRESO' ? 'Aporte al Fondo Común' : 'Retiro del Fondo Común' }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                Saldo disponible actual: <span class="font-mono font-bold text-emerald-400">Bs. {{ number_format($saldoActual, 2) }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModal" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit.prevent="guardarMovimiento" class="overflow-y-auto p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tipo de Movimiento -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Tipo de Operación <span class="text-rose-400">*</span>
                            </label>
                            <select wire:model.live="tipo" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                                <option value="INGRESO">Aporte (Ingreso al fondo)</option>
                                <option value="EGRESO">Retiro (Salida del fondo)</option>
                            </select>
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Fecha <span class="text-rose-400">*</span>
                            </label>
                            <input type="date" wire:model="fecha" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                            @error('fecha') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Concepto Sugerido / Personalizado -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Concepto <span class="text-rose-400">*</span>
                        </label>
                        <select wire:model="concepto" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none mb-2">
                            @if($tipo === 'INGRESO')
                                @foreach($conceptosAporte as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            @else
                                @foreach($conceptosRetiro as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            @endif
                        </select>
                        <input type="text" wire:model="concepto" placeholder="O escriba un concepto personalizado..." 
                               class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                        @error('concepto') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Monto -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Monto (Bs.) <span class="text-rose-400">*</span>
                            </label>
                            <input type="number" step="0.01" wire:model="monto" placeholder="0.00" 
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-bold focus:border-gold-500 focus:outline-none">
                            @error('monto') <span class="text-rose-400 text-[10px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Método -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Método / Respaldo <span class="text-rose-400">*</span>
                            </label>
                            <select wire:model="metodo" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                                <option value="Efectivo">Efectivo en Caja</option>
                                <option value="Transferencia QR">Transferencia QR</option>
                                <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                            </select>
                            @error('metodo') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Glosa -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Glosa Contable
                        </label>
                        <input type="text" wire:model="glosa" placeholder="Resumen corto para bitácora y comprobante" 
                               class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none">
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Observaciones adicionales
                        </label>
                        <textarea wire:model="observaciones" rows="2" placeholder="Detalles de justificación, nombres de aportantes o receptores..." 
                                  class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none"></textarea>
                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-4 border-t border-brand-border flex items-center justify-end gap-3">
                        <button type="button" wire:click="cerrarModal" 
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl {{ $tipo === 'INGRESO' ? 'bg-emerald-500 hover:bg-emerald-400 text-slate-950' : 'bg-rose-600 hover:bg-rose-500 text-white' }} font-bold text-xs flex items-center gap-2 transition-all shadow-md">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Confirmar {{ $tipo === 'INGRESO' ? 'Aporte' : 'Retiro' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Anular Movimiento -->
    @if($modalAnularOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-brand-card border border-rose-500/40 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-5 border-b border-brand-border flex items-center justify-between bg-rose-950/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-400 text-lg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Anular Movimiento de Fondo</h3>
                            <p class="text-xs text-rose-300/80">Esta acción revertirá el impacto sobre el saldo y se registrará en bitácora.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModalAnular" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    @if($movimiento_a_anular)
                        <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 font-bold">{{ $movimiento_a_anular->tipo }} #{{ $movimiento_a_anular->id }}</span>
                                <span class="font-mono font-bold text-rose-400">Bs. {{ number_format($movimiento_a_anular->monto, 2) }}</span>
                            </div>
                            <div class="text-white font-medium">{{ $movimiento_a_anular->concepto }}</div>
                            <div class="text-[11px] text-slate-500">
                                Fecha: {{ \Carbon\Carbon::parse($movimiento_a_anular->fecha)->format('d/m/Y') }} | Método: {{ $movimiento_a_anular->metodo }}
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Motivo de Anulación <span class="text-rose-400">*</span>
                        </label>
                        <textarea wire:model="motivo_anulacion" rows="3" placeholder="Indique la justificación para anular este movimiento de caja..." 
                                  class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-rose-500 focus:outline-none"></textarea>
                        @error('motivo_anulacion') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 border-t border-brand-border flex items-center justify-end gap-3">
                        <button type="button" wire:click="cerrarModalAnular" 
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-colors">
                            Cancelar
                        </button>
                        <button type="button" wire:click="confirmarAnulacion" 
                                class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs flex items-center gap-2 transition-all shadow-md">
                            <i class="fa-solid fa-ban"></i>
                            <span>Confirmar Anulación</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
