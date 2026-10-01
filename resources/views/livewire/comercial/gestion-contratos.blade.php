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

                    <div x-data="{
                        open: false,
                        selectedDate: @entangle('fecha_contrato').live,
                        currentYear: new Date().getFullYear(),
                        currentMonth: new Date().getMonth(),
                        months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                        weekdays: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                        init() {
                            if (this.selectedDate) {
                                const parts = this.selectedDate.split('-');
                                if (parts.length === 3) {
                                    this.currentYear = parseInt(parts[0]);
                                    this.currentMonth = parseInt(parts[1]) - 1;
                                }
                            }
                            this.$watch('selectedDate', (val) => {
                                if (val) {
                                    const parts = val.split('-');
                                    if (parts.length === 3) {
                                        this.currentYear = parseInt(parts[0]);
                                        this.currentMonth = parseInt(parts[1]) - 1;
                                    }
                                }
                            });
                        },
                        get daysInMonth() {
                            return new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                        },
                        get firstDayOfWeek() {
                            return new Date(this.currentYear, this.currentMonth, 1).getDay();
                        },
                        get formattedDisplay() {
                            if (!this.selectedDate) return 'Seleccionar Fecha';
                            const parts = this.selectedDate.split('-');
                            if (parts.length !== 3) return this.selectedDate;
                            return `${parts[2]}/${parts[1]}/${parts[0]}`;
                        },
                        selectDay(day) {
                            const m = String(this.currentMonth + 1).padStart(2, '0');
                            const d = String(day).padStart(2, '0');
                            this.selectedDate = `${this.currentYear}-${m}-${d}`;
                            $wire.set('fecha_contrato', this.selectedDate);
                            this.open = false;
                        },
                        prevMonth() {
                            if (this.currentMonth === 0) {
                                this.currentMonth = 11;
                                this.currentYear--;
                            } else {
                                this.currentMonth--;
                            }
                        },
                        nextMonth() {
                            if (this.currentMonth === 11) {
                                this.currentMonth = 0;
                                this.currentYear++;
                            } else {
                                this.currentMonth++;
                            }
                        },
                        selectToday() {
                            const today = new Date();
                            this.currentYear = today.getFullYear();
                            this.currentMonth = today.getMonth();
                            this.selectDay(today.getDate());
                        },
                        isToday(day) {
                            const today = new Date();
                            return today.getFullYear() === this.currentYear && 
                                   today.getMonth() === this.currentMonth && 
                                   today.getDate() === day;
                        },
                        isSelected(day) {
                            if (!this.selectedDate) return false;
                            const m = String(this.currentMonth + 1).padStart(2, '0');
                            const d = String(day).padStart(2, '0');
                            return this.selectedDate === `${this.currentYear}-${m}-${d}`;
                        }
                    }" @click.outside="open = false">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Fecha del Contrato *</label>

                        <div class="relative">
                            <button type="button" 
                                    @click="open = !open" 
                                    class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm text-left flex items-center justify-between hover:border-gold-500/60 transition-all cursor-pointer">
                                <span class="font-mono font-bold" :class="selectedDate ? 'text-gold-400' : 'text-slate-400'" x-text="formattedDisplay"></span>
                                <i class="fa-solid fa-calendar-days text-gold-400 text-sm"></i>
                            </button>

                            <!-- Desplegable de Calendario Visual -->
                            <div x-show="open" 
                                 x-cloak 
                                 x-transition 
                                 class="absolute left-0 top-full mt-1 z-50 w-72 p-3 bg-slate-900 border border-gold-500/50 rounded-2xl shadow-2xl space-y-3">
                                
                                <!-- Navegación de Mes y Año -->
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <button type="button" @click="prevMonth()" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                    </button>
                                    <div class="text-xs font-bold text-white flex items-center gap-1">
                                        <span x-text="months[currentMonth]"></span>
                                        <span class="text-gold-400" x-text="currentYear"></span>
                                    </div>
                                    <button type="button" @click="nextMonth()" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </button>
                                </div>

                                <!-- Encabezado Días de la Semana -->
                                <div class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 uppercase">
                                    <template x-for="w in weekdays" :key="w">
                                        <div x-text="w"></div>
                                    </template>
                                </div>

                                <!-- Cuadrícula de Días -->
                                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                    <!-- Espacios vacíos al inicio del mes -->
                                    <template x-for="blank in firstDayOfWeek" :key="'b' + blank">
                                        <div></div>
                                    </template>

                                    <!-- Días del mes -->
                                    <template x-for="d in daysInMonth" :key="d">
                                        <button type="button" 
                                                @click="selectDay(d)" 
                                                :class="{
                                                    'bg-gold-500 text-slate-950 font-extrabold shadow-md': isSelected(d),
                                                    'border border-gold-500/40 text-gold-300 font-bold': isToday(d) && !isSelected(d),
                                                    'text-slate-200 hover:bg-slate-800 hover:text-white': !isSelected(d) && !isToday(d)
                                                }" 
                                                class="h-7 w-7 mx-auto rounded-lg flex items-center justify-center transition-all">
                                            <span x-text="d"></span>
                                        </button>
                                    </template>
                                </div>

                                <!-- Acciones Rápidas -->
                                <div class="flex items-center justify-between border-t border-slate-800 pt-2 text-[11px]">
                                    <button type="button" @click="selectToday()" class="text-gold-400 hover:underline font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-calendar-day"></i> Hoy
                                    </button>
                                    <button type="button" @click="open = false" class="text-slate-400 hover:text-white">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                        @error('fecha_contrato') <span class="text-xs text-rose-400 mt-1">{{ $message }}</span> @enderror
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
