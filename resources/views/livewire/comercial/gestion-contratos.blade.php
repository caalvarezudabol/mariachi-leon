<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestionar Contratos</h1>
            <p class="text-xs text-slate-400">Contratos formales de presentación, pagos iniciales/señas y saldos
                pendientes.</p>
        </div>
        <button wire:click="abrirModal"
            class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-file-contract"></i>
            <span>Nuevo Contrato</span>
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
                    placeholder="Buscar N° contrato, cliente o evento..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
            </div>

            <div>
                <select wire:model.live="estado_filtro"
                    class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
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
                    <tr
                        class="bg-slate-950/80 border-b border-brand-border text-slate-400 text-xs uppercase tracking-wider">
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
                                <a href="{{ route('admin.contratos.pdf', $ctr->codigo_verificacion) }}" target="_blank"
                                   class="font-mono font-extrabold text-gold-400 hover:text-gold-300 transition-colors inline-block"
                                   title="Ver Contrato Oficial en PDF con QR">
                                    {{ $ctr->numero_contrato }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $clienteIdCtr = $ctr->cliente_id;
                                    $clienteTokenCtr = $clienteIdCtr ? \App\Services\Security\DocumentToken::encodeId($clienteIdCtr, 'cliente') : null;
                                    $ctrTokenCtr = \App\Services\Security\DocumentToken::encodeId($ctr->id, 'contrato');
                                @endphp
                                @if($clienteTokenCtr)
                                    <a href="{{ route('admin.clientes.historial-contratos.pdf', $clienteTokenCtr) }}" target="_blank"
                                       class="font-bold text-white hover:text-gold-400 transition-colors inline-block"
                                       title="Ver Historial de Contratos y Eventos del Cliente en PDF con QR">
                                        {{ $ctr->cliente->nombre_completo ?? 'N/A' }}
                                    </a>
                                @else
                                    <div class="font-bold text-white">{{ $ctr->cliente->nombre_completo ?? 'N/A' }}</div>
                                @endif
                                <div class="text-xs text-slate-400 font-mono mt-0.5">
                                    @if($clienteTokenCtr && $ctr->evento)
                                        <a href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenCtr, 'contrato' => $ctrTokenCtr, 'tipo' => 'contrato']) }}" target="_blank"
                                           class="text-gold-400/80 hover:text-gold-300 transition-colors"
                                           title="Ver Pagos de este Evento en PDF con QR">
                                            {{ $ctr->evento->codigo_evento }}
                                        </a>
                                    @else
                                        <span>{{ $ctr->evento->codigo_evento ?? '' }}</span>
                                    @endif
                                    &bull; {{ $ctr->servicio->nombre ?? '' }}
                                </div>
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
                            <td
                                class="py-3.5 px-4 text-right font-mono font-bold {{ $ctr->saldo_pendiente > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                Bs {{ number_format($ctr->saldo_pendiente, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-bold 
                                    @if ($ctr->estado === 'Confirmado' || $ctr->estado === 'Realizado' || $ctr->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($ctr->estado === 'Pendiente de Confirmacion' || $ctr->estado === 'En Ejecucion') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($ctr->estado === 'Cancelado') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                    {{ $ctr->estado }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                <!-- Ver Contrato Oficial en PDF -->
                                <a href="{{ route('admin.contratos.pdf', $ctr->codigo_verificacion) }}" target="_blank"
                                    class="p-2 rounded-lg bg-rose-600/20 text-rose-400 hover:text-white hover:bg-rose-600 transition-all inline-block shadow-sm"
                                    title="Ver Contrato Oficial en PDF con Cláusulas y QR">
                                    <i class="fa-solid fa-file-contract"></i>
                                </a>

                                <!-- Ver Pagos del Evento en Modal Interactivo -->
                                <button wire:click="abrirHistorialPagos({{ $ctr->id }})"
                                    class="p-2 rounded-lg bg-gold-500/10 text-gold-400 hover:text-slate-950 hover:bg-gold-500 border border-gold-500/20 transition-all inline-block shadow-sm"
                                    title="Ver Pagos del Cliente y Contrato por Evento">
                                    <i class="fa-solid fa-receipt"></i>
                                </button>

                                <!-- Ver Comisiones a Músicos y Personal por Evento -->
                                @if($ctr->evento)
                                    @php
                                        $ctrEvTok = \App\Services\Security\DocumentToken::encodeId($ctr->evento->id, 'evento');
                                    @endphp
                                    <button wire:click="abrirComisiones({{ $ctr->id }})"
                                        class="p-2 rounded-lg bg-sky-500/10 text-sky-400 hover:text-white hover:bg-sky-600 border border-sky-500/20 transition-all inline-block shadow-sm"
                                        title="Ver Comisiones a Músicos y Personal del Evento ({{ $ctr->evento->participantes->count() }} integrantes)">
                                        <i class="fa-solid fa-users-viewfinder"></i>
                                    </button>

                                    <!-- Planilla de Comisiones en PDF con QR -->
                                    <a href="{{ route('admin.eventos.comisiones.pdf', $ctrEvTok) }}" target="_blank"
                                        class="p-2 rounded-lg bg-amber-500/10 text-amber-400 hover:text-slate-950 hover:bg-amber-500 border border-amber-500/20 transition-all inline-block shadow-sm"
                                        title="Descargar Planilla Oficial de Comisiones y Liquidación a Músicos en PDF con QR">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </a>
                                @endif

                                <!-- Ver Extracto de Pagos del Contrato/Evento en PDF con QR -->
                                @if($clienteTokenCtr)
                                    <a href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $clienteTokenCtr, 'contrato' => $ctrTokenCtr, 'tipo' => 'contrato']) }}" target="_blank"
                                        class="p-2 rounded-lg bg-slate-800 text-amber-400 hover:text-white hover:bg-amber-600 transition-all inline-block shadow-sm"
                                        title="Descargar Extracto de Pagos de este Evento/Contrato en PDF con QR">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    <!-- Ver Historial de Contratos y Pagos del Cliente por Evento en PDF -->
                                    <a href="{{ route('admin.clientes.historial-contratos.pdf', $clienteTokenCtr) }}" target="_blank"
                                        class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 hover:text-white hover:bg-emerald-600 border border-emerald-500/20 transition-all inline-block shadow-sm"
                                        title="Ver Historial de Contratos y Eventos del Cliente en PDF con QR">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </a>
                                @endif

                                <button wire:click="editar({{ $ctr->id }})"
                                    class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all inline-block"
                                    title="Editar Contrato">
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
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div
                class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border px-6 py-4 shrink-0">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-file-contract text-gold-400"></i>
                        <span>{{ $isEdit ? 'Editar Contrato ' . $numero_contrato : 'Nuevo Contrato' }}</span>
                    </h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="overflow-y-auto px-6 py-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">N°
                                Contrato *</label>
                            <input type="text" wire:model="numero_contrato" readonly
                                class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-gold-400 font-mono font-bold text-sm">
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
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Fecha
                                del Contrato *</label>

                            <div class="relative">
                                <button type="button" @click="open = !open"
                                    class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm text-left flex items-center justify-between hover:border-gold-500/60 transition-all cursor-pointer">
                                    <span class="font-mono font-bold"
                                        :class="selectedDate ? 'text-gold-400' : 'text-slate-400'"
                                        x-text="formattedDisplay"></span>
                                    <i class="fa-solid fa-calendar-days text-gold-400 text-sm"></i>
                                </button>

                                <!-- Desplegable de Calendario Visual -->
                                <div x-show="open" x-cloak x-transition
                                    class="absolute left-0 top-full mt-1 z-50 w-72 p-3 bg-slate-900 border border-gold-500/50 rounded-2xl shadow-2xl space-y-3">

                                    <!-- Navegación de Mes y Año -->
                                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                        <button type="button" @click="prevMonth()"
                                            class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                                            <i class="fa-solid fa-chevron-left text-xs"></i>
                                        </button>
                                        <div class="text-xs font-bold text-white flex items-center gap-1">
                                            <span x-text="months[currentMonth]"></span>
                                            <span class="text-gold-400" x-text="currentYear"></span>
                                        </div>
                                        <button type="button" @click="nextMonth()"
                                            class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                                            <i class="fa-solid fa-chevron-right text-xs"></i>
                                        </button>
                                    </div>

                                    <!-- Encabezado Días de la Semana -->
                                    <div
                                        class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 uppercase">
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
                                            <button type="button" @click="selectDay(d)"
                                                :class="{
                                                    'bg-gold-500 text-slate-950 font-extrabold shadow-md': isSelected(
                                                        d),
                                                    'border border-gold-500/40 text-gold-300 font-bold': isToday(d) && !
                                                        isSelected(d),
                                                    'text-slate-200 hover:bg-slate-800 hover:text-white': !isSelected(
                                                        d) && !isToday(d)
                                                }"
                                                class="h-7 w-7 mx-auto rounded-lg flex items-center justify-center transition-all">
                                                <span x-text="d"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Acciones Rápidas -->
                                    <div
                                        class="flex items-center justify-between border-t border-slate-800 pt-2 text-[11px]">
                                        <button type="button" @click="selectToday()"
                                            class="text-gold-400 hover:underline font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-calendar-day"></i> Hoy
                                        </button>
                                        <button type="button" @click="open = false"
                                            class="text-slate-400 hover:text-white">
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('fecha_contrato')
                                <span class="text-xs text-rose-400 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Evento
                                Relacionado *</label>
                            <select wire:model.live="evento_id"
                                class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="">Seleccione Evento</option>
                                @foreach ($eventos as $ev)
                                    <option value="{{ $ev->id }}">
                                        {{ $ev->codigo_evento }} - {{ $ev->cliente->nombre_completo ?? 'N/A' }}
                                        ({{ $ev->servicio->nombre ?? 'N/A' }} -
                                        {{ $ev->fecha_evento->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('evento_id')
                                <span class="text-xs text-rose-400 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Monto
                                Total del Contrato (Bs) *</label>
                            <input type="number" step="0.50" wire:model.live="monto_total"
                                class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-bold text-sm focus:border-gold-500">
                            @error('monto_total')
                                <span class="text-xs text-rose-400 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Pago
                                Inicial / Seña (Bs) *</label>
                            <input type="number" step="0.50" wire:model.live="pago_inicial"
                                class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-emerald-400 font-mono font-bold text-sm focus:border-gold-500">
                            <span class="text-[11px] text-slate-400">Puede ser parcial o el 100% del total.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Total
                                Abonado (Bs)</label>
                            <input type="number" step="0.50" wire:model="total_pagado" readonly
                                class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-emerald-400 font-mono font-bold text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Saldo
                                Pendiente (Bs)</label>
                            <input type="number" step="0.50" wire:model="saldo_pendiente" readonly
                                class="w-full px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-rose-400 font-mono font-extrabold text-base">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Estado
                                del Contrato</label>
                            <select wire:model="estado"
                                class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
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
                            <label
                                class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Observaciones</label>
                            <textarea wire:model="observaciones" rows="2"
                                class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-brand-border px-6 py-4 shrink-0">
                    <button wire:click="$set('modalOpen', false)"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                        class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500">
                        {{ $isEdit ? 'Actualizar Contrato' : 'Guardar Contrato' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Historial de Pagos y Contrato por Evento -->
    @if ($modalHistorialPagosOpen && $contratoHistorial)
        @php
            $cTokenModal = $contratoHistorial->cliente_id ? \App\Services\Security\DocumentToken::encodeId($contratoHistorial->cliente_id, 'cliente') : null;
            $ctrTokenModal = \App\Services\Security\DocumentToken::encodeId($contratoHistorial->id, 'contrato');
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md animate-fade-in">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-4xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-brand-border px-6 py-4 shrink-0 bg-slate-950/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-400 text-lg shadow-sm">
                            <i class="fa-solid fa-file-contract"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-bold text-white">Contrato y Pagos del Evento</h3>
                                <span class="font-mono font-extrabold text-gold-400 bg-gold-500/10 border border-gold-500/20 px-2 py-0.5 rounded-md text-xs">
                                    {{ $contratoHistorial->numero_contrato }}
                                </span>
                                @if($contratoHistorial->evento)
                                    <span class="font-mono font-bold text-sky-400 bg-sky-500/10 border border-sky-500/20 px-2 py-0.5 rounded-md text-xs">
                                        <i class="fa-solid fa-calendar-day mr-1"></i>{{ $contratoHistorial->evento->codigo_evento }}
                                    </span>
                                @endif
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold 
                                    @if ($contratoHistorial->estado === 'Confirmado' || $contratoHistorial->estado === 'Realizado' || $contratoHistorial->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($contratoHistorial->estado === 'Pendiente de Confirmacion' || $contratoHistorial->estado === 'En Ejecucion') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($contratoHistorial->estado === 'Cancelado') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                    {{ $contratoHistorial->estado }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 flex items-center gap-2 mt-1 flex-wrap">
                                <span><i class="fa-solid fa-user text-slate-500 mr-1"></i>{{ $contratoHistorial->cliente->nombre_completo ?? 'Cliente N/A' }}</span>
                                <span class="text-slate-600">•</span>
                                <span><i class="fa-solid fa-music text-slate-500 mr-1"></i>{{ $contratoHistorial->servicio->nombre ?? 'Servicio General' }}</span>
                                @if($contratoHistorial->evento && $contratoHistorial->evento->lugar)
                                    <span class="text-slate-600">•</span>
                                    <span><i class="fa-solid fa-location-dot text-slate-500 mr-1"></i>{{ $contratoHistorial->evento->lugar }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Ver Contrato Oficial en PDF con QR -->
                        <a href="{{ route('admin.contratos.pdf', $contratoHistorial->codigo_verificacion) }}" 
                           target="_blank"
                           class="hidden sm:inline-flex px-3 py-1.5 rounded-xl bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white font-bold text-xs border border-rose-500/20 transition-all items-center gap-1.5 shadow-sm" 
                           title="Ver Contrato Oficial en PDF con Cláusulas y QR">
                            <i class="fa-solid fa-file-contract"></i>
                            <span>Contrato PDF</span>
                        </a>

                        @if($cTokenModal)
                            <a href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $cTokenModal, 'contrato' => $ctrTokenModal, 'tipo' => 'contrato']) }}" 
                               target="_blank"
                               class="hidden sm:inline-flex px-3 py-1.5 rounded-xl bg-gold-500/10 hover:bg-gold-500 text-gold-400 hover:text-slate-950 font-bold text-xs border border-gold-500/20 transition-all items-center gap-1.5 shadow-sm" 
                               title="Ver Extracto de Pagos en PDF con QR">
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>Extracto PDF</span>
                            </a>
                        @endif

                        <button wire:click="cerrarHistorialPagos" class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800 transition-colors text-lg" title="Cerrar">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Body (Scrollable) -->
                <div class="overflow-y-auto p-6 space-y-6">
                    <!-- Tarjetas de Resumen Financiero -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Total Contratado -->
                        <div class="bg-slate-900/90 border border-brand-border rounded-2xl p-4 flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-lg shrink-0">
                                <i class="fa-solid fa-handshake"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Monto Contratado</span>
                                <span class="text-lg font-extrabold font-mono text-white">
                                    Bs {{ number_format($resumenFinanciero['monto_total'], 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Total Abonado -->
                        <div class="bg-slate-900/90 border border-brand-border rounded-2xl p-4 flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-lg shrink-0">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Total Abonado</span>
                                <span class="text-lg font-extrabold font-mono text-emerald-400">
                                    Bs {{ number_format($resumenFinanciero['total_abonado'], 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Saldo Pendiente -->
                        <div class="bg-slate-900/90 border border-brand-border rounded-2xl p-4 flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'bg-rose-500/10 border-rose-500/20 text-rose-400' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' }} border flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'fa-hourglass-half' : 'fa-check-double' }}"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Saldo Pendiente</span>
                                <span class="text-lg font-extrabold font-mono {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                    Bs {{ number_format($resumenFinanciero['saldo_pendiente'], 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Evento y Contrato Vinculado -->
                    <div class="bg-slate-900/60 rounded-2xl border border-brand-border p-4 text-xs grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Fecha del Evento</span>
                            <div class="font-bold text-white font-mono flex items-center gap-1.5">
                                <i class="fa-solid fa-calendar-check text-gold-400"></i>
                                @if($contratoHistorial->evento)
                                    {{ $contratoHistorial->evento->fecha_evento ? $contratoHistorial->evento->fecha_evento->format('d/m/Y') : '-' }} 
                                    ({{ $contratoHistorial->evento->hora_inicio ? \Carbon\Carbon::parse($contratoHistorial->evento->hora_inicio)->format('H:i') : '' }})
                                @else
                                    <span class="text-slate-400">Sin evento asignado</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Lugar / Ubicación</span>
                            <div class="font-medium text-slate-200 truncate" title="{{ $contratoHistorial->evento->lugar ?? 'N/A' }}">
                                <i class="fa-solid fa-map-pin text-rose-400 mr-1"></i>
                                {{ $contratoHistorial->evento->lugar ?? 'No especificado' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Fecha Firma Contrato</span>
                            <div class="font-bold text-slate-200 font-mono flex items-center gap-1.5">
                                <i class="fa-solid fa-signature text-emerald-400"></i>
                                {{ $contratoHistorial->fecha_contrato ? $contratoHistorial->fecha_contrato->format('d/m/Y') : '-' }} {{ $contratoHistorial->hora_contrato }}
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Historial de Pagos del Evento -->
                    <div class="bg-slate-950/60 rounded-2xl border border-brand-border overflow-hidden">
                        <div class="px-4 py-3 bg-slate-900/70 border-b border-brand-border flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-gold-400"></i>
                                <span>Pagos Registrados para este Evento ({{ count($pagosHistorial) }})</span>
                            </h4>
                            <span class="text-[11px] text-slate-400">
                                Pagos anulados no suman al saldo
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-950/90 border-b border-brand-border text-slate-400 uppercase tracking-wider text-[11px]">
                                        <th class="py-2.5 px-3 font-bold">N° Recibo</th>
                                        <th class="py-2.5 px-3 font-bold">Fecha / Hora</th>
                                        <th class="py-2.5 px-3 font-bold">Detalle / Concepto</th>
                                        <th class="py-2.5 px-3 font-bold text-right">Importe (Bs)</th>
                                        <th class="py-2.5 px-3 font-bold text-center">Método</th>
                                        <th class="py-2.5 px-3 font-bold">Cobró</th>
                                        <th class="py-2.5 px-3 font-bold text-center">Estado</th>
                                        <th class="py-2.5 px-3 font-bold text-right">Recibo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-border/40 text-slate-300">
                                    @forelse($pagosHistorial as $pago)
                                        <tr class="hover:bg-slate-800/25 transition-all {{ $pago->estado === 'Anulado' ? 'opacity-60 bg-rose-950/10' : '' }}">
                                            <td class="py-2.5 px-3 font-mono font-bold text-gold-400">
                                                {{ $pago->numero_recibo }}
                                            </td>
                                            <td class="py-2.5 px-3 font-mono">
                                                <div>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : '-' }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $pago->fecha_pago ? $pago->fecha_pago->format('H:i') : '' }}</div>
                                            </td>
                                            <td class="py-2.5 px-3 max-w-xs truncate" title="{{ $pago->observaciones }}">
                                                {{ $pago->observaciones ?: 'Cobranza / Abono del servicio' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $pago->estado === 'Anulado' ? 'text-slate-400 line-through' : 'text-emerald-400' }}">
                                                Bs {{ number_format($pago->monto, 2) }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 border border-slate-700 text-slate-200">
                                                    {{ $pago->metodo_pago }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-3 text-slate-300">
                                                {{ $pago->user->name ?? 'Sistema' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pago->estado === 'Valido' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                                    {{ $pago->estado }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                <a href="{{ route('comercial.pagos.recibo', $pago->codigo_verificacion) }}" target="_blank"
                                                    class="p-1.5 rounded-lg bg-gold-500/10 text-gold-400 hover:text-slate-950 hover:bg-gold-500 border border-gold-500/20 transition-all inline-block"
                                                    title="Ver / Imprimir Recibo Web">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                                <a href="{{ route('comercial.pagos.pdf', $pago->codigo_verificacion) }}" target="_blank"
                                                    class="p-1.5 rounded-lg bg-rose-600/10 text-rose-400 hover:text-white hover:bg-rose-600 border border-rose-500/20 transition-all inline-block"
                                                    title="Descargar Recibo en PDF">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-8 text-slate-500">
                                                <i class="fa-solid fa-receipt text-2xl text-slate-600 mb-2 block"></i>
                                                No se registran pagos asociados a este contrato o evento.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-brand-border px-6 py-4 shrink-0 bg-slate-950/60">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <!-- Ver Contrato Oficial PDF con QR -->
                        <a href="{{ route('admin.contratos.pdf', $contratoHistorial->codigo_verificacion) }}" 
                           target="_blank"
                           class="px-3.5 py-2 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer"
                           title="Ver Contrato Oficial en formato PDF con cláusulas legales y código QR">
                            <i class="fa-solid fa-file-contract text-sm"></i>
                            <span>Ver Contrato en PDF</span>
                        </a>

                        @if($cTokenModal)
                            <!-- Botón: Ver Extracto de Pagos en PDF -->
                            <a href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $cTokenModal, 'contrato' => $ctrTokenModal, 'tipo' => 'contrato']) }}" 
                               target="_blank"
                               class="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-400 hover:to-gold-500 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-md hover:shadow-gold-500/20 transition-all cursor-pointer"
                               title="Ver Extracto de Pagos del Contrato {{ $contratoHistorial->numero_contrato }} en formato PDF oficial con QR">
                                <i class="fa-solid fa-file-pdf text-sm text-slate-950"></i>
                                <span>Extracto de Pagos en PDF</span>
                            </a>

                            <!-- Historial de Contratos del Cliente en PDF -->
                            <a href="{{ route('admin.clientes.historial-contratos.pdf', $cTokenModal) }}" 
                               target="_blank"
                               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 hover:text-white border border-emerald-500/20 font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm"
                               title="Ver Historial de Todos los Contratos y Eventos del Cliente en PDF con QR">
                                <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                <span>Historial del Cliente (PDF)</span>
                            </a>

                            <!-- Enlace a Vista Web Completa -->
                            <a href="{{ route('admin.clientes.estado-cuenta', ['token' => $cTokenModal, 'contrato' => $ctrTokenModal]) }}" 
                               class="text-xs font-bold text-slate-400 hover:text-gold-300 flex items-center gap-1.5 underline decoration-slate-600 hover:decoration-gold-400 transition-colors ml-1"
                               title="Ver Estado de Cuenta interactivo en el sistema">
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                <span>Estado de Cuenta Web</span>
                            </a>

                            @if($contratoHistorial->evento)
                                @php
                                    $tokEvHistCtr = \App\Services\Security\DocumentToken::encodeId($contratoHistorial->evento->id, 'evento');
                                @endphp
                                <a href="{{ route('admin.eventos.comisiones.pdf', $tokEvHistCtr) }}" 
                                   target="_blank"
                                   class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-sky-400 hover:text-white border border-sky-500/20 font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm"
                                   title="Ver Planilla Oficial de Comisiones a Músicos en PDF con QR">
                                    <i class="fa-solid fa-users-viewfinder text-xs"></i>
                                    <span>Comisiones Músicos (PDF)</span>
                                </a>
                            @endif
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <button wire:click="cerrarHistorialPagos"
                            class="px-5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-all">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Comisiones a Músicos y Personal por Evento -->
    @if ($modalComisionesOpen && $eventoComisiones)
        @php
            $tokenEvComCtr = $resumenComisiones['token'] ?? \App\Services\Security\DocumentToken::encodeId($eventoComisiones->id, 'evento');
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md animate-fade-in">
            <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-4xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-brand-border px-6 py-4 shrink-0 bg-slate-950/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-lg shadow-sm">
                            <i class="fa-solid fa-users-viewfinder"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-bold text-white">Comisiones a Músicos y Personal</h3>
                                <span class="font-mono font-extrabold text-gold-400 bg-gold-500/10 border border-gold-500/20 px-2 py-0.5 rounded-md text-xs">
                                    {{ $eventoComisiones->codigo_evento }}
                                </span>
                                @if($eventoComisiones->contrato)
                                    <span class="font-mono font-bold text-sky-400 bg-sky-500/10 border border-sky-500/20 px-2 py-0.5 rounded-md text-xs">
                                        Contrato: {{ $eventoComisiones->contrato->numero_contrato }}
                                    </span>
                                @else
                                    <span class="text-amber-400 italic text-xs bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
                                        Sin contrato formal
                                    </span>
                                @endif
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold 
                                    @if ($eventoComisiones->estado === 'Confirmado' || $eventoComisiones->estado === 'Realizado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($eventoComisiones->estado === 'Pendiente') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($eventoComisiones->estado === 'Cancelado') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @else bg-slate-800 text-slate-300 border border-slate-700 @endif">
                                    {{ $eventoComisiones->estado }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 flex items-center gap-2 mt-1 flex-wrap">
                                <span><i class="fa-solid fa-user text-slate-500 mr-1"></i>Cliente: <strong>{{ $eventoComisiones->cliente->nombre_completo ?? 'N/A' }}</strong></span>
                                <span class="text-slate-600">•</span>
                                <span><i class="fa-solid fa-music text-slate-500 mr-1"></i>{{ $eventoComisiones->servicio->nombre ?? 'Servicio Estándar' }}</span>
                                <span class="text-slate-600">•</span>
                                <span><i class="fa-solid fa-calendar text-slate-500 mr-1"></i>{{ $eventoComisiones->fecha_evento ? $eventoComisiones->fecha_evento->format('d/m/Y') : '-' }} {{ $eventoComisiones->hora_evento }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Botón Planilla PDF con QR -->
                        <a href="{{ route('admin.eventos.comisiones.pdf', $tokenEvComCtr) }}" 
                           target="_blank"
                           class="hidden sm:inline-flex px-3 py-1.5 rounded-xl bg-gold-500/10 hover:bg-gold-500 text-gold-400 hover:text-slate-950 font-bold text-xs border border-gold-500/20 transition-all items-center gap-1.5 shadow-sm" 
                           title="Descargar Planilla Oficial de Comisiones en PDF con QR">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Planilla PDF</span>
                        </a>

                        <button wire:click="cerrarComisiones" class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800 transition-colors text-lg" title="Cerrar">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Notificación Flash dentro del modal -->
                @if (session()->has('comision_success'))
                    <div class="mx-6 mt-4 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-xs flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                        <span>{{ session('comision_success') }}</span>
                    </div>
                @endif

                <!-- Body (Scrollable) -->
                <div class="overflow-y-auto p-6 space-y-6">
                    <!-- Tarjetas de Resumen Financiero -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                        <!-- Total Evento / Contratado -->
                        <div class="bg-slate-900/90 border border-brand-border rounded-2xl p-3.5">
                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Total Evento</span>
                                <i class="fa-solid fa-money-bill-wave text-blue-400 text-xs"></i>
                            </div>
                            <span class="text-base sm:text-lg font-extrabold font-mono text-white block">
                                Bs {{ number_format($resumenComisiones['monto_evento'] ?? 0, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400">Cobrado al cliente</span>
                        </div>

                        <!-- Total Comisiones Asignadas -->
                        <div class="bg-slate-900/90 border border-brand-border rounded-2xl p-3.5">
                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Total Comisiones</span>
                                <i class="fa-solid fa-users text-sky-400 text-xs"></i>
                            </div>
                            <span class="text-base sm:text-lg font-extrabold font-mono text-sky-400 block">
                                Bs {{ number_format($resumenComisiones['total_comisiones'] ?? 0, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400">{{ $resumenComisiones['count_participantes'] ?? 0 }} integrante(s)</span>
                        </div>

                        <!-- Comisiones Pagadas / Distribuidas -->
                        <div class="bg-slate-900/90 border border-brand-border rounded-2xl p-3.5">
                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Comisiones Pagadas</span>
                                <i class="fa-solid fa-circle-check text-emerald-400 text-xs"></i>
                            </div>
                            <span class="text-base sm:text-lg font-extrabold font-mono text-emerald-400 block">
                                Bs {{ number_format($resumenComisiones['total_pagado'] ?? 0, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400">{{ $resumenComisiones['count_pagados'] ?? 0 }} liquidado(s)</span>
                        </div>

                        <!-- Margen Mariachi León -->
                        <div class="bg-slate-900/90 border border-brand-border rounded-2xl p-3.5">
                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Margen Grupo</span>
                                <i class="fa-solid fa-vault text-gold-400 text-xs"></i>
                            </div>
                            <span class="text-base sm:text-lg font-extrabold font-mono {{ ($resumenComisiones['margen_grupo'] ?? 0) >= 0 ? 'text-gold-400' : 'text-rose-400' }} block">
                                Bs {{ number_format($resumenComisiones['margen_grupo'] ?? 0, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400">Utilidad Mariachi León</span>
                        </div>
                    </div>

                    <!-- Tabla de Músicos y Personal -->
                    <div class="bg-slate-950/60 rounded-2xl border border-brand-border overflow-hidden">
                        <div class="px-4 py-3 bg-slate-900/70 border-b border-brand-border flex items-center justify-between flex-wrap gap-2">
                            <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-users-gear text-gold-400"></i>
                                <span>Integrantes y Liquidación por Persona ({{ $eventoComisiones->participantes->count() }})</span>
                            </h4>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-slate-400">
                                    Haz clic en el estado para marcar como <strong>Pagado</strong> o <strong>Pendiente</strong>
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-950/90 border-b border-brand-border text-slate-400 uppercase tracking-wider text-[11px]">
                                        <th class="py-2.5 px-3 font-bold">Músico / Personal</th>
                                        <th class="py-2.5 px-3 font-bold">Función / Rol</th>
                                        <th class="py-2.5 px-3 font-bold">Concepto</th>
                                        <th class="py-2.5 px-3 font-bold text-right">Comisión (Bs)</th>
                                        <th class="py-2.5 px-3 font-bold text-center">Estado Liquidación</th>
                                        <th class="py-2.5 px-3 font-bold text-center">Acción Rápida</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-border/40 text-slate-300">
                                    @forelse($eventoComisiones->participantes as $part)
                                        <tr class="hover:bg-slate-800/25 transition-all">
                                            <td class="py-2.5 px-3">
                                                <div class="font-bold text-white">
                                                    {{ $part->persona->nombre_completo ?? ($part->persona->nombre ?? 'N/A') }}
                                                </div>
                                                <div class="text-[10px] text-slate-400">
                                                    CI: {{ $part->persona->ci_nit ?? '-' }} &bull; Tel: {{ $part->persona->telefono ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-800 border border-slate-700 text-gold-300">
                                                    {{ $part->funcion }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-3 text-slate-300 text-xs">
                                                {{ $part->concepto_pago ?: 'Comisión por Evento' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-400 text-sm">
                                                Bs {{ number_format($part->monto_asignado, 2) }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center">
                                                @if($part->estado_distribucion === 'Distribuido')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                                        <i class="fa-solid fa-check-double text-[9px]"></i>
                                                        <span>DISTRIBUIDO / PAGADO</span>
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 inline-flex items-center gap-1">
                                                        <i class="fa-solid fa-clock text-[9px]"></i>
                                                        <span>PENDIENTE DE PAGO</span>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                <button wire:click="cambiarEstadoComision({{ $part->id }})" wire:loading.attr="disabled"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-sm {{ $part->estado_distribucion === 'Distribuido' ? 'bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-500/20' : 'bg-emerald-600 hover:bg-emerald-500 text-white' }}"
                                                    title="{{ $part->estado_distribucion === 'Distribuido' ? 'Marcar como Pendiente' : 'Marcar como Pagado / Distribuido' }}">
                                                    <i class="fa-solid {{ $part->estado_distribucion === 'Distribuido' ? 'fa-arrow-rotate-left' : 'fa-check' }}"></i>
                                                    <span>{{ $part->estado_distribucion === 'Distribuido' ? 'Desmarcar' : 'Marcar Pagado' }}</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-8 text-slate-500">
                                                <i class="fa-solid fa-user-slash text-2xl text-slate-600 mb-2 block"></i>
                                                No se han asignado músicos ni personal a este evento.
                                                <div class="mt-2">
                                                    <a href="{{ route('admin.eventos.participantes', $eventoComisiones->id) }}" class="text-gold-400 hover:underline font-bold text-xs">
                                                        <i class="fa-solid fa-user-plus mr-1"></i>Asignar integrantes ahora
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-brand-border px-6 py-4 shrink-0 bg-slate-950/60">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <!-- Botón: Planilla de Comisiones en PDF con QR -->
                        <a href="{{ route('admin.eventos.comisiones.pdf', $tokenEvComCtr) }}" 
                           target="_blank"
                           class="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-500 to-amber-500 hover:from-gold-400 hover:to-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-md hover:shadow-gold-500/20 transition-all cursor-pointer"
                           title="Descargar Planilla Oficial de Comisiones y Liquidación a Músicos en formato PDF con QR">
                            <i class="fa-solid fa-file-pdf text-sm text-slate-950"></i>
                            <span>Descargar Planilla en PDF (con QR)</span>
                        </a>

                        <!-- Gestionar Participantes en pantalla completa -->
                        <a href="{{ route('admin.eventos.participantes', $eventoComisiones->id) }}" 
                           class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm"
                           title="Ir a la pantalla de asignación completa de participantes">
                            <i class="fa-solid fa-users text-xs"></i>
                            <span>Gestionar / Agregar Integrantes</span>
                        </a>
                    </div>
                    <div class="flex items-center gap-3">
                        <button wire:click="cerrarComisiones"
                            class="px-5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-all">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>