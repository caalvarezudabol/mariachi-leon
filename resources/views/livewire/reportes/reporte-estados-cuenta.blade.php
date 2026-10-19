<div class="space-y-6">
    <!-- Header de la Pantalla -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-brand-card p-6 rounded-3xl border border-brand-border shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-400 text-2xl shadow-inner">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Estados de Cuenta de Clientes</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700 font-mono">
                        Comercial
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Historial de cobros, anticipos, saldos pendientes y liquidación financiera con validación QR.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.finanzas.dashboard') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                <i class="fa-solid fa-chart-line text-gold-400"></i>
                <span>Dashboard</span>
            </a>

            @if($clienteSeleccionado && $tokenCliente)
                @php
                    $urlPdf = route('admin.clientes.estado-cuenta.pdf', [
                        'token' => $tokenCliente,
                        'tipo' => $modalidad,
                        'contrato' => $modalidad === 'contrato' ? $tokenContrato : null,
                    ]);
                @endphp
                <a href="{{ $urlPdf }}" 
                   target="_blank"
                   class="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-500 to-amber-500 hover:from-gold-400 hover:to-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-gold-500/20 transition-all">
                    <i class="fa-solid fa-file-pdf text-sm"></i>
                    <span>Descargar PDF Oficial con QR</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Selector de Modalidad y Filtros -->
    <div class="bg-brand-card p-5 rounded-3xl border border-brand-border space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-brand-border/60">
            <!-- Pestañas de Modalidad -->
            <div class="flex items-center bg-slate-950 p-1 rounded-2xl border border-slate-800">
                <button type="button" wire:click="$set('modalidad', 'contrato')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $modalidad === 'contrato' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-file-contract"></i>
                    <span>Por Contrato Específico</span>
                </button>
                <button type="button" wire:click="$set('modalidad', 'cliente')" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $modalidad === 'cliente' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Consolidado por Cliente</span>
                </button>
            </div>

            <span class="text-xs text-slate-400">
                Modalidad activa: <strong class="text-white">{{ $modalidad === 'contrato' ? 'Contrato Individual' : 'Historial Completo del Cliente' }}</strong>
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            <!-- Seleccionar Cliente -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                    Seleccionar Cliente <span class="text-rose-400">*</span>
                </label>
                <select wire:model.live="cliente_id" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <option value="">Seleccione un cliente...</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->nombre_completo }} ({{ $c->contratos_count }} contratos)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Seleccionar Contrato (si modalidad es por contrato) -->
            @if($modalidad === 'contrato')
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Seleccionar Contrato <span class="text-rose-400">*</span>
                    </label>
                    <select wire:model.live="contrato_id" 
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                        @if($contratosCliente->isEmpty())
                            <option value="">Este cliente no tiene contratos</option>
                        @else
                            @foreach($contratosCliente as $ct)
                                <option value="{{ $ct->id }}">
                                    {{ $ct->numero_contrato }} | {{ \Carbon\Carbon::parse($ct->fecha_contrato)->format('d/m/Y') }} | Bs. {{ number_format($ct->monto_total, 2) }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            @endif

            <!-- Filtro de Búsqueda Rápida de Cliente -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                    Buscar Cliente por Nombre / CI
                </label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search_cliente" 
                           placeholder="Filtrar lista de clientes..." 
                           class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-gold-500 focus:outline-none transition-all">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-500 text-xs"></i>
                </div>
            </div>
        </div>
    </div>

    @if($clienteSeleccionado)
        <!-- Bloque Institucional: Datos de la Empresa y Cliente -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Datos de la Empresa -->
            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border flex items-start gap-4 shadow-lg">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-building text-gold-400 text-xl"></i>
                </div>
                <div class="text-xs space-y-1">
                    <h4 class="font-bold text-white text-sm">{{ $empresa->nombre_comercial ?? 'Mariachi León Guanajuato' }}</h4>
                    <p class="text-slate-400">NIT/RUC: <span class="text-slate-300 font-mono">{{ $empresa->nit ?? 'S/N' }}</span></p>
                    <p class="text-slate-400">Dirección: <span class="text-slate-300">{{ $empresa->direccion ?? 'Cochabamba, Bolivia' }}</span></p>
                    <p class="text-slate-400">Teléfono: <span class="text-slate-300">{{ $empresa->telefono ?? '63854129' }}</span></p>
                </div>
            </div>

            <!-- Datos del Cliente -->
            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border flex items-start gap-4 shadow-lg">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-user-check text-blue-400 text-xl"></i>
                </div>
                <div class="text-xs space-y-1">
                    <h4 class="font-bold text-white text-sm">{{ $clienteSeleccionado->nombre_completo }}</h4>
                    <p class="text-slate-400">Doc. Identidad: <span class="text-slate-300 font-mono">{{ $clienteSeleccionado->ci_nit ?: 'S/D' }}</span></p>
                    <p class="text-slate-400">Teléfono: <span class="text-slate-300">{{ $clienteSeleccionado->telefono ?: 'S/N' }}</span></p>
                    <p class="text-slate-400">Correo: <span class="text-slate-300">{{ $clienteSeleccionado->email ?: 'No registrado' }}</span></p>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Resumen Financiero del Estado de Cuenta -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Monto Contratado -->
            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Monto Contratado</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-800 text-gold-400 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-file-signature"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-white font-mono">
                    Bs. {{ number_format($resumenFinanciero['total_contratado'], 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-2">
                    {{ $modalidad === 'contrato' ? 'Valor del contrato consultado' : 'Suma de todos sus contratos' }}
                </p>
            </div>

            <!-- Total Cobrado / Abonado -->
            <div class="bg-brand-card p-5 rounded-3xl border border-emerald-500/30 shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Total Abonado</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-300 font-mono">
                    Bs. {{ number_format($resumenFinanciero['total_cobrado'], 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-2">
                    {{ $resumenFinanciero['cantidad_pagos'] }} pago(s) confirmados
                </p>
            </div>

            <!-- Saldo Pendiente -->
            <div class="bg-brand-card p-5 rounded-3xl border {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'border-rose-500/40' : 'border-emerald-500/40' }} shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">Saldo Pendiente</span>
                    <span class="w-8 h-8 rounded-xl {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' }} border flex items-center justify-center text-sm">
                        <i class="fa-solid {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'fa-clock' : 'fa-check-double' }}"></i>
                    </span>
                </div>
                <div class="text-2xl font-black font-mono {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    Bs. {{ number_format($resumenFinanciero['saldo_pendiente'], 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-2">
                    {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'Por cancelar por el cliente' : 'Cuenta al día / Liquidada' }}
                </p>
            </div>

            <!-- Estado de Liquidación -->
            <div class="bg-brand-card p-5 rounded-3xl border border-brand-border shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Estado Liquidación</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </span>
                </div>
                <div class="text-sm font-bold mt-1">
                    @if($resumenFinanciero['saldo_pendiente'] <= 0.01)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                            Totalmente Liquidado
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                            Saldo Pendiente
                        </span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Condición financiera actual</p>
            </div>
        </div>

        <!-- Tabla Cronológica de Pagos y Movimientos -->
        <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden shadow-xl">
            <div class="p-5 border-b border-brand-border flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-gold-400"></i>
                    <span>Detalle Cronológico de Pagos y Anticipos</span>
                </h3>
                <span class="text-xs text-slate-400">
                    Total movimientos: <strong class="text-white">{{ $pagos->count() }}</strong>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-bold border-b border-brand-border">
                        <tr>
                            <th class="py-3.5 px-4">Fecha & Hora</th>
                            <th class="py-3.5 px-4">Recibo / Comprobante</th>
                            @if($modalidad === 'cliente')
                                <th class="py-3.5 px-4">Contrato</th>
                            @endif
                            <th class="py-3.5 px-4">Concepto</th>
                            <th class="py-3.5 px-4">Método de Pago</th>
                            <th class="py-3.5 px-4 text-right">Importe Pagado</th>
                            <th class="py-3.5 px-4 text-right">Saldo Posterior</th>
                            <th class="py-3.5 px-4 text-center">Estado</th>
                            <th class="py-3.5 px-4">Responsable</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/60">
                        @forelse($pagos as $p)
                            <tr class="hover:bg-slate-800/40 transition-colors {{ $p->estado === 'Anulado' ? 'opacity-60 bg-rose-950/10' : '' }}">
                                <!-- Fecha & Hora -->
                                <td class="py-3 px-4 font-mono text-slate-300 whitespace-nowrap">
                                    <div>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $p->created_at->format('H:i') }}</div>
                                </td>

                                <!-- Recibo -->
                                <td class="py-3 px-4 font-mono font-bold text-gold-400 whitespace-nowrap">
                                    {{ $p->numero_recibo }}
                                </td>

                                @if($modalidad === 'cliente')
                                    <!-- Contrato -->
                                    <td class="py-3 px-4 font-mono text-white whitespace-nowrap">
                                        {{ $p->contrato->numero_contrato ?? 'S/C' }}
                                    </td>
                                @endif

                                <!-- Concepto -->
                                <td class="py-3 px-4 max-w-xs">
                                    <div class="text-white font-medium truncate">{{ $p->concepto ?? 'Abono / Anticipo a presentación' }}</div>
                                    @if($p->observaciones)
                                        <div class="text-[10px] text-slate-400 truncate italic">{{ $p->observaciones }}</div>
                                    @endif
                                </td>

                                <!-- Método de Pago -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $p->metodo_pago }}
                                    </span>
                                </td>

                                <!-- Importe Pagado -->
                                <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold text-sm text-emerald-400">
                                    Bs. {{ number_format($p->monto, 2) }}
                                </td>

                                <!-- Saldo Posterior -->
                                <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold text-xs {{ ($p->saldo_posterior ?? 0) > 0 ? 'text-slate-300' : 'text-emerald-400' }}">
                                    Bs. {{ number_format($p->saldo_posterior ?? 0, 2) }}
                                </td>

                                <!-- Estado -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($p->estado === 'Valido')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            Confirmado
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                            Anulado
                                        </span>
                                    @endif
                                </td>

                                <!-- Responsable -->
                                <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                                    {{ $p->user->name ?? 'Sistema' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $modalidad === 'cliente' ? 9 : 8 }}" class="py-12 text-center text-slate-500">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 text-xl mx-auto mb-3">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-400">No se encontraron cobros registrados para esta consulta</p>
                                    <p class="text-xs text-slate-500 mt-1">Los pagos deben registrarse en el módulo Pagos & Anticipos.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Estado Vacío Inicial -->
        <div class="bg-brand-card p-12 rounded-3xl border border-brand-border text-center">
            <div class="w-16 h-16 rounded-3xl bg-slate-900 border border-slate-800 flex items-center justify-center text-gold-400 text-2xl mx-auto mb-4">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <h3 class="text-base font-bold text-white mb-1">Seleccione un Cliente</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                Elija un cliente y/o contrato desde la barra superior para generar su estado de cuenta interactivo y descargar el comprobante en PDF.
            </p>
        </div>
    @endif
</div>
