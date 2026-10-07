<x-layouts.app :title="$product->name">
    <x-page-header :eyebrow="$product->sku.' · '.$product->category" :title="$product->name" :subtitle="'Supplier: '.($product->supplier ?: '—')">
        <x-slot:actions>@php($st = $product->stockStatus())<span class="pill {{ $st === 'ok' ? 'bg-ok-tint text-ok' : ($st === 'low' ? 'bg-warn-tint text-warn' : 'bg-brand-tint text-brand-deep') }}">{{ ['ok' => 'Stok aman', 'low' => 'Low stock', 'out' => 'Habis'][$st] }}</span></x-slot:actions>
    </x-page-header>
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-stat-card label="Stok saat ini" :value="$product->stock.' '.$product->unit" :sub="'Minimum '.$product->min_stock" />
        <x-stat-card label="Harga beli" :value="rupiah($product->purchase_price)" />
        <x-stat-card label="Harga jual" :value="rupiah($product->selling_price)" />
        <x-stat-card label="Margin / unit" :value="rupiah($product->margin())" :sub="pct($product->marginPercent()).' dari harga jual'" />
        <x-stat-card label="{{ $predictive ? 'Prediksi 7 hari' : 'Terjual 14 hari' }}" :value="$predictive ? ($row['forecast_7d'] ?? 0).' '.$product->unit : num($row['sold'] ?? 0)" :sub="$row && $row['days_to_stockout'] !== null ? 'Habis ±'.num($row['days_to_stockout'], 1).' hari' : null" />
    </div>
    <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr]">
        <div class="space-y-6">
            <form method="POST" action="{{ route('inventory.sale', $product) }}" class="card p-5">@csrf
                <h3 class="font-semibold">Record sale (stock out)</h3>
                <p class="text-[12px] text-slate">Mengurangi stok, mencatat revenue & COGS di Growth Finance.</p>
                <div class="mt-3 grid grid-cols-2 gap-3"><x-field name="quantity" label="Jumlah" type="number" min="1" :max="max(1, $product->stock)" value="1" required /><x-select name="payment_method" label="Pembayaran" :options="config('tumbuh.payment_methods')" value="qris" /></div>
                <button class="btn-primary mt-4 w-full" @disabled($product->stock <= 0)>Catat penjualan</button>
            </form>
            <form method="POST" action="{{ route('inventory.stock-in', $product) }}" class="card p-5">@csrf
                <h3 class="font-semibold">Restock / purchase (stock in)</h3>
                <div class="mt-3 grid grid-cols-2 gap-3"><x-field name="quantity" label="Jumlah" type="number" min="1" :value="max(1, $row['restock_qty'] ?? $product->min_stock)" required /><x-field name="unit_cost" label="Biaya / unit" type="number" prefix="Rp" :value="$product->purchase_price" /></div>
                <label class="mt-3 flex items-center gap-2 text-[13px]"><input type="hidden" name="record_expense" value="0"><input type="checkbox" name="record_expense" value="1" checked class="check">Catat sebagai pengeluaran (cash out)</label>
                @if ($predictive && $row)<p class="mt-2 text-[12px] text-slate">AI restock: {{ $row['restock_qty'] }} {{ $product->unit }} (7 hari permintaan + stok minimum − stok).</p>@endif
                <button class="btn-secondary mt-4 w-full">Tambah stok</button>
            </form>
            <form method="POST" action="{{ route('inventory.adjust', $product) }}" class="card p-5">@csrf
                <h3 class="font-semibold">Stock opname</h3>
                <div class="mt-3 grid grid-cols-2 gap-3"><x-field name="stock" label="Stok fisik" type="number" min="0" :value="$product->stock" required /><x-field name="note" label="Catatan" value="Stock opname" /></div>
                <button class="btn-ghost mt-3 w-full">Sesuaikan stok</button>
            </form>
        </div>
        <div class="card overflow-x-auto">
            <div class="p-5 pb-0"><x-section-header title="Inventory movements" subtitle="Setiap pergerakan terhubung ke entri keuangan bila ada" /></div>
            <table class="w-full min-w-[560px] text-[13px]">
                <thead><tr class="border-y border-line bg-canvas text-left text-[11px] uppercase tracking-[0.06em] text-slate"><th class="px-5 py-2.5">Tanggal</th><th class="px-5 py-2.5">Tipe</th><th class="px-5 py-2.5 text-right">Qty</th><th class="px-5 py-2.5">Keuangan</th><th class="px-5 py-2.5">Sumber</th></tr></thead>
                <tbody>
                    @foreach ($movements as $m)
                        <tr class="border-b border-line last:border-0"><td class="px-5 py-2.5 text-slate">{{ $m->moved_on->translatedFormat('d M Y') }}</td><td class="px-5 py-2.5">{{ ['initial' => 'Stok awal', 'purchase' => 'Stock in', 'sale' => 'Sales / stock out', 'adjustment' => 'Penyesuaian'][$m->type] }}</td><td class="px-5 py-2.5 text-right font-semibold tabular {{ $m->quantity < 0 ? 'text-brand-deep' : 'text-ok' }}">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
                            <td class="px-5 py-2.5 text-[12px]">@if ($m->financialEntry){{ $m->financialEntry->type === 'income' ? 'Revenue '.rupiah($m->financialEntry->amount).' · COGS '.rupiah($m->financialEntry->cogs) : 'Cash out '.rupiah($m->financialEntry->amount) }}@else<span class="text-slate">—</span>@endif</td><td class="px-5 py-2.5 text-[12px] text-slate">{{ $m->source }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
