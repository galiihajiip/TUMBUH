<?php

namespace App\Services\Telkomsel;

/**
 * SiteSense — expansion & location intelligence. Returns SIMULATED
 * candidate-site attributes; scoring is done by ExpansionReadinessService.
 */
class SiteSenseAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'sitesense';
    }

    public function steps(): array
    {
        return ['Input Expansion Criteria', 'Location Analysis', 'Recommended Location'];
    }

    public function pendingStatus(): string
    {
        return 'eligibility_check';
    }

    /** Candidate sites: footfall, accessibility, competition (0–100), rent (Rp/month). */
    public function candidates(string $city): array
    {
        $sets = [
            'Surabaya' => [
                ['name' => 'Surabaya Barat — Citraland', 'area' => 'Surabaya Barat', 'footfall' => 88, 'accessibility' => 84, 'competition' => 55, 'rent' => 9_500_000, 'segments' => ['family', 'office']],
                ['name' => 'Surabaya Timur — Manyar Kertoarjo', 'area' => 'Surabaya Timur', 'footfall' => 82, 'accessibility' => 80, 'competition' => 68, 'rent' => 7_500_000, 'segments' => ['student', 'young_pro']],
                ['name' => 'Surabaya Pusat — Tunjungan', 'area' => 'Surabaya Pusat', 'footfall' => 92, 'accessibility' => 90, 'competition' => 85, 'rent' => 18_000_000, 'segments' => ['office', 'tourist']],
                ['name' => 'Sidoarjo — Jl. Pahlawan', 'area' => 'Sidoarjo Kota', 'footfall' => 70, 'accessibility' => 74, 'competition' => 40, 'rent' => 5_000_000, 'segments' => ['family']],
            ],
        ];

        return $sets[$city] ?? [
            ['name' => $city.' Pusat — Koridor Utama', 'area' => $city.' Pusat', 'footfall' => 85, 'accessibility' => 86, 'competition' => 75, 'rent' => 12_000_000, 'segments' => ['office', 'young_pro']],
            ['name' => $city.' Utara — Area Perumahan', 'area' => $city.' Utara', 'footfall' => 72, 'accessibility' => 70, 'competition' => 45, 'rent' => 6_000_000, 'segments' => ['family']],
            ['name' => $city.' Selatan — Dekat Kampus', 'area' => $city.' Selatan', 'footfall' => 79, 'accessibility' => 76, 'competition' => 60, 'rent' => 7_000_000, 'segments' => ['student']],
        ];
    }
}
