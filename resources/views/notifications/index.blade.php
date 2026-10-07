<x-layouts.app title="Notifikasi">
    <x-page-header eyebrow="Notification Center" title="Notifikasi terarah" subtitle="Action-oriented, bukan spam — setiap notifikasi membawa langkah berikutnya.">
        <x-slot:actions><form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn-secondary btn-sm">Tandai semua dibaca</button></form></x-slot:actions>
    </x-page-header>
    <div class="mb-4 flex flex-wrap gap-2 text-[13px]">
        <a href="{{ route('notifications.index') }}" class="rounded-full border px-3 py-1 {{ ! $type ? 'border-ink bg-ink text-white' : 'border-line' }}">Semua</a>
        @foreach (config('tumbuh.notification_types') as $k => $t)
            <a href="{{ route('notifications.index', ['type' => $k]) }}" class="rounded-full border px-3 py-1 {{ $type === $k ? 'border-ink bg-ink text-white' : 'border-line' }}">{{ $t['label'] }}@if ($counts[$k] ?? 0) <span class="ml-1 rounded-full bg-brand px-1.5 text-[11px] text-white">{{ $counts[$k] }}</span>@endif</a>
        @endforeach
    </div>
    <div class="card divide-y divide-line overflow-hidden">
        @forelse ($notifications as $n)<x-notification-item :n="$n" />@empty<p class="p-8 text-center text-[13px] text-slate">Tidak ada notifikasi.</p>@endforelse
    </div>
</x-layouts.app>
