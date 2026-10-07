<x-layouts.focus title="Daftar">
    <div class="card p-6 md:p-8">
        <div class="eyebrow-brand">Langkah 1 dari 3 · Akun</div>
        <h1 class="mt-1 text-[24px] font-bold">Start your growth journey</h1>
        <p class="mt-1 text-[13px] text-slate">Cukup data dasar dulu. Profil bisnis diisi setelah verifikasi.</p>
        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf
            <x-field name="name" label="Nama lengkap" required autocomplete="name" placeholder="mis. Aisha Rahma" />
            <x-field name="email" label="Email" type="email" required autocomplete="email" placeholder="nama@usaha.com" />
            <x-field name="phone" label="Nomor HP" required placeholder="0812xxxxxxxx" help="Nomor Telkomsel membantu pengecekan kelayakan UMKM Plus (simulasi)." />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="password" label="Password" type="password" required autocomplete="new-password" help="Min. 8 karakter, huruf & angka." />
                <x-field name="password_confirmation" label="Ulangi password" type="password" required autocomplete="new-password" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-select name="role" label="Peran" :options="config('tumbuh.roles')" value="owner" />
                <x-field name="referral_code" label="Kode referral (opsional)" />
            </div>
            <label class="flex items-start gap-2 text-[13px]">
                <input type="checkbox" name="terms" value="1" class="check mt-0.5" @checked(old('terms'))>
                <span>Saya menyetujui <a href="#" class="link">Syarat Layanan</a> & <a href="#" class="link">Kebijakan Privasi</a>, termasuk penggunaan data bisnis untuk Growth Score.</span>
            </label>
            @error('terms')<p class="field-error">{{ $message }}</p>@enderror
            <button class="btn-primary w-full">Create account <x-icon name="arrow_forward" size="18" /></button>
            <div class="flex items-center gap-3 text-[11px] text-slate-2"><span class="h-px flex-1 bg-line"></span>ATAU<span class="h-px flex-1 bg-line"></span></div>
            <button type="button" disabled class="btn-secondary w-full" title="Tidak tersedia di prototipe">Continue with Google <span class="text-[11px] font-normal">(tidak aktif di demo)</span></button>
        </form>
    </div>
    <p class="mt-5 text-center text-[13px] text-slate">Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk</a></p>
</x-layouts.focus>
