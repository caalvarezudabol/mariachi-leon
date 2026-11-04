@props(['label' => null, 'required' => false, 'name' => null])

<div>
    @if ($label)
        <label class="block text-sm font-semibold text-ink-primary mb-1">
            {{ $label }}:
            @if ($required)
                <span class="text-rose-500 font-black text-base leading-none">*</span>
            @endif
        </label>
    @endif
    <select {{ $attributes->merge(['class' => 'w-full h-9 px-3.5 bg-surface-sunken border border-outline rounded-lg text-ink-primary text-sm focus:border-gold-500 focus:outline-none transition-colors']) }}>
        {{ $slot }}
    </select>
    @if ($name)
        @error($name)
            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
        @enderror
    @endif
</div>
