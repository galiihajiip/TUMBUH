@if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 rounded-[12px] border border-[#a7f3d0] bg-ok-tint px-4 py-3 text-[13px] text-[#065f46]" role="status">
        <x-icon name="check_circle" class="text-ok" fill /><div class="flex-1">{{ session('success') }}</div>
        <button type="button" @click="show = false" class="text-[#065f46]/60 hover:text-[#065f46]" aria-label="Tutup"><x-icon name="close" size="18" /></button>
    </div>
@endif
@if ($errors->any())
    <div class="mb-5 flex items-start gap-3 rounded-[12px] border border-brand/30 bg-brand-tint px-4 py-3 text-[13px] text-brand-press" role="alert">
        <x-icon name="error" class="text-brand" fill />
        <div>@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
@endif
