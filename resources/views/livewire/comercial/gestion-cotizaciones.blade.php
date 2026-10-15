<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestionar Cotizaciones</h1>
            <p class="text-xs text-slate-400">Presupuestos de servicios y cotización formal para eventos.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-file-signature"></i>
            <span>Nueva Cotización</span>
        </button>
    </div>

    <!-- Mensajes de Notificación -->
    @if (session()->has('success_pago'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-400 text-sm flex flex-col sm:flex-row items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-2xl text-emerald-400"></i>
                <div>
                    <p class="font-bold text-white">{{ session('success_pago') }}</p>
                    <p class="text-xs text-slate-300">El pago se registró en la bitácora y se actualizaron los saldos del contrato.</p>
                </div>
            </div>
            @if($ultimoRecibo)
                <div class="flex items-center gap-2">
                    <a href="{{ route('comercial.pagos.recibo', $ultimoRecibo['id']) }}" target="_blank" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs flex items-center gap-2 shadow-lg shadow-amber-500/20 transition-all">
                        <i class="fa-solid fa-print"></i>
                        <span>Imprimir Recibo {{ $ultimoRecibo['numero'] }}</span>
                    </a>
                    <a href="{{ route('comercial.pagos.pdf', $ultimoRecibo['id']) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-rose-400 hover:text-white font-bold text-xs flex items-center gap-1.5 border border-slate-700 transition-all">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>PDF</span>
                    </a>
                </div>
            @endif
        </div>
    @endif
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative lg:col-span-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar N° cotización, cliente o evento..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
            </div>

            <div>
                <select wire:model.live="estado_filtro" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Estados</option>
                    <option value="Borrador">Borrador</option>
                    <option value="Enviada">Enviada</option>
                    <option value="Aceptada">Aceptada</option>
                    <option value="Rechazada">Rechazada</option>
                    <option value="Convertida en Contrato">Convertida en Contrato</option>
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
                        <th class="py-3.5 px-4 font-bold">Cotización</th>
                        <th class="py-3.5 px-4 font-bold">Cliente / Evento</th>
                        <th class="py-3.5 px-4 font-bold">Fecha</th>
                        <th class="py-3.5 px-4 font-bold text-right">Total</th>
                        <th class="py-3.5 px-4 font-bold text-right text-emerald-400">A Cuenta</th>
                        <th class="py-3.5 px-4 font-bold text-right text-rose-400">Saldo</th>
                        <th class="py-3.5 px-4 font-bold text-center">Estado</th>
                        <th class="py-3.5 px-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/50 text-slate-300">
                    @forelse($cotizaciones as $c)
                        <tr class="hover:bg-slate-800/30 transition-all">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-extrabold text-gold-400">{{ $c->numero_cotizacion }}</span>
                                @if($c->contrato)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        <i class="fa-solid fa-file-contract text-emerald-400 mr-1"></i>{{ $c->contrato->numero_contrato }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white">{{ $c->cliente->nombre_completo ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $c->evento->codigo_evento ?? '' }} &bull; {{ $c->servicio->nombre ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-300">
                                {{ $c->fecha_cotizacion->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-white">
                                Bs {{ number_format($c->monto_total, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                                @if($c->contrato)
                                    Bs {{ number_format($c->contrato->total_pagado, 2) }}
                                @else
                                    <span class="text-slate-600 font-sans font-normal text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold {{ $c->contrato && $c->contrato->saldo_pendiente <= 0 ? 'text-slate-500' : 'text-rose-400' }}">
                                @if($c->contrato)
                                    Bs {{ number_format($c->contrato->saldo_pendiente, 2) }}
                                @else
                                    Bs {{ number_format($c->monto_total, 2) }}
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold 
                                    @if($c->estado === 'Convertida en Contrato' || $c->estado === 'Aceptada') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($c->estado === 'Enviada') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($c->estado === 'Rechazada') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                    {{ $c->estado }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    @if($c->contrato)
                                        <!-- Descargar Contrato PDF con QR -->
                                        <a href="{{ route('comercial.contratos.pdf', $c->contrato->id) }}" target="_blank" 
                                           class="p-2 rounded-lg bg-slate-800 text-rose-400 hover:text-white hover:bg-rose-600 transition-all shadow" 
                                           title="Descargar Contrato Oficial PDF con QR">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>

                                        @if($c->contrato->saldo_pendiente > 0)
                                            <!-- Botón Modal Pago Rápido -->
                                            <button wire:click="abrirModalPagoRapido({{ $c->contrato->id }})" 
                                                    class="px-2.5 py-1.5 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs transition-all shadow flex items-center gap-1" 
                                                    title="Registrar Pago A Cuenta / Saldo">
                                                <i class="fa-solid fa-money-bill-wave"></i>
                                                <span>Pagar</span>
                                            </button>
                                        @else
                                            <span class="px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold inline-flex items-center gap-1" title="Contrato completamente pagado">
                                                <i class="fa-solid fa-check-double text-[10px]"></i>
                                                <span>Saldado</span>
                                            </span>
                                        @endif
                                    @else
                                        <button wire:click="convertirEnContrato({{ $c->id }})" wire:confirm="¿Desea generar el contrato formal para esta cotización?" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all" title="Convertir en Contrato">
                                            <i class="fa-solid fa-file-contract"></i>
                                            <span>Generar Contrato</span>
                                        </button>
                                    @endif

                                    <button wire:click="editar({{ $c->id }})" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500 text-sm">
                                No se encontraron cotizaciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $cotizaciones->links() }}
        </div>
    </div>

    <!-- Modal Pago Rápido -->
    @if($modalPagoRapidoOpen && $pagoContrato)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm animate-fade-in">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-xl overflow-hidden shadow-2xl space-y-5 p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">
                                Registrar Pago Rápido
                            </h3>
                            <p class="text-xs text-gold-400 font-mono">
                                Contrato N° {{ $pagoContrato->numero_contrato }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="$set('modalPagoRapidoOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Resumen de Cliente y Contrato -->
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-4 text-xs space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Cliente:</span>
                        <span class="font-bold text-white">{{ $pagoContrato->cliente->nombre_completo ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Evento:</span>
                        <span class="text-slate-300 font-mono">{{ $pagoContrato->evento->codigo_evento ?? 'N/A' }} ({{ $pagoContrato->evento ? $pagoContrato->evento->fecha_evento->format('d/m/Y') : '-' }})</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-800 text-center">
                        <div class="bg-slate-900/80 p-2 rounded-xl border border-slate-800">
                            <span class="text-[10px] text-slate-500 uppercase font-bold">Total Contrato</span>
                            <div class="text-xs font-black text-white mt-0.5">Bs {{ number_format($pagoContrato->monto_total, 2) }}</div>
                        </div>
                        <div class="bg-slate-900/80 p-2 rounded-xl border border-slate-800">
                            <span class="text-[10px] text-emerald-500 uppercase font-bold">Total Abonado</span>
                            <div class="text-xs font-black text-emerald-400 mt-0.5">Bs {{ number_format($pagoContrato->total_pagado, 2) }}</div>
                        </div>
                        <div class="bg-slate-900/80 p-2 rounded-xl border border-slate-800">
                            <span class="text-[10px] text-rose-500 uppercase font-bold">Saldo Actual</span>
                            <div class="text-xs font-black text-rose-400 mt-0.5">Bs {{ number_format($pagoContrato->saldo_pendiente, 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Selector de Tipo de Pago: A Cuenta vs Saldar Total -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" wire:click="seleccionarTipoPago('acuenta')" 
                            class="p-3 rounded-2xl border text-center transition-all {{ $tipoPago === 'acuenta' ? 'bg-amber-500/15 border-amber-500 text-amber-400 shadow-md font-bold' : 'bg-slate-900/50 border-slate-800 text-slate-400 hover:text-slate-200' }}">
                        <i class="fa-solid fa-hand-holding-dollar text-base mb-1 block"></i>
                        <span class="text-xs font-bold">Pago A Cuenta</span>
                    </button>
                    <button type="button" wire:click="seleccionarTipoPago('saldo')" 
                            class="p-3 rounded-2xl border text-center transition-all {{ $tipoPago === 'saldo' ? 'bg-emerald-500/15 border-emerald-500 text-emerald-400 shadow-md font-bold' : 'bg-slate-900/50 border-slate-800 text-slate-400 hover:text-slate-200' }}">
                        <i class="fa-solid fa-receipt text-base mb-1 block"></i>
                        <span class="text-xs font-bold">Saldar Totalidad</span>
                        <div class="text-[10px] font-mono mt-0.5 font-normal">Bs {{ number_format($pagoContrato->saldo_pendiente, 2) }}</div>
                    </button>
                </div>

                <!-- Formulario de Pago -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Monto a Cobrar (Bs) *</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Bs</span>
                                <input type="number" step="0.50" wire:model.live="pagoMonto" {{ $tipoPago === 'saldo' ? 'readonly' : '' }} 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-black text-base focus:border-amber-500">
                            </div>
                            @error('pagoMonto') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Método de Pago *</label>
                            <select wire:model="pagoMetodo" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-amber-500">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                                <option value="QR">QR / Billetera Móvil</option>
                                <option value="Tarjeta">Tarjeta Débito/Crédito</option>
                                <option value="Otro">Otro</option>
                            </select>
                            @error('pagoMetodo') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Fecha y Hora de Cobro *</label>
                            <input type="datetime-local" wire:model="pagoFecha" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-amber-500">
                            @error('pagoFecha') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">N° Comprobante / Referencia</label>
                            <input type="text" wire:model="pagoReferencia" placeholder="Ej: Transf. 981244" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-amber-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Concepto / Observaciones</label>
                        <input type="text" wire:model="pagoObservaciones" placeholder="Ej: Primer abono o cancelación" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-amber-500">
                    </div>

                    <!-- Proyección del Nuevo Saldo -->
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Saldo restante proyectado:</span>
                        <span class="font-mono font-black {{ ($pagoContrato->saldo_pendiente - (float)$pagoMonto) <= 0 ? 'text-emerald-400' : 'text-amber-400' }}">
                            Bs {{ number_format(max(0, $pagoContrato->saldo_pendiente - (float)$pagoMonto), 2) }}
                        </span>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="flex items-center justify-end gap-3 border-t border-brand-border pt-4">
                    <button wire:click="$set('modalPagoRapidoOpen', false)" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 transition-all">
                        Cancelar
                    </button>
                    <button wire:click="guardarPagoRapido" class="px-5 py-2.5 rounded-xl text-xs font-black bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-slate-950"></i>
                        <span>Confirmar Pago y Emitir Recibo</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Crear/Editar Cotización -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl space-y-6 p-6">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-file-signature text-gold-400"></i>
                        <span>{{ $isEdit ? 'Editar Cotización ' . $numero_cotizacion : 'Nueva Cotización' }}</span>
                    </h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">N° Cotización *</label>
                        <input type="text" wire:model="numero_cotizacion" readonly class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-gold-400 font-mono font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Fecha Cotización *</label>
                        <input type="date" wire:model="fecha_cotizacion" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Evento Relacionado *</label>
                        <select wire:model.live="evento_id" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione Evento</option>
                            @foreach($eventos as $ev)
                                <option value="{{ $ev->id }}">
                                    {{ $ev->codigo_evento }} - {{ $ev->cliente->nombre_completo ?? 'N/A' }} ({{ $ev->servicio->nombre ?? 'N/A' }} - {{ $ev->fecha_evento->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('evento_id') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Precio Base (Bs) *</label>
                        <input type="number" step="0.50" wire:model.live="precio_base" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Descuento (Bs)</label>
                        <input type="number" step="0.50" wire:model.live="descuento" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Monto Total Cotizado (Bs)</label>
                        <input type="number" step="0.50" wire:model="monto_total" readonly class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-emerald-400 font-mono font-extrabold text-base">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Estado</label>
                        <select wire:model="estado" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="Borrador">Borrador</option>
                            <option value="Enviada">Enviada</option>
                            <option value="Aceptada">Aceptada</option>
                            <option value="Rechazada">Rechazada</option>
                            <option value="Convertida en Contrato">Convertida en Contrato</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Observaciones</label>
                        <textarea wire:model="observaciones" rows="2" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-brand-border pt-4">
                    <button wire:click="$set('modalOpen', false)" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500">
                        {{ $isEdit ? 'Actualizar Cotización' : 'Guardar Cotización' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
