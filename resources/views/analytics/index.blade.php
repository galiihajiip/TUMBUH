<x-layouts.app title="Analytics">
    @php($labels = $ledger->map(fn ($r) => $r->period->translatedFormat('M y'))->all())
    <x-page-header eyebrow="Analytics" title="KPI & business performance" subtitle="Semua angka berasal dari data tersimpan — Growth Ledger, kampanye, Growth Finance, dan Growth Inventory." />
    @if ($kpiRows)
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($kpiRows as $r)
                <div class="card p-4">
                    <div class="flex items-center justify-between text-[12px] text-slate">{{ $r['label'] }}<x-source-badge :source="$r['source']" /></div>
                    <div class="metric mt-1 text-[22px]">{{ $kpi->format($r['metric'], $r['current']) }}</div>
                    <div class="text-[12px] font-semibold {{ $r['delta']['direction'] === 'up' ? 'text-ok' : ($r['delta']['direction'] === 'down' ? 'text-brand-deep' : 'text-slate') }}">{{ $kpi->formatDelta($r['delta']) }} vs baseline</div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card p-5"><x-section-header title="Business performance" subtitle="Omzet bulanan (Growth Ledger)" />
            @if (count($labels))<x-chart type="bar" format="rp" :labels="$labels" :series="[['name' => 'Omzet', 'values' => $ledger->pluck('revenue')->all(), 'color' => 'brand']]" />@else<p class="text-[13px] text-slate">Belum ada data.</p>@endif</div>
        <div class="card p-5"><x-section-header title="Growth history" subtitle="Snapshot Growth Score" />
            <x-chart type="line" format="score" :labels="$scores->map(fn ($s) => $s->calculated_at->translatedFormat('d M'))->all()" :series="[['name' => 'Growth Score', 'values' => $scores->pluck('overall')->all(), 'color' => 'brand']]" /></div>
        <div class="card p-5"><x-section-header title="Customer KPI" subtitle="Repeat customer & conversion (%)" />
            @if (count($labels))<x-chart type="line" format="pct" :labels="$labels" :series="[['name' => 'Repeat customer', 'values' => $ledger->pluck('repeat_rate')->all(), 'color' => 'brand'], ['name' => 'Conversion', 'values' => $ledger->pluck('conversion_rate')->all(), 'color' => 'info']]" />@endif</div>
        <div class="card p-5"><x-section-header title="Finance summary" subtitle="Laba bersih bulanan (Growth Finance)" />
            @if (count($finance))<x-chart type="bar" format="rp" :labels="collect($finance)->map(fn ($m) => $m['month']->translatedFormat('M y'))->all()" :series="[['name' => 'Laba bersih', 'values' => collect($finance)->pluck('net_profit')->all(), 'color' => 'info']]" />@else<p class="py-8 text-center text-[13px] text-slate">Belum ada transaksi di Growth Finance. <a class="link" href="{{ route('finance.index') }}">Mulai catat</a></p>@endif</div>
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <div class="card overflow-x-auto">
            <div class="p-5 pb-0"><x-section-header title="Campaign performance" subtitle="MyAds — Simulated" /></div>
            @if ($campaigns->isEmpty())<p class="px-5 pb-5 text-[13px] text-slate">Belum ada kampanye.</p>@else
            <table class="w-full min-w-[560px] text-[13px]"><thead><tr class="border-y border-line bg-canvas text-left text-[11px] uppercase text-slate"><th class="px-5 py-2.5">Kampanye</th><th class="px-5 py-2.5 text-right">Reach</th><th class="px-5 py-2.5 text-right">CTR</th><th class="px-5 py-2.5 text-right">Konversi</th><th class="px-5 py-2.5 text-right">ROAS</th></tr></thead>
                <tbody>@foreach ($campaigns as $c)<tr class="border-b border-line last:border-0"><td class="px-5 py-2.5 font-semibold">{{ $c->name }}</td><td class="px-5 py-2.5 text-right tabular">{{ num($c->reach) }}</td><td class="px-5 py-2.5 text-right tabular">{{ pct($c->ctr()) }}</td><td class="px-5 py-2.5 text-right tabular">{{ num($c->conversions) }}</td><td class="px-5 py-2.5 text-right font-semibold tabular">{{ num($c->roas(), 1) }}x</td></tr>@endforeach</tbody></table>@endif
        </div>
        <div class="card p-5">
            <x-section-header title="Inventory performance" />
            <div class="grid grid-cols-3 gap-2 text-center"><div class="module p-3"><div class="metric text-[20px]">{{ $inventory['total'] }}</div><div class="text-[11px] text-slate">produk</div></div><div class="module p-3"><div class="metric text-[20px]">{{ $inventory['low'] + $inventory['out'] }}</div><div class="text-[11px] text-slate">berisiko</div></div><div class="module p-3"><div class="metric text-[20px]">{{ rupiah_short($inventory['value']) }}</div><div class="text-[11px] text-slate">nilai stok</div></div></div>
            <ol class="mt-4 space-y-1.5 text-[13px]">@foreach ($top as $i => $r)<li class="flex justify-between"><span>{{ $i + 1 }}. {{ $r['product']->name }}</span><span class="text-slate">{{ num($r['sold']) }} terjual · GP {{ rupiah_short($r['gross_profit']) }}</span></li>@endforeach</ol>
            @unless ($advanced)<p class="mt-4 text-[12px] text-slate"><x-icon name="lock" size="14" />Advanced analytics (cohort & profitabilitas lanjutan) tersedia di Gold.</p>@endunless
        </div>
    </div>
</x-layouts.app>
