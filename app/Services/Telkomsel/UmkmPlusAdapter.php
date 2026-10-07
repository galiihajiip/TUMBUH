<?php

namespace App\Services\Telkomsel;

use App\Models\Business;

/** UMKM Plus — connectivity layer; package activation via MyTelkomsel for eligible numbers. */
class UmkmPlusAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'umkm_plus';
    }

    public function steps(): array
    {
        return ['View Package', 'Check Eligibility', 'Activate via MyTelkomsel', 'Monitor & Renew'];
    }

    public function pendingStatus(): string
    {
        return 'eligibility_check';
    }

    public function eligibility(Business $business): array
    {
        $phone = (string) $business->user?->phone;
        $ok = $phone === '' || str_starts_with($phone, '08') || str_starts_with($phone, '+628');

        return ['eligible' => $ok, 'reasons' => $ok ? ['Nomor terdaftar dapat diperiksa kelayakannya (simulasi).'] : ['Nomor Telkomsel diperlukan.']];
    }
}
