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
    @if (session()->has('success'))
        <div
            class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative lg:col-span-2">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
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
        </div>
    </div>

    <!-- Table -->
    <div class="bg-brand-card rounded-2xl border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr
                        class="bg-slate-950/80 border-b border-brand-border text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3.5 px-4 font-bold">N° Recibo</th>
                        <th class="py-3.5 px-4 font-bold">Contrato / Evento</th>
                        <th class="py-3.5 px-4 font-bold">Cliente</th>
                        <th class="py-3.5 px-4 font-bold">Fecha Pago</th>
                        <th class="py-3.5 px-4 font-bold text-center">Método</th>
                        <th class="py-3.5 px-4 font-bold text-right">Monto Pagado (Bs)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/50 text-slate-300">
                    @forelse($pagos as $p)
                        <tr class="hover:bg-slate-800/30 transition-all">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-extrabold text-gold-400">{{ $p->numero_recibo }}</span>
                                @if ($p->comprobante_referencia)
                                    <div class="text-[11px] text-slate-400 font-mono">Ref:
                                        {{ $p->comprobante_referencia }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white font-mono">{{ $p->contrato->numero_contrato ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-slate-400 font-mono">{{ $p->evento->codigo_evento ?? '' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white">{{ $p->cliente->nombre_completo ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-300">
                                {{ $p->fecha_pago->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-bold 
                                    @if ($p->metodo_pago === 'EFECTIVO') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($p->metodo_pago === 'QR') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @else bg-blue-500/10 text-blue-400 border border-blue-500/20 @endif">
                                    {{ $p->metodo_pago }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                                Bs {{ number_format($p->monto, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-500 text-sm">
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
                    <button wire:click="guardar"
                        class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500">
                        Guardar Pago
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>