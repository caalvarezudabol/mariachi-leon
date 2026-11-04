@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'bg-surface-card border border-outline rounded-xl' . ($padding ? ' p-3.5' : '')]) }}>
    {{ $slot }}
</div>
