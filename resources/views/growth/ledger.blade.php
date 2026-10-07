<x-layouts.app title="Growth Ledger">
    @php($kpi = app(\App\Services\KpiService::class))
    @php($labels = $ledger->map(fn ($r) => $r->period->translatedFormat('M y'))->all())
    <x-page-header eyebrow="Buku catatan pertumbuhan" title="Growth Ledger" subtitle="Seperti financial ledger, tetapi untuk pertumbuhan bisnis — setiap bulan tercatat dengan sumber datanya." />
    @include('partials.nav-growth')

    @if ($ledger->isEmpty())
        <x-empty-state icon="receipt_long" title="Ledger masih kosong" text="Baseline dibuat setelah Anda mengonfirmasi data di Growth Diagnostic."><a href="{{ route('diagnostic.start') }}" class="btn-primary">Mulai diagnostic</a></x-empty-state>
    @else
        @unless ($full)
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-[14px] border border-line bg-canvas px-4 py-3 text-[13px]">
                <span class="flex items-center gap-2"><x-icon name="lock" size="18" class="text-slate" />Bronze menampilkan ledger dasar (3 bulan terakhir). Riwayat penuh, campaign result & Before vs After tersedia di Silver.</span>
                <a href="{{ route('rewards.tiers') }}" class="btn-secondary btn-sm">Lihat tier</a>
            </div>
        @endunless
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card p-5">
                <x-section-header title="Revenue trend" subtitle="Omzet bulanan" />
                <x-chart type="bar" format="rp" :labels="$labels" :series="[['name' => 'Omzet', 'values' => $ledger->pluck('revenue')->all(), 'color' => 'brand']]" />
            </div>
            <div class="card p-5">
                <x-section-header title="Customer growth" subtitle="Pelanggan baru per bulan" />
                <x-chart type="line" format="count" :labels="$labels" :series="[['name' => 'Pelanggan baru', 'values' => $ledger->pluck('new_customers')->all(), 'color' => 'brand']]" />
            </div>
            <div class="card p-5">
                <x-section-header title="Conversion vs repeat customer" subtitle="Persentase — satu sumbu" />
                <x-chart type="line" format="pct" :labels="$labels" :series="[['name' => 'Repeat customer', 'values' => $ledger->pluck('repeat_rate')->all(), 'color' => 'brand'], ['name' => 'Conversion', 'values' => $ledger->pluck('conversion_rate')->all(), 'color' => 'info']]" />
            </div>
            <div class="card p-5">
                <x-section-header title="Growth Score" subtitle="Skor tercatat per bulan" />
                <x-chart type="line" format="score" :labels="$labels" :series="[['name' => 'Growth Score', 'values' => $ledger->pluck('growth_score')->all(), 'color' => 'brand']]" />
            </div>
        </div>

        {{-- Timeline --}}
        <div class="mt-6 card p-5 md:p-6">
            <x-section-header title="Timeline bulanan" subtitle="Dari yang terbaru" />
            <ol class="relative space-y-4">
                @foreach ($ledger->reverse()->values() as $i => $r)
                    @php($prev = $ledger->firstWhere(fn ($x) => $x->period->eq($r->period->copy()->subMonthNoOverflow())))
                    <li class="grid gap-4 rounded-[14px] border {{ $r->is_baseline ? 'border-ink' : 'border-line' }} p-4 md:grid-cols-[160px_1fr]">
                        <div>
                            <div class="font-display text-[18px] font-bold uppercase">{{ $r->period->translatedFormat('F Y') }}</div>
                            <div class="mt-1 flex flex-wrap gap-1">@if ($r->is_baseline)<span class="pill bg-ink text-white">Baseline</span>@endif<x-source-badge :source="$r->source" /></div>
                            @if ($r->note)<div class="mt-1 text-[11px] text-slate">{{ $r->note }}</div>@endif
                        </div>
                        <div>
                            <div class="grid grid-cols-2 gap-3 text-[13px] sm:grid-cols-4">
                                @foreach (['revenue' => 'Revenue', 'new_customers' => 'Pelanggan baru', 'conversion_rate' => 'Conversion', 'repeat_rate' => 'Repeat', 'marketing_spend' => 'Marketing spend', 'campaign_reach' => 'Campaign reach', 'digital_txn_share' => 'Tercatat digital', 'growth_score' => 'Growth Score'] as $m => $l)
                                    @continue(! $full && in_array($m, ['campaign_reach', 'marketing_spend']))
                                    <div>
                                        <div class="text-[11px] text-slate">{{ $l }}</div>
                                        <div class="font-semibold tabular">{{ $kpi->format($m, $r->{$m}) }}
                                            @if ($prev && $r->{$m} !== null && $prev->{$m} !== null && $r->{$m} != $prev->{$m})
                                                @php($dl = $kpi->delta($m, (float) $prev->{$m}, (float) $r->{$m}))
                                                <span class="text-[11px] {{ $dl['direction'] === 'up' ? 'text-ok' : 'text-brand-deep' }}">{{ $kpi->formatDelta($dl) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($r->contributions)
                                <div class="mt-3 flex flex-wrap gap-1.5">@foreach ($r->contributions as $c)<span class="pill border border-line bg-white text-ink-2">✓ {{ $c['label'] }} <span class="font-normal text-slate">· {{ $c['detail'] }}</span></span>@endforeach</div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        @if ($full && $campaigns->isNotEmpty())
            <div class="mt-6 card overflow-x-auto">
                <div class="p-5 pb-0"><x-section-header title="Campaign results" subtitle="MyAds — Simulated"><x-slot:actions><a class="text-[13px] font-semibold text-brand-deep" href="{{ route('campaigns.index') }}">Dashboard kampanye →</a></x-slot:actions></x-section-header></div>
                <table class="w-full min-w-[640px] text-[13px]">
                    <thead><tr class="border-y border-line bg-canvas text-left text-[11px] uppercase tracking-[0.06em] text-slate"><th class="px-5 py-2.5">Kampanye</th><th class="px-5 py-2.5">Mulai</th><th class="px-5 py-2.5 text-right">Reach</th><th class="px-5 py-2.5 text-right">Leads</th><th class="px-5 py-2.5 text-right">Konversi</th><th class="px-5 py-2.5 text-right">ROAS</th></tr></thead>
                    <tbody>@foreach ($campaigns as $c)<tr class="border-b border-line last:border-0"><td class="px-5 py-3 font-semibold">{{ $c->name }}</td><td class="px-5 py-3 text-slate">{{ $c->launched_at?->translatedFormat('d M Y') }}</td><td class="px-5 py-3 text-right tabular">{{ num($c->reach) }}</td><td class="px-5 py-3 text-right tabular">{{ num($c->leads) }}</td><td class="px-5 py-3 text-right tabular">{{ num($c->conversions) }}</td><td class="px-5 py-3 text-right font-semibold tabular">{{ num($c->roas(), 1) }}x</td></tr>@endforeach</tbody>
                </table>
            </div>
        @endif
    @endif
</x-layouts.app>
