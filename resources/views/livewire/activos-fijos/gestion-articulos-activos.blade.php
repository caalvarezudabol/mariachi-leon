<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Artículos & Productos (Activos Fijos)</h1>
            <p class="text-xs text-slate-400">Control de inventario, equipos individuales y artículos por cantidad (PPP).</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-box-open"></i>
            <span>Nuevo Artículo</span>
        </button>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-brand-card p-4 rounded-2xl border border-brand-border space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar código, nombre, N° serie..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
            </div>

            <div>
                <select wire:model.live="categoria_filtro" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todas las Categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }} ({{ $cat->codigo }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="estado_filtro" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Estados</option>
                    <option value="disponible">Disponible</option>
                    <option value="asignado">Asignado</option>
                    <option value="en_mantenimiento">En Mantenimiento</option>
                    <option value="deteriorado">Deteriorado</option>
                    <option value="perdido">Perdido</option>
                    <option value="dado_de_baja">Dado de Baja</option>
                </select>
            </div>

            <div>
                <select wire:model.live="tipo_control_filtro" class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                    <option value="">Todos los Tipos de Control</option>
                    <option value="individual">Individual (Guitarras, Micrófonos)</option>
                    <option value="cantidad">Por Cantidad (Cables, Accesorios)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Código / Serie</th>
                        <th class="px-6 py-4">Artículo / Categoría</th>
                        <th class="px-6 py-4">Tipo Control</th>
                        <th class="px-6 py-4">Existencia</th>
                        <th class="px-6 py-4">Costo / PPP</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Responsable</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($articulos as $item)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-mono font-bold text-gold-400">{{ $item->codigo }}</div>
                                @if($item->numero_serie)
                                    <div class="text-[11px] font-mono text-slate-400">SN: {{ $item->numero_serie }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ $item->nombre }}</div>
                                <div class="text-xs text-slate-400">{{ $item->category->nombre ?? 'Sin categoría' }}</div>
                                @if($item->marca || $item->modelo)
                                    <div class="text-[11px] text-slate-500">{{ $item->marca }} {{ $item->modelo }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item->tipo_control === 'individual')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        Individual
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                        Por Cantidad
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-200">
                                {{ number_format($item->existencia, 2) }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">
                                @if($item->tipo_control === 'cantidad')
                                    <div class="text-gold-400 font-bold">Bs {{ number_format($item->costo_promedio_ppp, 2) }}</div>
                                    <div class="text-[10px] text-slate-500">PPP Promedio</div>
                                @else
                                    <div class="text-slate-200 font-bold">Bs {{ number_format($item->costo_adquisicion, 2) }}</div>
                                    <div class="text-[10px] text-slate-500">Individual</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @switch($item->estado)
                                    @case('disponible')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Disponible</span>
                                        @break
                                    @case('asignado')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">Asignado</span>
                                        @break
                                    @case('en_mantenimiento')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Mantenimiento</span>
                                        @break
                                    @case('deteriorado')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-500/10 text-orange-400 border border-orange-500/20">Deteriorado</span>
                                        @break
                                    @case('perdido')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">Perdido</span>
                                        @break
                                    @case('dado_de_baja')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-400 border border-slate-700">Dado de Baja</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($item->responsable)
                                    <div class="font-bold text-slate-200"><i class="fa-solid fa-user-check text-emerald-400 mr-1"></i>{{ $item->responsable->nombre_completo }}</div>
                                @else
                                    <span class="text-slate-500">Sin Asignar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button wire:click="editar({{ $item->id }})" class="p-2 text-slate-400 hover:text-gold-400 hover:bg-gold-500/10 rounded-lg transition-colors" title="Editar Artículo">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">No se encontraron artículos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $articulos->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm overflow-y-auto">
            <div class="w-full max-w-2xl bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl my-8">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">{{ $isEdit ? 'Editar Artículo / Producto' : 'Nuevo Artículo / Producto' }}</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Código (AF)</label>
                            <input type="text" wire:model.defer="codigo" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono uppercase" placeholder="AF-00001">
                            @error('codigo') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre del Artículo</label>
                            <input type="text" wire:model.defer="nombre" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Guitarrón de Gala / Cable XLR 10M">
                            @error('nombre') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Categoría</label>
                            <select wire:model.defer="asset_category_id" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="">Seleccione Categoría...</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }} ({{ $cat->codigo }})</option>
                                @endforeach
                            </select>
                            @error('asset_category_id') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Tipo de Control</label>
                            <select wire:model.live="tipo_control" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="individual">Individual (Guitarras, Micrófonos, Consolas)</option>
                                <option value="cantidad">Por Cantidad (Cables, Adaptadores, Insumos)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Marca</label>
                            <input type="text" wire:model.defer="marca" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Shure / Yamaha">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Modelo</label>
                            <input type="text" wire:model.defer="modelo" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. SM58">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">N° de Serie (Opcional)</label>
                            <input type="text" wire:model.defer="numero_serie" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono" placeholder="Ej. SN-998822">
                            @error('numero_serie') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fecha Adquisición</label>
                            <input type="date" wire:model.defer="fecha_adquisicion" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Costo Adquisición (Bs.)</label>
                            <input type="number" step="0.01" wire:model.defer="costo_adquisicion" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('costo_adquisicion') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Existencia / Cantidad</label>
                            <input type="number" step="0.01" wire:model.defer="existencia" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('existencia') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Estado Actual</label>
                            <select wire:model.defer="estado" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="disponible">Disponible</option>
                                <option value="asignado">Asignado</option>
                                <option value="en_mantenimiento">En Mantenimiento</option>
                                <option value="deteriorado">Deteriorado</option>
                                <option value="perdido">Perdido</option>
                                <option value="dado_de_baja">Dado de Baja</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Responsable Actual (Músico / Personal)</label>
                            <select wire:model.defer="responsable_id" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="">Sin Responsable (En almacén)</option>
                                @foreach($responsables as $resp)
                                    <option value="{{ $resp->id }}">{{ $resp->nombre_completo }} ({{ $resp->tipo }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Descripción / Observaciones</label>
                        <textarea wire:model.defer="observaciones" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Detalles de condición, estuche, accesorios u observaciones de compra..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20">Guardar Artículo</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
