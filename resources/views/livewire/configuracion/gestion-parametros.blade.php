@php
    $nombresGrupo = [
        'general' => 'General',
        'financiero' => 'Financiero',
        'contrato' => 'Contratos',
    ];
@endphp

<div class="space-y-3">

    @if (session()->has('success'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Aviso: los datos de la empresa se editan en otra pantalla --}}
    <div
        class="flex items-start gap-3 p-3 rounded-lg border-l-[3px] border-sky-500 bg-sky-500/10 text-sm text-ink-primary">
        <i class="fa-solid fa-circle-info text-lg text-sky-600 dark:text-sky-400 mt-0.5"></i>
        <p>
            Los datos de la empresa (nombre, moneda, contacto, redes, logo, QR y términos de contrato) se editan en
            <a href="{{ route('config.empresa') }}" wire:navigate
                class="font-semibold text-gold-700 dark:text-gold-400 hover:underline">Empresa</a>.
            Aquí solo van los ajustes propios del sistema.
        </p>
    </div>

    <form wire:submit.prevent="guardar">
        <x-ui.panel>
            <x-slot:header>
                <div class="flex items-center gap-2.5 min-w-0">
                    <i class="fa-solid fa-sliders text-lg text-gold-600 dark:text-gold-400"></i>
                    <h1 class="text-base font-semibold text-ink-primary">Parámetros</h1>
                    <x-ui.badge variant="neutral">{{ $totalParametros }}</x-ui.badge>
                </div>
                @if ($totalParametros > 0)
                    <x-ui.button size="sm" type="submit" wire:loading.attr="disabled" wire:target="guardar">
                        <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardar"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardar"></i>
                        <span>Guardar</span>
                    </x-ui.button>
                @endif
            </x-slot:header>

            @forelse ($parametros as $grupo => $items)
                <div class="px-5 py-4 {{ !$loop->first ? 'border-t border-outline' : '' }}">
                    <h2 class="text-sm font-semibold text-ink-primary mb-3">
                        {{ $nombresGrupo[$grupo] ?? ucfirst($grupo) }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($items as $param)
                            @php $etiqueta = $param->descripcion ?: $param->clave; @endphp

                            @if (str_contains($param->clave, 'porcentaje'))
                                <div>
                                    <x-ui.input type="number" step="0.01" min="0" max="100"
                                        label="{{ $etiqueta }}" required name="config.{{ $param->clave }}"
                                        wire:model="config.{{ $param->clave }}" />
                                    <p class="text-xs text-ink-muted mt-1">Valor entre 0 y 100 (%).</p>
                                </div>
                            @elseif (strlen((string) $param->valor) > 60)
                                <x-ui.textarea class="md:col-span-2" label="{{ $etiqueta }}" rows="1"
                                    name="config.{{ $param->clave }}" wire:model="config.{{ $param->clave }}" />
                            @else
                                <x-ui.input label="{{ $etiqueta }}" name="config.{{ $param->clave }}"
                                    wire:model="config.{{ $param->clave }}" />
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-sm text-ink-secondary">
                    <i class="fa-solid fa-sliders text-2xl text-ink-muted block mb-2"></i>
                    No hay parámetros adicionales por configurar.
                </div>
            @endforelse
        </x-ui.panel>
    </form>
</div>