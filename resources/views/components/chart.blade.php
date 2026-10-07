@php($g = $geo)
<div class="relative w-full" x-data="{ i: null }" @mouseleave="i = null">
    @if (count($g['out']) > 1)
        <div class="mb-2 flex flex-wrap gap-4 text-[12px] text-slate">
            @foreach ($g['out'] as $s)
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2 w-3 rounded-sm" style="background: {{ $s['color'] }}"></span>{{ $s['name'] }}</span>
            @endforeach
        </div>
    @endif
    <svg viewBox="0 0 {{ $g['w'] }} {{ $g['h'] }}" class="block h-auto w-full" role="img" aria-label="{{ collect($g['out'])->pluck('name')->implode(', ') }}">
        @foreach ($g['ticks'] as $t)
            <line x1="{{ $g['pad']['l'] }}" x2="{{ $g['w'] - $g['pad']['r'] }}" y1="{{ $t['y'] }}" y2="{{ $t['y'] }}" stroke="#eef2f6" stroke-width="1" />
            <text x="{{ $g['pad']['l'] - 8 }}" y="{{ $t['y'] + 4 }}" text-anchor="end" font-size="11" fill="#94a3b8" class="tabular">{{ $t['label'] }}</text>
        @endforeach
        @foreach ($g['labels'] as $l)
            <text x="{{ $l['x'] }}" y="{{ $g['h'] - 8 }}" text-anchor="middle" font-size="11" fill="#64748b">{{ $l['label'] }}</text>
        @endforeach
        <template x-if="i !== null"><line :x1="[{{ collect($g['tooltips'])->pluck('x')->implode(',') }}][i]" :x2="[{{ collect($g['tooltips'])->pluck('x')->implode(',') }}][i]" y1="{{ $g['pad']['t'] }}" y2="{{ $g['baseline'] }}" stroke="#cbd5e1" stroke-width="1" /></template>
        @foreach ($g['out'] as $s)
            @if ($type === 'bar')
                @foreach ($s['bars'] as $b)
                    <path d="{{ $b['path'] }}" fill="{{ $s['color'] }}" />
                @endforeach
            @else
                @if (count($g['out']) === 1 && $s['area'])
                    <path d="{{ $s['area'] }}" fill="{{ $s['color'] }}" fill-opacity="0.08" />
                @endif
                <path d="{{ $s['line'] }}" fill="none" stroke="{{ $s['color'] }}" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
                @foreach ($s['points'] as $p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $loop->last ? 5 : 4 }}" fill="{{ $s['color'] }}" stroke="#ffffff" stroke-width="2" />
                @endforeach
                @if ($last = end($s['points']))
                    <text x="{{ $last['x'] }}" y="{{ $last['y'] - 12 }}" text-anchor="middle" font-size="11" font-weight="600" fill="#1a1d23">{{ $fmt($last['v']) }}</text>
                @endif
            @endif
        @endforeach
        @foreach ($g['tooltips'] as $idx => $t)
            <rect x="{{ $t['x'] - $g['band'] / 2 }}" y="0" width="{{ $g['band'] }}" height="{{ $g['h'] }}" fill="transparent" @mouseenter="i = {{ $idx }}" @focus="i = {{ $idx }}" tabindex="-1" />
        @endforeach
    </svg>
    @foreach ($g['tooltips'] as $idx => $t)
        <div x-cloak x-show="i === {{ $idx }}" class="pointer-events-none absolute top-6 z-10 min-w-[140px] -translate-x-1/2 rounded-lg border border-line bg-white px-3 py-2 text-[12px] shadow-[var(--shadow-modal)]" style="left: {{ round($t['x'] / $g['w'] * 100, 2) }}%">
            <div class="mb-1 font-semibold text-ink">{{ $t['label'] }}</div>
            @foreach ($t['rows'] as $r)
                <div class="flex items-center justify-between gap-3 text-slate"><span class="inline-flex items-center gap-1.5"><span class="inline-block h-2 w-2 rounded-full" style="background: {{ $r['color'] }}"></span>{{ $r['name'] }}</span><span class="tabular font-semibold text-ink">{{ $r['value'] }}</span></div>
            @endforeach
        </div>
    @endforeach
</div>
