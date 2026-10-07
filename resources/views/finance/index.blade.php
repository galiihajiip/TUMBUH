<x-layouts.app title="Growth Finance">
    @php($c = $summary['current'])
    <x-page-header eyebrow="Growth Finance · kapabilitas native TUMBUH" title="My Financials" :subtitle="'Laporan '.$c['label'].' — semua kalkulasi dilakukan di server dari catatan transaksi.'">
        <x-slot:actions>
            <a href="{{ route('finance.income.create') }}" class="btn-primary btn-sm"><x-icon name="add" size="16" />Add income</a>
            <a href="{{ route('finance.expense.create') }}" class="btn-secondary btn-sm"><x-icon name="remove" size="16" />Add expense</a>
        </x-slot:actions>
    </x-page-header>
    @include('partials.nav-operate')
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach ([['Revenue', $c['revenue'], $summary['revenue_change'], 'up'], ['Expenses (COGS + Opex)', $c['expenses'], $summary['expense_change'], 'down'], ['Net Profit', $c['net_profit'], $summary['profit_change'], 'up']] as [$l, $v, $chg, $good])
            <div class="card p-5">
                <div class="text-[13px] font-medium text-slate">{{ $l }}</div>
                <div class="metric mt-2 text-[28px] {{ $v < 0 ? 'text-brand-deep' : '' }}">{{ rupiah_short($v) }}</div>
                @if ($chg !== null)<div class="mt-1 text-[12px] font-semibold {{ ($chg >= 0) === ($good === 'up') ? 'text-ok' : 'text-brand-deep' }}">{{ signed($chg, 1, '%') }} vs bulan lalu</div>@endif
            </div>
        @endforeach
    </div>
    <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <div class="card p-5">
            <x-section-header title="Cash in vs cash out" subtitle="Bulanan — pembelian stok dihitung sebagai cash out" />
            @if (count($series))
                <x-chart type="bar" format="rp" :labels="collect($series)->map(fn ($m) => $m['month']->translatedFormat('M y'))->all()" :series="[['name' => 'Cash in', 'values' => collect($series)->pluck('cash_in')->all(), 'color' => 'info'], ['name' => 'Cash out', 'values' => collect($series)->pluck('cash_out')->all(), 'color' => 'brand']]" />
            @else
                <p class="text-[13px] text-slate">Belum ada transaksi.</p>
            @endif
        </div>
        <div class="space-y-6">
            <x-ai-insight title="AI Financial Insight">
                <p class="text-[14px] font-semibold leading-6">{{ $insight['headline'] }}</p>
                <p class="mt-2 text-[13px] text-slate">{{ $insight['action'] }}</p>
                <x-why :evidence="$insight['evidence']" />
            </x-ai-insight>
            <div class="card p-5">
                <div class="flex items-center justify-between"><span class="font-semibold">Bulan berjalan (MTD)</span><span class="text-[12px] text-slate">{{ $mtd['label'] }}</span></div>
                <div class="mt-2 grid grid-cols-2 gap-2 text-[13px]"><div>Cash in<br><b>{{ rupiah_short($mtd['cash_in']) }}</b></div><div>Cash out<br><b>{{ rupiah_short($mtd['cash_out']) }}</b></div></div>
            </div>
        </div>
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <a href="{{ $canScan ? route('finance.scan') : route('rewards.tiers') }}" class="card flex items-center gap-3 p-5 hover:shadow-[var(--shadow-hover)]"><x-icon name="document_scanner" class="text-brand" /><span class="flex-1"><b>Scan receipt</b><br><span class="text-[12px] text-slate">OCR simulasi + auto kategori</span></span>@unless ($canScan)<x-icon name="lock" size="16" />@endunless</a>
        <a href="{{ $canReport ? route('finance.report') : route('rewards.tiers') }}" class="card flex items-center gap-3 p-5 hover:shadow-[var(--shadow-hover)]"><x-icon name="summarize" class="text-brand" /><span class="flex-1"><b>Laba rugi & cash flow</b><br><span class="text-[12px] text-slate">Monthly P&L otomatis</span></span>@unless ($canReport)<x-icon name="lock" size="16" />@endunless</a>
        <a href="{{ route('inventory.index') }}" class="card flex items-center gap-3 p-5 hover:shadow-[var(--shadow-hover)]"><x-icon name="link" class="text-brand" /><span class="flex-1"><b>Sales → stock → COGS</b><br><span class="text-[12px] text-slate">Terhubung dengan Growth Inventory</span></span></a>
    </div>
    <div class="card mt-6 overflow-x-auto">
        <div class="p-5 pb-0"><x-section-header title="Transaksi terbaru" /></div>
        <table class="w-full min-w-[720px] text-[13px]">
            <thead><tr class="border-y border-line bg-canvas text-left text-[11px] uppercase tracking-[0.06em] text-slate"><th class="px-5 py-2.5">Tanggal</th><th class="px-5 py-2.5">Keterangan</th><th class="px-5 py-2.5">Kategori</th><th class="px-5 py-2.5">Metode</th><th class="px-5 py-2.5">Sumber</th><th class="px-5 py-2.5 text-right">Jumlah</th></tr></thead>
            <tbody>
                @forelse ($entries as $e)
                    <tr class="border-b border-line last:border-0">
                        <td class="px-5 py-3 text-slate">{{ $e->entry_date->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3">{{ $e->description ?? '—' }}@if ($e->cogs)<div class="text-[11px] text-slate">COGS {{ rupiah($e->cogs) }}</div>@endif</td>
                        <td class="px-5 py-3">{{ $e->categoryLabel() }}</td>
                        <td class="px-5 py-3 text-slate">{{ config('tumbuh.payment_methods.'.$e->payment_method, '—') }}</td>
                        <td class="px-5 py-3"><span class="pill bg-muted text-slate">{{ ['manual' => 'Manual', 'receipt_ocr' => 'Scan struk', 'inventory' => 'Inventory link', 'integration_demo' => 'Integrated (Demo)', 'demo' => 'Demo'][$e->source] ?? $e->source }}</span></td>
                        <td class="px-5 py-3 text-right font-semibold tabular {{ $e->type === 'income' ? 'text-ok' : 'text-ink' }}">{{ $e->type === 'income' ? '+' : '−' }}{{ rupiah($e->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate">Belum ada transaksi. Tambahkan pemasukan atau pengeluaran pertama Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
