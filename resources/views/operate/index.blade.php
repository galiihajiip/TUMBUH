<x-layouts.app title="Business Operating Dashboard">
    <x-page-header eyebrow="Business Operating Dashboard" title="My Business" subtitle="Growth Score, keuangan, inventaris, dan pemasaran dalam satu tampilan." />
    @include('partials.nav-operate')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card p-6 lg:row-span-2">
            <div class="eyebrow">Growth Score</div>
            <div class="mt-2 flex items-center gap-4">
                <div class="metric text-[56px] leading-none">{{ $score?->overall ?? '—' }}<span class="text-[18px] text-slate">/100</span></div>
            </div>
            @if ($score && $first)<div class="mt-2 text-[13px] font-semibold {{ $score->overall >= $first->overall ? 'text-ok' : 'text-brand-deep' }}">{{ signed($score->overall - $first->overall) }} sejak diagnostic pertama</div>@endif
            <div class="mt-6 rounded-[14px] border-l-4 border-l-brand bg-brand-soft p-4">
                <div class="eyebrow-brand flex items-center gap-1"><x-icon name="auto_awesome" size="14" fill />AI Business Insight</div>
                <p class="mt-1 text-[14px] leading-6">{{ $headline }}</p>
                <a href="{{ route('assistant') }}" class="btn-primary btn-sm mt-4">See next action <x-icon name="arrow_forward" size="16" /></a>
            </div>
        </div>
        @php($rev = $fin['current']['revenue'] ?? $latest?->revenue)
        <x-stat-card label="Revenue" :value="$rev !== null ? rupiah_short($rev) : '—'" icon="payments" :sub="$fin ? ($fin['current']['label'].($fin['revenue_change'] !== null ? ' · '.signed($fin['revenue_change'], 1, '%').' MoM' : '')) : ($latest ? 'Growth Ledger '.$latest->period->translatedFormat('M Y') : null)" />
        <x-stat-card label="Net Profit" :value="$fin ? rupiah_short($fin['current']['net_profit']) : '—'" icon="savings" :sub="$fin ? 'Margin kotor '.pct($fin['current']['gross_margin'] ?? 0).($fin['profit_change'] !== null ? ' · '.signed($fin['profit_change'], 1, '%').' MoM' : '') : 'Catat transaksi di Growth Finance'" />
        <x-stat-card label="Customers" :value="$latest ? num($latest->total_customers) : '—'" icon="groups" :sub="$latest ? num($latest->new_customers).' pelanggan baru'.($previous && $previous->new_customers ? ' · '.signed(($latest->new_customers - $previous->new_customers) / $previous->new_customers * 100, 1, '%') : '') : null" />
        <div class="card p-5">
            <div class="flex items-center justify-between"><span class="text-[13px] font-medium text-slate">Inventory</span><x-icon name="inventory_2" class="text-slate-2" /></div>
            <div class="mt-2 grid grid-cols-3 gap-2 text-center">
                <div><div class="metric text-[22px]">{{ $inv['total'] }}</div><div class="text-[11px] text-slate">produk</div></div>
                <div><div class="metric text-[22px] {{ $inv['low'] ? 'text-[#b45309]' : '' }}">{{ $inv['low'] }}</div><div class="text-[11px] text-slate">low stock</div></div>
                <div><div class="metric text-[22px] {{ $inv['out'] ? 'text-brand-deep' : '' }}">{{ $inv['out'] }}</div><div class="text-[11px] text-slate">out of stock</div></div>
            </div>
            <a href="{{ route('inventory.index') }}" class="mt-3 inline-flex text-[12px] font-semibold text-brand-deep">Growth Inventory →</a>
        </div>
        <div class="card p-5 lg:col-span-1">
            <div class="flex items-center justify-between"><span class="text-[13px] font-medium text-slate">Marketing</span><x-source-badge source="simulated" /></div>
            @if ($campaign)
                <div class="mt-2 text-[14px]">MyAds ROAS: <b class="font-display text-[20px]">{{ num($campaign->roas(), 1) }}x</b></div>
                <div class="text-[13px] text-slate">{{ $campaign->name }} · {{ num($campaign->conversions) }} konversi</div>
            @else
                <div class="mt-2 text-[13px] text-slate">Belum ada kampanye.</div>
            @endif
            <div class="mt-1 text-[13px]">Conversion: <b>{{ $latest ? pct($latest->conversion_rate) : '—' }}</b></div>
        </div>
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <a href="{{ route('finance.index') }}" class="card flex items-center gap-4 p-5 hover:shadow-[var(--shadow-hover)]"><span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon name="account_balance_wallet" /></span><span class="flex-1"><span class="block font-semibold">Growth Finance</span><span class="text-[12px] text-slate">Aktivitas bisnis → laporan keuangan terukur. Kapabilitas native TUMBUH.</span></span><x-icon name="chevron_right" /></a>
        <a href="{{ route('inventory.index') }}" class="card flex items-center gap-4 p-5 hover:shadow-[var(--shadow-hover)]"><span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon name="inventory_2" /></span><span class="flex-1"><span class="block font-semibold">Growth Inventory</span><span class="text-[12px] text-slate">Apakah inventaris membantu atau menghambat pertumbuhan?</span></span><x-icon name="chevron_right" /></a>
    </div>
</x-layouts.app>
