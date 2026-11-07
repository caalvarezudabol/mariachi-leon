@props([
    'model',
    'items' => [],
    'placeholder' => 'Seleccionar...',
    'live' => false,
    'hint' => 'Cuéntanos en el mensaje cuál es.',
    'invalid' => false,
])

{{-- Selector con buscador (escribe y elige). items: [{v, l, s?, g?, otro?}] --}}
<div class="relative" x-data="{
    open: false,
    q: '',
    esOtro: false,
    val: $wire.entangle('{{ $model }}') {{ $live ? '.live' : '' }},
    items: @js($items),
    get lista() {
        const t = this.q.toLowerCase();
        return this.items.filter(i => i.l.toLowerCase().includes(t));
    },
    get actual() {
        if (this.esOtro && !this.val) return 'Otro';
        const f = this.items.find(i => !i.otro && String(i.v) === String(this.val ?? ''));
        return f ? f.l : '';
    },
    pick(i) {
        this.esOtro = !!i.otro;
        this.val = i.otro ? '' : i.v;
        this.open = false;
        this.q = '';
    },
    cabecera(n) { return n === 0 || this.lista[n - 1].g !== this.lista[n].g; }
}" @click.outside="open = false" @keydown.escape="open = false">
    <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-slate-400 text-[10px] pointer-events-none"></i>
    <input type="text" autocomplete="off" :value="open ? q : actual" @input="q = $event.target.value"
        @focus="open = true; q = ''; $event.target.select()" placeholder="{{ $placeholder }}"
        class="w-full pl-3 pr-8 py-2 bg-white dark:bg-slate-950 border rounded-xl text-slate-900 dark:text-white text-sm placeholder:text-slate-400 focus:border-gold-500 focus:outline-none transition-colors {{ $invalid ? 'border-rose-500' : 'border-slate-200 dark:border-slate-800' }}">

    <div x-show="open" x-cloak x-transition.opacity
        class="absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl py-1">
        <template x-for="(i, n) in lista" :key="n">
            <div>
                <div x-show="i.g && cabecera(n)"
                    class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400"
                    x-text="i.g"></div>
                <button type="button" @click="pick(i)"
                    class="w-full text-left px-3 py-1.5 text-sm text-slate-800 dark:text-slate-100 hover:bg-gold-500/10 flex items-center justify-between gap-3">
                    <span class="truncate" x-text="i.l"></span>
                    <span x-show="i.s" class="text-xs text-slate-500 dark:text-slate-400 shrink-0"
                        x-text="i.s"></span>
                </button>
            </div>
        </template>
        <div x-show="lista.length === 0" class="px-3 py-2 text-sm text-slate-500">Sin resultados</div>
    </div>

    <p x-show="esOtro && !val" x-cloak class="mt-1 text-xs text-amber-700 dark:text-amber-300">
        <i class="fa-solid fa-circle-info mr-1"></i>{{ $hint }}
    </p>
</div>