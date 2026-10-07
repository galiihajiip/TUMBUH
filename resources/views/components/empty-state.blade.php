@props(['icon' => 'inbox', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'card flex flex-col items-center px-6 py-12 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted text-slate"><x-icon :name="$icon" size="24" /></span>
    <h3 class="mt-4 text-[16px] font-semibold">{{ $title }}</h3>
    @if ($text)<p class="mt-1 max-w-md text-[13px] text-slate">{{ $text }}</p>@endif
    @if (! $slot->isEmpty())<div class="mt-5 flex flex-wrap justify-center gap-2">{{ $slot }}</div>@endif
</div>
