<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Ingresos & Compras (Kardex Entrada)</h1>
            <p class="text-xs text-slate-400">Registro de adquisiciones, donaciones, reposiciones y ajustes positivos de inventario.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-emerald-500 to-emerald-600 text-slate-950 hover:from-emerald-400 hover:to-emerald-500 shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-arrow-down-to-bracket"></i>
            <span>Registrar Nuevo Ingreso</span>
        </button>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Fecha / Hora</th>
                        <th class="px-6 py-4">Artículo / Código</th>
                        <th class="px-6 py-4">Motivo</th>
                        <th class="px-6 py-4">Cantidad Ingresada</th>
                        <th class="px-6 py-4">Costo Unit. (Bs)</th>
                        <th class="px-6 py-4">Total Ingreso</th>
                        <th class="px-6 py-4">Saldo Inv. Post-Ingreso</th>
                        <th class="px-6 py-4">PPP Post-Ingreso</th>
                        <th class="px-6 py-4">Registrado Por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($ingresos as $mov)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono text-slate-300">
                                {{ $mov->fecha_movimiento->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $mov->asset->nombre ?? 'N/A' }}</div>
                                <div class="text-xs font-mono text-gold-400">{{ $mov->asset->codigo ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">
                                    {{ str_replace('_', ' ', $mov->motivo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-emerald-400">
                                +{{ number_format($mov->cantidad, 2) }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-200">
                                Bs {{ number_format($mov->costo_unitario, 2) }}
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-100">
                                Bs {{ number_format($mov->costo_total, 2) }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-300">
                                {{ number_format($mov->cantidad_saldo, 2) }} und.
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-gold-400">
                                Bs {{ number_format($mov->costo_ppp_saldo, 2) }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                <i class="fa-solid fa-user-check text-emerald-400 mr-1"></i>{{ $mov->user->name ?? 'Sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-slate-500">No se registran movimientos de ingreso.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $ingresos->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-xl bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">Registrar Ingreso de Inventario / Activo</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Seleccionar Artículo / Producto</label>
                        <select wire:model.live="asset_id" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            <option value="">Seleccione Artículo...</option>
                            @foreach($articulos as $item)
                                <option value="{{ $item->id }}">[{{ $item->codigo }}] {{ $item->nombre }} - (Stock: {{ number_format($item->existencia, 2) }})</option>
                            @endforeach
                        </select>
                        @error('asset_id') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fecha y Hora de Ingreso</label>
                            <input type="datetime-local" wire:model.defer="fecha_movimiento" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('fecha_movimiento') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Motivo de Ingreso</label>
                            <select wire:model.defer="motivo" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="compra">Compra Directa</option>
                                <option value="donacion">Donación</option>
                                <option value="devolucion">Devolución Excepcional</option>
                                <option value="reposicion">Reposición</option>
                                <option value="ajuste_positivo">Ajuste de Inventario (+)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Cantidad a Ingresar</label>
                            <input type="number" step="0.01" wire:model.defer="cantidad" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('cantidad') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Costo Unitario de Ingreso (Bs.)</label>
                            <input type="number" step="0.01" wire:model.defer="costo_unitario" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('costo_unitario') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Documento / Factura / Nota de Referencia</label>
                        <input type="text" wire:model.defer="documento_referencia" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Factura N° 4588 / Nota Recibo 12">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Observaciones</label>
                        <textarea wire:model.defer="observaciones" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Detalles de proveedor, estado o justificación del ingreso..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-400 shadow-lg shadow-emerald-500/20">Procesar Ingreso</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
