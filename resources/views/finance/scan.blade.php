<x-layouts.app title="Scan Struk">
    <x-page-header eyebrow="Growth Finance · Silver" title="Scan receipt" subtitle="Foto struk → OCR → konfirmasi → kategori → ledger." />
    <form method="POST" action="{{ route('finance.scan.upload') }}" enctype="multipart/form-data" class="card max-w-2xl p-6 md:p-8">@csrf
        <label class="flex cursor-pointer flex-col items-center gap-2 rounded-[16px] border-2 border-dashed border-line-strong bg-canvas p-10 text-center hover:border-ink">
            <x-icon name="photo_camera" size="40" class="text-brand" />
            <b>Unggah foto struk</b><span class="text-[12px] text-slate">JPG/PNG/WEBP, maks 5MB. Disimpan privat.</span>
            <input type="file" name="receipt" accept="image/*" class="mt-2 text-[13px]">
        </label>
        <div class="my-5 flex items-center gap-3 text-[11px] text-slate-2"><span class="h-px flex-1 bg-line"></span>ATAU GUNAKAN CONTOH STRUK DEMO<span class="h-px flex-1 bg-line"></span></div>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach (['Toko bahan kue', 'Pemasok kopi & susu', 'Token listrik', 'Grosir kemasan'] as $i => $l)
                <button name="sample" value="{{ $i }}" class="module flex items-center gap-2 p-3 text-left text-[13px] hover:border-ink"><x-icon name="receipt" size="18" />{{ $l }}</button>
            @endforeach
        </div>
        <button class="btn-primary mt-6 w-full">Scan struk</button>
        <x-sim-note class="mt-4" text="OCR disimulasikan secara lokal dan deterministik (file yang sama → hasil yang sama). Tidak ada layanan AI eksternal yang dipanggil." />
    </form>
</x-layouts.app>
