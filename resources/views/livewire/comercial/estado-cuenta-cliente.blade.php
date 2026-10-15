<div class="space-y-6">

    <!-- Estilos específicos para impresión profesional -->
    <style>
        @media print {
            body { background: #ffffff !important; color: #102846 !important; font-size: 8.5pt !important; }
            .no-print, header, aside, .sidebar-container, nav, button, .pagination { display: none !important; }
            .print-only { display: block !important; }
            .report-card { border: 1px solid #cbd5e1 !important; box-shadow: none !important; background: #ffffff !important; color: #0f172a !important; }
            table { width: 100% !important; border-collapse: collapse !important; font-size: 8pt !important; }
            thead { display: table-header-group !important; }
            tr { page-break-inside: avoid !important; }
            th { background-color: #102846 !important; color: #ffffff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            td, th { border: 1px solid #cbd5e1 !important; padding: 4px 6px !important; }
        }
        .print-only { display: none; }
    </style>

    <!-- Barra de Navegación y Acciones Superiores (No se imprime) -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-brand-border">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.clientes') }}" class="text-xs text-slate-400 hover:text-gold-400 transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Volver a Gestión de Clientes</span>
                </a>
                <span class="text-slate-600">&bull;</span>
                <span class="text-xs text-gold-400 font-bold uppercase tracking-wider">Módulo Financiero</span>
            </div>
            <h2 class="text-2xl font-black text-white flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-gold-500/10 text-gold-400 border border-gold-500/30">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </span>
                <span>Estados de Cuenta</span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Cliente: <strong class="text-white">{{ $cliente->nombre_completo }}</strong> &bull; CI/NIT: <span class="font-mono text-slate-300">{{ $cliente->ci_nit ?: 'S/N' }}</span>
            </p>
        </div>

        <!-- Botones de Acción Global -->
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Imprimir Vista -->
            <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs flex items-center gap-2 border border-slate-700 transition-all shadow cursor-pointer" title="Imprimir estado de cuenta">
                <i class="fa-solid fa-print text-amber-400"></i>
                <span>Imprimir</span>
            </button>

            <!-- Exportar a PDF -->
            <a href="{{ route('admin.clientes.estado-cuenta.pdf', [
                    'cliente' => $cliente->id,
                    'tipo' => $tipo_reporte,
                    'contrato_id' => $contrato_id,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'metodo_pago' => $metodo_pago,
                    'usuario_id' => $usuario_id,
                    'estado_pago' => $estado_pago,
                    'search_recibo' => $search_recibo
                ]) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white font-bold text-xs flex items-center gap-2 border border-rose-500/30 transition-all shadow" title="Generar PDF Oficial">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Exportar a PDF</span>
            </a>

            <!-- Exportar a Excel -->
            <button wire:click="exportarExcel" class="px-3.5 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white font-bold text-xs flex items-center gap-2 border border-emerald-500/30 transition-all shadow cursor-pointer" title="Descargar reporte en formato Excel/CSV">
                <i class="fa-solid fa-file-excel"></i>
                <span>Exportar a Excel</span>
            </button>
        </div>
    </div>

    <!-- Encabezado Institucional Exclusivo para Impresión -->
    <div class="print-only mb-6 border-b-2 border-[#C99A2E] pb-4">
        <table class="w-full">
            <tr>
                <td style="width: 25%; vertical-align: middle;">
                    <div style="font-size: 14pt; font-weight: 900; color: #102846;">MARIACHI LEÓN</div>
                    <div style="font-size: 8pt; color: #C99A2E; font-weight: bold;">GUANAJUATO S.R.L.</div>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: middle;">
                    <h1 style="font-size: 13pt; font-weight: bold; color: #102846; margin: 0; text-transform: uppercase;">
                        {{ $tipo_reporte === 'contrato' ? 'ESTADO DE CUENTA POR CONTRATO' : 'ESTADO DE CUENTA GENERAL DEL CLIENTE' }}
                    </h1>
                    <p style="font-size: 8pt; color: #475569; margin: 2px 0 0 0;">NIT: {{ $empresa->nit_ruc }} &bull; {{ $empresa->ciudad_pais }}</p>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: middle; font-size: 7.5pt; color: #475569;">
                    <div><strong>Fecha:</strong> {{ now()->format('d/m/Y H:i') }}</div>
                    <div><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tarjeta de Información del Cliente -->
    <div class="report-card bg-brand-card border border-brand-border rounded-2xl p-4 sm:p-5 shadow-lg">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px] uppercase tracking-wider font-bold mb-0.5">Nombre del Cliente</span>
                <span class="text-white font-extrabold text-sm">{{ $cliente->nombre_completo }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px] uppercase tracking-wider font-bold mb-0.5">Cédula / NIT</span>
                <span class="font-mono text-slate-200 font-bold">{{ $cliente->ci_nit ?: 'Sin documento' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px] uppercase tracking-wider font-bold mb-0.5">Teléfonos de Contacto</span>
                <span class="text-slate-200 flex items-center gap-2">
                    @if($cliente->telefono)
                        <span><i class="fa-solid fa-phone text-slate-400 mr-1"></i>{{ $cliente->telefono }}</span>
                    @endif
                    @if($cliente->whatsapp)
                        <span class="text-emerald-400 font-semibold"><i class="fa-brands fa-whatsapp mr-1"></i>{{ $cliente->whatsapp }}</span>
                    @endif
                    @if(!$cliente->telefono && !$cliente->whatsapp)
                        <span class="text-slate-500">No registrado</span>
                    @endif
                </span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px] uppercase tracking-wider font-bold mb-0.5">Correo Electrónico</span>
                <span class="text-slate-200">{{ $cliente->email ?: 'No registrado' }}</span>
            </div>
        </div>
    </div>

    <!-- Selector de Tipo de Reporte (Pestañas Corporativas - No se imprime) -->
    <div class="no-print bg-slate-900/80 border border-slate-800 rounded-2xl p-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md">
        <div class="flex items-center gap-1.5 p-1 bg-slate-950 rounded-xl border border-slate-800">
            <button wire:click="cambiarTipoReporte('general')" class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2 {{ $tipo_reporte === 'general' ? 'bg-gold-500 text-slate-950 shadow-md shadow-gold-500/20' : 'text-slate-400 hover:text-white' }}">
                <i class="fa-solid fa-table-list"></i>
                <span>Estado de Cuenta General</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $tipo_reporte === 'general' ? 'bg-slate-950 text-gold-400 font-black' : 'bg-slate-800 text-slate-300' }}">
                    {{ $contratosCliente->count() }}
                </span>
            </button>

            <button wire:click="cambiarTipoReporte('contrato')" class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2 {{ $tipo_reporte === 'contrato' ? 'bg-gold-500 text-slate-950 shadow-md shadow-gold-500/20' : 'text-slate-400 hover:text-white' }}">
                <i class="fa-solid fa-file-contract"></i>
                <span>Estado de Cuenta por Contrato</span>
            </button>
        </div>

        @if($tipo_reporte === 'contrato')
            <!-- Selector de Contrato Específico -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Contrato:</label>
                <select wire:model.live="contrato_id" class="px-3 py-1.5 bg-slate-950 border border-gold-500/40 rounded-xl text-gold-400 font-mono font-bold text-xs focus:border-gold-500 outline-none shadow-sm">
                    @forelse($contratosCliente as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->numero_contrato }} &bull; {{ $c->fecha_contrato ? $c->fecha_contrato->format('d/m/Y') : '' }} ({{ $c->servicio->nombre ?? 'Musical' }})
                        </option>
                    @empty
                        <option value="">Sin contratos registrados</option>
                    @endforelse
                </select>
            </div>
        @endif
    </div>

    <!-- Barra de Filtros Dinámicos (No se imprime) -->
    <div class="no-print bg-brand-card border border-brand-border rounded-2xl p-4 shadow-lg space-y-3">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <span class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-filter text-gold-400"></i>
                <span>Filtros de Búsqueda</span>
            </span>
            <button wire:click="limpiarFiltros" class="text-xs text-slate-400 hover:text-gold-400 font-medium transition-colors flex items-center gap-1">
                <i class="fa-solid fa-rotate-left text-[11px]"></i>
                <span>Limpiar Filtros</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Desde Fecha</label>
                <input type="date" wire:model.live="fecha_inicio" class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Hasta Fecha</label>
                <input type="date" wire:model.live="fecha_fin" class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Método de Pago</label>
                <select wire:model.live="metodo_pago" class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="">Todos los Métodos</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                    <option value="QR">Pago QR</option>
                    <option value="Tarjeta de Débito/Crédito">Tarjeta Débito/Crédito</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Usuario que Cobró</label>
                <select wire:model.live="usuario_id" class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="">Todos los Cobradores</option>
                    @foreach($usuariosCobradores as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Estado de Pago</label>
                <select wire:model.live="estado_pago" class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-gold-500">
                    <option value="Valido">Válidos (Cobros reales)</option>
                    <option value="Anulado">Solo Anulados</option>
                    <option value="Todos">Todos (Válidos y Anulados)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- VISTA A: ESTADO DE CUENTA POR CONTRATO -->
    @if($tipo_reporte === 'contrato')
        @if($contratoSeleccionado)
            <!-- Bloque de Datos del Contrato -->
            <div class="report-card bg-brand-card border border-brand-border rounded-2xl p-4 sm:p-5 shadow-lg space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/30">
                            <i class="fa-solid fa-file-contract"></i>
                        </span>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">
                            Detalles del Contrato N° <span class="text-gold-400 font-mono">{{ $contratoSeleccionado->numero_contrato }}</span>
                        </h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border 
                        @if($contratoSeleccionado->estado === 'Confirmado' || $contratoSeleccionado->estado === 'Concluido' || $contratoSeleccionado->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                        @elseif($contratoSeleccionado->estado === 'Pendiente de Confirmacion' || $contratoSeleccionado->estado === 'En Proceso') bg-amber-500/10 text-amber-400 border-amber-500/30
                        @else bg-slate-800 text-slate-300 border-slate-700 @endif">
                        {{ $contratoSeleccionado->estado }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Fecha del Contrato</span>
                        <span class="text-slate-200 font-mono font-bold">{{ $contratoSeleccionado->fecha_contrato ? $contratoSeleccionado->fecha_contrato->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Servicio Contratado</span>
                        <span class="text-slate-200">{{ $contratoSeleccionado->servicio->nombre ?? 'Servicio Musical' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Código Evento</span>
                        <span class="font-mono text-gold-400 font-bold">{{ $contratoSeleccionado->evento->codigo_evento ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Fecha del Evento</span>
                        <span class="text-slate-200">{{ $contratoSeleccionado->evento && $contratoSeleccionado->evento->fecha_evento ? $contratoSeleccionado->evento->fecha_evento->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Tarjetas Visuales de Resumen Financiero -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <!-- Monto Contratado -->
                <div class="report-card p-4 rounded-2xl bg-brand-card border border-slate-800 shadow-lg">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Monto Total Contratado</span>
                    <span class="text-base sm:text-lg font-black font-mono text-white">Bs {{ number_format($montoContratado, 2) }}</span>
                </div>

                <!-- Total Abonado Acumulado -->
                <div class="report-card p-4 rounded-2xl bg-emerald-950/30 border border-emerald-500/30 shadow-lg">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Total Abonado Acumulado</span>
                    <span class="text-base sm:text-lg font-black font-mono text-emerald-400">Bs {{ number_format($totalAbonado, 2) }}</span>
                </div>

                <!-- Saldo Pendiente Actual -->
                <div class="report-card p-4 rounded-2xl bg-rose-950/30 border border-rose-500/30 shadow-lg">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400 block mb-1">Saldo Pendiente Actual</span>
                    <span class="text-base sm:text-lg font-black font-mono text-rose-400">Bs {{ number_format($saldoPendiente, 2) }}</span>
                </div>

                <!-- Pagos Válidos -->
                <div class="report-card p-4 rounded-2xl bg-brand-card border border-slate-800 shadow-lg">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Cantidad de Pagos</span>
                    <span class="text-base sm:text-lg font-black text-slate-200">{{ $cantidadPagosValidos }}</span>
                </div>

                <!-- Fecha Último Pago -->
                <div class="report-card p-4 rounded-2xl bg-brand-card border border-slate-800 shadow-lg col-span-2 sm:col-span-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Fecha Último Pago</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-300 font-mono">
                        {{ $ultimoPago ? $ultimoPago->fecha_pago->format('d/m/Y') : 'Sin pagos' }}
                    </span>
                </div>
            </div>

            <!-- Tabla de Historial de Pagos del Contrato -->
            <div class="report-card bg-brand-card border border-brand-border rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-brand-border flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-list-ol text-gold-400"></i>
                        <span>Historial de Pagos y Movimientos del Contrato</span>
                    </h4>
                    <span class="text-xs text-slate-400">Mostrando {{ $pagos->total() }} movimientos</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-brand-border bg-slate-900/60 text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                                <th class="py-3 px-3.5 text-center">N°</th>
                                <th class="py-3 px-3.5 text-center cursor-pointer hover:text-white" wire:click="sortBy('numero_recibo')">
                                    Recibo <i class="fa-solid fa-sort text-[9px]"></i>
                                </th>
                                <th class="py-3 px-3.5 text-center cursor-pointer hover:text-white" wire:click="sortBy('fecha_pago')">
                                    Fecha y Hora <i class="fa-solid fa-sort text-[9px]"></i>
                                </th>
                                <th class="py-3 px-3.5">Detalle o Concepto</th>
                                <th class="py-3 px-3.5 text-right cursor-pointer hover:text-white" wire:click="sortBy('monto')">
                                    Importe (Bs) <i class="fa-solid fa-sort text-[9px]"></i>
                                </th>
                                <th class="py-3 px-3.5 text-center">Método de Pago</th>
                                <th class="py-3 px-3.5 text-center">Cobrado Por</th>
                                <th class="py-3 px-3.5 text-center">Estado</th>
                                <th class="no-print py-3 px-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border text-slate-200">
                            @forelse($pagos as $index => $pago)
                                <tr class="hover:bg-slate-900/40 transition-colors {{ $pago->estado === 'Anulado' ? 'opacity-60 bg-rose-950/10' : '' }}">
                                    <td class="py-3 px-3.5 text-center font-mono text-slate-400">{{ $index + 1 }}</td>
                                    <td class="py-3 px-3.5 text-center font-mono font-bold text-amber-400">
                                        {{ $pago->numero_recibo }}
                                    </td>
                                    <td class="py-3 px-3.5 text-center font-mono text-slate-300">
                                        {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-3.5 text-slate-300">
                                        {{ $pago->observaciones ?: 'Abono a cuenta por servicio musical' }}
                                    </td>
                                    <td class="py-3 px-3.5 text-right font-mono font-bold {{ $pago->estado === 'Anulado' ? 'text-rose-400 line-through' : 'text-emerald-400' }}">
                                        {{ number_format($pago->monto, 2) }}
                                    </td>
                                    <td class="py-3 px-3.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 border border-slate-700">
                                            {{ $pago->metodo_pago }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3.5 text-center text-slate-400 text-[11px]">
                                        {{ $pago->user ? $pago->user->name : 'Sistema' }}
                                    </td>
                                    <td class="py-3 px-3.5 text-center">
                                        @if($pago->estado === 'Valido')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                Válido
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20" title="{{ $pago->motivo_anulacion }}">
                                                Anulado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="no-print py-3 px-3.5 text-right space-x-1">
                                        <!-- Ver Recibo Oficial -->
                                        <a href="{{ route('comercial.pagos.recibo', $pago->codigo_verificacion) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-800 text-amber-400 hover:text-slate-950 hover:bg-amber-400 transition-all shadow inline-block" title="Ver Recibo Oficial con QR">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                        <!-- PDF Recibo Oficial -->
                                        <a href="{{ route('comercial.pagos.pdf', $pago->codigo_verificacion) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-800 text-rose-400 hover:text-white hover:bg-rose-600 transition-all shadow inline-block" title="Descargar PDF">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-8 text-slate-500 text-sm">
                                        No se encontraron pagos registrados para este contrato con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($pagos->count() > 0)
                            <tfoot>
                                <tr class="bg-slate-900/80 font-bold border-t border-brand-border">
                                    <td colspan="4" class="py-3 px-3.5 text-right text-slate-300 uppercase tracking-wider text-[11px]">
                                        Total Abonado Neto (Pagos Válidos):
                                    </td>
                                    <td class="py-3 px-3.5 text-right font-mono text-emerald-400 font-black text-sm">
                                        Bs {{ number_format($totalAbonado, 2) }}
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                @if($pagos->hasPages())
                    <div class="no-print p-4 border-t border-brand-border">
                        {{ $pagos->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Sin contratos para este cliente -->
            <div class="bg-brand-card border border-brand-border rounded-2xl p-8 text-center text-slate-400">
                <i class="fa-solid fa-file-circle-exclamation text-4xl text-amber-400 mb-2"></i>
                <h3 class="text-base font-bold text-white">El cliente no tiene contratos registrados</h3>
                <p class="text-xs text-slate-400 mt-1">Registre una cotización o contrato para poder consultar el estado de cuenta por contrato.</p>
            </div>
        @endif

    <!-- VISTA B: ESTADO DE CUENTA GENERAL DEL CLIENTE -->
    @else
        <!-- Tarjetas Visuales de Resumen Financiero Consolidado -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- Total Contratos -->
            <div class="report-card p-4 rounded-2xl bg-brand-card border border-slate-800 shadow-lg">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Total Contratos</span>
                <span class="text-base sm:text-lg font-black text-slate-200">{{ $totalContratos }}</span>
            </div>

            <!-- Monto Contratado Total -->
            <div class="report-card p-4 rounded-2xl bg-brand-card border border-slate-800 shadow-lg col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Monto Total Contratado</span>
                <span class="text-base sm:text-lg font-black font-mono text-white">Bs {{ number_format($montoContratadoTotal, 2) }}</span>
            </div>

            <!-- Total Abonado Neto -->
            <div class="report-card p-4 rounded-2xl bg-emerald-950/30 border border-emerald-500/30 shadow-lg col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Total Abonado Neto</span>
                <span class="text-base sm:text-lg font-black font-mono text-emerald-400">Bs {{ number_format($totalAbonadoNeto, 2) }}</span>
            </div>

            <!-- Saldo Pendiente Global -->
            <div class="report-card p-4 rounded-2xl bg-rose-950/30 border border-rose-500/30 shadow-lg col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400 block mb-1">Saldo Pendiente Global</span>
                <span class="text-base sm:text-lg font-black font-mono text-rose-400">Bs {{ number_format($saldoPendienteTotal, 2) }}</span>
            </div>

            <!-- Total Pagos Válidos -->
            <div class="report-card p-4 rounded-2xl bg-brand-card border border-slate-800 shadow-lg">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Pagos Válidos</span>
                <span class="text-base sm:text-lg font-black text-slate-200">{{ $cantidadPagosValidosTotal }}</span>
            </div>

            <!-- Último Pago -->
            <div class="report-card p-4 rounded-2xl bg-brand-card border border-slate-800 shadow-lg col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Último Pago</span>
                <span class="text-xs sm:text-sm font-bold text-slate-300 font-mono">
                    {{ $ultimoPagoTotal ? $ultimoPagoTotal->fecha_pago->format('d/m/Y') : 'Sin pagos' }}
                </span>
            </div>
        </div>

        <!-- TABLA 1: Resumen de Contratos del Cliente -->
        <div class="report-card bg-brand-card border border-brand-border rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-brand-border flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-gold-400"></i>
                    <span>1. Resumen de Contratos Registrados del Cliente</span>
                </h4>
                <span class="text-xs text-slate-400">{{ $contratosCliente->count() }} contratos en total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-brand-border bg-slate-900/60 text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                            <th class="py-3 px-3.5 text-center">N°</th>
                            <th class="py-3 px-3.5 text-center">Contrato</th>
                            <th class="py-3 px-3.5 text-center">Fecha</th>
                            <th class="py-3 px-3.5">Servicio / Evento</th>
                            <th class="py-3 px-3.5 text-right">Monto Contratado (Bs)</th>
                            <th class="py-3 px-3.5 text-right">Total Abonado (Bs)</th>
                            <th class="py-3 px-3.5 text-right">Saldo Pendiente (Bs)</th>
                            <th class="py-3 px-3.5 text-center">Estado</th>
                            <th class="no-print py-3 px-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border text-slate-200">
                        @forelse($contratosCliente as $index => $ctr)
                            @php
                                $abonadoContrato = (float)$ctr->pagos->where('estado', 'Valido')->sum('monto');
                                $saldoContrato = max(0, (float)$ctr->monto_total - $abonadoContrato);
                            @endphp
                            <tr class="hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 px-3.5 text-center font-mono text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-3.5 text-center font-mono font-bold text-amber-400">
                                    {{ $ctr->numero_contrato }}
                                </td>
                                <td class="py-3 px-3.5 text-center font-mono text-slate-300">
                                    {{ $ctr->fecha_contrato ? $ctr->fecha_contrato->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-bold text-white block">{{ $ctr->servicio->nombre ?? 'Servicio Musical' }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $ctr->evento->codigo_evento ?? 'S/E' }} &bull; {{ $ctr->evento && $ctr->evento->fecha_evento ? $ctr->evento->fecha_evento->format('d/m/Y') : '' }}</span>
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-bold text-slate-200">
                                    {{ number_format($ctr->monto_total, 2) }}
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-bold text-emerald-400">
                                    {{ number_format($abonadoContrato, 2) }}
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-bold text-rose-400">
                                    {{ number_format($saldoContrato, 2) }}
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border 
                                        @if($ctr->estado === 'Confirmado' || $ctr->estado === 'Concluido' || $ctr->estado === 'Finalizado') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                        @elseif($ctr->estado === 'Pendiente de Confirmacion' || $ctr->estado === 'En Proceso') bg-amber-500/10 text-amber-400 border-amber-500/20
                                        @else bg-slate-800 text-slate-300 border-slate-700 @endif">
                                        {{ $ctr->estado }}
                                    </span>
                                </td>
                                <td class="no-print py-3 px-3.5 text-right space-x-1">
                                    <!-- Abrir Estado de Cuenta de este Contrato -->
                                    <button wire:click="seleccionarContrato({{ $ctr->id }})" class="px-2.5 py-1.5 rounded-lg bg-gold-500/10 hover:bg-gold-500 hover:text-slate-950 text-gold-400 font-bold text-xs border border-gold-500/30 transition-all shadow inline-flex items-center gap-1" title="Ver Estado de Cuenta de este Contrato">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                        <span>Ver Detalle</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8 text-slate-500 text-sm">
                                    No se encontraron contratos registrados para este cliente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($contratosCliente->count() > 0)
                        <tfoot>
                            <tr class="bg-slate-900/80 font-bold border-t border-brand-border">
                                <td colspan="4" class="py-3 px-3.5 text-right text-slate-300 uppercase tracking-wider text-[11px]">
                                    Totales Consolidados del Cliente:
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono text-white font-black text-sm">
                                    Bs {{ number_format($montoContratadoTotal, 2) }}
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono text-emerald-400 font-black text-sm">
                                    Bs {{ number_format($totalAbonadoNeto, 2) }}
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono text-rose-400 font-black text-sm">
                                    Bs {{ number_format($saldoPendienteTotal, 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- TABLA 2: Historial Consolidado de Pagos -->
        <div class="report-card bg-brand-card border border-brand-border rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-brand-border flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-gold-400"></i>
                    <span>2. Historial Consolidado de Pagos y Abonos</span>
                </h4>
                <span class="text-xs text-slate-400">{{ $pagos->total() }} pagos registrados</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-brand-border bg-slate-900/60 text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                            <th class="py-3 px-3.5 text-center">N°</th>
                            <th class="py-3 px-3.5 text-center cursor-pointer hover:text-white" wire:click="sortBy('numero_recibo')">
                                Recibo <i class="fa-solid fa-sort text-[9px]"></i>
                            </th>
                            <th class="py-3 px-3.5 text-center">Contrato</th>
                            <th class="py-3 px-3.5 text-center cursor-pointer hover:text-white" wire:click="sortBy('fecha_pago')">
                                Fecha y Hora <i class="fa-solid fa-sort text-[9px]"></i>
                            </th>
                            <th class="py-3 px-3.5">Detalle o Concepto</th>
                            <th class="py-3 px-3.5 text-right cursor-pointer hover:text-white" wire:click="sortBy('monto')">
                                Importe (Bs) <i class="fa-solid fa-sort text-[9px]"></i>
                            </th>
                            <th class="py-3 px-3.5 text-center">Método de Pago</th>
                            <th class="py-3 px-3.5 text-center">Cobrado Por</th>
                            <th class="py-3 px-3.5 text-center">Estado</th>
                            <th class="no-print py-3 px-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border text-slate-200">
                        @forelse($pagos as $index => $pago)
                            <tr class="hover:bg-slate-900/40 transition-colors {{ $pago->estado === 'Anulado' ? 'opacity-60 bg-rose-950/10' : '' }}">
                                <td class="py-3 px-3.5 text-center font-mono text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-3.5 text-center font-mono font-bold text-amber-400">
                                    {{ $pago->numero_recibo }}
                                </td>
                                <td class="py-3 px-3.5 text-center font-mono text-slate-300">
                                    {{ $pago->contrato ? $pago->contrato->numero_contrato : 'N/A' }}
                                </td>
                                <td class="py-3 px-3.5 text-center font-mono text-slate-300">
                                    {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-3.5 text-slate-300">
                                    {{ $pago->observaciones ?: 'Abono a cuenta por servicio musical' }}
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-bold {{ $pago->estado === 'Anulado' ? 'text-rose-400 line-through' : 'text-emerald-400' }}">
                                    {{ number_format($pago->monto, 2) }}
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $pago->metodo_pago }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 text-center text-slate-400 text-[11px]">
                                    {{ $pago->user ? $pago->user->name : 'Sistema' }}
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @if($pago->estado === 'Valido')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Válido
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20" title="{{ $pago->motivo_anulacion }}">
                                            Anulado
                                        </span>
                                    @endif
                                </td>
                                <td class="no-print py-3 px-3.5 text-right space-x-1">
                                    <!-- Ver Recibo Oficial -->
                                    <a href="{{ route('comercial.pagos.recibo', $pago->codigo_verificacion) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-800 text-amber-400 hover:text-slate-950 hover:bg-amber-400 transition-all shadow inline-block" title="Ver Recibo Oficial con QR">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <!-- PDF Recibo Oficial -->
                                    <a href="{{ route('comercial.pagos.pdf', $pago->codigo_verificacion) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-800 text-rose-400 hover:text-white hover:bg-rose-600 transition-all shadow inline-block" title="Descargar PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-8 text-slate-500 text-sm">
                                    No se encontraron pagos registrados para este cliente con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($pagos->count() > 0)
                        <tfoot>
                            <tr class="bg-slate-900/80 font-bold border-t border-brand-border">
                                <td colspan="5" class="py-3 px-3.5 text-right text-slate-300 uppercase tracking-wider text-[11px]">
                                    Total Abonado Neto en Historial:
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono text-emerald-400 font-black text-sm">
                                    Bs {{ number_format($totalAbonadoNeto, 2) }}
                                </td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($pagos->hasPages())
                <div class="no-print p-4 border-t border-brand-border">
                    {{ $pagos->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
