<x-layouts.focus title="Review Diagnostic" width="max-w-[760px]">
    <div class="mb-6">
        <div class="eyebrow-brand">Review before submit</div>
        <h1 class="mt-1 text-[26px] font-bold">Periksa jawaban Anda</h1>
        <p class="text-[13px] text-slate">{{ $progress['answered'] }} dari {{ $progress['total'] }} pertanyaan terjawab. Klik pertanyaan untuk mengubah jawaban.</p>
    </div>
    <div class="card divide-y divide-line">
        @foreach ($questions as $i => $q)
            @php($a = $answers->get($q->id))
            @php($labels = collect($q->options ?? [])->keyBy('value'))
            <a href="{{ route('diagnostic.question', $i + 1) }}" class="flex items-start gap-4 px-5 py-3.5 hover:bg-canvas">
                <span class="mt-0.5 w-6 shrink-0 text-[12px] font-semibold text-slate-2">{{ $i + 1 }}</span>
                <span class="flex-1">
                    <span class="eyebrow">{{ $q->section }}</span>
                    <span class="block text-[14px] font-medium">{{ $q->prompt }}</span>
                    @if ($a)
                        <span class="block text-[13px] text-ink-2">→ {{ collect((array) $a->value)->map(fn ($v) => $labels[$v]['label'] ?? $v)->implode(', ') }}</span>
                    @else
                        <span class="block text-[13px] font-semibold text-brand-deep">Belum dijawab</span>
                    @endif
                </span>
                <x-icon name="edit" size="18" class="text-slate-2" />
            </a>
        @endforeach
    </div>
    <form method="POST" action="{{ route('diagnostic.submit') }}" class="mt-6 flex items-center justify-between">@csrf
        <a href="{{ route('diagnostic.question', $questions->count()) }}" class="btn-ghost"><x-icon name="arrow_back" size="18" />Kembali</a>
        <button class="btn-primary" @disabled($progress['answered'] < $progress['total'])>Lanjut ke konfirmasi data <x-icon name="arrow_forward" size="18" /></button>
    </form>
</x-layouts.focus>
