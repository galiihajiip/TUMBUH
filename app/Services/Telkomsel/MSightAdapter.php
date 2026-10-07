<?php

namespace App\Services\Telkomsel;

/**
 * MSIGHT — advanced business intelligence layer (consult → design → trial → implement).
 * Provides SIMULATED market data only; no telco data is used.
 */
class MSightAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'msight';
    }

    public function steps(): array
    {
        return ['Business Question', 'Data Requirement', 'Insight Design', 'Analysis & Insight'];
    }

    public function pendingStatus(): string
    {
        return 'eligibility_check';
    }

    /** Simulated area profiles per city (illustrative, not real data). */
    public function areaProfiles(string $city): array
    {
        $sets = [
            'Surabaya' => [
                ['area' => 'Surabaya Barat', 'segment' => 'Keluarga muda & pekerja kantoran', 'density' => 86, 'spend_index' => 78, 'growth' => 9.4, 'peak' => 'Sabtu–Minggu 10.00–14.00'],
                ['area' => 'Surabaya Timur', 'segment' => 'Mahasiswa & profesional muda', 'density' => 81, 'spend_index' => 70, 'growth' => 7.1, 'peak' => 'Hari kerja 16.00–20.00'],
                ['area' => 'Surabaya Pusat', 'segment' => 'Pekerja kantoran & wisatawan', 'density' => 74, 'spend_index' => 82, 'growth' => 4.2, 'peak' => 'Hari kerja 11.00–13.00'],
                ['area' => 'Sidoarjo Kota', 'segment' => 'Keluarga & komuter', 'density' => 69, 'spend_index' => 64, 'growth' => 8.3, 'peak' => 'Sabtu–Minggu 16.00–20.00'],
            ],
            'Jakarta Selatan' => [
                ['area' => 'Kebayoran Baru', 'segment' => 'Profesional muda', 'density' => 88, 'spend_index' => 90, 'growth' => 5.2, 'peak' => 'Hari kerja 12.00–14.00'],
                ['area' => 'Tebet', 'segment' => 'Mahasiswa & kreator', 'density' => 83, 'spend_index' => 74, 'growth' => 7.9, 'peak' => 'Malam & akhir pekan'],
                ['area' => 'Cilandak', 'segment' => 'Keluarga', 'density' => 72, 'spend_index' => 85, 'growth' => 4.8, 'peak' => 'Akhir pekan'],
            ],
        ];

        return $sets[$city] ?? [
            ['area' => $city.' Pusat', 'segment' => 'Pekerja kantoran', 'density' => 78, 'spend_index' => 72, 'growth' => 5.0, 'peak' => 'Hari kerja 11.00–13.00'],
            ['area' => $city.' Utara', 'segment' => 'Keluarga muda', 'density' => 70, 'spend_index' => 66, 'growth' => 7.5, 'peak' => 'Akhir pekan'],
            ['area' => $city.' Selatan', 'segment' => 'Mahasiswa', 'density' => 75, 'spend_index' => 60, 'growth' => 6.1, 'peak' => 'Sore hari'],
        ];
    }
}
