@props(['title' => 'AI Growth Assistant', 'tone' => 'brand'])
<div {{ $attributes->merge(['class' => 'card overflow-hidden '.($tone === 'brand' ? 'border-l-4 border-l-brand' : '')]) }}>
    <div class="p-5 md:p-6">
        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon name="auto_awesome" size="18" fill /></span>
            <span class="eyebrow-brand">{{ $title }}</span>
            <span class="pill bg-muted text-slate" title="Mesin rekomendasi lokal berbasis aturan — tidak memanggil LLM eksternal">Rule-based AI</span>
        </div>
        {{ $slot }}
    </div>
    @isset($footer)<div class="border-t border-line bg-canvas/60 px-5 py-3 md:px-6">{{ $footer }}</div>@endisset
</div>
