@props(['n'])
@php($t = config("tumbuh.notification_types.{$n->type}"))
<div {{ $attributes->merge(['class' => 'flex gap-3 px-5 py-4 '.($n->read_at ? '' : 'bg-brand-soft/60')]) }}>
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ in_array($n->type, ['warning']) ? 'bg-brand-tint text-brand-deep' : ($n->type === 'reward' ? 'bg-gold-tint text-gold' : 'bg-muted text-ink') }}"><x-icon :name="$t['icon'] ?? 'notifications'" size="18" /></span>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <span class="eyebrow">{{ $t['label'] ?? $n->type }}</span>
            <span class="text-[11px] text-slate-2">{{ $n->created_at->diffForHumans() }}</span>
            @unless ($n->read_at)<span class="h-2 w-2 rounded-full bg-brand" title="Belum dibaca"></span>@endunless
        </div>
        <div class="mt-0.5 text-[14px] font-semibold">{{ $n->title }}</div>
        @if ($n->body)<p class="text-[13px] text-slate">{{ $n->body }}</p>@endif
    </div>
    @if ($n->action_url)
        <form method="POST" action="{{ route('notifications.read', $n) }}" class="self-center">@csrf
            <button class="btn-secondary btn-sm">{{ $n->action_label ?? 'Buka' }}</button>
        </form>
    @endif
</div>
