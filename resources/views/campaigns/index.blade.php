<x-layouts.app title="MyAds Campaign">
    <x-page-header eyebrow="MyAds · Acquisition engine" title="Campaign Dashboard" subtitle="Hasil kampanye tersimulasi secara deterministik dari budget, radius, dan kesiapan bisnis Anda.">
        <x-slot:actions><a href="{{ route('campaigns.create') }}" class="btn-primary btn-sm"><x-icon name="add" size="16" />Kampanye baru</a></x-slot:actions>
    </x-page-header>
    @include('partials.nav-solutions')
    @if (! $current)
        <x-empty-state icon="campaign" title="Belum ada kampanye" text="Luncurkan kampanye berbasis lokasi untuk menjangkau pelanggan di sekitar toko."><a href="{{ route('campaigns.create') }}" class="btn-primary">Buat kampanye</a></x-empty-state>
    @else
        <div class="card mb-6 p-5 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><div class="eyebrow">Kampanye terbaru</div><h2 class="text-[20px] font-semibold">{{ $current->name }}</h2><div class="text-[12px] text-slate">{{ $current->audience }} · radius {{ $current->radius_km }} km · budget {{ rupiah($current->budget) }} · {{ $current->launched_at?->translatedFormat('d M Y') }}</div></div>
                <div class="flex items-center gap-2"><x-status-badge :status="$current->status" /><x-source-badge source="simulated" /></div>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-5">
                @foreach (['Reach' => num($current->reach), 'Clicks' => num($current->clicks), 'Leads' => num($current->leads), 'Conversion' => num($current->conversions), 'ROAS' => num($current->roas(), 1).'x'] as $l => $v)
                    <div class="module p-4"><div class="text-[12px] text-slate">{{ $l }}</div><div class="metric text-[24px]">{{ $v }}</div></div>
                @endforeach
            </div>
            <div class="mt-4 grid gap-3 text-[12px] text-slate sm:grid-cols-3">
                <div>CTR: <b class="text-ink">{{ pct($current->ctr()) }}</b></div><div>Leads → pembeli: <b class="text-ink">{{ pct($current->conversionRate()) }}</b></div><div>Pendapatan teratribusi: <b class="text-ink">{{ rupiah_short($current->attributed_revenue) }}</b></div>
            </div>
            @if ($insight)
                <div class="mt-5 flex flex-wrap items-center gap-4 rounded-[12px] {{ $insight['tone'] === 'bad' ? 'bg-brand-soft' : 'bg-ok-tint' }} p-4">
                    <x-icon name="auto_awesome" class="{{ $insight['tone'] === 'bad' ? 'text-brand' : 'text-ok' }}" fill />
                    <div class="flex-1 text-[14px]"><b>AI insight:</b> {{ $insight['text'] }}</div>
                    @if ($insight['tone'] === 'bad')<form method="POST" action="{{ route('campaigns.optimize', $current) }}">@csrf<button class="btn-primary btn-sm">Optimize campaign</button></form>@endif
                </div>
            @endif
        </div>
        <div class="card overflow-x-auto">
            <table class="w-full min-w-[720px] text-[13px]">
                <thead><tr class="border-b border-line bg-canvas text-left text-[11px] uppercase tracking-[0.06em] text-slate"><th class="px-5 py-2.5">Kampanye</th><th class="px-5 py-2.5">Objective</th><th class="px-5 py-2.5 text-right">Spend</th><th class="px-5 py-2.5 text-right">Reach</th><th class="px-5 py-2.5 text-right">Leads</th><th class="px-5 py-2.5 text-right">Konversi</th><th class="px-5 py-2.5 text-right">ROAS</th><th class="px-5 py-2.5">Status</th></tr></thead>
                <tbody>@foreach ($campaigns as $c)<tr class="border-b border-line last:border-0"><td class="px-5 py-3 font-semibold">{{ $c->name }}</td><td class="px-5 py-3">{{ ucfirst($c->objective) }}</td><td class="px-5 py-3 text-right tabular">{{ rupiah_short($c->spend) }}</td><td class="px-5 py-3 text-right tabular">{{ num($c->reach) }}</td><td class="px-5 py-3 text-right tabular">{{ num($c->leads) }}</td><td class="px-5 py-3 text-right tabular">{{ num($c->conversions) }}</td><td class="px-5 py-3 text-right font-semibold tabular">{{ num($c->roas(), 1) }}x</td><td class="px-5 py-3"><x-status-badge :status="$c->status" /></td></tr>@endforeach</tbody>
            </table>
        </div>
    @endif
    <x-sim-note class="mt-5" text="Simulated Data — tidak ada kampanye nyata yang dijalankan dan tidak ada saldo yang terpotong." />
</x-layouts.app>
