@props(['variant' => 'neutral', 'as' => 'span'])

@php
    $tag = $as;
    $variants = [
        'success' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20',
        'danger' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/20',
        'gold' => 'bg-gold-500/10 text-gold-700 dark:text-gold-400 border border-gold-500/20',
        'neutral' => 'bg-surface-sunken text-ink-muted border border-outline',
    ];
    $classes = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-colors '
        . ($variants[$variant] ?? $variants['neutral']);
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</{{ $tag }}>
