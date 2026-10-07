@props(['label', 'value', 'before' => null, 'target' => null, 'delta' => null, 'direction' => 'flat', 'progress' => null, 'source' => null, 'alert' => false, 'unitHint' => null])
<div {{ $attributes->merge(['class' => 'card p-5 flex flex-col '.($alert ? 'border-brand/40 bg-brand-soft' : '')]) }}>
    <div class="flex items-start justify-between gap-2">
        <div class="text-[13px] font-medium {{ $alert ? 'text-brand-deep' : 'text-slate' }}">{{ $label }}</div>
        @if ($source)<x-source-badge :source="$source" />@endif
    </div>
    <div class="metric mt-2 text-[30px] leading-9 {{ $alert ? 'text-brand-deep' : '' }}">{{ $value }}@if ($unitHint)<span class="ml-1 text-[14px] font-medium text-slate">{{ $unitHint }}</span>@endif</div>
    @if ($before !== null || $target !== null)
        <div class="mt-2 grid grid-cols-2 gap-2 text-[12px] text-slate">
            @if ($before !== null)<div>Sebelumnya<br><span class="font-semibold text-ink-2 tabular">{{ $before }}</span></div>@endif
            @if ($target !== null)<div class="text-right">Target<br><span class="font-semibold text-ink tabular">{{ $target }}</span></div>@endif
        </div>
    @endif
    <div class="mt-auto pt-3">
        @if ($progress !== null)<x-progress :value="$progress" :tone="$alert ? 'brand' : 'ink'" />@endif
        @if ($delta)
            <div class="mt-2 flex items-center justify-between text-[12px]">
                <span class="inline-flex items-center gap-0.5 font-semibold {{ $direction === 'up' ? 'text-ok' : ($direction === 'down' ? 'text-brand-deep' : 'text-slate') }}">
                    <x-icon :name="$direction === 'up' ? 'arrow_upward' : ($direction === 'down' ? 'arrow_downward' : 'remove')" size="14" />{{ $delta }}
                </span>
                @if ($progress !== null)<span class="text-slate tabular">{{ $progress }}% tercapai</span>@endif
            </div>
        @endif
    </div>
</div>
