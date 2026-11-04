@props(['variant' => 'default', 'href' => null])

@php
    $variants = [
        'default' => 'bg-surface-sunken text-ink-secondary border border-outline hover:bg-surface-hover hover:text-ink-primary',
        'primary' => 'bg-gold-500/10 text-gold-700 dark:text-gold-400 border border-gold-500/30 hover:bg-gold-500 hover:text-slate-950',
        'success' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500 hover:text-slate-950',
        'danger' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30 hover:bg-rose-600 hover:text-white',
    ];
    $classes = 'inline-flex items-center justify-center w-9 h-9 rounded-lg transition-colors shrink-0 '
        . ($variants[$variant] ?? $variants['default']);
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
