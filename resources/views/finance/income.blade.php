<x-layouts.app title="Catat Pemasukan">
    <x-page-header eyebrow="Growth Finance" title="Catat pemasukan" subtitle="Untuk penjualan per produk dengan pengurangan stok otomatis, gunakan Record Sale di Growth Inventory." />
    <form method="POST" action="{{ route('finance.income.store') }}" class="card max-w-2xl p-6 md:p-8">@csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="entry_date" label="Tanggal" type="date" :value="now()->toDateString()" required />
            <x-select name="category" label="Kategori pemasukan" :options="config('tumbuh.income_categories')" value="product_sales" />
            <x-field name="amount" label="Jumlah" type="number" prefix="Rp" min="1" required />
            <x-select name="payment_method" label="Metode pembayaran" :options="config('tumbuh.payment_methods')" value="qris" />
            <x-field name="customer" label="Pelanggan (opsional)" />
            <x-select name="product_id" label="Produk / layanan (opsional)" :options="$products->pluck('name', 'id')->all()" placeholder="— tidak terkait produk —" />
            <x-field name="description" label="Keterangan" class="sm:col-span-2" placeholder="mis. Penjualan kopi susu" />
            <div class="sm:col-span-2"><label class="label" for="notes">Catatan</label><textarea id="notes" name="notes" rows="2" class="input"></textarea></div>
        </div>
        <p class="mt-4 rounded-[12px] bg-canvas p-3 text-[12px] text-slate">Transaksi QRIS/Soundbox dapat masuk otomatis <b>hanya jika</b> tersedia integrasi partner/acquirer dan persetujuan pengguna. Di prototipe, input manual.</p>
        <div class="mt-6 flex justify-between"><a href="{{ route('finance.index') }}" class="btn-ghost">Batal</a><button class="btn-primary">Simpan pemasukan</button></div>
    </form>
</x-layouts.app>
