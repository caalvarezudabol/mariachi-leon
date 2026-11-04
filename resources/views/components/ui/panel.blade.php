{{-- Panel estándar: cabecera (franja dorada) + filtros + contenido + pie. Ver CLAUDE.md "Panel/Tabla estándar". --}}
<div {{ $attributes->merge(['class' => 'bg-surface-card border border-outline rounded-lg overflow-hidden']) }}>
    @isset($header)
        <div class="flex items-center justify-between gap-3 pl-3 pr-4 py-3 border-b border-outline border-l-4 border-l-gold-500">
            {{ $header }}
        </div>
    @endisset

    @isset($filters)
        <div class="px-4 py-2 border-b border-outline bg-surface-card">
            {{ $filters }}
        </div>
    @endisset

    {{ $slot }}

    @isset($footer)
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-4 py-2.5 border-t border-outline text-xs text-ink-secondary">
            {{ $footer }}
        </div>
    @endisset
</div>
