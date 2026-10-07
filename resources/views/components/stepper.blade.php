@props(['steps' => [], 'current' => 0])
{{-- $current = number of completed steps --}}
<ol {{ $attributes->merge(['class' => 'space-y-0']) }}>
    @foreach ($steps as $i => $label)
        @php($state = $i < $current ? 'done' : ($i === $current ? 'now' : 'todo'))
        <li class="relative flex gap-3 pb-5 last:pb-0">
            @if (! $loop->last)<span class="absolute left-[13px] top-7 h-[calc(100%-24px)] w-px {{ $state === 'done' ? 'bg-ok' : 'bg-line' }}"></span>@endif
            <span class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[12px] font-bold {{ $state === 'done' ? 'bg-ok text-white' : ($state === 'now' ? 'bg-brand text-white ring-4 ring-brand-tint' : 'bg-muted text-slate') }}">
                @if ($state === 'done')<x-icon name="check" size="16" />@else{{ $i + 1 }}@endif
            </span>
            <div class="pt-0.5">
                <div class="text-[13px] font-semibold {{ $state === 'todo' ? 'text-slate' : 'text-ink' }}">{{ $label }}</div>
                @if ($state === 'now')<div class="text-[12px] text-brand-deep">Langkah berikutnya</div>@elseif ($state === 'done')<div class="text-[12px] text-ok">Selesai (simulasi)</div>@endif
            </div>
        </li>
    @endforeach
</ol>
