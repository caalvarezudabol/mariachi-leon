<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestionar Contratos</h1>
            <p class="text-xs text-slate-400">Contratos formales de presentación, pagos iniciales/señas y saldos pendientes.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-file-contract"></i>
            <span>Nuevo Contrato</span>
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
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar N° contrato, cliente o evento..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
            </div>

            <div>
                <select wire:model.live="estado_filtro" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Estados</option>
                    <option value="Borrador">Borrador</option>
                    <option value="Pendiente de Confirmacion">Pendiente de Confirmación</option>
                    <option value="Confirmado">Confirmado</option>
                    <option value="En Ejecucion">En Ejecución</option>
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
                        <th class="py-3.5 px-4 font-bold">N° Contrato</th>
                        <th class="py-3.5 px-4 font-bold">Cliente / Evento</th>
                        <th class="py-3.5 px-4 font-bold">Fecha / Hora</th>
                        <th class="py-3.5 px-4 font-bold text-right">Monto Total</th>
                        <th class="py-3.5 px-4 font-bold text-right">Pago Inicial / Abonado</th>
                        <th class="py-3.5 px-4 font-bold text-right">Saldo Pendiente</th>
                        <th class="py-3.5 px-4 font-bold text-center">Estado</th>
                        <th class="py-3.5 px-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/50 text-slate-300">
                    @forelse($contratos as $ctr)
                        <tr class="hover:bg-slate-800/30 transition-all">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-extrabold text-gold-400">{{ $ctr->numero_contrato }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white">{{ $ctr->cliente->nombre_completo ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $ctr->evento->codigo_evento ?? '' }} &bull; {{ $ctr->servicio->nombre ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs">
                                <div class="font-bold text-white">{{ $ctr->fecha_contrato->format('d/m/Y') }}</div>
                                <div class="text-slate-400">{{ $ctr->hora_contrato ?: '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-white">
                                Bs {{ number_format($ctr->monto_total, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-emerald-400">
                                Bs {{ number_format($ctr->total_pagado, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold {{ $ctr->saldo_pendiente > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                Bs {{ number_format($ctr->saldo_pendiente, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold 
                                    @if($ctr->estado === 'Confirmado' || $ctr->estado === 'Realizado' || $ctr->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($ctr->estado === 'Pendiente de Confirmacion' || $ctr->estado === 'En Ejecucion') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($ctr->estado === 'Cancelado') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                    {{ $ctr->estado }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('admin.contratos.pdf', $ctr->id) }}" target="_blank" class="p-2 rounded-lg bg-rose-600/20 text-rose-400 hover:text-white hover:bg-rose-600 transition-all inline-block" title="Imprimir Contrato PDF">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <button wire:click="editar({{ $ctr->id }})" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all" title="Editar Contrato">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500 text-sm">
                                No se encontraron contratos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $contratos->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar Contrato -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl space-y-6 p-6">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-file-contract text-gold-400"></i>
                        <span>{{ $isEdit ? 'Editar Contrato ' . $numero_contrato : 'Nuevo Contrato' }}</span>
                    </h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">N° Contrato *</label>
                        <input type="text" wire:model="numero_contrato" readonly class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-gold-400 font-mono font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Fecha del Contrato *</label>
                        <input type="date" wire:model="fecha_contrato" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
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
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Monto Total del Contrato (Bs) *</label>
                        <input type="number" step="0.50" wire:model.live="monto_total" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-bold text-sm focus:border-gold-500">
                        @error('monto_total') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Pago Inicial / Seña (Bs) *</label>
                        <input type="number" step="0.50" wire:model.live="pago_inicial" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-emerald-400 font-mono font-bold text-sm focus:border-gold-500">
                        <span class="text-[11px] text-slate-400">Puede ser parcial o el 100% del total.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Total Abonado (Bs)</label>
                        <input type="number" step="0.50" wire:model="total_pagado" readonly class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-emerald-400 font-mono font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Saldo Pendiente (Bs)</label>
                        <input type="number" step="0.50" wire:model="saldo_pendiente" readonly class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-rose-400 font-mono font-extrabold text-base">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Estado del Contrato</label>
                        <select wire:model="estado" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="Borrador">Borrador</option>
                            <option value="Pendiente de Confirmacion">Pendiente de Confirmación</option>
                            <option value="Confirmado">Confirmado</option>
                            <option value="En Ejecucion">En Ejecución</option>
                            <option value="Realizado">Realizado</option>
                            <option value="Cancelado">Cancelado</option>
                            <option value="Finalizado">Finalizado</option>
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
                        {{ $isEdit ? 'Actualizar Contrato' : 'Guardar Contrato' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
