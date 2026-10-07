@props(['title', 'eyebrow' => null, 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-start justify-between gap-4']) }}>
    <div class="max-w-3xl">
        @if ($eyebrow)<div class="eyebrow-brand mb-2 flex items-center gap-1.5">{{ $eyebrow }}</div>@endif
        <h1 class="text-[26px] font-bold leading-9 md:text-[30px] md:leading-10">{{ $title }}</h1>
        @if ($subtitle)<p class="mt-1.5 text-[14px] leading-6 text-slate">{{ $subtitle }}</p>@endif
        {{ $slot }}
    </div>
    @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
</div>
