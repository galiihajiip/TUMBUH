<?php

namespace App\Services\Telkomsel;

use InvalidArgumentException;

/** Resolves the adapter for a solution key. Swap mocks for real clients here. */
class AdapterRegistry
{
    private const MAP = [
        'myads' => MyAdsAdapter::class,
        'kuncie' => KuncieAdapter::class,
        'umkm_plus' => UmkmPlusAdapter::class,
        'digipos' => DigiPosAdapter::class,
        'soundbox' => SoundboxAdapter::class,
        'digiads' => DigiAdsAdapter::class,
        'rcs' => RcsAdapter::class,
        'msight' => MSightAdapter::class,
        'sitesense' => SiteSenseAdapter::class,
        'dce' => DceAdapter::class,
    ];

    public function for(string $key): TelkomselAdapter
    {
        if (! isset(self::MAP[$key])) {
            throw new InvalidArgumentException("No adapter for solution [{$key}].");
        }

        return app(self::MAP[$key]);
    }

    public function has(string $key): bool
    {
        return isset(self::MAP[$key]);
    }
}
