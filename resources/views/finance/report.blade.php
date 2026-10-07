<x-layouts.app title="Laporan Keuangan">
    <x-page-header eyebrow="Monthly income statement" :title="'Laporan Keuangan · '.$cur['label']" subtitle="Dihitung otomatis dari pemasukan, pengeluaran, dan COGS penjualan produk.">
        <x-slot:actions>
            <form method="GET" class="flex items-center gap-2"><select name="month" class="input h-9 w-auto text-[13px]" onchange="this.form.submit()">@foreach ($months as $m)<option value="{{ $m }}" @selected($m === $cur['month']->format('Y-m'))>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y') }}</option>@endforeach</select></form>
        </x-slot:actions>
    </x-page-header>
    @include('partials.nav-operate')
    <div class="grid gap-6 lg:grid-cols-[1.2fr_1fr]">
        <div class="card overflow-hidden">
            <table class="w-full text-[14px]">
                <thead><tr class="border-b border-line bg-canvas text-left text-[11px] uppercase tracking-[0.06em] text-slate"><th class="px-5 py-3">Profit & Loss</th><th class="px-5 py-3 text-right">{{ $cur['month']->translatedFormat('M Y') }}</th><th class="px-5 py-3 text-right">{{ $prev['month']->translatedFormat('M Y') }}</th><th class="px-5 py-3 text-right">Δ</th></tr></thead>
                <tbody>
                    @foreach ([['Total Revenue', 'revenue', false], ['COGS', 'cogs', false], ['Gross Profit', 'gross_profit', true], ['Operating Expense', 'opex', false], ['Net Profit', 'net_profit', true]] as [$l, $k, $bold])
                        @php($chg = $analytics->change($cur[$k], $prev[$k] ?: null))
                        <tr class="border-b border-line {{ $bold ? 'bg-canvas/60 font-semibold' : '' }}"><td class="px-5 py-3">{{ $l }}</td><td class="px-5 py-3 text-right tabular {{ $cur[$k] < 0 ? 'text-brand-deep' : '' }}">{{ rupiah($cur[$k]) }}</td><td class="px-5 py-3 text-right tabular text-slate">{{ rupiah($prev[$k]) }}</td><td class="px-5 py-3 text-right text-[12px] tabular">{{ $chg !== null ? signed($chg, 1, '%') : '—' }}</td></tr>
                    @endforeach
                    <tr class="border-b border-line"><td class="px-5 py-3">Gross margin</td><td class="px-5 py-3 text-right font-semibold">{{ $cur['gross_margin'] !== null ? pct($cur['gross_margin']) : '—' }}</td><td class="px-5 py-3 text-right text-slate">{{ $prev['gross_margin'] !== null ? pct($prev['gross_margin']) : '—' }}</td><td class="px-5 py-3 text-right text-[12px]">{{ $cur['gross_margin'] !== null && $prev['gross_margin'] !== null ? signed($cur['gross_margin'] - $prev['gross_margin'], 1, 'pp') : '—' }}</td></tr>
                    <tr><td class="px-5 py-3 font-semibold">Cash flow (in − out)</td><td class="px-5 py-3 text-right font-semibold tabular {{ $cur['net_cash'] < 0 ? 'text-brand-deep' : 'text-ok' }}">{{ rupiah($cur['net_cash']) }}</td><td class="px-5 py-3 text-right tabular text-slate">{{ rupiah($prev['net_cash']) }}</td><td></td></tr>
                </tbody>
            </table>
            <div class="border-t border-line px-5 py-3 text-[12px] text-slate">Cash in {{ rupiah($cur['cash_in']) }} · Cash out {{ rupiah($cur['cash_out']) }} (termasuk pembelian stok {{ rupiah($cur['by_category']['inventory'] ?? 0) }})</div>
        </div>
        <div class="space-y-6">
            <x-ai-insight title="AI Financial Insight">
                <p class="text-[14px] font-semibold leading-6">{{ $insight['headline'] }}</p>
                <p class="mt-2 text-[13px] text-slate">{{ $insight['action'] }}</p>
            </x-ai-insight>
            <div class="card p-5">
                <div class="flex items-center justify-between"><h3 class="text-[16px] font-semibold">Top cost drivers</h3>@unless ($advanced)<span class="pill bg-gold-tint text-gold">Analisis lengkap: Gold</span>@endunless</div>
                <div class="mt-3 space-y-2">
                    @foreach ($drivers as $d)
                        <div class="flex items-center justify-between rounded-[10px] border border-line px-3 py-2 text-[13px]"><span>{{ $d['label'] }}</span><span class="tabular"><b>{{ rupiah_short($d['amount']) }}</b>@if ($advanced && $d['change'] !== null) <span class="{{ $d['change'] > 0 ? 'text-brand-deep' : 'text-ok' }}">{{ signed($d['change'], 1, '%') }}</span>@endif</span></div>
                    @endforeach
                </div>
            </div>
            @if ($advanced)
                <div class="card p-5"><div class="eyebrow">Financial health score · Gold</div><div class="metric mt-1 text-[36px]">{{ $health ?? '—' }}<span class="text-[14px] text-slate">/100</span></div><p class="text-[12px] text-slate">Margin 35% · cash flow 25% · pertumbuhan biaya vs omzet 25% · disiplin pencatatan 15%.</p></div>
            @endif
        </div>
    </div>
    @if (count($series) > 1)
        <div class="card mt-6 p-5">
            <x-section-header title="Gross margin trend" subtitle="{{ $advanced ? 'Margin analysis' : 'Tren bulanan' }}" />
            <x-chart type="line" format="pct" :width="1200" :height="260" :labels="collect($series)->map(fn ($m) => $m['month']->translatedFormat('M y'))->all()" :series="[['name' => 'Gross margin', 'values' => collect($series)->pluck('gross_margin')->all(), 'color' => 'brand']]" />
        </div>
    @endif
</x-layouts.app>
