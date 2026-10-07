<?php

namespace App\Services;

use App\Models\Business;
use App\Models\KpiSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * KPI tracking, Growth Ledger series and Before-vs-After comparisons.
 * All values come from kpi_snapshots — nothing is hardcoded in views.
 */
class KpiService
{
    /** KPI definitions: unit drives delta semantics (pp for rates). */
    public const METRICS = [
        'revenue' => ['label' => 'Revenue / Omzet', 'unit' => 'rp', 'benchmark' => null],
        'new_customers' => ['label' => 'Pelanggan Baru', 'unit' => 'count', 'benchmark' => null],
        'total_customers' => ['label' => 'Total Pelanggan', 'unit' => 'count', 'benchmark' => null],
        'conversion_rate' => ['label' => 'Conversion Rate', 'unit' => 'rate', 'benchmark' => 5.0],
        'repeat_rate' => ['label' => 'Repeat Customer', 'unit' => 'rate', 'benchmark' => 30.0],
        'marketing_spend' => ['label' => 'Marketing Spend', 'unit' => 'rp', 'benchmark' => null],
        'campaign_reach' => ['label' => 'Campaign Reach', 'unit' => 'count', 'benchmark' => null],
        'gross_margin' => ['label' => 'Gross Margin', 'unit' => 'rate', 'benchmark' => 40.0],
        'digital_txn_share' => ['label' => 'Transaksi Tercatat Digital', 'unit' => 'rate', 'benchmark' => 70.0],
        'growth_score' => ['label' => 'Growth Score', 'unit' => 'score', 'benchmark' => null],
    ];

    public function baseline(Business $business): ?KpiSnapshot
    {
        return $business->kpiSnapshots()->where('is_baseline', true)->first()
            ?? $business->kpiSnapshots()->first();
    }

    public function latest(Business $business): ?KpiSnapshot
    {
        return $business->kpiSnapshots()->reorder()->orderByDesc('period')->first();
    }

    public function previous(Business $business): ?KpiSnapshot
    {
        return $business->kpiSnapshots()->reorder()->orderByDesc('period')->skip(1)->first();
    }

    public function ledger(Business $business): Collection
    {
        return $business->kpiSnapshots()->get();
    }

    /** Target per metric, from business goals first, then sensible benchmarks. */
    public function target(Business $business, string $metric, ?KpiSnapshot $baseline = null): ?float
    {
        $goal = $business->goals->firstWhere('metric', $metric);
        if ($goal && $goal->target_value) {
            return (float) $goal->target_value;
        }

        $baseline ??= $this->baseline($business);
        if (! $baseline) {
            return self::METRICS[$metric]['benchmark'] ?? null;
        }

        return match ($metric) {
            'revenue' => round($baseline->revenue * 1.25, -5),
            'new_customers' => (float) ceil($baseline->new_customers * 1.33),
            'conversion_rate' => max(5.0, $baseline->conversion_rate + 2),
            'repeat_rate' => max(30.0, $baseline->repeat_rate + 5),
            default => self::METRICS[$metric]['benchmark'] ?? null,
        };
    }

    /** Delta semantics: absolute, relative %, and percentage-point for rates. */
    public function delta(string $metric, float $before, float $after): array
    {
        $unit = self::METRICS[$metric]['unit'] ?? 'count';
        $abs = $after - $before;
        $rel = $before != 0.0 ? ($abs / $before) * 100 : null;

        return [
            'absolute' => round($abs, 2),
            'relative' => $rel !== null ? round($rel, 1) : null,
            'pp' => $unit === 'rate' ? round($abs, 1) : null,
            'direction' => $abs > 0 ? 'up' : ($abs < 0 ? 'down' : 'flat'),
            'unit' => $unit,
        ];
    }

    /** KPI table rows: before / current / target (dashboard + My Growth). */
    public function kpiRows(Business $business, array $metrics = ['revenue', 'new_customers', 'conversion_rate', 'repeat_rate']): array
    {
        $baseline = $this->baseline($business);
        $latest = $this->latest($business);
        if (! $baseline || ! $latest) {
            return [];
        }

        return collect($metrics)->map(function (string $metric) use ($business, $baseline, $latest) {
            $before = (float) $baseline->{$metric};
            $current = (float) $latest->{$metric};
            $target = $this->target($business, $metric, $baseline);
            $progress = null;
            if ($target !== null && $target != $before) {
                $progress = max(0, min(100, ($current - $before) / ($target - $before) * 100));
            }

            return [
                'metric' => $metric,
                'label' => self::METRICS[$metric]['label'],
                'unit' => self::METRICS[$metric]['unit'],
                'before' => $before,
                'current' => $current,
                'target' => $target,
                'progress' => $progress !== null ? round($progress) : null,
                'delta' => $this->delta($metric, $before, $current),
                'source' => $latest->source,
            ];
        })->all();
    }

    /**
     * Before vs After: the baseline month against the latest closed month.
     * Returns null until at least one post-baseline month exists.
     */
    public function beforeAfter(Business $business): ?array
    {
        $baseline = $this->baseline($business);
        $latest = $this->latest($business);
        if (! $baseline || ! $latest || $latest->id === $baseline->id) {
            return null;
        }

        $rows = collect(['revenue', 'new_customers', 'conversion_rate', 'repeat_rate', 'gross_margin'])
            ->filter(fn ($m) => $baseline->{$m} !== null && $latest->{$m} !== null)
            ->map(fn ($m) => [
                'metric' => $m,
                'label' => self::METRICS[$m]['label'],
                'unit' => self::METRICS[$m]['unit'],
                'before' => (float) $baseline->{$m},
                'after' => (float) $latest->{$m},
                'delta' => $this->delta($m, (float) $baseline->{$m}, (float) $latest->{$m}),
            ])->values()->all();

        return [
            'baseline' => $baseline,
            'latest' => $latest,
            'rows' => $rows,
            'days' => (int) $baseline->period->diffInDays($latest->period),
            'contributions' => $this->contributions($business, $baseline->period),
            'score_before' => $baseline->growth_score,
            'score_after' => $latest->growth_score,
        ];
    }

    /** Contribution tags collected from every ledger month after the baseline. */
    public function contributions(Business $business, Carbon $since): array
    {
        return $business->kpiSnapshots()
            ->where('period', '>', $since)
            ->get()
            ->flatMap(fn (KpiSnapshot $s) => $s->contributions ?? [])
            ->unique('label')
            ->values()
            ->all();
    }

    public function format(string $metric, float|int|null $value): string
    {
        if ($value === null) {
            return '—';
        }

        return match (self::METRICS[$metric]['unit'] ?? 'count') {
            'rp' => rupiah_short($value),
            'rate' => pct($value),
            'score' => (string) (int) $value,
            default => num($value),
        };
    }

    public function formatDelta(array $delta): string
    {
        if ($delta['unit'] === 'rate') {
            return signed($delta['pp'], 1, 'pp');
        }
        if ($delta['relative'] !== null && $delta['unit'] !== 'score') {
            return signed($delta['relative'], 1, '%');
        }

        return signed($delta['absolute']);
    }
}
