<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard de Activos Fijos & Bienes</h1>
            <p class="text-xs text-slate-400">Resumen ejecutivo del inventario, valorización total (PPP), asignaciones y estados.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.activos-fijos.articulos') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-box-open"></i>
                <span>Ver Todos los Artículos</span>
            </a>
        </div>
    </div>

    <!-- Valor Total Inventario Highlight Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-slate-950 p-6 rounded-3xl border border-gold-500/30 relative overflow-hidden shadow-2xl">
        <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 w-64 h-64 bg-gold-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
            <div class="space-y-1">
                <span class="text-xs uppercase font-bold text-gold-400 tracking-wider">Valor Total del Inventario (Moneda Oficial Bs.)</span>
                <div class="text-3xl sm:text-4xl font-extrabold font-mono text-white">
                    Bs {{ number_format($valorTotal, 2) }}
                </div>
                <p class="text-xs text-slate-400">Valoración calculada mediante Precio Promedio Ponderado (PPP) y Costo de Adquisición Individual.</p>
            </div>
            <div class="p-4 bg-slate-950/80 rounded-2xl border border-gold-500/30 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gold-500/20 text-gold-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-400">Total de Artículos</div>
                    <div class="text-2xl font-bold font-mono text-white">{{ $totalArticulos }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Disponibles -->
        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Disponibles</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-emerald-400">{{ $disponibles }}</div>
            <div class="text-[11px] text-slate-500">En almacén</div>
        </div>

        <!-- Asignados -->
        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Asignados</span>
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-blue-400">{{ $asignados }}</div>
            <div class="text-[11px] text-slate-500">En posesión músico</div>
        </div>

        <!-- En Mantenimiento -->
        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Mantenimiento</span>
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-amber-400">{{ $enMantenimiento }}</div>
            <div class="text-[11px] text-slate-500">En reparación</div>
        </div>

        <!-- Deteriorados -->
        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Deteriorados</span>
                <span class="w-2 h-2 rounded-full bg-orange-400"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-orange-400">{{ $deteriorados }}</div>
            <div class="text-[11px] text-slate-500">Requiere revisión</div>
        </div>

        <!-- Perdidos -->
        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Perdidos</span>
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-rose-400">{{ $perdidos }}</div>
            <div class="text-[11px] text-slate-500">No localizado</div>
        </div>

        <!-- Bajas -->
        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Dados de Baja</span>
                <span class="w-2 h-2 rounded-full bg-slate-500"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-400">{{ $dadosDeBaja }}</div>
            <div class="text-[11px] text-slate-500">Desincorporados</div>
        </div>
    </div>

    <!-- Recent Activity & Categories Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Movements -->
        <div class="lg:col-span-2 bg-brand-card p-6 rounded-3xl border border-brand-border space-y-4">
            <div class="flex items-center justify-between border-b border-brand-border pb-4">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-gold-400"></i>
                    <span>Últimos Movimientos del Kardex</span>
                </h3>
                <a href="{{ route('admin.activos-fijos.kardex') }}" class="text-xs font-bold text-gold-400 hover:text-gold-300">Ver Todo el Kardex &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse($ultimosMovimientos as $mov)
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            @if($mov->tipo_movimiento === 'entrada')
                                <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-arrow-down"></i>
                                </div>
                            @else
                                <div class="w-9 h-9 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-arrow-up"></i>
                                </div>
                            @endif
                            <div>
                                <div class="font-bold text-white text-xs">{{ $mov->asset->nombre ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $mov->asset->codigo ?? '' }} • Motivo: <span class="text-gold-400 uppercase font-semibold">{{ str_replace('_', ' ', $mov->motivo) }}</span></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-bold text-xs {{ $mov->tipo_movimiento === 'entrada' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $mov->tipo_movimiento === 'entrada' ? '+' : '-' }}{{ number_format($mov->cantidad, 2) }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $mov->fecha_movimiento->format('d/m H:i') }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Sin movimientos recientes.</p>
                @endforelse
            </div>
        </div>

        <!-- Categories Summary -->
        <div class="bg-brand-card p-6 rounded-3xl border border-brand-border space-y-4">
            <div class="flex items-center justify-between border-b border-brand-border pb-4">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-folder text-gold-400"></i>
                    <span>Categorías Registradas</span>
                </h3>
                <a href="{{ route('admin.activos-fijos.categorias') }}" class="text-xs font-bold text-gold-400 hover:text-gold-300">Gestionar &rarr;</a>
            </div>

            <div class="space-y-2">
                @forelse($categorias as $cat)
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white text-xs">{{ $cat->nombre }}</div>
                            <div class="text-[10px] font-mono text-gold-400">{{ $cat->codigo }}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-gold-500/10 text-gold-400 border border-gold-500/20">
                            {{ $cat->assets_count }} ítems
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Sin categorías registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
