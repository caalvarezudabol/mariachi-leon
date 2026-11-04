<div x-data="{
        open: false,
        top: 0,
        left: 0,
        openMenu() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const menuWidth = 224;
            this.top = rect.bottom + 4;
            this.left = Math.max(8, Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - 8));
            this.open = true;
        }
    }"
    @keydown.escape.window="open = false"
    @scroll.window="open = false"
    class="inline-block">
    <button x-ref="trigger" @click="open ? (open = false) : openMenu()" type="button"
        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-ink-secondary hover:bg-surface-hover hover:text-ink-primary transition-colors">
        <i class="fa-solid fa-ellipsis-vertical"></i>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak
            @click="open = false"
            @click.outside="if (!$refs.trigger.contains($event.target)) open = false"
            :style="`top: ${top}px; left: ${left}px;`"
            x-transition.origin.top.right
            class="fixed w-56 rounded-lg border border-outline bg-surface-card shadow-xl z-[70] py-1">
            {{ $slot }}
        </div>
    </template>
</div>
