<?php

namespace App\Services;

/**
 * Picks the biggest growth bottleneck from dimension scores.
 * Rule: lowest score wins; ties go to the dimension with the larger
 * weight (it costs more Growth Score). Priority follows the gap size.
 */
class BottleneckService
{
    public const HIGH_BELOW = 50;

    public const MEDIUM_BELOW = 65;

    public function analyse(array $dimensions): array
    {
        $weights = collect(config('tumbuh.dimensions'))->map(fn ($d) => $d['weight']);

        $ranked = collect($dimensions)
            ->map(fn ($score, $key) => ['key' => $key, 'score' => (int) $score, 'weight' => (float) ($weights[$key] ?? 0)])
            ->sort(fn ($a, $b) => [$a['score'], -$a['weight']] <=> [$b['score'], -$b['weight']])
            ->values();

        $bottleneck = $ranked->first();
        $strongest = $ranked->sortByDesc('score')->first();

        return [
            'bottleneck' => $bottleneck['key'],
            'secondary' => $ranked->get(1)['key'] ?? null,
            'strongest' => $strongest['key'],
            'priority' => $this->priority($bottleneck['score']),
        ];
    }

    public function priority(int $score): string
    {
        return match (true) {
            $score < self::HIGH_BELOW => 'HIGH',
            $score < self::MEDIUM_BELOW => 'MEDIUM',
            default => 'LOW',
        };
    }
}
