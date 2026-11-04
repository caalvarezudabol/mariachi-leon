<div class="space-y-4">
    <!-- Mensajes de Notificación -->
    @if (session()->has('success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <x-ui.panel>
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-calendar-check text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Eventos</h1>
                <x-ui.badge variant="neutral" class="hidden sm:inline-flex">{{ $totalEventos }}</x-ui.badge>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-ui.button variant="secondary" size="sm" class="hidden sm:inline-flex"
                    href="{{ route('admin.eventos.pdf', ['search' => $search, 'cliente_id' => $cliente_filtro, 'estado' => $estado_filtro]) }}"
                    target="_blank" title="Exportar Programación de Eventos y Estado Financiero a PDF">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Exportar PDF</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" wire:click="abrirModal">
                    <i class="fa-solid fa-plus"></i>
                    <span class="hidden sm:inline">Nuevo evento</span>
                    <span class="sm:hidden">Nuevo</span>
                </x-ui.button>
            </div>
        </x-slot:header>

        <x-slot:filters>
            <div class="flex flex-col gap-2">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <div class="relative flex-1 min-w-0">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                        <x-ui.input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por código, cliente, dirección..." class="pl-9 h-8 text-sm" />
                    </div>
                    <x-ui.select wire:model.live="cliente_filtro" class="sm:w-56 shrink-0">
                        <option value="">Todos los clientes</option>
                        @foreach ($clientes as $cli)
                            <option value="{{ $cli->id }}">{{ $cli->nombre_completo }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.filter-chips>
                        <x-ui.filter-chip wire:click="$set('estado_filtro', '')" :active="$estado_filtro === ''">
                            Todos <span class="opacity-70">{{ $totalEventos }}</span>
                        </x-ui.filter-chip>
                        @foreach ($estados as $estado => $count)
                            <x-ui.filter-chip wire:click="$set('estado_filtro', '{{ $estado }}')" :active="$estado_filtro === $estado">
                                {{ $estado }} <span class="opacity-70">{{ $count }}</span>
                            </x-ui.filter-chip>
                        @endforeach
                    </x-ui.filter-chips>
                </div>

                @if ($cliente_filtro)
                    @php $clienteActivo = $clientes->firstWhere('id', $cliente_filtro); @endphp
                    @if ($clienteActivo)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 rounded-lg bg-amber-500/10 border-l-[3px] border-amber-500 text-xs">
                            <div class="flex items-center gap-2 text-ink-primary">
                                <i class="fa-solid fa-user-check text-amber-600 dark:text-amber-400"></i>
                                <span>Filtrando por cliente: <strong>{{ $clienteActivo->nombre_completo }}</strong>
                                    @if ($clienteActivo->ci_nit)
                                        <span class="text-ink-muted">({{ $clienteActivo->ci_nit }})</span>
                                    @endif
                                </span>
                            </div>
                            <button wire:click="$set('cliente_filtro', '')"
                                class="inline-flex items-center gap-1.5 text-amber-700 dark:text-amber-400 hover:underline font-semibold self-start sm:self-auto">
                                <i class="fa-solid fa-xmark"></i>
                                <span>Quitar filtro</span>
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </x-slot:filters>

        <!-- Tabla (desktop) -->
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="py-1.5 px-3 border-r border-outline">Evento</th>
                    <th class="py-1.5 px-3 border-r border-outline">Fecha y Hora</th>
                    <th class="py-1.5 px-3 border-r border-outline">Servicio</th>
                    <th class="py-1.5 px-3 border-r border-outline text-right">Monto</th>
                    <th class="py-1.5 px-3 border-r border-outline text-right">Saldo</th>
                    <th class="py-1.5 px-3 border-r border-outline">Estado</th>
                    <th class="py-1.5 px-3 text-right w-px whitespace-nowrap">Acciones</th>
                </x-slot:header>

                @forelse ($eventos as $ev)
                    @php
                        $telRow = preg_replace('/[^0-9]/', '', $ev->telefono_contacto ?: $ev->cliente->telefono ?? '');
                        if ($telRow && strlen($telRow) === 8) {
                            $telRow = '591' . $telRow;
                        }
                        $waUrl = $telRow
                            ? 'https://api.whatsapp.com/send?phone=' . $telRow . '&text=' . urlencode('Hola ' . ($ev->cliente->nombre_completo ?? '') . ', le contactamos del Mariachi León Guanajuato sobre su evento ' . $ev->codigo_evento)
                            : null;
                    @endphp
                    <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                        <td class="py-1.5 px-3 border-r border-outline">
                            <div class="text-xs text-gold-700 dark:text-gold-400 font-semibold">{{ $ev->codigo_evento }}</div>
                            <div class="text-sm font-semibold text-ink-primary truncate max-w-[220px]">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</div>
                            @if ($waUrl)
                                <a href="{{ $waUrl }}" target="_blank"
                                    class="text-xs text-emerald-600 dark:text-emerald-400 font-medium hover:underline inline-flex items-center gap-1"
                                    title="Abrir WhatsApp de {{ $ev->cliente->nombre_completo ?? 'Cliente' }}">
                                    <i class="fa-brands fa-whatsapp"></i>
                                    <span>{{ $ev->telefono_contacto ?: $ev->cliente->telefono }}</span>
                                </a>
                            @endif
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-sm text-ink-secondary whitespace-nowrap">
                            {{ $ev->fecha_evento->format('d/m/Y') }} - {{ $ev->hora_evento }}
                            <div class="text-xs text-ink-muted">{{ $ev->duracion_horas }}h</div>
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-sm text-ink-secondary">
                            {{ $ev->servicio->nombre ?? 'N/A' }}
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-right whitespace-nowrap">
                            @if ($ev->contrato)
                                <div class="text-sm font-semibold text-ink-primary tabular-nums">Bs {{ number_format($ev->monto_contratado, 2) }}</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 tabular-nums">Abonado Bs {{ number_format($ev->total_abonado, 2) }}</div>
                            @else
                                <span class="text-xs text-ink-muted italic">Sin contrato</span>
                            @endif
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline text-right whitespace-nowrap">
                            <span class="text-sm font-semibold tabular-nums {{ $ev->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Bs {{ number_format($ev->saldo_pendiente, 2) }}
                            </span>
                        </td>
                        <td class="py-1.5 px-3 border-r border-outline">
                            @php
                                $dotColor = match (true) {
                                    in_array($ev->estado, ['Confirmado', 'Realizado', 'Finalizado']) => 'bg-emerald-500',
                                    in_array($ev->estado, ['Reservado', 'Cotizado']) => 'bg-amber-500',
                                    $ev->estado === 'Cancelado' => 'bg-rose-500',
                                    default => 'bg-slate-400',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-secondary">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $dotColor }}"></span>
                                {{ $ev->estado }}
                            </span>
                        </td>
                        <td class="py-1.5 px-3 text-right w-px whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button wire:click="abrirHistorialPagos({{ $ev->id }})" title="Historial de Pagos y Estado Financiero">
                                    <i class="fa-solid fa-receipt text-xs"></i>
                                </x-ui.icon-button>
                                <x-ui.icon-button wire:click="editar({{ $ev->id }})" title="Editar Evento">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </x-ui.icon-button>
                                <x-ui.action-menu>
                                    @if ($ev->contrato && $ev->cliente_id)
                                        @php
                                            $evClientTok = \App\Services\Security\DocumentToken::encodeId($ev->cliente_id, 'cliente');
                                            $evCtrTok = \App\Services\Security\DocumentToken::encodeId($ev->contrato->id, 'contrato');
                                        @endphp
                                        <x-ui.action-menu-item icon="fa-solid fa-file-pdf" target="_blank"
                                            href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $evClientTok, 'contrato' => $evCtrTok, 'tipo' => 'contrato']) }}">
                                            Extracto de Pagos (PDF)
                                        </x-ui.action-menu-item>
                                    @endif
                                    <x-ui.action-menu-item icon="fa-solid fa-users-viewfinder" wire:click="abrirComisiones({{ $ev->id }})">
                                        Ver Comisiones ({{ $ev->participantes->count() }})
                                    </x-ui.action-menu-item>
                                    @php $evTokCom = \App\Services\Security\DocumentToken::encodeId($ev->id, 'evento'); @endphp
                                    <x-ui.action-menu-item icon="fa-solid fa-file-invoice-dollar" target="_blank"
                                        href="{{ route('admin.eventos.comisiones.pdf', $evTokCom) }}">
                                        Planilla de Comisiones (PDF)
                                    </x-ui.action-menu-item>
                                    <x-ui.action-menu-item icon="fa-solid fa-people-group"
                                        href="{{ route('admin.eventos.participantes', $ev->id) }}">
                                        Gestionar Participantes
                                    </x-ui.action-menu-item>
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-ink-muted text-sm">
                            No se encontraron eventos registrados.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        <!-- Filas (móvil) -->
        <div class="md:hidden divide-y divide-outline">
            @forelse ($eventos as $ev)
                @php
                    $telRow = preg_replace('/[^0-9]/', '', $ev->telefono_contacto ?: $ev->cliente->telefono ?? '');
                    if ($telRow && strlen($telRow) === 8) {
                        $telRow = '591' . $telRow;
                    }
                    $waUrl = $telRow
                        ? 'https://api.whatsapp.com/send?phone=' . $telRow . '&text=' . urlencode('Hola ' . ($ev->cliente->nombre_completo ?? '') . ', le contactamos del Mariachi León Guanajuato sobre su evento ' . $ev->codigo_evento)
                        : null;
                    $dotColor = match (true) {
                        in_array($ev->estado, ['Confirmado', 'Realizado', 'Finalizado']) => 'bg-emerald-500',
                        in_array($ev->estado, ['Reservado', 'Cotizado']) => 'bg-amber-500',
                        $ev->estado === 'Cancelado' => 'bg-rose-500',
                        default => 'bg-slate-400',
                    };
                @endphp
                <div class="px-4 py-2.5 space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-xs text-gold-700 dark:text-gold-400 font-semibold">{{ $ev->codigo_evento }}</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-sm text-ink-primary truncate">{{ $ev->cliente->nombre_completo ?? 'N/A' }}</span>
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $dotColor }}"></span>
                            </div>
                        </div>
                        <x-ui.action-menu>
                            <x-ui.action-menu-item icon="fa-solid fa-receipt" wire:click="abrirHistorialPagos({{ $ev->id }})">
                                Historial de Pagos
                            </x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-pen-to-square" wire:click="editar({{ $ev->id }})">
                                Editar Evento
                            </x-ui.action-menu-item>
                            @if ($ev->contrato && $ev->cliente_id)
                                @php
                                    $evClientTok = \App\Services\Security\DocumentToken::encodeId($ev->cliente_id, 'cliente');
                                    $evCtrTok = \App\Services\Security\DocumentToken::encodeId($ev->contrato->id, 'contrato');
                                @endphp
                                <x-ui.action-menu-item icon="fa-solid fa-file-pdf" target="_blank"
                                    href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $evClientTok, 'contrato' => $evCtrTok, 'tipo' => 'contrato']) }}">
                                    Extracto de Pagos (PDF)
                                </x-ui.action-menu-item>
                            @endif
                            <x-ui.action-menu-item icon="fa-solid fa-users-viewfinder" wire:click="abrirComisiones({{ $ev->id }})">
                                Ver Comisiones ({{ $ev->participantes->count() }})
                            </x-ui.action-menu-item>
                            @php $evTokCom = \App\Services\Security\DocumentToken::encodeId($ev->id, 'evento'); @endphp
                            <x-ui.action-menu-item icon="fa-solid fa-file-invoice-dollar" target="_blank"
                                href="{{ route('admin.eventos.comisiones.pdf', $evTokCom) }}">
                                Planilla de Comisiones (PDF)
                            </x-ui.action-menu-item>
                            <x-ui.action-menu-item icon="fa-solid fa-people-group"
                                href="{{ route('admin.eventos.participantes', $ev->id) }}">
                                Gestionar Participantes
                            </x-ui.action-menu-item>
                        </x-ui.action-menu>
                    </div>
                    <div class="text-xs text-ink-muted">
                        {{ $ev->fecha_evento->format('d/m/Y') }} - {{ $ev->hora_evento }} - {{ $ev->servicio->nombre ?? 'N/A' }}
                    </div>
                    @if ($waUrl)
                        <a href="{{ $waUrl }}" target="_blank" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-1">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span>{{ $ev->telefono_contacto ?: $ev->cliente->telefono }}</span>
                        </a>
                    @endif
                    <div class="flex items-center gap-3 text-xs">
                        @if ($ev->contrato)
                            <span class="text-ink-secondary tabular-nums">Monto Bs {{ number_format($ev->monto_contratado, 2) }}</span>
                        @endif
                        <span class="font-semibold tabular-nums {{ $ev->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            Saldo Bs {{ number_format($ev->saldo_pendiente, 2) }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-center py-8 text-ink-muted text-sm">No se encontraron eventos registrados.</p>
            @endforelse
        </div>

        <x-slot:footer>
            <span>
                @if ($eventos->total() > 0)
                    Mostrando {{ $eventos->firstItem() }}–{{ $eventos->lastItem() }} de {{ $eventos->total() }}
                @else
                    Sin resultados
                @endif
            </span>
            <div>{{ $eventos->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    <!-- Modal rápido: Nuevo Cliente (se abre encima del formulario de Evento) -->
    @if ($modalClienteOpen)
        <x-ui.modal title="Nuevo Cliente" icon="fa-solid fa-user-plus" :nested="true" onClose="cerrarModalNuevoCliente">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-5">
                <x-ui.input class="sm:col-span-2" label="Nombre Completo" required name="nc_nombre_completo"
                    wire:model="nc_nombre_completo" wire:keydown.enter="guardarNuevoCliente" autofocus />

                <x-ui.input label="C.I. / NIT" name="nc_ci_nit" wire:model="nc_ci_nit" placeholder="ej. 10293847" />

                <x-ui.input label="Teléfono Principal" wire:model="nc_telefono" placeholder="ej. 70000000" />

                <x-ui.input label="WhatsApp Comercial" wire:model="nc_whatsapp" placeholder="ej. +591 70000000" />

                <x-ui.input type="email" label="Correo Electrónico" name="nc_email" wire:model="nc_email"
                    placeholder="cliente@ejemplo.com" />

                <x-ui.input class="sm:col-span-2" label="Dirección de Domicilio / Oficina" wire:model="nc_direccion"
                    placeholder="Av. Principal #123" />
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" wire:click="cerrarModalNuevoCliente">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" wire:click="guardarNuevoCliente"
                    wire:loading.attr="disabled" wire:target="guardarNuevoCliente">
                    <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardarNuevoCliente"></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardarNuevoCliente"></i>
                    <span>Guardar y Seleccionar</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Crear/Editar Evento -->
    @if ($modalOpen)
        <form wire:submit.prevent="guardar" id="formEvento" x-data
            x-on:evento-error-validacion.window="document.getElementById('modalEventoBody')?.scrollTo({top: 0, behavior: 'smooth'})">
            <x-ui.modal maxWidth="max-w-3xl" bodyId="modalEventoBody" onClose="$set('modalOpen', false)">
                <x-slot:header>
                    <h3 class="text-base font-bold text-ink-primary flex items-center gap-2 truncate">
                        <i class="fa-solid fa-calendar-check text-gold-600 dark:text-gold-400"></i>
                        <span class="truncate">{{ $isEdit ? 'Editar Evento ' . $codigo_evento : 'Nuevo Evento' }}</span>
                    </h3>
                </x-slot:header>

                <div class="px-6 py-6 space-y-6">
                    <!-- Banner Alerta Superior de Errores de Validación -->
                    @if ($errors->any() || session()->has('modal_error') || session()->has('participante_error'))
                        <div class="p-4 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 space-y-2">
                            <div class="flex items-center gap-2 font-semibold text-sm">
                                <i class="fa-solid fa-triangle-exclamation text-base"></i>
                                <span>Por favor revise los siguientes datos requeridos:</span>
                            </div>
                            @if (session()->has('modal_error'))
                                <div class="text-xs">{{ session('modal_error') }}</div>
                            @endif
                            @if (session()->has('participante_error'))
                                <div class="text-xs">{{ session('participante_error') }}</div>
                            @endif
                            @if ($errors->any())
                                <ul class="list-disc list-inside text-xs space-y-1 pl-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="min-w-0">
                            <label class="block text-sm font-semibold text-ink-primary mb-1">Código del evento: <span class="text-rose-500 font-black text-base leading-none">*</span></label>
                            <input type="text" wire:model="codigo_evento" readonly
                                class="w-full h-9 px-3.5 bg-surface-sunken border border-outline rounded-lg text-ink-muted font-semibold text-sm cursor-not-allowed">
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-semibold text-ink-primary">Cliente: <span class="text-rose-500 font-black text-base leading-none">*</span></label>
                                <button type="button" wire:click="abrirModalNuevoCliente"
                                    class="text-[11px] font-semibold text-gold-600 dark:text-gold-400 hover:underline flex items-center gap-1 transition-colors"
                                    title="Registrar un cliente nuevo sin salir de este formulario">
                                    <i class="fa-solid fa-user-plus"></i>
                                    <span>Nuevo cliente</span>
                                </button>
                            </div>
                            @php
                                $opcionesClientesEvento = $clientes->map(fn ($c) => [
                                    'id' => $c->id,
                                    'label' => $c->nombre_completo,
                                    'meta' => trim(($c->ci_nit ? 'CI: ' . $c->ci_nit : '') . ($c->telefono ? ' - Tel: ' . $c->telefono : '')),
                                    'search' => mb_strtolower($c->nombre_completo . ' ' . $c->ci_nit . ' ' . $c->telefono),
                                ])->values();
                            @endphp
                            <x-ui.select-search name="cliente_id" :options="$opcionesClientesEvento"
                                placeholder="Buscar cliente..." push-event="cliente-creado"
                                on-select="seleccionarCliente(option.id)" />
                            @error('cliente_id')
                                <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="min-w-0">
                            <label class="block text-sm font-semibold text-ink-primary mb-1">Servicio contratado: <span class="text-rose-500 font-black text-base leading-none">*</span></label>
                            <x-ui.select wire:model.live="servicio_id" name="servicio_id">
                                <option value="">Seleccione Servicio</option>
                                @foreach ($servicios as $srv)
                                    <option value="{{ $srv->id }}">{{ $srv->nombre }} (Bs {{ number_format($srv->precio_base, 2) }})</option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <x-ui.input class="min-w-0" label="Contacto del evento" wire:model="contacto_evento"
                            placeholder="Nombre persona a recibir" />

                        <x-ui.input class="min-w-0" label="Teléfono de contacto" wire:model="telefono_contacto"
                            placeholder="ej. 70000000" />

                        <x-ui.select class="min-w-0" label="Estado del evento" wire:model="estado">
                            <option value="Pendiente">Pendiente</option>
                            <option value="Cotizado">Cotizado</option>
                            <option value="Reservado">Reservado</option>
                            <option value="Confirmado">Confirmado</option>
                            <option value="Realizado">Realizado</option>
                            <option value="Cancelado">Cancelado</option>
                            <option value="Finalizado">Finalizado</option>
                        </x-ui.select>

                            <!-- Calendario Interactivo Desplegable para Fecha de Evento -->
                            <div class="relative min-w-0" x-data="{
                                open: false,
                                selectedDate: @entangle('fecha_evento'),
                                currentYear: new Date().getFullYear(),
                                currentMonth: new Date().getMonth(),
                                months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                                weekdays: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                                init() {
                                    this.syncFromSelected();
                                    this.$watch('selectedDate', () => this.syncFromSelected());
                                },
                                syncFromSelected() {
                                    if (this.selectedDate) {
                                        const parts = this.selectedDate.split('-');
                                        if (parts.length === 3) {
                                            this.currentYear = parseInt(parts[0]);
                                            this.currentMonth = parseInt(parts[1]) - 1;
                                        }
                                    }
                                },
                                get daysInMonth() {
                                    return new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                                },
                                get firstDayOfWeek() {
                                    return new Date(this.currentYear, this.currentMonth, 1).getDay();
                                },
                                get formattedDisplay() {
                                    if (!this.selectedDate) return 'Seleccionar Fecha...';
                                    const parts = this.selectedDate.split('-');
                                    if (parts.length === 3) {
                                        return `${parts[2]}/${parts[1]}/${parts[0]}`;
                                    }
                                    return this.selectedDate;
                                },
                                selectDay(day) {
                                    const m = String(this.currentMonth + 1).padStart(2, '0');
                                    const d = String(day).padStart(2, '0');
                                    const full = `${this.currentYear}-${m}-${d}`;
                                    this.selectedDate = full;
                                    $wire.set('fecha_evento', full);
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
                                <label class="block text-sm font-semibold text-ink-primary mb-1">Fecha del evento: <span class="text-rose-500 font-black text-base leading-none">*</span></label>

                                <div class="relative">
                                    <button type="button" @click="open = !open"
                                        class="w-full h-9 px-3.5 bg-surface-sunken border border-outline rounded-lg text-sm text-left flex items-center justify-between hover:border-gold-500/60 transition-colors cursor-pointer">
                                        <span class="font-semibold"
                                            :class="selectedDate ? 'text-gold-600 dark:text-gold-400' : 'text-ink-muted'"
                                            x-text="formattedDisplay"></span>
                                        <i class="fa-solid fa-calendar-days text-gold-600 dark:text-gold-400 text-sm"></i>
                                    </button>

                                    <!-- Desplegable de Calendario Visual -->
                                    <div x-show="open" x-cloak x-transition
                                        class="absolute left-0 top-full mt-1 z-50 w-72 p-3 bg-surface-card border border-gold-500/50 rounded-xl shadow-2xl space-y-3">

                                        <!-- Navegación de Mes y Año -->
                                        <div class="flex items-center justify-between border-b border-outline pb-2">
                                            <button type="button" @click="prevMonth()"
                                                class="p-1 text-ink-secondary hover:text-ink-primary rounded-lg hover:bg-surface-hover">
                                                <i class="fa-solid fa-chevron-left text-xs"></i>
                                            </button>
                                            <div class="text-xs font-semibold text-ink-primary flex items-center gap-1">
                                                <span x-text="months[currentMonth]"></span>
                                                <span class="text-gold-600 dark:text-gold-400" x-text="currentYear"></span>
                                            </div>
                                            <button type="button" @click="nextMonth()"
                                                class="p-1 text-ink-secondary hover:text-ink-primary rounded-lg hover:bg-surface-hover">
                                                <i class="fa-solid fa-chevron-right text-xs"></i>
                                            </button>
                                        </div>

                                        <!-- Encabezado Días de la Semana -->
                                        <div class="grid grid-cols-7 text-center text-[10px] font-semibold text-ink-muted">
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
                                                        'bg-gold-500 text-slate-950 font-bold': isSelected(
                                                            d),
                                                        'border border-gold-500/40 text-gold-600 dark:text-gold-400 font-semibold': isToday(
                                                            d) && !
                                                            isSelected(d),
                                                        'text-ink-secondary hover:bg-surface-hover hover:text-ink-primary': !
                                                            isSelected(
                                                                d) && !isToday(d)
                                                    }"
                                                    class="h-7 w-7 mx-auto rounded-lg flex items-center justify-center transition-colors">
                                                    <span x-text="d"></span>
                                                </button>
                                            </template>
                                        </div>

                                        <!-- Acciones Rápidas -->
                                        <div class="flex items-center justify-between border-t border-outline pt-2 text-[11px]">
                                            <button type="button" @click="selectToday()"
                                                class="text-gold-600 dark:text-gold-400 hover:underline font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-calendar-day"></i> Hoy
                                            </button>
                                            <button type="button" @click="open = false"
                                                class="text-ink-muted hover:text-ink-primary">
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @error('fecha_evento')
                                    <span class="text-xs text-rose-400 mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <x-ui.input class="min-w-0" type="time" label="Hora del evento" required
                                name="hora_evento" wire:model="hora_evento" />

                            <div class="min-w-0">
                                <x-ui.input type="number" step="0.5" label="Duración (horas)" required
                                    name="duracion_horas" wire:model="duracion_horas" placeholder="1.0" />
                                @php $servicioSeleccionado = $servicios->firstWhere('id', $servicio_id); @endphp
                                @if ($servicioSeleccionado)
                                    <p class="text-xs text-ink-muted mt-1">
                                        Servicio: {{ $servicioSeleccionado->duracion_minutos }} min ≈ {{ number_format($servicioSeleccionado->duracion_minutos / 60, 2) }} h
                                    </p>
                                @endif
                            </div>

                            <div class="lg:col-span-3 sm:col-span-2 min-w-0">
                                @php
                                    $telModal = preg_replace('/[^0-9]/', '', $telefono_contacto ?: '');
                                    if ($telModal && strlen($telModal) === 8) {
                                        $telModal = '591' . $telModal;
                                    }
                                @endphp
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-semibold text-ink-primary">Dirección del evento (lugar exacto): <span class="text-rose-500 font-black text-base leading-none">*</span></label>
                                    @if ($telModal)
                                        <a href="https://api.whatsapp.com/send?phone={{ $telModal }}&text={{ urlencode('Hola, le escribimos del Mariachi León Guanajuato sobre su evento ' . $codigo_evento . '. Por favor compártenos la ubicación o referencias de la presentación.') }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-600 text-white hover:bg-emerald-500 transition-colors shrink-0"
                                            title="Abrir WhatsApp del cliente">
                                            <i class="fa-brands fa-whatsapp"></i>
                                            <span>WhatsApp</span>
                                        </a>
                                    @endif
                                </div>
                                <input type="text" wire:model="direccion_evento"
                                    placeholder="ej. Salón de Eventos Las Palmas, Av. Banzer 4to Anillo"
                                    class="w-full h-9 px-3.5 bg-surface-sunken border @error('direccion_evento') border-rose-500 ring-1 ring-rose-500 @else border-outline @enderror rounded-lg text-ink-primary text-sm placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                                @error('direccion_evento')
                                    <span class="text-xs text-rose-400 mt-1 block font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <x-ui.textarea class="lg:col-span-3 sm:col-span-2 min-w-0" label="Observaciones"
                                wire:model="observaciones" rows="1"
                                placeholder="Indicaciones especiales para la llegada del mariachi, vestimenta, canciones requeridas..." />
                        </div>

                        <!-- SECCIÓN: Participantes sugeridos y distribución económica -->
                        <div class="pt-4 border-t border-outline space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface-card p-4 border border-gold-500/30 rounded-lg">
                                <h4 class="text-sm font-bold text-ink-primary flex items-center gap-2">
                                    <i class="fa-solid fa-users-gear text-gold-600 dark:text-gold-400"></i>
                                    <span>Participantes sugeridos y distribución económica</span>
                                    <i class="fa-solid fa-circle-info text-ink-muted text-xs cursor-help"
                                        title="Integrantes sugeridos según la plantilla del servicio seleccionado. Puedes marcar/desmarcar, ajustar montos o agregar personas adicionales."></i>
                                </h4>
                                @if ($servicio_id)
                                    <div class="flex items-center gap-2">
                                        <x-ui.button variant="secondary" size="sm" wire:click="recargarPlantillaServicio"
                                            wire:loading.attr="disabled"
                                            title="Obtener o actualizar integrantes desde la plantilla del servicio seleccionado">
                                            <i class="fa-solid fa-rotate text-gold-600 dark:text-gold-400" wire:loading.class="fa-spin"></i>
                                            <span>Obtener integrantes</span>
                                        </x-ui.button>
                                        <x-ui.button variant="primary" size="sm" wire:click="abrirModalAgregarParticipante">
                                            <i class="fa-solid fa-user-plus"></i>
                                            <span>Agregar otro</span>
                                        </x-ui.button>
                                    </div>
                                @endif
                            </div>

                            @if (session()->has('participante_success'))
                                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-700 dark:text-emerald-400 text-xs flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-sm"></i>
                                    <span>{{ session('participante_success') }}</span>
                                </div>
                            @endif

                            @if (session()->has('participante_error'))
                                <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-lg text-rose-700 dark:text-rose-400 text-xs flex items-center gap-2">
                                    <i class="fa-solid fa-circle-exclamation text-sm"></i>
                                    <span>{{ session('participante_error') }}</span>
                                </div>
                            @endif

                            @error('participantes')
                                <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-lg text-rose-700 dark:text-rose-400 text-xs flex items-center gap-2">
                                    <i class="fa-solid fa-circle-exclamation text-sm"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror

                            @if ($servicio_id)
                                @if (count($participantesSugeridos) > 0)
                                    <x-ui.card :padding="false" class="overflow-hidden">
                                        <x-ui.table>
                                            <x-slot:header>
                                                <th class="py-1.5 px-3 border-r border-outline text-center w-12">Incluir</th>
                                                <th class="py-1.5 px-3 border-r border-outline">Participante</th>
                                                <th class="py-1.5 px-3 border-r border-outline">Función</th>
                                                <th class="py-1.5 px-3 border-r border-outline">Concepto</th>
                                                <th class="py-1.5 px-3 border-r border-outline text-center">Tipo / Sugerido</th>
                                                <th class="py-1.5 px-3 border-r border-outline text-right w-36">Monto Asignado (Bs)</th>
                                                <th class="py-1.5 px-3 text-right w-px whitespace-nowrap">Acción</th>
                                            </x-slot:header>
                                            @php
                                                        $totalFijos = 0;
                                                        $totalPorcentajes = 0;
                                                        $totalGeneral = 0;
                                                        $countSeleccionados = 0;
                                                    @endphp
                                                    @foreach ($participantesSugeridos as $idx => $p)
                                                        @php
                                                            $monto = (float) ($p['monto_asignado'] ?? 0);
                                                            $isIncluido = !empty($p['incluido']);
                                                            if ($isIncluido) {
                                                                $countSeleccionados++;
                                                                $totalGeneral += $monto;
                                                                if (($p['tipo_calculo'] ?? '') === 'porcentaje') {
                                                                    $totalPorcentajes += $monto;
                                                                } else {
                                                                    $totalFijos += $monto;
                                                                }
                                                            }
                                                        @endphp
                                                        <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ !$isIncluido ? 'opacity-50' : '' }}">
                                                            <td class="py-1.5 px-3 border-r border-outline text-center">
                                                                <input type="checkbox"
                                                                    wire:click="toggleIncluidoParticipante({{ $idx }})"
                                                                    {{ $isIncluido ? 'checked' : '' }}
                                                                    class="w-4 h-4 rounded bg-surface-sunken border-outline text-gold-500 focus:ring-gold-500 cursor-pointer">
                                                            </td>
                                                            <td class="py-1.5 px-3 border-r border-outline">
                                                                <div class="font-semibold text-ink-primary flex items-center gap-1.5">
                                                                    <span>{{ $p['nombre_completo'] }}</span>
                                                                    @if (($p['origen'] ?? '') === 'adicional')
                                                                        <x-ui.badge variant="gold">Adicional</x-ui.badge>
                                                                    @endif
                                                                </div>
                                                                @if (!empty($p['ci_nit']))
                                                                    <div class="text-[11px] text-ink-muted">CI: {{ $p['ci_nit'] }}</div>
                                                                @endif
                                                                @if (!empty($p['tiene_pagos']))
                                                                    <x-ui.badge variant="success" class="mt-0.5">
                                                                        Pagado: Bs {{ number_format($p['total_pagado'], 2) }}
                                                                    </x-ui.badge>
                                                                @endif
                                                            </td>
                                                            <td class="py-1.5 px-3 border-r border-outline">
                                                                <input type="text"
                                                                    wire:model="participantesSugeridos.{{ $idx }}.funcion"
                                                                    class="w-28 h-8 px-2 bg-surface-sunken border border-outline rounded-lg text-ink-primary text-xs focus:border-gold-500 focus:outline-none">
                                                            </td>
                                                            <td class="py-1.5 px-3 border-r border-outline">
                                                                <input type="text"
                                                                    wire:model="participantesSugeridos.{{ $idx }}.concepto_pago"
                                                                    class="w-32 h-8 px-2 bg-surface-sunken border border-outline rounded-lg text-ink-primary text-xs focus:border-gold-500 focus:outline-none">
                                                            </td>
                                                            <td class="py-1.5 px-3 border-r border-outline text-center">
                                                                @if (($p['tipo_calculo'] ?? '') === 'porcentaje')
                                                                    <div class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold">
                                                                        {{ number_format($p['valor_sugerido'], 2) }}%
                                                                    </div>
                                                                    <div class="text-[10px] text-ink-muted">
                                                                        (Sugerido: Bs {{ number_format($p['monto_sugerido'], 2) }})
                                                                    </div>
                                                                @else
                                                                    <div class="text-[11px] text-sky-600 dark:text-sky-400 font-semibold">
                                                                        Fijo
                                                                    </div>
                                                                    <div class="text-[10px] text-ink-muted">
                                                                        Bs {{ number_format($p['valor_sugerido'], 2) }}
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="py-1.5 px-3 border-r border-outline text-right">
                                                                <div class="relative min-w-[125px]">
                                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-ink-muted text-xs font-semibold">Bs</span>
                                                                    <input type="number" step="0.50"
                                                                        min="0"
                                                                        wire:model.live.debounce.300ms="participantesSugeridos.{{ $idx }}.monto_asignado"
                                                                        class="w-full h-8 pl-8 pr-2 bg-surface-sunken border border-outline rounded-lg text-ink-primary font-semibold text-xs text-right tabular-nums focus:border-gold-500 focus:outline-none {{ abs((float) ($p['monto_asignado'] ?? 0) - (float) ($p['monto_sugerido'] ?? 0)) > 0.01 ? 'border-amber-500/50 text-amber-600 dark:text-amber-400' : '' }}">
                                                                </div>
                                                                @if (abs((float) ($p['monto_asignado'] ?? 0) - (float) ($p['monto_sugerido'] ?? 0)) > 0.01)
                                                                    <div class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5">
                                                                        Monto modificado</div>
                                                                @endif
                                                            </td>
                                                            <td class="py-1.5 px-3 text-right w-px whitespace-nowrap">
                                                                @if ($isIncluido)
                                                                    <x-ui.icon-button variant="danger" wire:click="quitarParticipante({{ $idx }})" title="Excluir de este evento">
                                                                        <i class="fa-solid fa-user-minus text-xs"></i>
                                                                    </x-ui.icon-button>
                                                                @else
                                                                    <x-ui.icon-button variant="success" wire:click="toggleIncluidoParticipante({{ $idx }})" title="Reincorporar al evento">
                                                                        <i class="fa-solid fa-user-check text-xs"></i>
                                                                    </x-ui.icon-button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                        </x-ui.table>

                                        <!-- Resumen de totales de distribución -->
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3.5 border-t border-outline text-xs">
                                            <div>
                                                <span class="text-ink-muted block text-[11px] font-semibold">Seleccionados</span>
                                                <span class="text-ink-primary font-bold text-sm">{{ $countSeleccionados }}
                                                    de {{ count($participantesSugeridos) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-ink-muted block text-[11px] font-semibold">Total Fijos</span>
                                                <span class="text-sky-600 dark:text-sky-400 font-bold text-sm tabular-nums">Bs
                                                    {{ number_format($totalFijos, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-ink-muted block text-[11px] font-semibold">Total Porcentajes</span>
                                                <span class="text-amber-600 dark:text-amber-400 font-bold text-sm tabular-nums">Bs
                                                    {{ number_format($totalPorcentajes, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-ink-muted block text-[11px] font-semibold">Total Asignado Evento</span>
                                                <span class="text-emerald-600 dark:text-emerald-400 font-bold text-base tabular-nums">Bs
                                                    {{ number_format($totalGeneral, 2) }}</span>
                                            </div>
                                        </div>
                                    </x-ui.card>
                                @else
                                    <div
                                        class="p-6 bg-surface-card border border-dashed border-outline rounded-lg text-center space-y-3">
                                        <i class="fa-solid fa-users-slash text-2xl text-ink-muted"></i>
                                        <div class="text-xs text-ink-muted">Este servicio no tiene integrantes
                                            predeterminados en su plantilla económica o aún no han sido cargados.</div>
                                        <div class="flex items-center justify-center gap-2 pt-1">
                                            <x-ui.button variant="primary" size="sm" wire:click="recargarPlantillaServicio">
                                                <i class="fa-solid fa-rotate"></i>
                                                <span>Obtener integrantes del servicio</span>
                                            </x-ui.button>
                                            <x-ui.button variant="secondary" size="sm" wire:click="abrirModalAgregarParticipante">
                                                <i class="fa-solid fa-plus"></i>
                                                <span>Agregar participante manualmente</span>
                                            </x-ui.button>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="p-4 bg-surface-card border border-outline rounded-lg text-center text-xs text-ink-muted">
                                    <i class="fa-solid fa-arrow-up mr-1 text-gold-600 dark:text-gold-400"></i> Seleccione un servicio
                                    contratado para cargar automáticamente los participantes sugeridos.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Banner Inferior de Advertencia Visible cuando se está abajo -->
                    @if ($errors->any() || session()->has('participante_error') || session()->has('modal_error'))
                        <div class="px-4 py-3 rounded-lg bg-rose-500/10 border border-rose-500/30 flex items-center justify-between text-xs text-rose-700 dark:text-rose-400">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation text-sm shrink-0"></i>
                                <span class="font-medium">
                                    @if (session()->has('participante_error'))
                                        {{ session('participante_error') }}
                                    @elseif(session()->has('modal_error'))
                                        {{ session('modal_error') }}
                                    @else
                                        Existen datos obligatorios incompletos o inválidos en el formulario.
                                    @endif
                                </span>
                            </div>
                            <button type="button"
                                @click="document.getElementById('modalEventoBody')?.scrollTo({top: 0, behavior: 'smooth'})"
                                class="text-xs hover:underline font-semibold shrink-0 ml-3 flex items-center gap-1">
                                <span>Ver campos faltantes</span>
                                <i class="fa-solid fa-arrow-up"></i>
                            </button>
                        </div>
                    @endif

                </div>

                <x-slot:footer>
                    <x-ui.button variant="secondary" size="sm" type="button" wire:click="$set('modalOpen', false)">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </x-ui.button>
                    <x-ui.button variant="primary" size="sm" type="submit" wire:loading.attr="disabled" wire:target="guardar">
                        <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardar"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardar"></i>
                        <span>{{ $isEdit ? 'Actualizar' : 'Guardar' }}</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </form>
    @endif

    <!-- Modal Historial de Pagos y Estado Financiero -->
    @if ($modalHistorialPagosOpen && $eventoHistorial)
        <x-ui.modal maxWidth="max-w-4xl" onClose="cerrarHistorialPagos">
            <x-slot:header>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gold-500/10 border border-gold-500/30 flex items-center justify-center text-gold-600 dark:text-gold-400 text-lg shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-ink-primary">Historial de Pagos</h3>
                            <x-ui.badge variant="gold">{{ $eventoHistorial->codigo_evento }}</x-ui.badge>
                        </div>
                        <p class="text-xs text-ink-muted truncate">
                            {{ $eventoHistorial->cliente->nombre_completo ?? 'Cliente N/A' }}
                            @if ($resumenFinanciero['tiene_contrato'])
                                - Contrato: <strong class="text-ink-secondary">{{ $resumenFinanciero['numero_contrato'] }}</strong>
                            @else
                                - <span class="text-amber-600 dark:text-amber-400 italic">Sin contrato formal</span>
                            @endif
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <div class="p-6 space-y-6">
                <!-- Tarjetas de Resumen Financiero -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-surface-card border border-outline rounded-lg p-4 flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg shrink-0">
                            <i class="fa-solid fa-handshake"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-semibold text-ink-muted block">Monto Contratado</span>
                            <span class="text-lg font-bold text-ink-primary tabular-nums">
                                Bs {{ number_format($resumenFinanciero['monto_total'], 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-surface-card border border-outline rounded-lg p-4 flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-semibold text-ink-muted block">Total Abonado</span>
                            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                Bs {{ number_format($resumenFinanciero['total_abonado'], 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-surface-card border border-outline rounded-lg p-4 flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-lg {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'bg-rose-500/10 border-rose-500/20 text-rose-600 dark:text-rose-400' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400' }} border flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'fa-hourglass-half' : 'fa-check-double' }}"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-semibold text-ink-muted block">Saldo Pendiente</span>
                            <span class="text-lg font-bold tabular-nums {{ $resumenFinanciero['saldo_pendiente'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Bs {{ number_format($resumenFinanciero['saldo_pendiente'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Historial de Pagos -->
                <x-ui.card :padding="false" class="overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-outline flex items-center justify-between">
                        <h4 class="text-xs font-semibold text-ink-primary flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-gold-600 dark:text-gold-400"></i>
                            <span>Movimientos y Cobranzas Registradas ({{ count($pagosHistorial) }})</span>
                        </h4>
                        <span class="text-[11px] text-ink-muted">Pagos anulados no suman al saldo</span>
                    </div>

                    <x-ui.table>
                        <x-slot:header>
                            <th class="py-1.5 px-3 border-r border-outline">N° Recibo</th>
                            <th class="py-1.5 px-3 border-r border-outline">Fecha y Hora</th>
                            <th class="py-1.5 px-3 border-r border-outline">Detalle / Concepto</th>
                            <th class="py-1.5 px-3 border-r border-outline text-right">Importe</th>
                            <th class="py-1.5 px-3 border-r border-outline">Método</th>
                            <th class="py-1.5 px-3 border-r border-outline">Cobró</th>
                            <th class="py-1.5 px-3 border-r border-outline">Estado</th>
                            <th class="py-1.5 px-3 text-right w-px whitespace-nowrap">Recibo</th>
                        </x-slot:header>

                        @forelse ($pagosHistorial as $pago)
                            <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $pago->estado === 'Anulado' ? 'opacity-60' : '' }}">
                                <td class="py-1.5 px-3 border-r border-outline text-xs text-gold-700 dark:text-gold-400 font-semibold">
                                    {{ $pago->numero_recibo }}
                                </td>
                                <td class="py-1.5 px-3 border-r border-outline text-xs text-ink-secondary whitespace-nowrap">
                                    {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') . ' - ' . $pago->fecha_pago->format('H:i') : '-' }}
                                </td>
                                <td class="py-1.5 px-3 border-r border-outline text-xs text-ink-secondary max-w-xs truncate"
                                    title="{{ $pago->observaciones }}">
                                    {{ $pago->observaciones ?: 'Cobranza / Abono del servicio' }}
                                </td>
                                <td class="py-1.5 px-3 border-r border-outline text-right text-xs font-semibold tabular-nums {{ $pago->estado === 'Anulado' ? 'text-ink-muted line-through' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    Bs {{ number_format($pago->monto, 2) }}
                                </td>
                                <td class="py-1.5 px-3 border-r border-outline text-xs text-ink-secondary">
                                    {{ $pago->metodo_pago }}
                                </td>
                                <td class="py-1.5 px-3 border-r border-outline text-xs text-ink-secondary">
                                    {{ $pago->user->name ?? 'Sistema' }}
                                </td>
                                <td class="py-1.5 px-3 border-r border-outline">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-secondary">
                                        <span class="w-2 h-2 rounded-full shrink-0 {{ $pago->estado === 'Valido' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $pago->estado }}
                                    </span>
                                </td>
                                <td class="py-1.5 px-3 text-right w-px whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui.icon-button variant="primary" href="{{ route('comercial.pagos.recibo', $pago->codigo_verificacion) }}"
                                            target="_blank" title="Ver / Imprimir Recibo">
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </x-ui.icon-button>
                                        <x-ui.icon-button variant="danger" href="{{ route('comercial.pagos.pdf', $pago->codigo_verificacion) }}"
                                            target="_blank" title="Descargar PDF">
                                            <i class="fa-solid fa-file-pdf text-xs"></i>
                                        </x-ui.icon-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-ink-muted text-sm">
                                    <i class="fa-solid fa-receipt text-2xl text-ink-muted mb-2 block"></i>
                                    No se registran pagos asociados a este evento.
                                </td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <x-slot:footer>
                <div class="flex items-center gap-2 flex-wrap mr-auto">
                    @if (
                        !empty($resumenFinanciero['tiene_contrato']) &&
                            !empty($resumenFinanciero['contrato_id']) &&
                            $eventoHistorial->cliente_id)
                        @php
                            $cToken = \App\Services\Security\DocumentToken::encodeId($eventoHistorial->cliente_id, 'cliente');
                            $ctrToken = \App\Services\Security\DocumentToken::encodeId($resumenFinanciero['contrato_id'], 'contrato');
                        @endphp
                        <x-ui.button variant="primary" size="sm"
                            href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $cToken, 'contrato' => $ctrToken, 'tipo' => 'contrato']) }}"
                            target="_blank"
                            title="Ver Extracto de Pagos del Contrato {{ $resumenFinanciero['numero_contrato'] }} en formato PDF oficial con QR">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Extracto de Pagos (PDF)</span>
                        </x-ui.button>

                        <x-ui.button variant="danger" size="sm"
                            href="{{ route('admin.clientes.estado-cuenta.pdf', ['token' => $cToken, 'tipo' => 'general']) }}"
                            target="_blank" title="Ver Extracto de Pagos Global Consolidado del Cliente en PDF">
                            <i class="fa-solid fa-receipt"></i>
                            <span>Extracto General del Cliente</span>
                        </x-ui.button>

                        <a href="{{ route('admin.clientes.estado-cuenta', ['token' => $cToken, 'contrato' => $ctrToken]) }}"
                            class="text-xs font-semibold text-ink-secondary hover:text-gold-600 dark:hover:text-gold-400 hover:underline flex items-center gap-1.5"
                            title="Ver Estado de Cuenta interactivo en el sistema">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            <span>Estado de Cuenta Web</span>
                        </a>
                    @endif
                </div>
                <x-ui.button variant="secondary" size="sm" wire:click="cerrarHistorialPagos">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cerrar</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Comisiones a Músicos y Personal por Evento -->
    @if ($modalComisionesOpen && $eventoComisiones)
        @php
            $tokenEvCom =
                $resumenComisiones['token'] ??
                \App\Services\Security\DocumentToken::encodeId($eventoComisiones->id, 'evento');
            $dotEstadoCom = match (true) {
                in_array($eventoComisiones->estado, ['Confirmado', 'Realizado']) => 'bg-emerald-500',
                $eventoComisiones->estado === 'Pendiente' => 'bg-amber-500',
                $eventoComisiones->estado === 'Cancelado' => 'bg-rose-500',
                default => 'bg-slate-400',
            };
        @endphp
        <x-ui.modal maxWidth="max-w-4xl" onClose="cerrarComisiones">
            <x-slot:header>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg shrink-0">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-bold text-ink-primary">Comisiones a Músicos y Personal</h3>
                            <x-ui.badge variant="gold">{{ $eventoComisiones->codigo_evento }}</x-ui.badge>
                            @if ($eventoComisiones->contrato)
                                <x-ui.badge variant="neutral">Contrato: {{ $eventoComisiones->contrato->numero_contrato }}</x-ui.badge>
                            @else
                                <span class="text-amber-600 dark:text-amber-400 italic text-xs">Sin contrato formal</span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-secondary">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $dotEstadoCom }}"></span>
                                {{ $eventoComisiones->estado }}
                            </span>
                        </div>
                        <p class="text-xs text-ink-muted truncate">
                            {{ $eventoComisiones->cliente->nombre_completo ?? 'N/A' }}
                            - {{ $eventoComisiones->servicio->nombre ?? 'Servicio Estándar' }}
                            - {{ $eventoComisiones->fecha_evento ? $eventoComisiones->fecha_evento->format('d/m/Y') : '-' }} {{ $eventoComisiones->hora_evento }}
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <!-- Notificación Flash dentro del modal -->
            @if (session()->has('comision_success'))
                <div class="mx-6 mt-4 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-700 dark:text-emerald-400 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                    <span>{{ session('comision_success') }}</span>
                </div>
            @endif

            <div class="p-6 space-y-6">
                    <!-- Tarjetas de Resumen Financiero -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                        <div class="bg-surface-card border border-outline rounded-lg p-3.5">
                            <div class="flex items-center justify-between text-ink-muted mb-1">
                                <span class="text-[11px] font-semibold">Total Evento</span>
                                <i class="fa-solid fa-money-bill-wave text-blue-600 dark:text-blue-400 text-xs"></i>
                            </div>
                            <span class="text-base sm:text-lg font-bold text-ink-primary tabular-nums block">
                                Bs {{ number_format($resumenComisiones['monto_evento'] ?? 0, 2) }}
                            </span>
                            <span class="text-[11px] text-ink-muted">Cobrado al cliente</span>
                        </div>

                        <div class="bg-surface-card border border-outline rounded-lg p-3.5">
                            <div class="flex items-center justify-between text-ink-muted mb-1">
                                <span class="text-[11px] font-semibold">Total Comisiones</span>
                                <i class="fa-solid fa-users text-sky-600 dark:text-sky-400 text-xs"></i>
                            </div>
                            <span class="text-base sm:text-lg font-bold text-sky-600 dark:text-sky-400 tabular-nums block">
                                Bs {{ number_format($resumenComisiones['total_comisiones'] ?? 0, 2) }}
                            </span>
                            <span class="text-[11px] text-ink-muted">{{ $resumenComisiones['count_participantes'] ?? 0 }} integrante(s)</span>
                        </div>

                        <div class="bg-surface-card border border-outline rounded-lg p-3.5">
                            <div class="flex items-center justify-between text-ink-muted mb-1">
                                <span class="text-[11px] font-semibold">Comisiones Pagadas</span>
                                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-xs"></i>
                            </div>
                            <span class="text-base sm:text-lg font-bold text-emerald-600 dark:text-emerald-400 tabular-nums block">
                                Bs {{ number_format($resumenComisiones['total_pagado'] ?? 0, 2) }}
                            </span>
                            <span class="text-[11px] text-ink-muted">{{ $resumenComisiones['count_pagados'] ?? 0 }} liquidado(s)</span>
                        </div>

                        <div class="bg-surface-card border border-outline rounded-lg p-3.5">
                            <div class="flex items-center justify-between text-ink-muted mb-1">
                                <span class="text-[11px] font-semibold">Margen Grupo</span>
                                <i class="fa-solid fa-vault text-gold-600 dark:text-gold-400 text-xs"></i>
                            </div>
                            <span class="text-base sm:text-lg font-bold tabular-nums block {{ ($resumenComisiones['margen_grupo'] ?? 0) >= 0 ? 'text-gold-700 dark:text-gold-400' : 'text-rose-600 dark:text-rose-400' }}">
                                Bs {{ number_format($resumenComisiones['margen_grupo'] ?? 0, 2) }}
                            </span>
                            <span class="text-[11px] text-ink-muted">Utilidad Mariachi León</span>
                        </div>
                    </div>

                    <!-- Tabla de Músicos y Personal -->
                    <x-ui.card :padding="false" class="overflow-hidden">
                        <div class="px-4 py-2.5 border-b border-outline flex items-center justify-between flex-wrap gap-2">
                            <h4 class="text-xs font-semibold text-ink-primary flex items-center gap-2">
                                <i class="fa-solid fa-users-gear text-gold-600 dark:text-gold-400"></i>
                                <span>Integrantes y Liquidación por Persona ({{ $eventoComisiones->participantes->count() }})</span>
                            </h4>
                            <span class="text-[11px] text-ink-muted">
                                Haz clic en el estado para marcar como <strong>Pagado</strong> o <strong>Pendiente</strong>
                            </span>
                        </div>

                        <x-ui.table>
                            <x-slot:header>
                                <th class="py-1.5 px-3 border-r border-outline">Músico / Personal</th>
                                <th class="py-1.5 px-3 border-r border-outline">Función / Rol</th>
                                <th class="py-1.5 px-3 border-r border-outline">Concepto</th>
                                <th class="py-1.5 px-3 border-r border-outline text-right">Comisión</th>
                                <th class="py-1.5 px-3 border-r border-outline">Estado Liquidación</th>
                                <th class="py-1.5 px-3 text-right w-px whitespace-nowrap">Acción Rápida</th>
                            </x-slot:header>

                            @forelse ($eventoComisiones->participantes as $part)
                                <tr class="border-b border-outline hover:bg-gold-500/5 transition-colors">
                                    <td class="py-1.5 px-3 border-r border-outline">
                                        <div class="font-semibold text-sm text-ink-primary">
                                            {{ $part->persona->nombre_completo ?? ($part->persona->nombre ?? 'N/A') }}
                                        </div>
                                        <div class="text-[11px] text-ink-muted">
                                            CI: {{ $part->persona->ci_nit ?? '-' }} - Tel: {{ $part->persona->telefono ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-outline text-xs text-ink-secondary">
                                        {{ $part->funcion }}
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-outline text-xs text-ink-secondary">
                                        {{ $part->concepto_pago ?: 'Comisión por Evento' }}
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-outline text-right text-sm font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                        Bs {{ number_format($part->monto_asignado, 2) }}
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-outline">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-secondary">
                                            <span class="w-2 h-2 rounded-full shrink-0 {{ $part->estado_distribucion === 'Distribuido' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                            {{ $part->estado_distribucion === 'Distribuido' ? 'Distribuido / Pagado' : 'Pendiente de pago' }}
                                        </span>
                                    </td>
                                    <td class="py-1.5 px-3 text-right w-px whitespace-nowrap">
                                        @if ($part->estado_distribucion === 'Distribuido')
                                            <x-ui.button variant="secondary" size="sm"
                                                wire:click="solicitarDesmarcarComision({{ $part->id }})"
                                                wire:loading.attr="disabled"
                                                title="Desmarcar comisión y revertir pago con observación">
                                                <i class="fa-solid fa-arrow-rotate-left"></i>
                                                <span>Desmarcar</span>
                                            </x-ui.button>
                                        @else
                                            <x-ui.button variant="primary" size="sm"
                                                wire:click="marcarComisionPagada({{ $part->id }})"
                                                wire:loading.attr="disabled"
                                                title="Marcar como Pagado / Distribuido">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Marcar Pagado</span>
                                            </x-ui.button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-ink-muted text-sm">
                                        <i class="fa-solid fa-user-slash text-2xl text-ink-muted mb-2 block"></i>
                                        No se han asignado músicos ni personal a este evento.
                                        <div class="mt-2">
                                            <a href="{{ route('admin.eventos.participantes', $eventoComisiones->id) }}"
                                                class="text-gold-600 dark:text-gold-400 hover:underline font-semibold text-xs">
                                                <i class="fa-solid fa-user-plus mr-1"></i>Asignar integrantes ahora
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </x-ui.table>
                    </x-ui.card>
                </div>

                <x-slot:footer>
                    <div class="flex items-center gap-2 flex-wrap mr-auto">
                        <x-ui.button variant="primary" size="sm"
                            href="{{ route('admin.eventos.comisiones.pdf', $tokenEvCom) }}" target="_blank"
                            title="Descargar Planilla Oficial de Comisiones y Liquidación a Músicos en formato PDF con QR">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Descargar Planilla en PDF (con QR)</span>
                        </x-ui.button>

                        <x-ui.button variant="secondary" size="sm"
                            href="{{ route('admin.eventos.participantes', $eventoComisiones->id) }}"
                            title="Ir a la pantalla de asignación completa de participantes">
                            <i class="fa-solid fa-users"></i>
                            <span>Gestionar / Agregar Integrantes</span>
                        </x-ui.button>
                    </div>
                    <x-ui.button variant="secondary" size="sm" wire:click="cerrarComisiones">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cerrar</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
    @endif

    <!-- Modal para Desmarcar Comisión y Revertir Pago -->
    @if ($modalDesmarcarOpen && $desmarcarParticipante)
        <form wire:submit.prevent="confirmarDesmarcarComision">
            <x-ui.modal title="Desmarcar y Revertir Pago" icon="fa-solid fa-rotate-left" :nested="true"
                onClose="cerrarModalDesmarcar">
                <div class="p-6 space-y-4">
                    <!-- Info Box del Participante -->
                    <div class="p-3.5 rounded-lg bg-surface-sunken border border-outline text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-ink-muted">Participante:</span>
                            <span class="font-semibold text-ink-primary text-sm">{{ $desmarcarParticipante->persona->nombre_completo ?? ($desmarcarParticipante->persona->nombre ?? 'N/A') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-ink-muted">Rol / Función:</span>
                            <span class="text-gold-700 dark:text-gold-400 font-semibold">{{ $desmarcarParticipante->funcion }}
                                ({{ $desmarcarParticipante->concepto_pago ?: 'Comisión por Evento' }})</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-ink-muted">Monto Comisión:</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400 text-sm tabular-nums">Bs {{ number_format($desmarcarParticipante->monto_asignado, 2) }}</span>
                        </div>
                        @if ($eventoComisiones)
                            <div class="flex items-center justify-between">
                                <span class="text-ink-muted">Evento:</span>
                                <span class="text-ink-secondary">{{ $eventoComisiones->codigo_evento }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Advertencia -->
                    <div class="p-3 rounded-lg bg-amber-500/10 border-l-[3px] border-amber-500 text-xs text-ink-primary flex items-start gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-sm text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                        <p>
                            Al desmarcar, el estado de la liquidación volverá a <strong>Pendiente de pago</strong>, se
                            revertirán los registros de pago y se actualizarán inmediatamente los totales de comisiones
                            pagadas. Esta acción se asentará en la <strong>Bitácora de Auditoría</strong>.
                        </p>
                    </div>

                    <x-ui.textarea label="Motivo / Observación de la Reversión" required name="observacionDesmarcar"
                        wire:model="observacionDesmarcar" rows="1"
                        placeholder="Ingrese el motivo o justificación para desmarcar esta comisión (ej: error en registro, cambio de integrante, ajuste de liquidación)..." />
                </div>

                <x-slot:footer>
                    <x-ui.button variant="secondary" size="sm" type="button" wire:click="cerrarModalDesmarcar">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </x-ui.button>
                    <x-ui.button variant="danger" size="sm" type="submit" wire:loading.attr="disabled"
                        wire:target="confirmarDesmarcarComision">
                        <i class="fa-solid fa-rotate-left" wire:loading.remove wire:target="confirmarDesmarcarComision"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="confirmarDesmarcarComision"></i>
                        <span wire:loading.remove wire:target="confirmarDesmarcarComision">Confirmar Reversión</span>
                        <span wire:loading wire:target="confirmarDesmarcarComision">Revirtiendo...</span>
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </form>
    @endif

    <!-- Modal Advertencia Cambio de Servicio -->
    @if ($modalCambioServicioOpen)
        <x-ui.modal title="Cambio de Servicio Detectado" icon="fa-solid fa-triangle-exclamation" :nested="true"
            maxWidth="max-w-md" onClose="cancelarCambioServicio">
            <div class="p-6 space-y-4">
                <div class="text-sm text-ink-primary space-y-2 bg-surface-sunken p-3.5 rounded-lg border border-outline">
                    <p>
                        ¿Desea cargar la plantilla de participantes predeterminada del nuevo servicio seleccionado o
                        conservar los participantes y montos actuales?
                    </p>
                    <p class="text-ink-muted text-xs">
                        <i class="fa-solid fa-shield-halved text-emerald-600 dark:text-emerald-400 mr-1"></i> Los participantes con pagos ya
                        registrados en este evento serán preservados automáticamente.
                    </p>
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" wire:click="cancelarCambioServicio">
                    <span>Conservar Actuales</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" wire:click="confirmarCambioServicio">
                    <span>Cargar Nueva Plantilla</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Agregar Participante Adicional al Evento -->
    @if ($mostrarModalAgregarParticipante)
        <x-ui.modal title="Incorporar Participante al Evento" icon="fa-solid fa-user-plus" :nested="true"
            onClose="cerrarModalAgregarParticipante">
            <div class="p-6 space-y-4">
                <x-ui.select label="Participante (Persona)" required name="nuevo_part_persona_id"
                    wire:model.live="nuevo_part_persona_id">
                    <option value="">-- Seleccionar Persona del Catálogo --</option>
                    @foreach ($personasDisponibles as $persona)
                        <option value="{{ $persona->id }}">
                            {{ $persona->nombre_completo }}
                            {{ $persona->ci_nit ? '(' . $persona->ci_nit . ')' : '' }} -
                            {{ $persona->tipo ?: 'Músico' }}
                        </option>
                    @endforeach
                </x-ui.select>

                <div class="grid grid-cols-2 gap-3">
                    <x-ui.input label="Función en el Evento" required name="nuevo_part_funcion"
                        wire:model="nuevo_part_funcion" placeholder="ej. Músico" />
                    <x-ui.input label="Concepto Económico" required name="nuevo_part_concepto"
                        wire:model="nuevo_part_concepto" placeholder="ej. Comisión por Evento" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-ui.select label="Tipo de Cálculo" wire:model.live="nuevo_part_tipo_calculo">
                        <option value="monto_fijo">Monto Fijo (Bs)</option>
                        <option value="porcentaje">Porcentaje (%)</option>
                    </x-ui.select>
                    <x-ui.input type="number" step="0.50" required name="nuevo_part_valor"
                        wire:model="nuevo_part_valor"
                        :label="$nuevo_part_tipo_calculo === 'porcentaje' ? 'Porcentaje (%)' : 'Monto (Bs)'" />
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" wire:click="cerrarModalAgregarParticipante">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" wire:click="agregarParticipanteAdicional">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Incorporar al Evento</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>