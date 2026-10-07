<?php

namespace App\Services;

use App\Models\Business;

/**
 * Growth Passport — maturity levels earned by performance criteria,
 * not by training count. Levels are sequential: a level is reached only
 * when all of its criteria and all previous levels are met.
 */
class PassportService
{
    public function __construct(
        private KpiService $kpi,
        private ExpansionReadinessService $expansion,
    ) {}

    /** @return array<string, array{label: string, criteria: array<int, array{label: string, met: bool, detail: string}>}> */
    public function evaluate(Business $business): array
    {
        $score = $business->latestScore;
        $ledger = $this->kpi->ledger($business);
        $baseline = $this->kpi->baseline($business);
        $latest = $this->kpi->latest($business);
        $dim = fn (string $k) => $score?->scoreFor($k) ?? 0;

        $digitalChannels = array_intersect($business->sales_channels ?? [], ['whatsapp', 'instagram', 'tiktok', 'website', 'marketplace']);
        $revGrowth = $baseline && $latest && $baseline->revenue > 0 ? ($latest->revenue - $baseline->revenue) / $baseline->revenue * 100 : 0;
        $customerGrowth = $baseline && $latest && $baseline->new_customers > 0 ? ($latest->new_customers - $baseline->new_customers) / $baseline->new_customers * 100 : 0;
        $kpiImproved = $baseline && $latest && $latest->id !== $baseline->id
            && ($latest->revenue > $baseline->revenue || $latest->conversion_rate > $baseline->conversion_rate);
        $nonDeclining = $this->nonDecliningMonths($ledger);
        $marketingDone = $business->campaigns()->count() + $business->missions()->where('status', 'completed')->whereIn('solution_key', ['myads', 'rcs', 'digiads'])->count();
        $advancedMarketing = $business->campaigns()->get()->contains(fn ($c) => $c->roas() >= 2.0)
            || $business->activations()->where('status', 'active')->whereHas('solution', fn ($q) => $q->whereIn('key', ['digiads', 'rcs']))->exists();
        $readiness = $this->expansion->readiness($business)['score'];
        $financeEntries = $business->financialEntries()->count();
        $completed = $business->missions()->whereIn('status', ['completed', 'partial'])->count();
        $paymentDigital = $business->usesTool('qris') || $business->usesTool('pos') || $business->usesProduct('digipos') || $business->usesProduct('soundbox');

        $c = fn (string $label, bool $met, string $detail) => compact('label', 'met', 'detail');

        return [
            'digital_ready' => ['label' => 'Digital Ready', 'criteria' => [
                $c('Profil bisnis lengkap', $business->onboardingComplete(), $business->onboardingComplete() ? 'Onboarding selesai' : 'Lengkapi onboarding'),
                $c('Growth Diagnostic selesai', (bool) $score, $score ? 'Skor '.$score->overall : 'Belum diagnosis'),
            ]],
            'digital_active' => ['label' => 'Digital Active', 'criteria' => [
                $c('Digital presence aktif', $dim('digital_presence') >= 50, 'Kehadiran Digital '.$dim('digital_presence').'/100 (≥ 50)'),
                $c('Kanal penjualan digital aktif', count($digitalChannels) >= 1, count($digitalChannels).' kanal digital'),
                $c('Basic KPI tracking', $ledger->count() >= 1, $ledger->count().' bulan tercatat di Growth Ledger'),
            ]],
            'productive' => ['label' => 'Productive', 'criteria' => [
                $c('Digital transaction tracking', $paymentDigital || ($latest?->digital_txn_share ?? 0) >= 40, 'Transaksi tercatat digital '.pct($latest?->digital_txn_share ?? 0, 0)),
                $c('Produktivitas memadai', $dim('productivity') >= 60, 'Produktivitas '.$dim('productivity').'/100 (≥ 60)'),
                $c('Penggunaan konsisten', $ledger->count() >= 2 || $completed >= 2 || $financeEntries >= 20, $ledger->count().' bulan data · '.$completed.' misi'),
            ]],
            'growing' => ['label' => 'Growing', 'criteria' => [
                $c('KPI improvement', $kpiImproved, $kpiImproved ? 'KPI naik vs baseline' : 'Belum ada kenaikan vs baseline'),
                $c('Revenue / customer growth', $revGrowth >= 5 || $customerGrowth >= 10, 'Omzet '.signed($revGrowth, 1, '%').' · pelanggan baru '.signed($customerGrowth, 1, '%')),
                $c('Repeatable marketing activity', $marketingDone >= 1, $marketingDone.' aktivitas pemasaran terukur'),
            ]],
            'scaling' => ['label' => 'Scaling', 'criteria' => [
                $c('Stable growth', $nonDeclining >= 3, $nonDeclining.' bulan omzet tidak turun berturut-turut (≥ 3)'),
                $c('Advanced marketing', $advancedMarketing, $advancedMarketing ? 'ROAS ≥ 2x / DigiAds / RCS' : 'Belum ada kampanye ROAS ≥ 2x'),
                $c('Data-driven decision making', $ledger->count() >= 4 && $financeEntries >= 30, $ledger->count().' bulan ledger · '.$financeEntries.' transaksi'),
                $c('Expansion readiness', $readiness >= 70, 'Readiness '.$readiness.'/100 (≥ 70)'),
            ]],
            'go_global' => ['label' => 'Go Global', 'criteria' => [
                $c('Expansion readiness tinggi', $readiness >= 85, 'Readiness '.$readiness.'/100 (≥ 85)'),
                $c('Kanal lintas wilayah', $business->hasChannel('marketplace') || $business->hasChannel('website'), 'Marketplace / website aktif'),
                $c('Akselerasi DCE', in_array($business->dceApplication?->stage, ['selection', 'mentoring'], true), 'Tahap DCE: '.($business->dceApplication?->stage ?? '—')),
            ]],
        ];
    }

    public function level(Business $business, ?array $evaluation = null): string
    {
        $evaluation ??= $this->evaluate($business);
        $level = 'digital_ready';
        foreach ($evaluation as $key => $lvl) {
            if (! collect($lvl['criteria'])->every('met')) {
                break;
            }
            $level = $key;
        }

        return $level;
    }

    /** Re-evaluate and persist; returns [old, new]. */
    public function refresh(Business $business): array
    {
        $old = $business->passport_level;
        $new = $this->level($business);
        if ($old !== $new) {
            $business->update(['passport_level' => $new]);
        }

        return [$old, $new];
    }

    private function nonDecliningMonths($ledger): int
    {
        $streak = 0;
        $values = $ledger->pluck('revenue')->values();
        for ($i = $values->count() - 1; $i > 0; $i--) {
            if ($values[$i] >= $values[$i - 1]) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}
