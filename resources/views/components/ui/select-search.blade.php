{{--
    Selector con buscador (combobox). Cada opción en `options` debe venir ya armada por quien
    llama como: ['id' => ..., 'label' => 'Texto principal', 'meta' => 'Texto secundario', 'search' => 'texto en minúsculas para filtrar'].
    Opcional: 'aside' => 'Bs 600.00' muestra un texto en rojo a la derecha (por ejemplo un saldo pendiente).
    `name` es la propiedad Livewire a enlazar (two-way, vía @entangle). Ver CLAUDE.md "Selector con buscador".
--}}
@props(['name', 'options' => [], 'placeholder' => 'Buscar...', 'pushEvent' => null, 'onSelect' => null])

<div x-data="{
    open: false,
    search: '',
    selectedId: @entangle($name).live,
    options: @js($options),
    top: 0,
    left: 0,
    width: 0,
    get selected() {
        return this.options.find(o => o.id == this.selectedId) ?? null;
    },
    get filtered() {
        if (!this.search) return this.options;
        const s = this.search.toLowerCase();
        return this.options.filter(o => o.search.includes(s));
    },
    openPanel() {
        const rect = this.$refs.trigger.getBoundingClientRect();
        this.top = rect.bottom + 4;
        this.left = rect.left;
        this.width = rect.width;
        this.search = '';
        this.open = true;
        this.$nextTick(() => this.$refs.searchInput?.focus());
    },
    select(option) {
        this.selectedId = option.id;
        this.open = false;
        @if($onSelect)
        this.$wire.{{ $onSelect }};
        @endif
    }
}" @keydown.escape.window="open = false" @scroll.window="open = false" @if ($pushEvent)
    x-on:{{ $pushEvent }}.window="options.push($event.detail); selectedId = $event.detail.id; open = false;"
    @endif
    class="relative">

    <button x-ref="trigger" type="button" @click="open ? (open = false) : openPanel()"
        class="w-full h-9 px-3.5 bg-surface-sunken border border-outline rounded-lg text-sm text-left flex items-center justify-between gap-2 focus:border-gold-500 transition-colors">
        <span class="truncate" :class="selected ? 'text-ink-primary font-medium' : 'text-ink-muted'"
            x-text="selected ? selected.label : @js($placeholder)"></span>
        <span class="ml-auto flex items-center gap-2 shrink-0">
            <span x-show="selected && selected.aside" x-text="selected ? selected.aside : ''"
                class="text-sm font-bold text-rose-600 dark:text-rose-400 tabular-nums"></span>
            <i class="fa-solid fa-chevron-down text-xs text-ink-muted"></i>
        </span>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak @click.outside="if (!$refs.trigger.contains($event.target)) open = false"
            :style="`top: ${top}px; left: ${left}px; width: ${width}px;`" x-transition
            class="fixed z-[70] bg-surface-card border border-outline rounded-lg shadow-xl overflow-hidden">
            <div class="p-2 border-b border-outline">
                <div class="relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                    <input x-ref="searchInput" type="text" x-model="search" placeholder="{{ $placeholder }}"
                        class="w-full h-8 pl-8 pr-3 bg-surface-sunken border border-outline rounded-md text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none">
                </div>
            </div>
            <div class="max-h-60 overflow-y-auto divide-y divide-outline">
                <template x-for="option in filtered" :key="option.id">
                    <button type="button" @click="select(option)"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 text-left hover:bg-surface-hover transition-colors">
                        <span class="min-w-0">
                            <span class="block text-sm text-ink-primary font-medium truncate"
                                x-text="option.label"></span>
                            <span class="block text-xs text-ink-muted truncate" x-text="option.meta"></span>
                        </span>
                        <span class="flex items-center gap-2 shrink-0">
                            <span x-show="option.aside" x-text="option.aside"
                                class="text-sm font-bold text-rose-600 dark:text-rose-400 tabular-nums"></span>
                            <i class="fa-solid fa-check text-gold-500 text-xs" x-show="option.id == selectedId"></i>
                        </span>
                    </button>
                </template>
                <template x-if="filtered.length === 0">
                    <div class="px-3 py-4 text-center text-xs text-ink-muted">Sin resultados.</div>
                </template>
            </div>
        </div>
    </template>
</div>