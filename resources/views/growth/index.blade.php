<x-layouts.app title="My Growth">
    <x-page-header eyebrow="My Growth" title="Perjalanan pertumbuhan Anda" subtitle="Diagnostic → Growth Score → bottleneck → aksi → ukur → perbaiki. Semua tercatat di sini.">
        <x-slot:actions>
            <form method="POST" action="{{ route('diagnostic.new') }}" onsubmit="return confirm('Mulai Growth Diagnostic siklus baru?')">@csrf<button class="btn-secondary btn-sm"><x-icon name="restart_alt" size="16" />Diagnostic siklus baru</button></form>
        </x-slot:actions>
    </x-page-header>
    @include('partials.nav-growth')
    @php($kpi = app(\App\Services\KpiService::class))
    @unless ($score)
        <x-empty-state icon="troubleshoot" title="Belum ada Growth Score" text="Selesaikan Growth Diagnostic untuk memulai.">
            <a href="{{ route('diagnostic.start') }}" class="btn-primary">Mulai diagnostic</a>
        </x-empty-state>
    @else
        <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr]">
            <div class="card p-6">
                <div class="flex items-center justify-between"><h3 class="text-[16px] font-semibold">Growth Score saat ini</h3><span class="text-[12px] text-slate">{{ $score->calculated_at->translatedFormat('d M Y') }}</span></div>
                <div class="mt-4 flex items-center gap-6">
                    <x-score-ring :value="$score->overall" :size="128" />
                    <div class="space-y-2 text-[13px]">
                        <div><span class="text-slate">Area terkuat</span><br><b class="text-ok">✓ {{ config("tumbuh.dimensions.{$score->strongest}.label") }}</b></div>
                        <div><span class="text-slate">Bottleneck terbesar</span><br><b class="text-brand-deep">! {{ config("tumbuh.dimensions.{$score->bottleneck}.label") }}</b></div>
                        <x-status-badge :status="$score->priority" />
                    </div>
                </div>
                <p class="mt-4 rounded-[12px] bg-canvas p-3 text-[13px] text-ink-2">{{ $score->explanation }}</p>
            </div>
            <div class="card p-6">
                <x-section-header title="Riwayat Growth Score" subtitle="Setiap snapshot tersimpan dan dapat ditelusuri penyebabnya" />
                <x-chart type="line" format="score" :labels="$history->map(fn ($s) => $s->calculated_at->translatedFormat('d M'))->all()" :series="[['name' => 'Growth Score', 'values' => $history->pluck('overall')->all(), 'color' => 'brand']]" :height="200" />
                <div class="mt-3 flex flex-wrap gap-2 text-[11px] text-slate">
                    @foreach ($history->slice(-4) as $h)<span class="pill border border-line">{{ $h->calculated_at->translatedFormat('d M') }} · {{ \App\Services\GrowthScoreService::triggerLabel($h->trigger) }} · {{ $h->overall }}</span>@endforeach
                </div>
            </div>
        </div>
        @if ($kpiRows)
            <div class="card mt-6 overflow-x-auto">
                <div class="p-5 pb-0"><x-section-header title="KPI: Before · Current · Target" /></div>
                <table class="w-full min-w-[640px] text-[13px]">
                    <thead><tr class="border-y border-line bg-canvas text-left text-[11px] uppercase tracking-[0.06em] text-slate"><th class="px-5 py-2.5">KPI</th><th class="px-5 py-2.5 text-right">Baseline</th><th class="px-5 py-2.5 text-right">Saat ini</th><th class="px-5 py-2.5 text-right">Target</th><th class="px-5 py-2.5 text-right">Perubahan</th><th class="px-5 py-2.5">Progress</th><th class="px-5 py-2.5">Sumber</th></tr></thead>
                    <tbody>
                        @foreach ($kpiRows as $r)
                            <tr class="border-b border-line last:border-0">
                                <td class="px-5 py-3 font-semibold">{{ $r['label'] }}</td>
                                <td class="px-5 py-3 text-right tabular text-slate">{{ $kpi->format($r['metric'], $r['before']) }}</td>
                                <td class="px-5 py-3 text-right font-semibold tabular">{{ $kpi->format($r['metric'], $r['current']) }}</td>
                                <td class="px-5 py-3 text-right tabular">{{ $r['target'] !== null ? $kpi->format($r['metric'], $r['target']) : '—' }}</td>
                                <td class="px-5 py-3 text-right font-semibold tabular {{ $r['delta']['direction'] === 'up' ? 'text-ok' : ($r['delta']['direction'] === 'down' ? 'text-brand-deep' : 'text-slate') }}">{{ $kpi->formatDelta($r['delta']) }}</td>
                                <td class="w-40 px-5 py-3">@if ($r['progress'] !== null)<x-progress :value="$r['progress']" />@endif</td>
                                <td class="px-5 py-3"><x-source-badge :source="$r['source']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ([['Growth Ledger', 'Timeline KPI bulanan', 'receipt_long', 'growth.ledger'], ['Before vs After', 'Bukti dampak 30 hari', 'compare_arrows', 'growth.before-after'], ['AI Performance Review', 'Apa yang berhasil & belum', 'rate_review', 'growth.review']] as [$t, $d, $i, $r])
                <a href="{{ route($r) }}" class="card flex items-center gap-4 p-5 hover:shadow-[var(--shadow-hover)]"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon :name="$i" /></span><span class="flex-1"><span class="block font-semibold">{{ $t }}</span><span class="text-[12px] text-slate">{{ $d }}</span></span><x-icon name="chevron_right" class="text-slate" /></a>
            @endforeach
        </div>
    @endunless
</x-layouts.app>
