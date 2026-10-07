<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Diagnostic;
use App\Models\GrowthScoreSnapshot;
use App\Models\KpiSnapshot;
use Illuminate\Support\Arr;

/**
 * Deterministic, reproducible Growth Score.
 *
 *  1. Diagnostic score: weighted mean of normalised answers per dimension,
 *     blended with confirmed baseline KPIs for conversion and retention.
 *  2. Recalculation: the cycle's diagnostic scores plus explicit,
 *     capped adjustments from measured KPI change and platform activity.
 *  3. Overall = Σ(dimension × weight) using config('tumbuh.dimensions').
 */
class GrowthScoreService
{
    public function __construct(
        private BottleneckService $bottlenecks,
        private InventoryAnalyticsService $inventory,
    ) {}

    public function weights(): array
    {
        return collect(config('tumbuh.dimensions'))->map(fn ($d) => $d['weight'])->all();
    }

    public function overall(array $dimensions): int
    {
        $total = 0.0;
        foreach ($this->weights() as $key => $weight) {
            $total += ($dimensions[$key] ?? 0) * $weight;
        }

        return (int) round($total);
    }

    /** Dimension scores from diagnostic answers + confirmed baseline KPIs. */
    public function dimensionsFromDiagnostic(Diagnostic $diagnostic): array
    {
        $responses = $diagnostic->responses()->with('question')->get();
        $dimensions = [];

        foreach (array_keys($this->weights()) as $key) {
            $items = $responses->filter(fn ($r) => $r->question->dimension === $key);
            $weightSum = $items->sum(fn ($r) => $r->question->weight);
            $dimensions[$key] = $weightSum > 0
                ? $items->sum(fn ($r) => $r->normalized_score * $r->question->weight) / $weightSum
                : 50.0;
        }

        $kpi = $diagnostic->kpi_snapshot_id ? KpiSnapshot::find($diagnostic->kpi_snapshot_id) : null;
        if ($kpi) {
            // Benchmarks: repeat 40% ≈ excellent, conversion 6% ≈ excellent.
            $dimensions['retention'] = 0.6 * $dimensions['retention'] + 0.4 * min(100, $kpi->repeat_rate / 40 * 100);
            $dimensions['conversion'] = 0.6 * $dimensions['conversion'] + 0.4 * min(100, $kpi->conversion_rate / 6 * 100);
        }

        return array_map(fn ($v) => (int) round(max(0, min(100, $v))), $dimensions);
    }

    public function fromDiagnostic(Business $business, Diagnostic $diagnostic): GrowthScoreSnapshot
    {
        $dimensions = $this->dimensionsFromDiagnostic($diagnostic);

        return $this->store($business, $dimensions, 'diagnostic', $diagnostic->id, []);
    }

    /**
     * Rule-based score update from what actually happened since the cycle's
     * diagnostic. Every adjustment is capped and carries a human reason.
     */
    public function recalculate(Business $business, string $trigger = 'recalculation'): ?GrowthScoreSnapshot
    {
        $anchor = $business->scoreSnapshots()->reorder()->where('trigger', 'diagnostic')->orderByDesc('calculated_at')->orderByDesc('id')->first();
        if (! $anchor) {
            return null;
        }

        $diagnostic = $anchor->diagnostic_id ? Diagnostic::find($anchor->diagnostic_id) : null;
        $base = $diagnostic?->kpi_snapshot_id ? KpiSnapshot::find($diagnostic->kpi_snapshot_id) : null;
        $latest = $business->kpiSnapshots()->reorder()->orderByDesc('period')->first();
        $since = $diagnostic?->baseline_confirmed_at ?? $anchor->calculated_at;

        $adjust = $this->adjustments($business, $base, $latest, $since);

        $dimensions = [];
        foreach ($anchor->dimensions as $key => $score) {
            $dimensions[$key] = (int) round(max(0, min(100, $score + ($adjust[$key]['delta'] ?? 0))));
        }

        return $this->store($business, $dimensions, $trigger, $anchor->diagnostic_id, $adjust);
    }

    /** @return array<string, array{delta: float, reasons: string[]}> */
    public function adjustments(Business $business, ?KpiSnapshot $base, ?KpiSnapshot $latest, $since): array
    {
        $adj = collect(array_keys($this->weights()))->mapWithKeys(fn ($k) => [$k => ['delta' => 0.0, 'reasons' => []]])->all();
        $add = function (string $key, float $delta, string $reason) use (&$adj) {
            if (abs($delta) < 0.5) {
                return;
            }
            $adj[$key]['delta'] += $delta;
            $adj[$key]['reasons'][] = $reason;
        };
        $clamp = fn (float $v, float $min, float $max) => max($min, min($max, $v));

        if ($base && $latest && $latest->id !== $base->id) {
            if ($base->new_customers > 0) {
                $pct = ($latest->new_customers - $base->new_customers) / $base->new_customers * 100;
                $add('acquisition', $clamp($pct * 1.0, -15, 25), 'Pelanggan baru '.num($base->new_customers).' → '.num($latest->new_customers).' ('.signed($pct, 1, '%').')');
            }
            $convPp = $latest->conversion_rate - $base->conversion_rate;
            $add('conversion', $clamp($convPp * 12, -15, 25), 'Conversion '.pct($base->conversion_rate).' → '.pct($latest->conversion_rate).' ('.signed($convPp, 1, 'pp').')');

            $repPp = $latest->repeat_rate - $base->repeat_rate;
            $add('retention', $clamp($repPp * 2.5, -15, 25), 'Repeat customer '.pct($base->repeat_rate).' → '.pct($latest->repeat_rate).' ('.signed($repPp, 1, 'pp').')');

            $sharePp = $latest->digital_txn_share - $base->digital_txn_share;
            $add('productivity', $clamp($sharePp * 0.3, -10, 15), 'Transaksi tercatat digital '.signed($sharePp, 0, 'pp'));

            if ($base->revenue > 0) {
                $revPct = ($latest->revenue - $base->revenue) / $base->revenue * 100;
                $add('financial_health', $clamp($revPct * 0.4, -8, 10), 'Omzet '.rupiah_short($base->revenue).' → '.rupiah_short($latest->revenue).' ('.signed($revPct, 1, '%').')');
            }
            if ($base->gross_margin !== null && $latest->gross_margin !== null) {
                $mPp = $latest->gross_margin - $base->gross_margin;
                $add('financial_health', $clamp($mPp * 1.5, -15, 15), 'Gross margin '.pct($base->gross_margin).' → '.pct($latest->gross_margin));
            }
        }

        $campaigns = $business->campaigns()->where('launched_at', '>=', $since)->get();
        if ($campaigns->sum('conversions') > 0) {
            $conv = $campaigns->sum('conversions');
            $add('acquisition', min(5, $conv / 10), 'Kampanye MyAds: '.num($conv).' konversi tercatat (Simulated)');
        }

        $activated = $business->activations()->where('status', 'active')->where('activated_at', '>=', $since)->with('solution')->get();
        if ($activated->isNotEmpty()) {
            $add('digital_presence', min(12, 4 * $activated->count()), 'Solusi baru aktif: '.$activated->map(fn ($a) => $a->solution->name)->implode(', '));
        }

        $done = $business->missions()->whereIn('status', ['completed', 'partial'])->where('completed_at', '>=', $since)->count();
        if ($done > 0) {
            $add('productivity', min(8, 2 * $done), num($done).' misi mingguan diselesaikan');
        }

        if ($business->products()->exists()) {
            $stats = $this->inventory->stats($business);
            $risk = $stats['total'] ? ($stats['low'] + $stats['out']) / $stats['total'] : 0;
            if ($risk <= 0.1) {
                $add('inventory_health', 5, 'Stok sehat: '.num($stats['low'] + $stats['out']).' dari '.num($stats['total']).' produk menipis');
            } elseif ($risk >= 0.3) {
                $add('inventory_health', -round($risk * 30), num($stats['low'] + $stats['out']).' dari '.num($stats['total']).' produk stok menipis/habis');
            }
        }

        $others = collect($adj)->except('growth_readiness')->pluck('delta')->filter(fn ($d) => $d != 0);
        if ($others->isNotEmpty()) {
            $add('growth_readiness', $clamp($others->avg() * 0.6, -10, 12), 'Mengikuti tren rata-rata dimensi lain');
        }

        return array_map(fn ($a) => ['delta' => round($a['delta'], 1), 'reasons' => $a['reasons']], $adj);
    }

    private function store(Business $business, array $dimensions, string $trigger, ?int $diagnosticId, array $adjustments): GrowthScoreSnapshot
    {
        $previous = $business->latestScore()->first();
        $analysis = $this->bottlenecks->analyse($dimensions);
        $overall = $this->overall($dimensions);

        $changes = [];
        foreach ($dimensions as $key => $score) {
            $changes[$key] = [
                'delta' => $previous ? $score - $previous->scoreFor($key) : 0,
                'reasons' => $adjustments[$key]['reasons'] ?? [],
            ];
        }

        $snapshot = $business->scoreSnapshots()->create([
            'diagnostic_id' => $diagnosticId,
            'overall' => $overall,
            'dimensions' => $dimensions,
            'strongest' => $analysis['strongest'],
            'bottleneck' => $analysis['bottleneck'],
            'secondary' => $analysis['secondary'],
            'priority' => $analysis['priority'],
            'changes' => $changes,
            'trigger' => $trigger,
            'explanation' => $this->explain($overall, $previous, $changes, $analysis),
            'calculated_at' => now(),
        ]);

        // Keep the ledger's score column in sync for the latest month.
        $business->kpiSnapshots()->reorder()->orderByDesc('period')->first()?->update(['growth_score' => $overall]);

        return $snapshot;
    }

    private function explain(int $overall, ?GrowthScoreSnapshot $previous, array $changes, array $analysis): string
    {
        $bottleneck = config("tumbuh.dimensions.{$analysis['bottleneck']}.label");
        if (! $previous) {
            return "Skor awal {$overall}/100. Bottleneck terbesar Anda adalah {$bottleneck}.";
        }

        $diff = $overall - $previous->overall;
        $movers = collect($changes)->filter(fn ($c) => $c['delta'] != 0)->sortByDesc(fn ($c) => abs($c['delta']))->take(3)
            ->map(fn ($c, $k) => signed($c['delta']).' '.config("tumbuh.dimensions.{$k}.label"))->implode(', ');

        $head = $diff === 0 ? "Skor tetap {$overall}." : "Skor ".($diff > 0 ? 'naik' : 'turun')." {$previous->overall} → {$overall}.";

        return trim($head.($movers ? " Penyebab utama: {$movers}." : '')." Bottleneck saat ini: {$bottleneck}.");
    }

    /** Score history for charts. */
    public function history(Business $business): array
    {
        return $business->scoreSnapshots()->get()
            ->map(fn ($s) => ['label' => $s->calculated_at->translatedFormat('d M'), 'value' => $s->overall, 'trigger' => $s->trigger])
            ->all();
    }

    public static function triggerLabel(string $trigger): string
    {
        return Arr::get([
            'diagnostic' => 'Growth Diagnostic',
            'recalculation' => 'Recalculate',
            'cycle_close' => 'Penutupan siklus 30 hari',
            'mission' => 'Misi selesai',
            'review' => 'AI Performance Review',
        ], $trigger, $trigger);
    }
}
