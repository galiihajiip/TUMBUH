<x-layouts.app title="My Business Data">
    <x-page-header eyebrow="Profile & data governance" title="My Business Data" subtitle="Data yang Anda berikan, data yang terhubung, dan data yang digunakan untuk rekomendasi — dengan kontrol persetujuan." />
    <div class="grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.personal') }}" class="card p-6">@csrf @method('PUT')
            <h3 class="text-[16px] font-semibold">Personal data</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-field name="name" label="Nama" :value="$user->name" required />
                <div><span class="label">Email</span><div class="input flex items-center bg-canvas text-slate">{{ $user->email }}</div></div>
                <x-field name="phone" label="Telepon" :value="$user->phone" required />
                <x-select name="role" label="Peran" :options="config('tumbuh.roles')" :value="$user->role" />
            </div>
            <button class="btn-secondary btn-sm mt-4">Simpan</button>
        </form>
        <form method="POST" action="{{ route('profile.business') }}" class="card p-6">@csrf @method('PUT')
            <div class="flex items-center justify-between"><h3 class="text-[16px] font-semibold">Business data</h3><a href="{{ route('goals.edit') }}" class="text-[12px] font-semibold text-brand-deep">Ubah goal →</a></div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-field name="name" label="Nama usaha" :value="$business->name" required />
                <x-select name="category" label="Industri" :options="array_combine(array_keys(config('tumbuh.categories')), array_keys(config('tumbuh.categories')))" :value="$business->category" />
                <x-field name="city" label="Kota" :value="$business->city" required />
                <x-field name="district" label="Kecamatan" :value="$business->district" />
                <x-field name="employees" label="Karyawan" type="number" :value="$business->employees" required />
                <x-select name="revenue_range" label="Omzet" :options="collect(config('tumbuh.revenue_ranges'))->map(fn ($r) => $r['label'])->all()" :value="$business->revenue_range" />
            </div>
            <div class="mt-3 text-[12px] text-slate">Saluran: {{ collect($business->sales_channels)->map(fn ($c) => config("tumbuh.sales_channels.{$c}"))->implode(', ') }} · Goal: {{ $business->goals->map->label()->implode(', ') }}</div>
            <button class="btn-secondary btn-sm mt-4">Simpan</button>
        </form>
    </div>

    <div class="card mt-6 p-6">
        <h3 class="text-[16px] font-semibold">Connected services</h3>
        <p class="text-[12px] text-slate">Status koneksi setiap layanan. Di prototipe, tidak ada koneksi produksi — status "Connected (Demo)" / "Simulated" menandai integrasi simulasi.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($activations as $a)
                <div class="module p-3"><div class="flex items-center gap-2 text-[13px] font-semibold"><x-icon :name="$a->solution->icon" size="18" class="text-brand" />{{ $a->solution->name }}</div><div class="mt-2"><x-status-badge :status="$a->connection" /></div></div>
            @endforeach
            <div class="module p-3"><div class="flex items-center gap-2 text-[13px] font-semibold"><x-icon name="smartphone" size="18" class="text-brand" />MyTelkomsel</div><div class="mt-2"><x-status-badge :status="$business->usesProduct('mytelkomsel') ? 'self_reported' : 'unavailable'" /></div></div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_1fr]">
        <div class="card p-6">
            <h3 class="text-[16px] font-semibold">Privacy — data apa yang dipakai?</h3>
            <div class="mt-4 grid gap-3 sm:grid-cols-3 text-[13px]">
                <div class="module p-4"><div class="eyebrow">Data yang saya berikan</div><ul class="mt-2 space-y-1"><li>Profil & onboarding</li><li>{{ $counts['responses'] }} jawaban diagnostic</li><li>{{ $counts['missions'] }} check-in misi</li><li>{{ $counts['finance'] }} transaksi keuangan</li><li>{{ $counts['products'] }} produk</li></ul></div>
                <div class="module p-4"><div class="eyebrow">Data yang terhubung</div><ul class="mt-2 space-y-1"><li>{{ $counts['campaigns'] }} kampanye (Simulated)</li><li>Status aktivasi solusi (Simulated)</li><li class="text-slate">Tidak ada data transaksi Telkomsel nyata</li></ul></div>
                <div class="module p-4"><div class="eyebrow">Dipakai untuk rekomendasi</div><ul class="mt-2 space-y-1"><li>{{ $counts['kpi'] }} bulan Growth Ledger</li><li>Skor 8 dimensi</li><li>Margin & stok</li><li>Status misi</li></ul></div>
            </div>
        </div>
        <form method="POST" action="{{ route('profile.consents') }}" class="card p-6">@csrf @method('PUT')
            <h3 class="text-[16px] font-semibold">Consent</h3>
            <div class="mt-4 space-y-3">
                @foreach (config('tumbuh.consents') as $k => $c)
                    @php($row = $consents[$k] ?? null)
                    <label class="flex items-start justify-between gap-4 rounded-[12px] border border-line p-3">
                        <span><span class="block text-[14px] font-semibold">{{ $c['label'] }}</span><span class="block text-[12px] text-slate">{{ $c['help'] }}</span>
                            <span class="block text-[11px] text-slate-2">@if ($row?->granted)Diberikan {{ $row->granted_at?->translatedFormat('d M Y H:i') }}@elseif ($row?->revoked_at)Dicabut {{ $row->revoked_at->translatedFormat('d M Y H:i') }}@else Belum diberikan @endif</span></span>
                        <input type="checkbox" name="consents[{{ $k }}]" value="1" @checked($row?->granted) class="mt-1 h-5 w-9 cursor-pointer appearance-none rounded-full bg-line transition before:block before:h-4 before:w-4 before:translate-x-0.5 before:translate-y-0.5 before:rounded-full before:bg-white before:transition checked:bg-brand checked:before:translate-x-[18px]">
                    </label>
                @endforeach
            </div>
            <button class="btn-primary btn-sm mt-4">Simpan persetujuan</button>
        </form>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.password') }}" class="card p-6">@csrf @method('PUT')
            <h3 class="text-[16px] font-semibold">Security · password</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-3"><x-field name="current_password" label="Saat ini" type="password" required /><x-field name="password" label="Baru" type="password" required /><x-field name="password_confirmation" label="Ulangi" type="password" required /></div>
            <button class="btn-secondary btn-sm mt-4">Ubah password</button>
        </form>
        <div class="card p-6">
            <div class="flex items-center justify-between"><h3 class="text-[16px] font-semibold">Login devices / sessions</h3><form method="POST" action="{{ route('profile.logout-others') }}">@csrf<button class="btn-ghost btn-sm">Akhiri sesi lain</button></form></div>
            <div class="mt-3 space-y-2">
                @foreach ($sessions as $s)
                    <div class="flex items-center justify-between rounded-[10px] border border-line px-3 py-2 text-[12px]"><span class="truncate"><x-icon name="devices" size="16" /> {{ \Illuminate\Support\Str::limit($s->user_agent, 60) }} · {{ $s->ip_address }}</span><span class="shrink-0 text-slate">{{ $s->id === $currentSession ? 'Sesi ini' : \Illuminate\Support\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}</span></div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
