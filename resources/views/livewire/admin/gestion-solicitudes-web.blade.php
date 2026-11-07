@php
    $estadosMap = [
        'nuevo' => ['label' => 'Nuevo', 'dot' => 'bg-amber-500', 'txt' => 'text-amber-500'],
        'atendido' => ['label' => 'Atendido', 'dot' => 'bg-emerald-500', 'txt' => 'text-emerald-500'],
        'cotizado' => ['label' => 'Cotizado', 'dot' => 'bg-violet-500', 'txt' => 'text-violet-500'],
        'descartado' => ['label' => 'Descartado', 'dot' => 'bg-slate-400', 'txt' => 'text-slate-400'],
    ];
    $moneda = $empresa->moneda_simbolo ?? 'Bs.';
    $waUrl = function ($telefono, $nombre) {
        $tel = preg_replace('/[^0-9]/', '', $telefono ?? '');
        if (strlen($tel) === 8) {
            $tel = '591' . $tel;
        }
        $msg = urlencode(
            "Hola {$nombre}, un cordial saludo de Mariachi León Guanajuato. Recibimos tu solicitud de cotización y con gusto te atendemos.",
        );
        return "https://api.whatsapp.com/send?phone={$tel}&text={$msg}";
    };
    $hayFiltros =
        $search !== '' ||
        $filtro_estado !== '' ||
        $filtro_servicio !== '' ||
        $fecha_desde !== '' ||
        $fecha_hasta !== '';
@endphp

<div class="space-y-3">

    {{-- Avisos --}}
    @if (session()->has('success'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('info'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-sky-500/30 bg-sky-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-info text-lg text-sky-600 dark:text-sky-400"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <x-ui.panel>
        {{-- Cabecera --}}
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-inbox text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary">Solicitudes web</h1>
                <x-ui.badge variant="neutral">{{ $estados->sum() }}</x-ui.badge>
            </div>
            <x-ui.button size="sm" variant="secondary" href="{{ route('web.contacto') }}" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span class="hidden sm:inline">Ver formulario</span>
            </x-ui.button>
        </x-slot:header>

        {{-- Filtros: una sola fila --}}
        <x-slot:filters>
            @php
                $tarjetas = [
                    '' => [
                        'label' => 'Total',
                        'icon' => 'fa-layer-group',
                        'tono' => 'bg-surface-sunken text-ink-secondary',
                    ],
                    'nuevo' => [
                        'label' => 'Nuevas',
                        'icon' => 'fa-bell',
                        'tono' => 'bg-amber-500/15 text-amber-700 dark:text-amber-400',
                    ],
                    'atendido' => [
                        'label' => 'En atención',
                        'icon' => 'fa-comments',
                        'tono' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400',
                    ],
                    'cotizado' => [
                        'label' => 'Cotizadas',
                        'icon' => 'fa-file-invoice-dollar',
                        'tono' => 'bg-violet-500/15 text-violet-700 dark:text-violet-400',
                    ],
                    'descartado' => [
                        'label' => 'Descartadas',
                        'icon' => 'fa-ban',
                        'tono' => 'bg-surface-sunken text-ink-muted',
                    ],
                ];
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-2 mb-2">
                @foreach ($tarjetas as $clave => $t)
                    @php
                        $cantidad = $clave === '' ? $estados->sum() : $estados[$clave] ?? 0;
                        $activa = $filtro_estado === $clave;
                    @endphp
                    <button type="button" wire:click="$set('filtro_estado', '{{ $clave }}')"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg border bg-surface-card text-left transition-colors {{ $activa ? 'border-gold-500' : ($clave === 'nuevo' && $cantidad > 0 ? 'border-amber-500/50 hover:border-gold-500' : 'border-outline hover:border-gold-500/60') }}">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $t['tono'] }}">
                            <i class="fa-solid {{ $t['icon'] }} text-sm"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs text-ink-secondary truncate">{{ $t['label'] }}</span>
                            <span
                                class="block text-lg font-bold leading-tight tabular-nums text-ink-primary">{{ $cantidad }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
            <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="relative flex-1 min-w-0">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar cliente, teléfono o mensaje..."
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select wire:model.live="filtro_servicio"
                        class="h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors">
                        <option value="">Todos los servicios</option>
                        @foreach ($servicios as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                    <input type="date" wire:model.live="fecha_desde" title="Recibidas desde"
                        class="h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors">
                    @if ($hayFiltros)
                        <x-ui.icon-button wire:click="limpiarFiltros" title="Limpiar filtros">
                            <i class="fa-solid fa-rotate-left"></i>
                        </x-ui.icon-button>
                    @endif
                </div>
            </div>
        </x-slot:filters>

        {{-- Tabla (escritorio) --}}
        <div class="hidden md:block">
            <x-ui.table>
                <x-slot:header>
                    <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                    <th class="px-3 py-2 border-r border-outline">Cliente</th>
                    <th class="px-3 py-2 border-r border-outline">Servicio</th>
                    <th class="px-3 py-2 border-r border-outline">Mensaje</th>
                    <th class="px-3 py-2 border-r border-outline">Estado</th>
                    <th class="px-3 py-2 w-px whitespace-nowrap text-right">Acciones</th>
                </x-slot:header>

                @forelse ($solicitudes as $sol)
                    @php $cfg = $estadosMap[$sol->estado] ?? $estadosMap['descartado']; @endphp
                    <tr
                        class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $sol->estado === 'nuevo' ? 'bg-amber-500/5' : '' }}">
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <div class="tabular-nums text-ink-primary">{{ $sol->created_at->format('d/m/Y') }}<span
                                    class="text-ink-secondary"> - {{ $sol->created_at->format('H:i') }}</span></div>
                            <div class="text-xs text-ink-secondary">{{ $sol->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            <div class="font-semibold text-ink-primary whitespace-nowrap">{{ $sol->nombre }}</div>
                            <div class="flex items-center gap-2 text-xs">
                                @if ($sol->telefono)
                                    <a href="{{ $waUrl($sol->telefono, $sol->nombre) }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 hover:underline whitespace-nowrap"
                                        title="Abrir chat en WhatsApp">
                                        <i class="fa-brands fa-whatsapp"></i>{{ $sol->telefono }}
                                    </a>
                                @endif
                                @if ($sol->email)
                                    <a href="mailto:{{ $sol->email }}"
                                        class="text-ink-secondary hover:text-gold-700 dark:hover:text-gold-400 truncate max-w-[160px]">{{ $sol->email }}</a>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline">
                            @if ($sol->servicio)
                                <div class="font-semibold text-gold-700 dark:text-gold-400">
                                    {{ $sol->servicio->nombre }}</div>
                                <div class="text-xs text-ink-secondary tabular-nums">{{ $moneda }}
                                    {{ number_format($sol->servicio->precio_base, 2) }}</div>
                            @elseif ($sol->paquete)
                                <div class="font-semibold text-gold-700 dark:text-gold-400">{{ $sol->paquete->nombre }}
                                </div>
                                <div class="text-xs text-ink-secondary tabular-nums">{{ $moneda }}
                                    {{ number_format($sol->paquete->precio_paquete, 2) }}</div>
                            @else
                                <div class="text-ink-secondary italic">Consulta general</div>
                            @endif
                            @if ($sol->tipoEvento || $sol->fecha_estimada)
                                <div class="text-xs text-ink-secondary">
                                    {{ $sol->tipoEvento->nombre ?? '' }}
                                    @if ($sol->tipoEvento && $sol->fecha_estimada)
                                        -
                                    @endif
                                    @if ($sol->fecha_estimada)
                                        Para el {{ $sol->fecha_estimada->format('d/m/Y') }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline max-w-[260px]">
                            <button type="button" wire:click="verDetalle({{ $sol->id }})"
                                class="text-left w-full" title="Ver mensaje completo">
                                <span class="block text-sm text-ink-primary line-clamp-2">{{ $sol->mensaje }}</span>
                            </button>
                        </td>
                        <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap">
                            <span
                                class="inline-flex items-center gap-1.5 text-sm text-ink-primary {{ $sol->estado === 'nuevo' ? 'font-semibold' : '' }}">
                                <span class="w-2 h-2 rounded-full {{ $cfg['dot'] }}"></span>{{ $cfg['label'] }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 w-px whitespace-nowrap text-right">
                            <div class="inline-flex items-center gap-1">
                                <x-ui.icon-button variant="primary" wire:click="verDetalle({{ $sol->id }})"
                                    title="Ver detalle">
                                    <i class="fa-solid fa-eye"></i>
                                </x-ui.icon-button>
                                @if ($sol->telefono)
                                    <x-ui.icon-button href="{{ $waUrl($sol->telefono, $sol->nombre) }}" target="_blank"
                                        title="Contactar por WhatsApp">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </x-ui.icon-button>
                                @endif
                                <x-ui.action-menu>
                                    <x-ui.action-menu-item icon="fa-solid fa-user-plus"
                                        wire:click="convertirEnCliente({{ $sol->id }})">Registrar como
                                        cliente</x-ui.action-menu-item>
                                    @foreach ($estadosMap as $clave => $c)
                                        @if ($clave !== $sol->estado)
                                            <x-ui.action-menu-item icon="fa-solid fa-circle {{ $c['txt'] }}"
                                                wire:click="cambiarEstado({{ $sol->id }}, '{{ $clave }}')">Marcar
                                                como {{ strtolower($c['label']) }}</x-ui.action-menu-item>
                                        @endif
                                    @endforeach
                                    <x-ui.action-menu-item variant="danger" icon="fa-solid fa-trash-can"
                                        wire:click="eliminar({{ $sol->id }})"
                                        wire:confirm="¿Seguro que deseas eliminar esta solicitud web?">Eliminar</x-ui.action-menu-item>
                                </x-ui.action-menu>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-sm text-ink-secondary">
                            No se encontraron solicitudes. Las que lleguen desde el sitio público aparecerán aquí.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Lista compacta (móvil) --}}
        <div class="md:hidden divide-y divide-outline">
            @forelse ($solicitudes as $sol)
                @php $cfg = $estadosMap[$sol->estado] ?? $estadosMap['descartado']; @endphp
                <div
                    class="flex items-center gap-3 px-4 py-2.5 {{ $sol->estado === 'nuevo' ? 'bg-amber-500/5' : '' }}">
                    <button type="button" wire:click="verDetalle({{ $sol->id }})"
                        class="flex-1 min-w-0 text-left">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-sm text-ink-primary truncate">{{ $sol->nombre }}</span>
                            <span class="inline-flex items-center gap-1 text-xs text-ink-secondary shrink-0">
                                <span class="w-2 h-2 rounded-full {{ $cfg['dot'] }}"></span>{{ $cfg['label'] }}
                            </span>
                        </div>
                        <div class="text-xs text-ink-secondary truncate">
                            {{ $sol->servicio->nombre ?? ($sol->paquete->nombre ?? 'Consulta general') }} -
                            {{ $sol->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="text-sm text-ink-primary line-clamp-1">{{ $sol->mensaje }}</div>
                    </button>
                    <x-ui.action-menu>
                        <x-ui.action-menu-item icon="fa-solid fa-eye"
                            wire:click="verDetalle({{ $sol->id }})">Ver detalle</x-ui.action-menu-item>
                        @if ($sol->telefono)
                            <x-ui.action-menu-item icon="fa-brands fa-whatsapp"
                                href="{{ $waUrl($sol->telefono, $sol->nombre) }}"
                                target="_blank">WhatsApp</x-ui.action-menu-item>
                        @endif
                        <x-ui.action-menu-item icon="fa-solid fa-user-plus"
                            wire:click="convertirEnCliente({{ $sol->id }})">Registrar como
                            cliente</x-ui.action-menu-item>
                        @foreach ($estadosMap as $clave => $c)
                            @if ($clave !== $sol->estado)
                                <x-ui.action-menu-item icon="fa-solid fa-circle {{ $c['txt'] }}"
                                    wire:click="cambiarEstado({{ $sol->id }}, '{{ $clave }}')">Marcar
                                    como {{ strtolower($c['label']) }}</x-ui.action-menu-item>
                            @endif
                        @endforeach
                        <x-ui.action-menu-item variant="danger" icon="fa-solid fa-trash-can"
                            wire:click="eliminar({{ $sol->id }})"
                            wire:confirm="¿Seguro que deseas eliminar esta solicitud web?">Eliminar</x-ui.action-menu-item>
                    </x-ui.action-menu>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-ink-secondary">No se encontraron solicitudes.</div>
            @endforelse
        </div>

        <x-slot:footer>
            <span>Mostrando {{ $solicitudes->firstItem() ?? 0 }}–{{ $solicitudes->lastItem() ?? 0 }} de
                {{ $solicitudes->total() }}</span>
            <div>{{ $solicitudes->links() }}</div>
        </x-slot:footer>
    </x-ui.panel>

    {{-- Modal de detalle --}}
    @if ($modalDetalleOpen && $solicitudSeleccionada)
        @php
            $sel = $solicitudSeleccionada;
            $cfgSel = $estadosMap[$sel->estado] ?? $estadosMap['descartado'];
        @endphp
        <x-ui.modal icon="fa-solid fa-file-lines" title="Solicitud web #{{ $sel->id }}" maxWidth="max-w-2xl"
            onClose="cerrarModal">

            <div class="px-6 py-5 space-y-4">
                <p class="text-xs text-ink-secondary">
                    Recibida el {{ $sel->created_at->format('d/m/Y') }} - {{ $sel->created_at->format('H:i') }}
                    ({{ $sel->created_at->diffForHumans() }})
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    <div>
                        <span class="block text-xs text-ink-secondary">Cliente:</span>
                        <span class="text-sm font-semibold text-ink-primary">{{ $sel->nombre }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">WhatsApp:</span>
                        @if ($sel->telefono)
                            <a href="{{ $waUrl($sel->telefono, $sel->nombre) }}" target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 dark:text-emerald-400 hover:underline">
                                <i class="fa-brands fa-whatsapp"></i>{{ $sel->telefono }}
                            </a>
                        @else
                            <span class="text-sm text-ink-secondary">No proporcionado</span>
                        @endif
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">Correo:</span>
                        <span
                            class="text-sm text-ink-primary break-all">{{ $sel->email ?: 'No proporcionado' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary mb-0.5">Estado:</span>
                        <select wire:change="cambiarEstado({{ $sel->id }}, $event.target.value)"
                            class="h-8 px-3 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary focus:border-gold-500 focus:outline-none transition-colors">
                            @foreach ($estadosMap as $clave => $c)
                                <option value="{{ $clave }}" @selected($sel->estado === $clave)>{{ $c['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">Servicio:</span>
                        @if ($sel->servicio)
                            <span
                                class="text-sm font-semibold text-gold-700 dark:text-gold-400">{{ $sel->servicio->nombre }}</span>
                            <span class="text-xs text-ink-secondary tabular-nums"> - {{ $moneda }}
                                {{ number_format($sel->servicio->precio_base, 2) }}</span>
                        @elseif ($sel->paquete)
                            <span
                                class="text-sm font-semibold text-gold-700 dark:text-gold-400">{{ $sel->paquete->nombre }}</span>
                            <span class="text-xs text-ink-secondary tabular-nums"> - {{ $moneda }}
                                {{ number_format($sel->paquete->precio_paquete, 2) }}</span>
                        @else
                            <span class="text-sm text-ink-secondary italic">Consulta general</span>
                        @endif
                    </div>
                    <div>
                        <span class="block text-xs text-ink-secondary">Celebración y fecha:</span>
                        <span class="text-sm text-ink-primary">
                            {{ $sel->tipoEvento->nombre ?? 'No especificada' }} -
                            {{ $sel->fecha_estimada ? $sel->fecha_estimada->format('d/m/Y') : 'Por coordinar' }}
                        </span>
                    </div>
                </div>

                <div>
                    <span class="block text-xs text-ink-secondary mb-1">Mensaje del cliente:</span>
                    <div
                        class="p-3 rounded-lg bg-surface-sunken border border-outline text-sm text-ink-primary leading-relaxed whitespace-pre-wrap select-text">
                        {{ $sel->mensaje }}</div>
                </div>
            </div>

            <x-slot:footer>
                @if ($sel->telefono)
                    <x-ui.button type="button" variant="secondary"
                        href="{{ $waUrl($sel->telefono, $sel->nombre) }}" target="_blank">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </x-ui.button>
                @endif
                <x-ui.button type="button" variant="secondary" wire:click="convertirEnCliente({{ $sel->id }})"
                    wire:loading.attr="disabled" wire:target="convertirEnCliente">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Registrar como cliente</span>
                </x-ui.button>
                <x-ui.button type="button" wire:click="cerrarModal">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cerrar</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
