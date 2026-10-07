<x-layouts.focus title="Business Goal" width="max-w-[720px]">
    <form method="POST" action="{{ route('goals.store') }}" class="card p-6 md:p-8" x-data="{ goals: @js(old('goals', $selected)) }">
        @csrf
        <div class="eyebrow-brand">Langkah 3 dari 3 · Business Goal</div>
        <h1 class="mt-1 text-[24px] font-bold">What do you want to achieve?</h1>
        <p class="mt-1 text-[13px] text-slate">Pilih maksimal 3 goal. Goal menjadi baseline → target untuk pengukuran dan leaderboard.</p>
        <div class="mt-6 grid gap-2 sm:grid-cols-3">
            @foreach (config('tumbuh.goals') as $k => $g)
                <label class="module flex cursor-pointer flex-col gap-2 p-4 transition" :class="goals.includes('{{ $k }}') ? 'border-ink bg-brand-soft ring-[3px] ring-brand-tint' : (goals.length >= 3 ? 'opacity-50' : 'hover:border-line-strong')">
                    <span class="flex items-center justify-between"><x-icon :name="$g['icon']" class="text-brand" /><input type="checkbox" name="goals[]" value="{{ $k }}" x-model="goals" class="check" :disabled="!goals.includes('{{ $k }}') && goals.length >= 3"></span>
                    <span class="text-[14px] font-semibold">{{ $g['label'] }}</span>
                    <span class="text-[12px] text-slate">{{ $g['id'] }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-2 text-right text-[12px] text-slate"><span x-text="goals.length"></span>/3 dipilih</div>

        <div x-show="goals.includes('increase_revenue')" x-cloak class="mt-6 rounded-[16px] border border-line bg-canvas p-5">
            <div class="font-semibold">Target omzet</div>
            <div class="mt-3 grid gap-4 sm:grid-cols-3">
                <x-field name="current_revenue" label="Omzet bulanan saat ini" type="number" prefix="Rp" :value="$revenueGoal?->baseline_value ? (int) $revenueGoal->baseline_value : $suggestedRevenue" />
                <x-field name="target_revenue" label="Target omzet bulanan" type="number" prefix="Rp" :value="$revenueGoal?->target_value ? (int) $revenueGoal->target_value : (int) round($suggestedRevenue * 1.25, -5)" />
                <x-select name="target_period" label="Periode target" :options="[30 => '30 hari', 60 => '60 hari', 90 => '90 hari']" :value="$revenueGoal?->target_period_days ?? 60" />
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between border-t border-line pt-5">
            <a href="{{ route('onboarding.show', 6) }}" class="btn-ghost"><x-icon name="arrow_back" size="18" />Kembali</a>
            <button class="btn-primary" :disabled="goals.length === 0">Simpan goal & mulai diagnostic <x-icon name="arrow_forward" size="18" /></button>
        </div>
    </form>
</x-layouts.focus>
