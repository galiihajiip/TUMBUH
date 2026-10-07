<x-layouts.app title="AI Growth Assistant">
    <x-page-header eyebrow="AI Business Growth Advisor" title="AI Growth Assistant" subtitle="Bukan chatbot generik — setiap rekomendasi menjawab 4 pertanyaan berdasarkan data bisnis Anda." />
    @include('partials.nav-growth')
    @if (! $recommendation)
        <x-empty-state icon="auto_awesome" title="Asisten menunggu diagnostic Anda" text="Selesaikan Growth Diagnostic agar AI bisa menemukan bottleneck."><a href="{{ route('diagnostic.start') }}" class="btn-primary">Mulai diagnostic</a></x-empty-state>
    @else
        <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
            <div class="space-y-6">
                <x-ai-insight>
                    <p class="text-[13px] text-slate">{{ $recommendation->bottleneck === 'expansion' ? 'Saya menemukan peluang berikutnya:' : 'Saya menemukan satu bottleneck utama:' }}</p>
                    <h2 class="mt-1 font-display text-[24px] font-extrabold uppercase tracking-[0.01em] text-brand-deep">{{ $recommendation->bottleneck === 'expansion' ? 'Expansion Readiness' : config("tumbuh.dimensions.{$recommendation->bottleneck}.label") }}</h2>
                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        @foreach ([['1', "What's wrong?", $recommendation->whats_wrong, 'error'], ['2', 'Why does it matter?', $recommendation->why_it_matters, 'lightbulb'], ['3', 'What should I do?', $recommendation->what_to_do, 'directions_run'], ['4', 'How do I know it worked?', $recommendation->success_metric, 'flag']] as [$n, $q, $a, $icon])
                            <div class="module p-4 {{ $n === '3' ? 'border-ink' : '' }}">
                                <div class="flex items-center gap-2 text-[12px] font-semibold text-slate"><span class="flex h-5 w-5 items-center justify-center rounded-full bg-ink text-[10px] text-white">{{ $n }}</span>{{ $q }}</div>
                                <p class="mt-2 text-[14px] leading-6">{{ $a }}</p>
                            </div>
                        @endforeach
                    </div>
                    <x-why :evidence="$recommendation->evidence" />
                    <x-slot:footer>
                        <div class="flex flex-wrap gap-2"><a href="{{ route('plan.show') }}" class="btn-primary btn-sm">See action plan <x-icon name="arrow_forward" size="16" /></a><a href="{{ route('solutions.index') }}" class="btn-secondary btn-sm">Solusi yang cocok</a></div>
                    </x-slot:footer>
                </x-ai-insight>

                <div class="card p-5 md:p-6" id="ask">
                    <x-section-header title="Tanya asisten" subtitle="Pertanyaan bisnis umum, dijawab dari data Anda (bukan LLM eksternal)" />
                    <form method="POST" action="{{ route('assistant.ask') }}" class="flex flex-wrap gap-2">@csrf
                        @foreach ($intents as $k => $q)<button name="intent" value="{{ $k }}" class="rounded-full border border-line px-3.5 py-2 text-[13px] hover:border-ink {{ ($answer['q'] ?? '') === $q ? 'border-ink bg-brand-soft' : '' }}">{{ $q }}</button>@endforeach
                    </form>
                    @if ($answer)
                        <div class="mt-5 space-y-3">
                            <div class="ml-auto max-w-[80%] rounded-[16px] rounded-tr-sm bg-ink px-4 py-2.5 text-[14px] text-white">{{ $answer['q'] }}</div>
                            <div class="max-w-[90%] rounded-[16px] rounded-tl-sm border border-line bg-canvas px-4 py-3 text-[14px]">
                                <div class="mb-1 flex items-center gap-1.5 text-[12px] font-semibold text-brand-deep"><x-icon name="auto_awesome" size="14" fill />TUMBUH AI</div>
                                @foreach ($answer['a'] as $line)<p class="mb-1">{{ $line }}</p>@endforeach
                                @if ($answer['cta'])<a href="{{ $answer['cta']['url'] }}" class="btn-secondary btn-sm mt-2">{{ $answer['cta']['label'] }}</a>@endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="space-y-6">
                <div class="card p-5">
                    <x-section-header title="Recommended solutions" subtitle="Berdasarkan bottleneck, bukan katalog" />
                    <div class="space-y-2">
                        @foreach ($recommendation->solution_keys as $key)
                            @php($s = \App\Models\TelkomselSolution::where('key', $key)->first())
                            @php($n = $native[$key] ?? null)
                            <a href="{{ $s ? route('solutions.show', $s) : route($n['route']) }}" class="module flex items-center gap-3 p-3 hover:border-line-strong">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon :name="$s->icon ?? $n['icon']" size="18" /></span>
                                <span class="flex-1"><span class="block text-[14px] font-semibold">{{ $s->name ?? $n['name'] }}</span><span class="text-[12px] text-slate">{{ $s->role ?? $n['role'] }}</span></span>
                                @if ($alreadyUses($key))<span class="pill bg-muted text-slate">Optimasi</span>@endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @if ($learning)
                    <div class="card p-5">
                        <div class="eyebrow">Recommended for you · Kuncie</div>
                        <div class="mt-1 text-[16px] font-semibold">{{ $learning['title'] }}</div>
                        <div class="text-[12px] text-slate">{{ $learning['lessons'] }} lessons · {{ $learning['minutes'] }} min</div>
                        <a href="{{ route('solutions.show', 'kuncie') }}" class="btn-secondary btn-sm mt-4">Start learning</a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-layouts.app>
