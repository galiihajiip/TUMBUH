@props(['status'])
@php
    $map = [
        'active' => ['Active', 'bg-ok-tint text-ok', 'check_circle'],
        'activation_required' => ['Activation Required', 'bg-brand-tint text-brand-deep', 'warning'],
        'eligibility_check' => ['Eligibility Check', 'bg-warn-tint text-warn', 'search'],
        'not_activated' => ['Not Activated', 'bg-muted text-slate', 'radio_button_unchecked'],
        'simulated' => ['Simulated', 'bg-[#f5f3ff] text-[#6d28d9]', 'science'],
        'completed' => ['Selesai', 'bg-ok-tint text-ok', 'check_circle'],
        'partial' => ['Sebagian', 'bg-warn-tint text-warn', 'timelapse'],
        'upcoming' => ['Belum mulai', 'bg-muted text-slate', 'schedule'],
        'running' => ['Running', 'bg-info-tint text-info', 'play_circle'],
        'draft' => ['Draft', 'bg-muted text-slate', 'edit'],
        'connected' => ['Connected (Demo)', 'bg-info-tint text-info', 'link'],
        'pending' => ['Pending', 'bg-warn-tint text-warn', 'hourglass_top'],
        'unavailable' => ['Unavailable', 'bg-muted text-slate', 'block'],
        'self_reported' => ['Self-reported', 'bg-muted text-slate', 'edit_note'],
        'HIGH' => ['Prioritas Tinggi', 'bg-brand-tint text-brand-deep', 'priority_high'],
        'MEDIUM' => ['Prioritas Sedang', 'bg-warn-tint text-warn', 'remove'],
        'LOW' => ['Prioritas Rendah', 'bg-ok-tint text-ok', 'arrow_downward'],
    ];
    [$label, $cls, $icon] = $map[$status] ?? [ucfirst(str_replace('_', ' ', $status)), 'bg-muted text-slate', 'circle'];
    if ($status === 'active' && $attributes->get('label')) { $label = $attributes->get('label'); }
@endphp
<span {{ $attributes->except('label')->merge(['class' => "pill {$cls}"]) }}><x-icon :name="$icon" size="13" />{{ $label }}</span>
