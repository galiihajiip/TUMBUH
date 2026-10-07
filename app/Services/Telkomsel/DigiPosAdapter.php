<?php

namespace App\Services\Telkomsel;

/**
 * DigiPOS — digital sales / transaction activity layer for SUPPORTED use
 * cases (Telkomsel products & digital transactions). Not a generic POS
 * for every UMKM SKU; Growth Inventory remains the product ledger.
 */
class DigiPosAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'digipos';
    }

    public function steps(): array
    {
        return ['Register', 'Outlet Verification', 'Start Selling', 'Sales Report Linked'];
    }
}
