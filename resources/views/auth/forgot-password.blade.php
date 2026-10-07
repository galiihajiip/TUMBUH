<x-layouts.focus title="Lupa password">
    <div class="card p-6 md:p-8">
        <h1 class="text-[24px] font-bold">Lupa password</h1>
        <p class="mt-1 text-[13px] text-slate">Masukkan email akun Anda. Kami akan membuat tautan reset.</p>
        @if (session('status'))
            <div class="mt-4 rounded-[12px] border border-line bg-canvas p-3 text-[13px]">
                {{ session('status') }}
                @if (session('demo_link'))
                    <div class="mt-2 flex items-center gap-2"><span class="pill bg-[#f5f3ff] text-[#6d28d9]">Demo Mode</span><a href="{{ session('demo_link') }}" class="link break-all">Buka tautan reset</a></div>
                @endif
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">@csrf
            <x-field name="email" label="Email" type="email" required />
            <button class="btn-primary w-full">Buat tautan reset</button>
        </form>
    </div>
    <p class="mt-5 text-center text-[13px]"><a href="{{ route('login') }}" class="link">Kembali ke login</a></p>
</x-layouts.focus>
