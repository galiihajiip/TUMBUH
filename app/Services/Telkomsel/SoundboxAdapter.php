<?php

namespace App\Services\Telkomsel;

use App\Models\Business;

/** Soundbox — QRIS payment notification; requires bank/fintech/QRIS acquirer integration. */
class SoundboxAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'soundbox';
    }

    public function steps(): array
    {
        return ['Eligibility Check', 'Partner / Acquirer Integration', 'Device Activation', 'QRIS Payment Monitoring'];
    }

    public function pendingStatus(): string
    {
        return 'eligibility_check';
    }

    public function eligibility(Business $business): array
    {
        $reasons = [];
        if (! $business->has_physical_store) {
            $reasons[] = 'Soundbox membutuhkan toko fisik.';
        }
        if (! $business->usesTool('qris')) {
            $reasons[] = 'Aktifkan QRIS terlebih dahulu (melalui bank / acquirer).';
        }

        return ['eligible' => $reasons === [], 'reasons' => $reasons ?: ['Toko fisik ✓', 'QRIS ✓', 'Volume transaksi sesuai']];
    }
}
