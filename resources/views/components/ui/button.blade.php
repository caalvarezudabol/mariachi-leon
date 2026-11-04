@props(['variant' => 'primary', 'size' => 'md', 'href' => null])

@php
    $variants = [
        'primary' => 'bg-gold-500 text-slate-950 hover:bg-gold-400',
        'secondary' => 'bg-surface-sunken text-ink-primary border border-outline hover:bg-surface-hover',
        'success' => 'bg-emerald-600/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 hover:bg-emerald-600 hover:text-white',
        'danger' => 'bg-rose-600/15 text-rose-700 dark:text-rose-400 border border-rose-500/30 hover:bg-rose-600 hover:text-white',
        'ghost' => 'bg-transparent text-ink-secondary hover:bg-surface-hover',
    ];
    $sizes = [
        'sm' => 'h-8 px-3 text-xs',
        'md' => 'h-9 px-4 text-sm',
    ];
    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed '
        . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
