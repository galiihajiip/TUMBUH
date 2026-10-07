<x-layouts.app title="Tambah Produk">
    <x-page-header eyebrow="Growth Inventory" title="Add product" subtitle="Add product → initial stock → purchase → stock in → sales → alert → restock." />
    <form method="POST" action="{{ route('inventory.store') }}" enctype="multipart/form-data" class="card max-w-3xl p-6 md:p-8" x-data="{ buy: {{ old('purchase_price', 18000) }}, sell: {{ old('selling_price', 35000) }} }">@csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="name" label="Nama produk" required placeholder="Tiramisu Cake" />
            <x-field name="sku" label="SKU" required placeholder="TIRA-001" />
            <x-field name="category" label="Kategori" placeholder="Cake" />
            <x-field name="supplier" label="Supplier" placeholder="ABC Supplier" />
            <x-field name="purchase_price" label="Harga beli / biaya per unit" type="number" prefix="Rp" x-model="buy" required />
            <x-field name="selling_price" label="Harga jual" type="number" prefix="Rp" x-model="sell" required />
            <x-field name="stock" label="Stok awal" type="number" min="0" value="18" required />
            <x-field name="min_stock" label="Stok minimum (alert)" type="number" min="0" value="5" required />
            <x-field name="unit" label="Satuan" value="pcs" required />
            <div><label class="label" for="image">Foto produk</label><input id="image" type="file" name="image" accept="image/*" class="input pt-2 text-[13px]"></div>
        </div>
        <div class="mt-4 rounded-[12px] bg-canvas p-4 text-[13px]">Margin per unit: <b x-text="'Rp' + (sell - buy).toLocaleString('id-ID')"></b> (<span x-text="sell > 0 ? Math.round((sell - buy) / sell * 100) : 0"></span>%)</div>
        <div class="mt-6 flex justify-between"><a href="{{ route('inventory.index') }}" class="btn-ghost">Batal</a><button class="btn-primary">Simpan produk</button></div>
    </form>
</x-layouts.app>
