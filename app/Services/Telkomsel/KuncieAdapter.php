<?php

namespace App\Services\Telkomsel;

/** Kuncie — learning enabler. TUMBUH recommends; learning happens in Kuncie. */
class KuncieAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'kuncie';
    }

    public function steps(): array
    {
        return ['Connect Kuncie Account', 'Enroll Learning Plan', 'Complete Lessons'];
    }

    /** Simulated catalogue, keyed by bottleneck. */
    public function learningPlan(string $focus): array
    {
        $plans = [
            'acquisition' => ['title' => 'Customer Acquisition 101', 'lessons' => 4, 'minutes' => 45],
            'conversion' => ['title' => 'Offer & Conversion', 'lessons' => 5, 'minutes' => 50],
            'retention' => ['title' => 'Customer Retention Strategy', 'lessons' => 4, 'minutes' => 40],
            'digital_presence' => ['title' => 'Digital Marketing Fundamentals', 'lessons' => 4, 'minutes' => 45],
            'financial_health' => ['title' => 'Manajemen Keuangan UMKM', 'lessons' => 6, 'minutes' => 60],
            'inventory_health' => ['title' => 'Inventory Planning for Growth', 'lessons' => 4, 'minutes' => 40],
            'productivity' => ['title' => 'Digitalisasi Operasional', 'lessons' => 4, 'minutes' => 35],
        ];

        return $plans[$focus] ?? ['title' => 'Scaling Your Business', 'lessons' => 6, 'minutes' => 70];
    }
}
