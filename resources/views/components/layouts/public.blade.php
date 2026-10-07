@props(['title' => null])
<x-layouts.base :title="$title ?? null" body-class="bg-white">
    <header x-data="{ open: false }" class="sticky top-0 z-30 border-b border-line bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-[72px] max-w-[1200px] items-center gap-6 px-4 md:px-8">
            <a href="{{ route('landing') }}"><x-logo /></a>
            <nav class="ml-6 hidden gap-6 text-[13px] font-semibold text-ink-2 md:flex">
                <a href="{{ route('landing') }}#cara-kerja" class="hover:text-brand-deep">Cara Kerja</a>
                <a href="{{ route('landing') }}#keuntungan" class="hover:text-brand-deep">Keuntungan</a>
                <a href="{{ route('landing') }}#ekosistem" class="hover:text-brand-deep">Ekosistem</a>
                <a href="{{ route('landing') }}#growth-loop" class="hover:text-brand-deep">Growth Loop</a>
            </nav>
            <div class="ml-auto flex items-center gap-2">
                @auth
                    <a href="{{ route('overview') }}" class="btn-primary btn-sm">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm">Mulai Perjalanan</a>
                @endauth
            </div>
        </div>
    </header>
    {{ $slot }}
    <footer class="border-t border-line bg-canvas">
        <div class="mx-auto grid max-w-[1200px] gap-8 px-4 py-12 md:grid-cols-[2fr_1fr] md:px-8">
            <div>
                <x-logo />
                <p class="mt-3 max-w-md text-[14px] leading-6 text-slate">Membantu UMKM memahami bisnisnya, menentukan langkah yang tepat, dan melihat pertumbuhannya dari waktu ke waktu.</p>
            </div>
            <div class="text-[14px] text-slate">
                <div class="mb-2 font-semibold text-ink">Platform</div>
                <a class="block py-1 hover:text-ink" href="{{ route('how-it-works') }}">Cara Kerja TUMBUH</a>
                <a class="block py-1 hover:text-ink" href="{{ route('register') }}">Growth Diagnostic</a>
                <a class="block py-1 hover:text-ink" href="{{ route('demo.index') }}">Demo</a>
            </div>
        </div>
    </footer>
</x-layouts.base>
