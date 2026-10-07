<x-layouts.focus title="Verifikasi">
    <div class="card p-6 md:p-8">
        <div class="flex items-center justify-between">
            <div class="eyebrow-brand">Langkah 2 dari 3 · Verifikasi</div>
            <span class="pill bg-[#f5f3ff] text-[#6d28d9]"><x-icon name="science" size="13" />Demo Verification Mode</span>
        </div>
        <h1 class="mt-1 text-[24px] font-bold">Masukkan kode OTP</h1>
        <p class="mt-1 text-[13px] text-slate">Kode dikirim ke <b>{{ $user->phone }}</b> dan <b>{{ $user->email }}</b>. Di lingkungan demo tidak ada SMS/email terkirim — gunakan kode <b class="text-ink">{{ config('tumbuh.demo.otp') }}</b>.</p>
        <form method="POST" action="{{ route('otp.verify') }}" class="mt-6 space-y-4" x-data="{ code: '' }">
            @csrf
            <input name="code" x-model="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" autofocus class="input h-14 text-center font-display text-[28px] font-bold tracking-[0.5em]" placeholder="••••••">
            @error('code')<p class="field-error">{{ $message }}</p>@enderror
            <div class="flex items-center justify-between text-[12px] text-slate" x-data="{ left: {{ max(0, (int) now()->diffInSeconds($expiresAt, false)) }} }" x-init="setInterval(() => left > 0 && left--, 1000)">
                <span>Kedaluwarsa dalam <b class="tabular text-ink" x-text="Math.floor(left/60)+':'+String(left%60).padStart(2,'0')"></b></span>
                <span x-show="left === 0" x-cloak class="text-brand-deep">Kode kedaluwarsa</span>
            </div>
            <button class="btn-primary w-full" :disabled="code.length !== 6">Verifikasi</button>
        </form>
        <form method="POST" action="{{ route('otp.resend') }}" class="mt-3 text-center" x-data="{ wait: {{ $resendIn }} }" x-init="setInterval(() => wait > 0 && wait--, 1000)">@csrf
            <button class="text-[13px] font-semibold text-slate hover:text-ink disabled:opacity-50" :disabled="wait > 0"><span x-show="wait > 0">Kirim ulang dalam <span x-text="wait"></span> dtk</span><span x-show="wait === 0" x-cloak>Kirim ulang kode</span></button>
        </form>
    </div>
</x-layouts.focus>
