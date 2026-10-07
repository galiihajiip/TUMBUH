<x-layouts.focus title="Konfirmasi Data Bisnis" width="max-w-[720px]">
    <div class="mb-6">
        <div class="eyebrow-brand">Data baseline</div>
        <h1 class="mt-1 text-[26px] font-bold">Let's confirm your current business performance.</h1>
        <p class="text-[13px] text-slate">Angka ini menjadi titik awal (baseline) untuk mengukur dampak. Periode: <b>{{ $period->translatedFormat('F Y') }}</b>. Edit bila perlu, lalu konfirmasi.</p>
    </div>
    <form method="POST" action="{{ route('diagnostic.baseline.confirm') }}" class="card p-6 md:p-8">@csrf
        <div class="mb-4 flex items-center gap-2 text-[12px] text-slate">Sumber: <x-source-badge :source="$kpi['source']" /> <span>— diisi dari jawaban diagnostic & onboarding. Setelah dikonfirmasi menjadi <b>Self-reported</b>.</span></div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="revenue" label="Omzet bulanan" type="number" prefix="Rp" :value="$kpi['revenue']" required />
            <x-field name="marketing_spend" label="Biaya pemasaran / bulan" type="number" prefix="Rp" :value="$kpi['marketing_spend']" required />
            <x-field name="new_customers" label="Pelanggan baru / bulan" type="number" :value="$kpi['new_customers']" required />
            <x-field name="total_customers" label="Total pelanggan aktif" type="number" :value="$kpi['total_customers']" required />
            <x-field name="repeat_rate" label="Repeat customer (%)" type="number" step="0.1" :value="$kpi['repeat_rate']" required />
            <x-field name="conversion_rate" label="Conversion rate (%)" type="number" step="0.1" :value="$kpi['conversion_rate']" required help="Pembeli ÷ orang yang bertanya." />
        </div>
        <div class="mt-6 rounded-[12px] bg-canvas p-4 text-[12px] text-slate">
            <b class="text-ink">Kenapa ada label sumber data?</b> Agar platform tidak mengklaim semua angka sebagai data transaksi terverifikasi. Label: Self-reported · Integrated (Demo) · Verified · Estimated.
        </div>
        <div class="mt-6 flex items-center justify-between border-t border-line pt-5">
            <a href="{{ route('diagnostic.review') }}" class="btn-ghost"><x-icon name="arrow_back" size="18" />Ubah jawaban</a>
            <button class="btn-primary">Konfirmasi & hitung Growth Score <x-icon name="arrow_forward" size="18" /></button>
        </div>
    </form>
</x-layouts.focus>
