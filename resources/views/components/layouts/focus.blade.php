@props(['title' => null, 'width' => 'max-w-[560px]'])
{{-- Focused flow layout (auth, onboarding, diagnostic) --}}
<x-layouts.base :title="$title ?? null" body-class="bg-canvas">
    <header class="border-b border-line bg-white">
        <div class="mx-auto flex h-16 max-w-[1100px] items-center justify-between px-4 md:px-8">
            <a href="{{ auth()->check() ? route('overview') : route('landing') }}"><x-logo size="sm" /></a>
            <div class="flex items-center gap-3 text-[12px] text-slate">
                {{ $header ?? '' }}
                @auth
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-ghost btn-sm">Keluar</button></form>
                @endauth
            </div>
        </div>
    </header>
    <main class="mx-auto w-full {{ $width }} px-4 py-8 md:py-12">
        <x-flash />
        {{ $slot }}
    </main>
</x-layouts.base>
