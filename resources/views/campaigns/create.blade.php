<x-layouts.app title="Kampanye baru">
    <x-page-header eyebrow="MyAds · Create campaign" title="Buat kampanye tertarget" subtitle="Objective → audience → lokasi → budget → launch → monitor." />
    @include('partials.nav-solutions')
    @php($retention = $focus === 'retention')
    <form method="POST" action="{{ route('campaigns.store') }}" class="card max-w-3xl p-6 md:p-8" x-data="{ budget: {{ old('budget', 500000) }}, radius: {{ old('radius_km', 2) }} }">@csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="name" label="Nama kampanye" :value="$retention ? 'Ajak Kembali — Voucher Kunjungan Kedua' : 'Weekend Promo Radius 2 km'" required class="sm:col-span-2" />
            <x-select name="objective" label="Objective" :options="['acquisition' => 'Akuisisi pelanggan baru', 'retention' => 'Retensi / re-engagement', 'conversion' => 'Konversi penawaran']" :value="$retention ? 'retention' : 'acquisition'" />
            <x-field name="audience" label="Target audience" :value="$retention ? 'Pelanggan tidak aktif 14 hari' : 'Pekerja & keluarga radius 2 km'" required />
            <div><label class="label">Radius lokasi: <span x-text="radius"></span> km</label><input type="range" name="radius_km" min="1" max="10" x-model="radius" class="w-full accent-[#ec1c24]"></div>
            <div><x-field name="budget" label="Budget" type="number" prefix="Rp" min="100000" step="50000" x-model="budget" required /><p class="mt-1 text-[12px] text-slate">Estimasi jangkauan: <b x-text="Math.round(budget * 0.049 * (0.8 + 0.1 * radius)).toLocaleString('id-ID')"></b> orang (simulasi)</p></div>
        </div>
        <div class="mt-6 rounded-[12px] bg-canvas p-4 text-[12px] text-slate"><b class="text-ink">Biaya penggunaan:</b> kampanye MyAds menggunakan saldo iklan (top up) di luar langganan TUMBUH. Di prototipe, peluncuran adalah simulasi — tidak ada biaya nyata.</div>
        <div class="mt-6 flex justify-between"><a href="{{ route('campaigns.index') }}" class="btn-ghost">Batal</a><button class="btn-primary">Submit & launch <x-icon name="rocket_launch" size="18" /></button></div>
    </form>
</x-layouts.app>
