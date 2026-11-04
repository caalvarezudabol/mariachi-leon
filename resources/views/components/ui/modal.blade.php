@props(['title' => null, 'icon' => null, 'maxWidth' => 'max-w-2xl', 'onClose' => null, 'nested' => false, 'bodyId' => null])

<div class="fixed inset-0 {{ $nested ? 'z-[60]' : 'z-50' }} flex items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm">
    <div
        class="bg-surface-card border border-outline rounded-none sm:rounded-xl w-full {{ $maxWidth }} overflow-hidden shadow-xl flex flex-col h-full sm:h-auto max-h-screen sm:max-h-[90vh]">
        <div class="flex items-center justify-between gap-3 border-b border-outline px-6 py-4 shrink-0">
            @isset($header)
                <div class="flex-1 min-w-0">{{ $header }}</div>
            @else
                <h3 class="text-base font-bold text-ink-primary flex items-center gap-2">
                    @if ($icon)
                        <i class="{{ $icon }} text-gold-400"></i>
                    @endif
                    <span>{{ $title }}</span>
                </h3>
            @endisset
            @if ($onClose)
                <button wire:click="{{ $onClose }}" type="button" class="text-ink-muted hover:text-ink-primary text-lg shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            @endif
        </div>

        <div @if ($bodyId) id="{{ $bodyId }}" @endif class="overflow-y-auto flex-1">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex items-center justify-end gap-3 border-t border-outline px-6 py-4 shrink-0">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
