<?php

namespace App\Services;

use App\Models\Business;
use App\Models\LeaderboardSnapshot;
use Illuminate\Support\Collection;

/**
 * Monthly Growth Leaderboard — ranks by Growth Achievement Score, never by
 * absolute revenue, so small UMKM compete fairly:
 *   50% Target Achievement + 25% Mission Completion
 * + 15% KPI Improvement   + 10% Data Discipline
 */
class LeaderboardService
{
    public function __construct(private KpiService $kpi) {}

    public function weights(): array
    {
        return config('tumbuh.leaderboard_weights');
    }

    public function score(array $components): float
    {
        $total = 0;
        foreach ($this->weights() as $key => $w) {
            $total += ($components[$key] ?? 0) * $w;
        }

        return round($total, 1);
    }

    /** Live components for a business with real activity. */
    public function components(Business $business): array
    {
        $baseline = $this->kpi->baseline($business);
        $latest = $this->kpi->latest($business);

        // Target achievement: progress from baseline to the revenue target.
        $target = 0.0;
        if ($baseline && $latest) {
            $goal = $this->kpi->target($business, 'revenue', $baseline);
            $target = $goal && $goal > $baseline->revenue
                ? max(0, min(100, ($latest->revenue - $baseline->revenue) / ($goal - $baseline->revenue) * 100))
                : 0;
        }

        $missions = $business->missions()->where('status', '!=', 'upcoming')->get();
        $missionPct = $missions->count() ? $missions->filter->isDone()->count() / $missions->count() * 100 : 0;

        $kpiImprovement = 50.0;
        if ($baseline && $latest && $latest->id !== $baseline->id) {
            $rev = $baseline->revenue > 0 ? ($latest->revenue - $baseline->revenue) / $baseline->revenue * 100 : 0;
            $kpiImprovement = max(0, min(100, 50 + $rev * 1.5 + ($latest->conversion_rate - $baseline->conversion_rate) * 8 + ($latest->repeat_rate - $baseline->repeat_rate) * 2));
        }

        $months = $business->kpiSnapshots()->where('period', '>=', now()->startOfMonth()->subMonths(3))->count();
        $finance = $business->financialEntries()->where('entry_date', '>=', now()->subDays(60))->count();
        $checkins = $business->missions()->has('submissions')->count();
        $discipline = min(100, $months / 3 * 50 + min(30, $finance) + min(20, $checkins * 5));

        return [
            'target_achievement' => round($target, 1),
            'mission_completion' => round($missionPct, 1),
            'kpi_improvement' => round($kpiImprovement, 1),
            'data_discipline' => round($discipline, 1),
        ];
    }

    /** Ranked board: live businesses + seeded peer cohort snapshots. */
    public function board(): Collection
    {
        $live = Business::where('is_peer', false)->whereNotNull('onboarded_at')->get()
            ->map(fn (Business $b) => ['business' => $b, 'components' => $c = $this->components($b), 'score' => $this->score($c)]);

        $peers = LeaderboardSnapshot::with('business')
            ->whereHas('business', fn ($q) => $q->where('is_peer', true))
            ->get()
            ->map(fn (LeaderboardSnapshot $s) => ['business' => $s->business, 'components' => $s->only(array_keys($this->weights())), 'score' => (float) $s->score]);

        return $live->concat($peers)
            ->sortByDesc('score')
            ->values()
            ->map(fn ($row, $i) => $row + ['rank' => $i + 1]);
    }

    /** Current rank and distance to the next milestone. */
    public function position(Business $business): array
    {
        $board = $this->board();
        $row = $board->first(fn ($r) => $r['business']->id === $business->id);
        if (! $row) {
            return ['rank' => null, 'score' => 0, 'total' => $board->count(), 'next' => null, 'gap' => null, 'components' => $this->components($business)];
        }

        $milestones = [20, 10, 3, 1];
        $milestone = collect($milestones)->first(fn ($m) => $row['rank'] > $m);
        $targetRow = $milestone ? $board[$milestone - 1] : null;

        return [
            'rank' => $row['rank'],
            'score' => $row['score'],
            'total' => $board->count(),
            'components' => $row['components'],
            'next' => $milestone ? 'Top '.$milestone : null,
            'gap' => $targetRow ? round($targetRow['score'] - $row['score'] + 0.1, 1) : null,
            'percentile' => round((1 - ($row['rank'] - 1) / max(1, $board->count())) * 100),
        ];
    }
}
