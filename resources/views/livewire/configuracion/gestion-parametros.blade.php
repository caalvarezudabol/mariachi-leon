<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Parámetros del Sistema</h1>
            <p class="text-xs text-slate-400">Ajustes globales de la agrupación, datos fiscales y porcentajes por defecto.</p>
        </div>
    </div>

    <form wire:submit.prevent="guardar" class="space-y-6">
        @foreach($parametros as $grupo => $items)
            <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4">
                <h3 class="font-bold text-sm text-gold-400 uppercase tracking-widest border-b border-brand-border pb-3">
                    Grupo: {{ strtoupper($grupo) }}
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($items as $param)
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                {{ $param->descripcion ?? $param->clave }}
                            </label>
                            @if(strlen($param->valor) > 60)
                                <textarea wire:model.defer="config.{{ $param->clave }}" rows="3" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500"></textarea>
                            @else
                                <input type="text" wire:model.defer="config.{{ $param->clave }}" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all">
                Guardar Todos los Parámetros
            </button>
        </div>
    </form>
</div>
