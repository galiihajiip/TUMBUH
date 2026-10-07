@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'prefix' => null])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <div class="relative">
        @if ($prefix)<span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[14px] text-slate">{{ $prefix }}</span>@endif
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $attributes->except('class')->merge(['class' => 'input'.($prefix ? ' pl-10' : '')]) }}>
    </div>
    @if ($help)<p class="mt-1 text-[12px] text-slate">{{ $help }}</p>@endif
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>
