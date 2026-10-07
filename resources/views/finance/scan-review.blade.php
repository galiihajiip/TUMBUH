<x-layouts.app title="Konfirmasi Struk">
    @php($p = $receipt->parsed)
    <x-page-header eyebrow="Tinjau hasil OCR" title="Konfirmasi pengeluaran" subtitle="Periksa hasil pembacaan, ubah bila perlu, lalu konfirmasi." />
    <div class="grid max-w-5xl gap-6 lg:grid-cols-2">
        <div class="card p-6">
            <div class="flex items-center justify-between"><b>{{ $p['merchant'] }}</b><span class="pill bg-[#f5f3ff] text-[#6d28d9]">{{ $p['engine'] }}</span></div>
            <div class="mt-1 text-[12px] text-slate">{{ \Illuminate\Support\Carbon::parse($p['date'])->translatedFormat('d M Y') }} · confidence {{ (int) ($p['confidence'] * 100) }}%</div>
            <table class="mt-4 w-full text-[13px]">
                <thead><tr class="border-b border-line text-left text-[11px] uppercase text-slate"><th class="py-2">Item</th><th class="py-2 text-right">Qty</th><th class="py-2 text-right">Harga</th><th class="py-2 text-right">Subtotal</th></tr></thead>
                <tbody>@foreach ($p['items'] as $it)<tr class="border-b border-line"><td class="py-2">{{ $it['name'] }}</td><td class="py-2 text-right">{{ $it['qty'] }}</td><td class="py-2 text-right tabular">{{ rupiah($it['price']) }}</td><td class="py-2 text-right tabular">{{ rupiah($it['qty'] * $it['price']) }}</td></tr>@endforeach</tbody>
                <tfoot><tr><td colspan="3" class="py-2 text-right text-slate">Pajak</td><td class="py-2 text-right tabular">{{ rupiah($p['tax']) }}</td></tr><tr><td colspan="3" class="py-2 text-right font-semibold">Total</td><td class="py-2 text-right font-bold tabular">{{ rupiah($p['total']) }}</td></tr></tfoot>
            </table>
        </div>
        <form method="POST" action="{{ route('finance.scan.confirm', $receipt) }}" class="card p-6">@csrf
            @if ($receipt->status === 'confirmed')
                <div class="rounded-[12px] bg-ok-tint p-3 text-[13px] text-[#065f46]">Struk ini sudah dikonfirmasi.</div>
            @else
                <div class="space-y-4">
                    <x-field name="merchant" label="Merchant" :value="$p['merchant']" required />
                    <x-field name="entry_date" label="Tanggal" type="date" :value="$p['date']" required />
                    <x-field name="amount" label="Total" type="number" prefix="Rp" :value="$p['total']" required />
                    <x-select name="category" label="Kategori (auto)" :options="config('tumbuh.expense_categories')" :value="$p['suggested_category']" />
                    <x-select name="payment_method" label="Metode pembayaran" :options="config('tumbuh.payment_methods')" value="cash" />
                </div>
                <p class="mt-3 text-[12px] text-slate">Kategori disarankan otomatis dari kata kunci item: <b>{{ config('tumbuh.expense_categories.'.$p['suggested_category']) }}</b>.</p>
                <button class="btn-primary mt-6 w-full">Konfirmasi & simpan</button>
            @endif
        </form>
    </div>
</x-layouts.app>
