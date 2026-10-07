@props(['items' => []])
{{-- items: [ [label, route, icon?, locked?] ] --}}
<nav {{ $attributes->merge(['class' => '-mx-1 mb-6 flex gap-1 overflow-x-auto pb-1']) }} aria-label="Sub navigation">
    @foreach ($items as $item)
        @php($active = request()->routeIs($item[1]))
        <a href="{{ route($item[1]) }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-1.5 text-[13px] font-semibold transition {{ $active ? 'bg-ink text-white' : 'text-slate hover:bg-muted hover:text-ink' }}">
            @isset($item[2])<x-icon :name="$item[2]" size="16" />@endisset
            {{ $item[0] }}
            @if (! empty($item[3]))<x-icon name="lock" size="14" class="opacity-70" />@endif
        </a>
    @endforeach
</nav>
