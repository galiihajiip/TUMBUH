<x-layouts.focus :title="'Onboarding · '.$steps[$step]['title']" width="max-w-[720px]">
    <x-slot:header><span>Let's understand your business</span></x-slot:header>
    @php($b = $business)
    {{-- Progress --}}
    <div class="mb-6">
        <div class="mb-2 flex items-center justify-between text-[12px] font-semibold text-slate"><span>Langkah {{ $step }} dari 6</span><span>{{ round(($step - 1) / 6 * 100) }}%</span></div>
        <div class="grid grid-cols-6 gap-1.5">
            @foreach ($steps as $n => $s)
                <a @if ($n < $business->onboarding_step) href="{{ route('onboarding.show', $n) }}" @endif class="h-1.5 rounded-full {{ $n < $step ? 'bg-ink' : ($n === $step ? 'bg-brand' : 'bg-line') }}" title="{{ $s['title'] }}"></a>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('onboarding.store', $step) }}" enctype="multipart/form-data" class="card p-6 md:p-8">
        @csrf
        <div class="mb-6 flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon :name="$steps[$step]['icon']" /></span>
            <div><div class="eyebrow">{{ $steps[$step]['en'] }}</div><h1 class="text-[22px] font-bold">{{ $steps[$step]['title'] }}</h1></div>
        </div>

        @switch($step)
            @case(1)
                <div class="space-y-4" x-data="{ cat: '{{ old('category', $b->category) }}', subs: @js(config('tumbuh.categories')) }">
                    <x-field name="name" label="Nama usaha" :value="$b->name" required placeholder="mis. Chacha Coffee" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="category">Kategori</label>
                            <select id="category" name="category" x-model="cat" class="input" required>
                                <option value="">Pilih kategori</option>
                                @foreach (config('tumbuh.categories') as $c => $_)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                            </select>
                            @error('category')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="sub_category">Sub-kategori</label>
                            <select id="sub_category" name="sub_category" class="input">
                                <template x-for="s in (subs[cat] || [])"><option :value="s" x-text="s" :selected="s === @js(old('sub_category', $b->sub_category))"></option></template>
                            </select>
                        </div>
                    </div>
                    <div><label class="label" for="description">Deskripsi singkat</label><textarea id="description" name="description" rows="3" class="input" placeholder="Apa yang Anda jual dan siapa pelanggan utama Anda?">{{ old('description', $b->description) }}</textarea></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field name="start_year" label="Tahun mulai usaha" type="number" :value="$b->start_year" min="1950" :max="now()->year" required />
                        <div><label class="label" for="logo">Logo usaha (opsional)</label><input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="input pt-2 text-[13px]">@error('logo')<p class="field-error">{{ $message }}</p>@enderror</div>
                    </div>
                </div>
                @break
            @case(2)
                <div class="space-y-4" x-data="{ prov: '{{ old('province', $b->province) }}', cities: @js(config('tumbuh.provinces')), store: '{{ old('has_physical_store', $b->has_physical_store ? 1 : 0) }}' }">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="label" for="province">Provinsi</label>
                            <select id="province" name="province" x-model="prov" class="input" required><option value="">Pilih provinsi</option>@foreach (config('tumbuh.provinces') as $p => $_)<option value="{{ $p }}">{{ $p }}</option>@endforeach</select>
                            @error('province')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div><label class="label" for="city">Kota / Kabupaten</label>
                            <select id="city" name="city" class="input" required><template x-for="c in (cities[prov] || [])"><option :value="c" x-text="c" :selected="c === @js(old('city', $b->city))"></option></template></select></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field name="district" label="Kecamatan" :value="$b->district" required />
                        <x-field name="outlets" label="Jumlah outlet" type="number" min="0" :value="$b->outlets ?? 1" required />
                    </div>
                    <x-field name="address" label="Alamat usaha" :value="$b->address" />
                    <div><span class="label">Punya toko fisik?</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ([1 => 'Ya, ada toko fisik', 0 => 'Tidak, online / rumahan'] as $v => $l)
                                <label class="module flex cursor-pointer items-center gap-2 p-3 text-[13px]" :class="store == '{{ $v }}' && 'border-ink ring-[3px] ring-brand-tint'"><input type="radio" name="has_physical_store" value="{{ $v }}" x-model="store" class="accent-[#ec1c24]">{{ $l }}</label>
                            @endforeach
                        </div>
                    </div>
                    <p class="flex gap-2 rounded-[12px] bg-canvas p-3 text-[12px] text-slate"><x-icon name="info" size="16" />Data lokasi digunakan untuk rekomendasi pemasaran berbasis lokasi dan expansion intelligence.</p>
                </div>
                @break
            @case(3)
                <div class="space-y-4">
                    <div><span class="label">Omzet bulanan</span>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach (config('tumbuh.revenue_ranges') as $k => $r)
                                <label class="module flex cursor-pointer items-center gap-2 p-3 text-[13px] has-[:checked]:border-ink has-[:checked]:ring-[3px] has-[:checked]:ring-brand-tint"><input type="radio" name="revenue_range" value="{{ $k }}" class="accent-[#ec1c24]" @checked(old('revenue_range', $b->revenue_range) === $k)>{{ $r['label'] }}</label>
                            @endforeach
                        </div>
                        @error('revenue_range')<p class="field-error">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[12px] text-slate">Range saja — angka pasti dikonfirmasi nanti di baseline.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field name="employees" label="Jumlah karyawan" type="number" min="0" :value="$b->employees" required />
                        <x-field name="avg_monthly_transactions" label="Rata-rata transaksi / bulan" type="number" min="0" :value="$b->avg_monthly_transactions" required />
                        <x-field name="avg_order_value" label="Rata-rata nilai transaksi" type="number" min="0" prefix="Rp" :value="$b->avg_order_value" required />
                        <x-field name="monthly_customers" label="Jumlah pelanggan / bulan" type="number" min="0" :value="$b->monthly_customers" required />
                    </div>
                </div>
                @break
            @case(4)
                <div x-data="{ ch: @js(old('sales_channels', $b->sales_channels ?? [])) }" class="space-y-4">
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach (config('tumbuh.sales_channels') as $k => $l)
                            <label class="module flex cursor-pointer items-center gap-2 p-3 text-[13px] has-[:checked]:border-ink has-[:checked]:bg-brand-soft"><input type="checkbox" name="sales_channels[]" value="{{ $k }}" x-model="ch" class="check">{{ $l }}</label>
                        @endforeach
                    </div>
                    @error('sales_channels')<p class="field-error">{{ $message }}</p>@enderror
                    <div x-show="ch.includes('instagram')" x-cloak><x-field name="instagram_url" label="Instagram URL" type="url" :value="$b->instagram_url" placeholder="https://instagram.com/usahaanda" /></div>
                    <div x-show="ch.includes('marketplace')" x-cloak><x-field name="marketplace_url" label="Marketplace URL" type="url" :value="$b->marketplace_url" placeholder="https://..." /></div>
                </div>
                @break
            @case(5)
                <p class="mb-3 text-[13px] text-slate">Pilih yang sudah Anda gunakan (boleh kosong).</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach (config('tumbuh.digital_tools') as $k => $l)
                        <label class="module flex cursor-pointer items-center gap-2 p-3 text-[13px] has-[:checked]:border-ink has-[:checked]:bg-brand-soft"><input type="checkbox" name="digital_tools[]" value="{{ $k }}" class="check" @checked(in_array($k, old('digital_tools', $b->digital_tools ?? []), true))>{{ $l }}</label>
                    @endforeach
                </div>
                @break
            @case(6)
                <p class="mb-3 text-[13px] text-slate">Which Telkomsel solutions are you currently using? Ini mencegah TUMBUH merekomendasikan produk yang sebenarnya sudah Anda pakai.</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (config('tumbuh.telkomsel_products') as $k => $l)
                        <label class="module flex cursor-pointer items-center gap-2 p-3 text-[13px] has-[:checked]:border-ink has-[:checked]:bg-brand-soft"><input type="checkbox" name="telkomsel_products[]" value="{{ $k }}" class="check" @checked(in_array($k, old('telkomsel_products', $b->telkomsel_products ?? []), true))>{{ $l }}</label>
                    @endforeach
                </div>
                @error('telkomsel_products')<p class="field-error">{{ $message }}</p>@enderror
                @break
        @endswitch

        <div class="mt-8 flex items-center justify-between border-t border-line pt-5">
            @if ($step > 1)<a href="{{ route('onboarding.show', $step - 1) }}" class="btn-ghost"><x-icon name="arrow_back" size="18" />Kembali</a>@else<span></span>@endif
            <button class="btn-primary">{{ $step < 6 ? 'Simpan & lanjut' : 'Selesai — tentukan goal' }} <x-icon name="arrow_forward" size="18" /></button>
        </div>
    </form>
    <p class="mt-4 text-center text-[12px] text-slate">Setiap langkah tersimpan otomatis. Anda bisa melanjutkan kapan saja.</p>
</x-layouts.focus>
