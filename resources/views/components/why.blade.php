@props(['evidence' => [], 'label' => 'Why am I seeing this?'])
<div x-data="{ open: false }" {{ $attributes->merge(['class' => 'mt-3']) }}>
    <button type="button" @click="open = !open" class="inline-flex items-center gap-1 text-[12px] font-semibold text-slate hover:text-ink" :aria-expanded="open">
        <x-icon name="help" size="16" /> {{ $label }} <x-icon name="expand_more" size="16" x-bind:class="open && 'rotate-180'" class="transition" />
    </button>
    <div x-show="open" x-collapse x-cloak class="mt-2">
        <div class="grid gap-2 sm:grid-cols-3">
            @foreach ($evidence as $e)
                <div class="module px-3 py-2">
                    <div class="text-[11px] text-slate">{{ $e['label'] }}</div>
                    <div class="font-display text-[16px] font-bold tabular {{ ($e['tone'] ?? '') === 'bad' ? 'text-brand-deep' : (($e['tone'] ?? '') === 'good' ? 'text-ok' : 'text-ink') }}">{{ $e['value'] }}</div>
                </div>
            @endforeach
        </div>
        <p class="mt-2 text-[11px] text-slate">Dihitung dari data bisnis Anda yang tersimpan (Growth Ledger, Diagnostic, Growth Finance). Mesin rekomendasi bersifat deterministik.</p>
    </div>
</div>
