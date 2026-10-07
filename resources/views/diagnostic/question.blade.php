<x-layouts.focus :title="'Growth Diagnostic · '.$index.'/'.$total" width="max-w-[680px]">
    <x-slot:header><span class="hidden sm:inline">Growth Diagnostic — find the biggest growth bottleneck</span></x-slot:header>
    <div class="mb-6">
        <div class="mb-2 flex items-center justify-between text-[12px] font-semibold">
            <span class="eyebrow-brand">{{ $question->section }}</span>
            <span class="text-slate">Question {{ $index }} of {{ $total }}</span>
        </div>
        <x-progress :value="($index - 1) / $total * 100" tone="brand" height="h-2" />
        <div class="mt-2 flex flex-wrap gap-1.5">
            @foreach ($sections as $s)<span class="pill {{ $s === $question->section ? 'bg-ink text-white' : 'bg-white text-slate border border-line' }}">{{ $s }}</span>@endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('diagnostic.answer', $index) }}" class="card p-6 md:p-8"
          x-data="diagnostic({{ $question->id }}, @js($answer), '{{ $question->type }}')">
        @csrf
        <h1 class="text-[22px] font-bold leading-8 md:text-[24px]">{{ $question->prompt }}</h1>
        @if ($question->help)<p class="mt-1 text-[13px] text-slate">{{ $question->help }}</p>@endif

        <div class="mt-6 space-y-2">
            @if ($question->type === 'single')
                @foreach ($question->options as $o)
                    <label class="module flex cursor-pointer items-center gap-3 p-4 text-[14px] transition hover:border-line-strong" :class="value === '{{ $o['value'] }}' && 'border-ink bg-brand-soft ring-[3px] ring-brand-tint'">
                        <input type="radio" name="answer" value="{{ $o['value'] }}" x-model="value" @change="save()" class="h-4 w-4 accent-[#ec1c24]">
                        <span>{{ $o['label'] }}</span>
                    </label>
                @endforeach
            @elseif ($question->type === 'multi')
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($question->options as $o)
                        <label class="module flex cursor-pointer items-center gap-3 p-4 text-[14px] transition hover:border-line-strong" :class="(value || []).includes('{{ $o['value'] }}') && 'border-ink bg-brand-soft'">
                            <input type="checkbox" name="answer[]" value="{{ $o['value'] }}" x-model="value" @change="save()" class="check">
                            <span>{{ $o['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <input type="number" name="answer" x-model="value" @change="save()" min="0" step="any" class="input h-14 text-[20px]">
            @endif
        </div>
        @error('answer')<p class="field-error mt-3">{{ $message }}</p>@enderror

        <div class="mt-3 h-5 text-[12px] text-slate" aria-live="polite">
            <span x-show="state === 'saving'" x-cloak>Menyimpan…</span>
            <span x-show="state === 'saved'" x-cloak class="text-ok"><x-icon name="cloud_done" size="14" /> Tersimpan otomatis <span x-text="at"></span></span>
            <span x-show="state === 'error'" x-cloak class="text-brand-deep" x-text="error"></span>
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-line pt-5">
            <button name="nav" value="back" class="btn-ghost" @disabled($index === 1)><x-icon name="arrow_back" size="18" />Back</button>
            <span class="text-[12px] text-slate">{{ $progress['answered'] }}/{{ $total }} terjawab</span>
            <button name="nav" value="next" class="btn-primary">{{ $index < $total ? 'Next' : 'Review jawaban' }} <x-icon name="arrow_forward" size="18" /></button>
        </div>
    </form>
    <p class="mt-4 text-center text-[12px] text-slate">Pertanyaan diambil dari question bank yang dapat dikonfigurasi. Jawaban disimpan otomatis setiap kali dipilih.</p>

    <script>
        function diagnostic(questionId, initial, type) {
            return {
                value: initial ?? (type === 'multi' ? [] : ''),
                state: 'idle', at: '', error: '',
                async save() {
                    if (type === 'multi' && this.value.length === 0) return;
                    this.state = 'saving';
                    try {
                        const res = await fetch(@js(route('diagnostic.autosave')), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ question_id: questionId, answer: this.value }),
                        });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Gagal menyimpan');
                        this.state = 'saved'; this.at = data.at;
                    } catch (e) { this.state = 'error'; this.error = e.message; }
                },
            };
        }
    </script>
</x-layouts.focus>
