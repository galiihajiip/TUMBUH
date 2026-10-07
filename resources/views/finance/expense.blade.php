<x-layouts.app title="Catat Pengeluaran">
    <x-page-header eyebrow="Growth Finance" title="Add expense" />
    <div class="mb-6 grid max-w-2xl gap-3 sm:grid-cols-2">
        <a href="{{ $canScan ? route('finance.scan') : route('rewards.tiers') }}" class="card flex flex-col items-center gap-2 p-6 text-center hover:shadow-[var(--shadow-hover)]"><x-icon name="photo_camera" size="32" class="text-brand" /><b>Scan receipt</b><span class="text-[12px] text-slate">{{ $canScan ? 'OCR simulasi membaca merchant, item, total' : 'Tersedia di Silver' }}</span></a>
        <div class="card flex flex-col items-center gap-2 border-ink p-6 text-center"><x-icon name="edit_note" size="32" /><b>Enter manually</b><span class="text-[12px] text-slate">Isi formulir di bawah</span></div>
    </div>
    <form method="POST" action="{{ route('finance.expense.store') }}" class="card max-w-2xl p-6 md:p-8">@csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="entry_date" label="Tanggal" type="date" :value="now()->toDateString()" required />
            <x-select name="category" label="Kategori" :options="config('tumbuh.expense_categories')" value="raw_material" />
            <x-field name="amount" label="Jumlah" type="number" prefix="Rp" min="1" required />
            <x-select name="payment_method" label="Metode pembayaran" :options="config('tumbuh.payment_methods')" value="cash" />
            <x-field name="description" label="Keterangan" class="sm:col-span-2" required placeholder="mis. Belanja susu & gula" />
            <div class="sm:col-span-2"><label class="label" for="notes">Catatan</label><textarea id="notes" name="notes" rows="2" class="input"></textarea></div>
        </div>
        <div class="mt-6 flex justify-between"><a href="{{ route('finance.index') }}" class="btn-ghost">Batal</a><button class="btn-primary">Simpan pengeluaran</button></div>
    </form>
</x-layouts.app>
