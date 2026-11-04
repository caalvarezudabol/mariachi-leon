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
    <textarea
        x-data="{ resize() { this.$el.style.height = 'auto'; this.$el.style.height = this.$el.scrollHeight + 'px'; } }"
        x-init="resize()" @input="resize()"
        {{ $attributes->merge(['class' => 'w-full px-3.5 py-2 bg-surface-sunken border border-outline rounded-lg text-ink-primary text-sm placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors resize-none min-h-[36px]']) }}></textarea>
    @if ($name)
        @error($name)
            <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
        @enderror
    @endif
</div>
