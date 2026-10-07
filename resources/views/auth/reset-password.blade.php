<x-layouts.focus title="Reset password">
    <div class="card p-6 md:p-8">
        <h1 class="text-[24px] font-bold">Buat password baru</h1>
        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-field name="email" label="Email" type="email" :value="$email" required />
            <x-field name="password" label="Password baru" type="password" required help="Min. 8 karakter, huruf & angka." />
            <x-field name="password_confirmation" label="Ulangi password" type="password" required />
            <button class="btn-primary w-full">Simpan password</button>
        </form>
    </div>
</x-layouts.focus>
