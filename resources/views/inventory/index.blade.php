<x-layouts.app title="Growth Inventory">
    <x-page-header eyebrow="Growth Inventory · kapabilitas native TUMBUH" title="Is inventory helping or blocking growth?" subtitle="Penjualan → stock out → revenue → COGS → gross profit. Inventory tidak berdiri sendiri.">
        <x-slot:actions><a href="{{ route('inventory.create') }}" class="btn-primary btn-sm"><x-icon name="add" size="16" />Tambah produk</a></x-slot:actions>
    </x-page-header>
    @include('partials.nav-operate')
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total products" :value="$stats['total']" icon="inventory_2" />
        <x-stat-card label="Low stock" :value="$stats['low']" icon="warning" :tone="$stats['low'] ? 'brand' : 'ink'" />
        <x-stat-card label="Out of stock" :value="$stats['out']" icon="block" :tone="$stats['out'] ? 'brand' : 'ink'" />
        <x-stat-card label="Inventory value" :value="$canValue ? rupiah_short($stats['value']) : '🔒 Silver'" icon="account_balance" :sub="$canValue ? 'Nilai stok pada harga beli' : 'Nilai inventaris & COGS di Silver'" />
    </div>
    @if ($stats['total'] === 0)
        <x-empty-state icon="inventory_2" title="Add your products" text="Mulai dengan katalog produk: harga beli, harga jual, stok awal, dan stok minimum."><a href="{{ route('inventory.create') }}" class="btn-primary">Tambah produk pertama</a></x-empty-state>
    @else
        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            <div class="contents">
                <x-ai-insight title="Inventory Intelligence">
                    @forelse (array_slice($insights, 0, $predictive ? 6 : 3) as $i)
                        <div class="mb-3 flex gap-3 last:mb-0">
                            <x-icon :name="['bad' => 'error', 'warn' => 'info', 'good' => 'trending_up'][$i['tone']]" size="18" class="{{ ['bad' => 'text-brand', 'warn' => 'text-[#b45309]', 'good' => 'text-ok'][$i['tone']] }}" fill />
                            <div class="flex-1 text-[13px]"><div>{{ $i['text'] }}</div><div class="text-[12px] font-semibold text-slate">→ {{ $i['action'] }}</div></div>
                            @if ($i['product_id'] && in_array($i['type'], ['stockout', 'stockout_risk']))<a href="{{ route('inventory.show', $i['product_id']) }}" class="btn-secondary btn-sm">Restock</a>@endif
                        </div>
                    @empty
                        <p class="text-[13px] text-slate">Stok sehat. Belum ada risiko terdeteksi.</p>
                    @endforelse
                    @unless ($predictive)<p class="mt-3 text-[11px] text-slate"><x-icon name="lock" size="13" /> Demand prediction & AI restock lengkap tersedia di Gold.</p>@endunless
                </x-ai-insight>
                <div class="card p-5">
                    <h3 class="text-[16px] font-semibold">Top selling (14 hari)</h3>
                    <ol class="mt-3 space-y-2">
                        @forelse ($top as $i => $r)
                            <li class="flex items-center justify-between text-[13px]"><span><b>{{ $i + 1 }}.</b> {{ $r['product']->name }}</span><span class="tabular text-slate">{{ num($r['sold']) }} {{ $r['product']->unit }}</span></li>
                        @empty
                            <li class="text-[13px] text-slate">Belum ada penjualan tercatat.</li>
                        @endforelse
                    </ol>
                </div>
            </div>
            <div class="card overflow-x-auto lg:col-span-2">
                <table class="w-full min-w-[720px] text-[13px]">
                    <thead><tr class="border-b border-line bg-canvas text-left text-[11px] uppercase tracking-[0.06em] text-slate"><th class="px-4 py-2.5">Produk</th><th class="px-4 py-2.5 text-right">Stok</th><th class="px-4 py-2.5 text-right">Harga jual</th><th class="px-4 py-2.5 text-right">Margin</th><th class="px-4 py-2.5 text-right">{{ $predictive ? 'Habis dalam' : 'Terjual 14h' }}</th><th class="px-4 py-2.5">Status</th></tr></thead>
                    <tbody>
                        @foreach ($rows as $r)
                            @php($p = $r['product'])
                            <tr class="border-b border-line last:border-0 hover:bg-canvas">
                                <td class="px-4 py-3"><a href="{{ route('inventory.show', $p) }}" class="font-semibold hover:underline">{{ $p->name }}</a><div class="text-[11px] text-slate">{{ $p->sku }} · {{ $p->category }}@if ($r['movement'] !== 'normal') · <span class="{{ $r['movement'] === 'fast' ? 'text-ok' : 'text-[#b45309]' }}">{{ $r['movement'] === 'fast' ? 'fast-moving' : 'slow-moving' }}</span>@endif</div></td>
                                <td class="px-4 py-3 text-right tabular"><b>{{ $p->stock }}</b> <span class="text-slate">/ min {{ $p->min_stock }}</span></td>
                                <td class="px-4 py-3 text-right tabular">{{ rupiah($p->selling_price) }}</td>
                                <td class="px-4 py-3 text-right tabular">{{ pct($p->marginPercent(), 0) }}</td>
                                <td class="px-4 py-3 text-right tabular">{{ $predictive ? ($r['days_to_stockout'] !== null ? num($r['days_to_stockout'], 1).' hari' : '—') : num($r['sold']) }}</td>
                                <td class="px-4 py-3">@php($st = $p->stockStatus())<span class="pill {{ $st === 'ok' ? 'bg-ok-tint text-ok' : ($st === 'low' ? 'bg-warn-tint text-warn' : 'bg-brand-tint text-brand-deep') }}">{{ ['ok' => 'Aman', 'low' => 'Low stock', 'out' => 'Habis'][$st] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <p class="mt-5 flex items-start gap-2 text-[12px] text-slate"><x-icon name="info" size="16" />DigiPOS adalah lapisan aktivitas transaksi digital untuk use case yang didukung — bukan POS umum untuk semua SKU. Growth Inventory menerima input manual dan integrasi di masa depan.</p>
    @endif
</x-layouts.app>
