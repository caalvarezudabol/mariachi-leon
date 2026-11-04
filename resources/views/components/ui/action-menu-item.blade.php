@props(['icon' => null, 'variant' => 'default', 'href' => null])

@php
    $variants = [
        'default' => 'text-ink-secondary hover:bg-surface-hover hover:text-ink-primary',
        'danger' => 'text-rose-700 dark:text-rose-400 hover:bg-rose-500/10',
    ];
    $classes = 'w-full flex items-center gap-2.5 px-3.5 py-2 text-sm text-left transition-colors '
        . ($variants[$variant] ?? $variants['default']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }} w-4 text-center"></i>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }} w-4 text-center"></i>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
