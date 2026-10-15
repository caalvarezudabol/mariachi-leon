<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestionar Pagos de Clientes</h1>
            <p class="text-xs text-slate-400">Registro de ingresos por cobranzas de contratos (Efectivo, QR o
                Transferencia).</p>
        </div>
        <button wire:click="abrirModal"
            class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-receipt"></i>
            <span>Nuevo Pago de Cliente</span>
        </button>
    </div>

    <!-- Mensajes de Notificación -->
    @if (session()->has('success_pago'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-400 text-sm flex flex-col sm:flex-row items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-2xl text-emerald-400"></i>
                <div>
                    <p class="font-bold text-white">{{ session('success_pago') }}</p>
                    <p class="text-xs text-slate-300">Pago registrado en la bitácora del sistema y saldos actualizados.</p>
                </div>
            </div>
            @if($ultimoRecibo)
                <div class="flex items-center gap-2">
                    <a href="{{ route('comercial.pagos.recibo', $ultimoRecibo['codigo_verificacion']) }}" target="_blank" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs flex items-center gap-2 shadow-lg shadow-amber-500/20 transition-all">
                        <i class="fa-solid fa-print"></i>
                        <span>Imprimir Recibo {{ $ultimoRecibo['numero'] }}</span>
                    </a>
                    <a href="{{ route('comercial.pagos.pdf', $ultimoRecibo['codigo_verificacion']) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-rose-400 hover:text-white font-bold text-xs flex items-center gap-1.5 border border-slate-700 transition-all">
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
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar N° recibo, N° contrato, cliente..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
            </div>

            <div>
                <select wire:model.live="metodo_filtro"
                    class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Métodos</option>
                    <option value="EFECTIVO">EFECTIVO</option>
                    <option value="QR">QR BANCO</option>
                    <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                </select>
            </div>

            <div>
                <select wire:model.live="estado_filtro"
                    class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Estados</option>
                    <option value="Valido">Válidos</option>
                    <option value="Anulado">Anulados</option>
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
                        <th class="py-3.5 px-4 font-bold">N° Recibo</th>
                        <th class="py-3.5 px-4 font-bold">Contrato / Evento</th>
                        <th class="py-3.5 px-4 font-bold">Cliente</th>
                        <th class="py-3.5 px-4 font-bold">Fecha Pago</th>
                        <th class="py-3.5 px-4 font-bold text-center">Método</th>
                        <th class="py-3.5 px-4 font-bold text-right">Monto (Bs)</th>
                        <th class="py-3.5 px-4 font-bold text-center">Estado</th>
                        <th class="py-3.5 px-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/50 text-slate-300">
                    @forelse($pagos as $p)
                        <tr class="hover:bg-slate-800/30 transition-all {{ $p->estado === 'Anulado' ? 'opacity-70 bg-rose-950/10' : '' }}">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-extrabold {{ $p->estado === 'Anulado' ? 'line-through text-rose-400' : 'text-gold-400' }}">{{ $p->numero_recibo }}</span>
                                @if ($p->comprobante_referencia)
                                    <div class="text-[11px] text-slate-400 font-mono">Ref: {{ $p->comprobante_referencia }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white font-mono">{{ $p->contrato->numero_contrato ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $p->evento->codigo_evento ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white">{{ $p->cliente->nombre_completo ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-300">
                                {{ $p->fecha_pago->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold 
                                    @if ($p->metodo_pago === 'EFECTIVO') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($p->metodo_pago === 'QR') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @else bg-blue-500/10 text-blue-400 border border-blue-500/20 @endif">
                                    {{ $p->metodo_pago }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold {{ $p->estado === 'Anulado' ? 'line-through text-slate-500' : 'text-emerald-400' }}">
                                Bs {{ number_format($p->monto, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($p->estado === 'Anulado')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30 inline-flex items-center gap-1" 
                                          title="Motivo: {{ $p->motivo_anulacion ?: 'Sin motivo' }} - Anulado por: {{ $p->anuladoPor ? $p->anuladoPor->name : 'Admin' }}">
                                        <i class="fa-solid fa-ban text-[10px]"></i>
                                        <span>Anulado</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Válido</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Ver e Imprimir Recibo Cuarto Oficio -->
                                    <a href="{{ route('comercial.pagos.recibo', $p->codigo_verificacion) }}" target="_blank"
                                       class="p-2 rounded-lg bg-slate-800 text-amber-400 hover:text-slate-950 hover:bg-amber-400 transition-all shadow"
                                       title="Ver e Imprimir Recibo Oficial (Cuarto Oficio)">
                                        <i class="fa-solid fa-print"></i>
                                    </a>

                                    <!-- Descargar Recibo Oficial PDF con QR -->
                                    <a href="{{ route('comercial.pagos.pdf', $p->codigo_verificacion) }}" target="_blank"
                                       class="p-2 rounded-lg bg-slate-800 text-rose-400 hover:text-white hover:bg-rose-600 transition-all shadow"
                                       title="Descargar Recibo Oficial PDF con QR">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    @if($p->estado !== 'Anulado')
                                        @php
                                            $evento = $p->evento ?: ($p->contrato ? $p->contrato->evento : null);
                                            $eventoConcluido = $evento && in_array($evento->estado, ['Realizado', 'Finalizado', 'Concluido']);
                                            $user = auth()->user();
                                            $puedeAnular = ($user->can('gestionar-pagos') || $user->hasRole('Administrador')) && !$eventoConcluido;
                                        @endphp

                                        @if($puedeAnular)
                                            <button wire:click="abrirModalAnular({{ $p->id }})" 
                                                    class="p-2 rounded-lg bg-slate-800 text-rose-400 hover:text-white hover:bg-rose-900/60 transition-all shadow" 
                                                    title="Anular Pago">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @elseif($eventoConcluido)
                                            <span class="p-2 rounded-lg bg-slate-900 text-slate-600 cursor-not-allowed inline-flex items-center" 
                                                  title="No se puede anular: El evento ya ha sido concluido">
                                                <i class="fa-solid fa-lock text-xs"></i>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500 text-sm">
                                No se encontraron registros de pagos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $pagos->links() }}
        </div>
    </div>

    <!-- Modal Nuevo Pago -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 bg-slate-950/80 backdrop-blur-sm">
            <div
                class="bg-brand-card border border-brand-border rounded-none sm:rounded-3xl w-full max-w-xl overflow-hidden shadow-2xl flex flex-col h-full sm:h-auto max-h-screen sm:max-h-[90vh]">
                <div class="flex items-center justify-between border-b border-brand-border px-6 py-4 flex-shrink-0">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-gold-400"></i>
                        <span>Registrar Pago de Cliente</span>
                    </h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">N° Recibo
                            *</label>
                        <input type="text" wire:model="numero_recibo" readonly
                            class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-gold-400 font-mono font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Fecha & Hora
                            Pago *</label>
                        <input type="datetime-local" wire:model="fecha_pago"
                            class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Contrato
                            *</label>
                        <select wire:model.live="contrato_id"
                            class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione Contrato con Saldo Pendiente</option>
                            @foreach ($contratosPendientes as $ctr)
                                <option value="{{ $ctr->id }}">
                                    {{ $ctr->numero_contrato }} - {{ $ctr->cliente->nombre_completo ?? 'N/A' }} (Saldo
                                    Pendiente: Bs {{ number_format($ctr->saldo_pendiente, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('contrato_id')
                            <span class="text-xs text-rose-400 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Monto a
                            Pagar (Bs) *</label>
                        <input type="number" step="0.50" wire:model="monto"
                            class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-emerald-400 font-mono font-bold text-sm focus:border-gold-500">
                        @error('monto')
                            <span class="text-xs text-rose-400 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Método de
                            Pago *</label>
                        <select wire:model="metodo_pago"
                            class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="EFECTIVO">EFECTIVO</option>
                            <option value="QR">QR BANCO</option>
                            <option value="TRANSFERENCIA">TRANSFERENCIA BANCARIA</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Comprobante
                            / N° de Transacción (opcional)</label>
                        <input type="text" wire:model="comprobante_referencia"
                            placeholder="ej. N° de Depósito o referencia QR"
                            class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label
                            class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Observaciones</label>
                        <textarea wire:model="observaciones" rows="2"
                            class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-brand-border px-6 py-4 flex-shrink-0">
                    <button wire:click="$set('modalOpen', false)"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cancelar
                    </button>
                    <button wire:click="guardar" wire:confirm="¿Está seguro de realizar el pago?"
                        class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500">
                        Guardar Pago
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Anular Pago -->
    @if ($modalAnularOpen && $pagoParaAnular)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm animate-fade-in">
            <div class="bg-brand-card border border-rose-500/30 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl space-y-5 p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-400 text-lg">
                            <i class="fa-solid fa-ban"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">
                                Anular Cobro / Recibo
                            </h3>
                            <p class="text-xs text-rose-400 font-mono">
                                Recibo N° {{ $pagoParaAnular->numero_recibo }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="$set('modalAnularOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Advertencia -->
                <div class="bg-rose-500/10 border border-rose-500/20 rounded-2xl p-4 text-xs text-rose-300 space-y-1.5">
                    <p class="font-bold flex items-center gap-1.5 text-rose-400">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>¡ATENCIÓN! Acción irreversible</span>
                    </p>
                    <p class="text-slate-300">
                        Al anular este cobro, el monto de <strong class="text-white">Bs {{ number_format($pagoParaAnular->monto, 2) }}</strong> será deducido del total pagado y devuelto al saldo pendiente del contrato <strong class="text-white">{{ $pagoParaAnular->contrato->numero_contrato ?? 'N/A' }}</strong>.
                    </p>
                    <p class="text-slate-400 text-[11px]">
                        Esta acción registrará fecha, hora y el usuario que autoriza en la bitácora de auditoría.
                    </p>
                </div>

                <!-- Detalle del Pago a Anular -->
                <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-3 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Cliente:</span>
                        <span class="font-bold text-white">{{ $pagoParaAnular->cliente->nombre_completo ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Monto del Pago:</span>
                        <span class="font-bold text-emerald-400 font-mono">Bs {{ number_format($pagoParaAnular->monto, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Fecha Original:</span>
                        <span class="text-slate-300 font-mono">{{ $pagoParaAnular->fecha_pago->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <!-- Campo Motivo de Anulación -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Motivo de la Anulación *
                    </label>
                    <textarea wire:model="motivoAnulacion" rows="3" 
                              placeholder="Indique detalladamente la razón por la que se anula este pago (ej: error de digitación, pago rebotado, etc.)..."
                              class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-rose-500 placeholder-slate-600"></textarea>
                    @error('motivoAnulacion')
                        <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Footer Acciones -->
                <div class="flex items-center justify-end gap-3 border-t border-brand-border pt-4">
                    <button wire:click="$set('modalAnularOpen', false)" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 transition-all">
                        Cancelar
                    </button>
                    <button wire:click="confirmarAnulacion" class="px-5 py-2.5 rounded-xl text-xs font-black bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-ban"></i>
                        <span>Confirmar Anulación</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>