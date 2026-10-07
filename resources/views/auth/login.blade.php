<x-layouts.focus title="Masuk" width="max-w-[960px]">
    <div class="grid gap-6 md:grid-cols-[1fr_1fr]">
        <div class="card p-6 md:p-8">
            <h1 class="text-[24px] font-bold">Selamat datang kembali</h1>
            <p class="mt-1 text-[13px] text-slate">Masuk untuk melanjutkan Growth Loop bisnis Anda.</p>
            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf
                <x-field name="email" label="Email" type="email" required autofocus autocomplete="email" />
                <x-field name="password" label="Password" type="password" required autocomplete="current-password" />
                <div class="flex items-center justify-between text-[13px]">
                    <label class="flex items-center gap-2"><input type="checkbox" name="remember" class="check"> Ingat saya</label>
                    <a href="{{ route('password.request') }}" class="link">Lupa password?</a>
                </div>
                <button class="btn-primary w-full">Masuk</button>
            </form>
            <p class="mt-5 text-center text-[13px] text-slate">Belum punya akun? <a href="{{ route('register') }}" class="link">Daftar gratis</a></p>
        </div>
        @if ($demoAccounts)
            <div class="card p-6 md:p-8">
                <div class="flex items-center justify-between">
                    <div class="eyebrow">Demo Accounts</div>
                    <span class="pill bg-[#f5f3ff] text-[#6d28d9]"><x-icon name="science" size="13" />Demo Environment</span>
                </div>
                <p class="mt-1 text-[12px] text-slate">Akses cepat untuk presentasi. Password semua akun: <code class="rounded bg-muted px-1">{{ config('tumbuh.demo.password') }}</code></p>
                <div class="mt-4 space-y-2">
                    @foreach ($demoAccounts as $key => $a)
                        <form method="POST" action="{{ route('demo.login', $key) }}">@csrf
                            <button class="flex w-full items-center gap-3 rounded-[12px] border border-line p-3 text-left transition hover:border-line-strong hover:shadow-[var(--shadow-hover)]">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-tint font-bold text-brand-deep">{{ substr($a['name'], 0, 1) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2 text-[14px] font-semibold">{{ $a['business'] }} <x-tier-badge :tier="$a['tier']" /></span>
                                    <span class="block truncate text-[12px] text-slate">{{ $a['name'] }} · {{ $a['story'] }}</span>
                                </span>
                                <x-icon name="login" class="text-slate" />
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.focus>
