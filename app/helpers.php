<?php

if (! function_exists('rupiah')) {
    /** Full rupiah format, e.g. Rp20.000.000 */
    function rupiah(int|float|null $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('rupiah_short')) {
    /** Compact rupiah format, e.g. Rp20M / Rp450rb / Rp1,2M */
    function rupiah_short(int|float|null $amount): string
    {
        $amount = (float) $amount;
        $sign = $amount < 0 ? '-' : '';
        $abs = abs($amount);

        if ($abs >= 1_000_000_000) {
            return $sign.'Rp'.rtrim(rtrim(number_format($abs / 1_000_000_000, 1, ',', '.'), '0'), ',').'M';
        }
        if ($abs >= 1_000_000) {
            return $sign.'Rp'.rtrim(rtrim(number_format($abs / 1_000_000, 1, ',', '.'), '0'), ',').'jt';
        }
        if ($abs >= 1_000) {
            return $sign.'Rp'.number_format($abs / 1_000, 0, ',', '.').'rb';
        }

        return $sign.'Rp'.number_format($abs, 0, ',', '.');
    }
}

if (! function_exists('num')) {
    function num(int|float|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }
}

if (! function_exists('pct')) {
    function pct(int|float|null $value, int $decimals = 1): string
    {
        return number_format((float) $value, $decimals, ',', '.').'%';
    }
}

if (! function_exists('signed')) {
    /** Signed number with arrow-friendly prefix, e.g. +4 / -2,5 */
    function signed(int|float $value, int $decimals = 0, string $suffix = ''): string
    {
        $prefix = $value > 0 ? '+' : ($value < 0 ? '−' : '±');

        return $prefix.number_format(abs($value), $decimals, ',', '.').$suffix;
    }
}

if (! function_exists('current_business')) {
    function current_business(): ?App\Models\Business
    {
        return auth()->user()?->business;
    }
}
