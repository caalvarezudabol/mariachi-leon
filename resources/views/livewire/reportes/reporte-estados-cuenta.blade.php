@php
    $urlPdf =
        $clienteSeleccionado && $tokenCliente
            ? route('admin.clientes.estado-cuenta.pdf', [
                'token' => $tokenCliente,
                'tipo' => $modalidad,
                'contrato' => $modalidad === 'contrato' ? $tokenContrato : null,
            ])
            : null;
    $saldo = (float) ($resumenFinanciero['saldo_pendiente'] ?? 0);
    $liquidado = $saldo <= 0.01;

    $clientesJs = collect($clientes)
        ->map(fn($c) => ['id' => $c->id, 'n' => $c->nombre_completo, 'k' => $c->contratos_count])
        ->values();

    $datosCliente = '';
    $variosContratos = $contratosCliente->count() > 1;
    if ($clienteSeleccionado) {
        $datosCliente = collect([
            $clienteSeleccionado->ci_nit ? 'C.I./NIT: ' . $clienteSeleccionado->ci_nit : null,
            $clienteSeleccionado->telefono ? 'Tel.: ' . $clienteSeleccionado->telefono : null,
            $clienteSeleccionado->email ?: null,
        ])
            ->filter()
            ->implode(' · ');
    }
@endphp

<div class="space-y-3">

    <x-ui.panel>
        <x-slot:header>
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-file-invoice-dollar text-lg text-gold-600 dark:text-gold-400"></i>
                <h1 class="text-base font-semibold text-ink-primary truncate">Estados de cuenta</h1>
            </div>
            <div class="flex items-center gap-2">
                @if ($urlPdf)
                    <x-ui.button size="sm" href="{{ $urlPdf }}" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>PDF</span>
                    </x-ui.button>
                @endif
                <x-ui.action-menu>
                    <x-ui.action-menu-item icon="fa-solid fa-receipt" href="{{ route('admin.pagos') }}"
                        wire:navigate>Cobros</x-ui.action-menu-item>
                </x-ui.action-menu>
            </div>
        </x-slot:header>

        <x-slot:filters>
            <div class="flex flex-col gap-2">
                <div class="flex flex-col md:flex-row md:items-start gap-2">
                    <x-ui.filter-chips>
                        <x-ui.filter-chip wire:click="$set('modalidad', 'contrato')" :active="$modalidad === 'contrato'">
                            Por contrato
                        </x-ui.filter-chip>
                        <x-ui.filter-chip wire:click="$set('modalidad', 'cliente')" :active="$modalidad === 'cliente'">
                            Todo el cliente
                        </x-ui.filter-chip>
                    </x-ui.filter-chips>

                    {{-- Buscador de cliente (escribe y elige) --}}
                    <div class="relative flex-1 min-w-[220px] max-w-md" x-data="{
                        open: false,
                        q: '',
                        val: $wire.entangle('cliente_id').live,
                        items: @js($clientesJs),
                        get lista() {
                            const t = this.q.toLowerCase();
                            return this.items.filter(i => i.n.toLowerCase().includes(t)).slice(0, 30);
                        },
                        get actual() {
                            const f = this.items.find(i => i.id == this.val);
                            return f ? f.n : '';
                        },
                        pick(i) { this.val = i.id;
                            this.open = false;
                            this.q = ''; },
                        clear() { this.val = '';
                            this.q = '';
                            this.open = false; }
                    }"
                        @click.outside="open = false">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-ink-muted text-xs pointer-events-none"></i>
                        <input type="text" :value="open ? q : actual" @input="q = $event.target.value"
                            @focus="open = true; q = ''" placeholder="Buscar cliente..."
                            class="w-full h-8 pl-8 pr-8 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors">
                        <button type="button" x-show="val" x-cloak @click="clear()"
                            class="absolute right-2 top-1.5 text-ink-muted hover:text-ink-primary text-xs px-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                        <div x-show="open" x-cloak
                            class="mt-1 max-h-56 overflow-y-auto rounded-lg border border-outline bg-surface-card">
                            <template x-for="i in lista" :key="i.id">
                                <button type="button" @click="pick(i)"
                                    class="w-full text-left px-3 py-1.5 text-sm text-ink-primary hover:bg-gold-500/10 flex items-center justify-between gap-2">
                                    <span class="truncate" x-text="i.n"></span>
                                    <span class="text-xs text-ink-secondary shrink-0" x-text="i.k + ' contr.'"></span>
                                </button>
                            </template>
                            <div x-show="lista.length === 0" class="px-3 py-2 text-sm text-ink-secondary">Sin resultados
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contratos del cliente (solo si tiene más de uno) --}}
                @if ($modalidad === 'contrato' && $variosContratos)
                    <x-ui.filter-chips>
                        @foreach ($contratosCliente as $ct)
                            <x-ui.filter-chip wire:click="$set('contrato_id', {{ $ct->id }})" :active="(string) $contrato_id === (string) $ct->id">
                                {{ $ct->numero_contrato }} · Bs {{ number_format($ct->monto_total, 0) }}
                            </x-ui.filter-chip>
                        @endforeach
                    </x-ui.filter-chips>
                @endif
            </div>
        </x-slot:filters>

        @if ($clienteSeleccionado)
            {{-- Cliente (solo datos que existen) --}}
            <div class="px-4 pt-3 text-sm">
                <span class="font-semibold text-ink-primary">{{ $clienteSeleccionado->nombre_completo }}</span>
                @if ($datosCliente !== '')
                    <span class="text-ink-secondary"> · {{ $datosCliente }}</span>
                @endif
            </div>

            {{-- Resumen --}}
            <div class="grid grid-cols-3 gap-2 p-4 border-b border-outline">
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="text-xs text-ink-secondary">Contratado</div>
                    <div class="text-base sm:text-lg font-semibold tabular-nums text-ink-primary">Bs
                        {{ number_format($resumenFinanciero['total_contratado'], 2) }}</div>
                </div>
                <div class="rounded-lg border border-outline bg-surface-sunken px-3 py-2">
                    <div class="text-xs text-ink-secondary">Abonado</div>
                    <div class="text-base sm:text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">
                        Bs {{ number_format($resumenFinanciero['total_cobrado'], 2) }}</div>
                </div>
                <div
                    class="rounded-lg border px-3 py-2 {{ $liquidado ? 'border-emerald-500/30 bg-emerald-500/10' : 'border-rose-500/30 bg-rose-500/10' }}">
                    <div class="text-xs text-ink-secondary">{{ $liquidado ? 'Al día' : 'Saldo pendiente' }}</div>
                    <div
                        class="text-base sm:text-lg font-semibold tabular-nums {{ $liquidado ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                        Bs {{ number_format($saldo, 2) }}</div>
                </div>
            </div>

            {{-- Detalle (escritorio) --}}
            <div class="hidden md:block">
                <x-ui.table>
                    <x-slot:header>
                        <th class="px-3 py-2 border-r border-outline whitespace-nowrap">Fecha</th>
                        <th class="px-3 py-2 border-r border-outline">Recibo</th>
                        @if ($modalidad === 'cliente')
                            <th class="px-3 py-2 border-r border-outline">Contrato</th>
                        @endif
                        <th class="px-3 py-2 border-r border-outline">Método</th>
                        <th class="px-3 py-2 border-r border-outline text-right">Importe</th>
                        <th class="px-3 py-2 text-right">Saldo</th>
                    </x-slot:header>

                    @forelse ($pagos as $p)
                        @php $anulado = $p->estado === 'Anulado'; @endphp
                        <tr
                            class="border-b border-outline hover:bg-gold-500/5 transition-colors {{ $anulado ? 'opacity-60' : '' }}">
                            <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap tabular-nums">
                                {{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline whitespace-nowrap font-semibold {{ $anulado ? 'line-through text-rose-700 dark:text-rose-400' : 'text-gold-700 dark:text-gold-400' }}">
                                {{ $p->numero_recibo }}
                                @if ($anulado)
                                    <span class="text-xs font-normal no-underline text-rose-700 dark:text-rose-400">·
                                        anulado</span>
                                @endif
                            </td>
                            @if ($modalidad === 'cliente')
                                <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-ink-primary">
                                    {{ $p->contrato->numero_contrato ?? '-' }}</td>
                            @endif
                            <td class="px-3 py-1.5 border-r border-outline whitespace-nowrap text-ink-primary">
                                {{ $p->metodo_pago }}</td>
                            <td
                                class="px-3 py-1.5 border-r border-outline text-right whitespace-nowrap tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : 'text-emerald-700 dark:text-emerald-400' }}">
                                Bs {{ number_format($p->monto, 2) }}</td>
                            <td
                                class="px-3 py-1.5 text-right whitespace-nowrap tabular-nums {{ ($p->saldo_posterior ?? 0) > 0 ? 'text-ink-primary' : 'text-emerald-700 dark:text-emerald-400' }}">
                                Bs {{ number_format($p->saldo_posterior ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $modalidad === 'cliente' ? 6 : 5 }}"
                                class="text-center py-8 text-sm text-ink-secondary">
                                Sin cobros registrados.
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </div>

            {{-- Lista compacta (móvil) --}}
            <div class="md:hidden divide-y divide-outline">
                @forelse ($pagos as $p)
                    @php $anulado = $p->estado === 'Anulado'; @endphp
                    <div class="px-4 py-2.5 {{ $anulado ? 'opacity-60' : '' }}">
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="text-sm font-semibold {{ $anulado ? 'line-through text-rose-700 dark:text-rose-400' : 'text-gold-700 dark:text-gold-400' }}">{{ $p->numero_recibo }}</span>
                            <span
                                class="text-sm tabular-nums font-semibold {{ $anulado ? 'line-through text-ink-muted' : 'text-emerald-700 dark:text-emerald-400' }}">Bs
                                {{ number_format($p->monto, 2) }}</span>
                        </div>
                        <div class="text-xs text-ink-secondary truncate">
                            {{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }} · {{ $p->metodo_pago }}
                            @if ($modalidad === 'cliente')
                                · {{ $p->contrato->numero_contrato ?? '-' }}
                            @endif
                            @if ($anulado)
                                · anulado
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-sm text-ink-secondary">Sin cobros registrados.</div>
                @endforelse
            </div>

            <x-slot:footer>
                <span>{{ $pagos->count() }} movimiento(s)</span>
                <span class="text-ink-secondary">Los anulados no descuentan del saldo</span>
            </x-slot:footer>
        @else
            <div class="py-10 text-center text-sm text-ink-secondary">
                <i class="fa-solid fa-user-clock text-2xl text-ink-muted block mb-2"></i>
                <p class="font-semibold text-ink-primary">Busca un cliente</p>
                <p class="mt-1">Escribe su nombre arriba para ver su estado de cuenta y el PDF.</p>
            </div>
        @endif
    </x-ui.panel>
</div>
