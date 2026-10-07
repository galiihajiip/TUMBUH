@props(['title', 'eyebrow' => null, 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap items-end justify-between gap-3']) }}>
    <div>
        @if ($eyebrow)<div class="eyebrow-brand mb-1">{{ $eyebrow }}</div>@endif
        <h2 class="text-[18px] font-semibold leading-7">{{ $title }}</h2>
        @if ($subtitle)<p class="text-[13px] text-slate">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
</div>
