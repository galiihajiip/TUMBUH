<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Reward;

/**
 * Reward Center — "reward follows outcome, not activity".
 * Each reward has an explicit, checkable rule over measured results.
 */
class RewardService
{
    public function __construct(
        private LeaderboardService $leaderboard,
        private NotificationService $notifications,
    ) {}

    /** @return array<string, array{met: bool, reason: string}> */
    public function rules(Business $business): array
    {
        $pos = $this->leaderboard->position($business);
        $c = $pos['components'];
        $first = $business->scoreSnapshots()->first();
        $latest = $business->latestScore;
        $scoreGain = $first && $latest ? $latest->overall - $first->overall : 0;
        $done = $business->missions()->whereIn('status', ['completed', 'partial'])->count();
        $passportRank = array_search($business->passport_level, array_keys(config('tumbuh.passport_levels')), true);
        $topQuintile = $pos['rank'] && $pos['rank'] <= max(1, (int) ceil($pos['total'] * 0.2));

        return [
            'silver_trial' => ['met' => $business->tier === 'bronze' && ($topQuintile || $pos['score'] >= 70), 'reason' => 'Bronze di Top 20% leaderboard (rank #'.($pos['rank'] ?? '—').')'],
            'gold_trial' => ['met' => $business->tier === 'silver' && ($topQuintile && $pos['score'] >= 75), 'reason' => 'Silver di Top 20% dengan Achievement Score ≥ 75'],
            'myads_credit' => ['met' => $c['target_achievement'] >= 50, 'reason' => 'Target achievement '.pct($c['target_achievement'], 0).' (≥ 50%)'],
            'kuncie_premium' => ['met' => $done >= 3 && $c['kpi_improvement'] > 50, 'reason' => $done.' misi selesai dengan KPI membaik'],
            'expert_mentoring' => ['met' => $scoreGain >= 5, 'reason' => 'Growth Score naik '.signed($scoreGain).' poin (≥ +5)'],
            'growth_consultation' => ['met' => $passportRank !== false && $passportRank >= 3, 'reason' => 'Growth Passport mencapai Growing'],
            'market_insight_session' => ['met' => ($latest?->overall ?? 0) >= 80, 'reason' => 'Growth Score ≥ 80 (siap ekspansi)'],
        ];
    }

    /** Unlock newly-earned rewards; never revokes (outcomes already proven). */
    public function evaluate(Business $business): array
    {
        $unlocked = [];
        foreach ($this->rules($business) as $key => $rule) {
            if (! $rule['met']) {
                continue;
            }
            $reward = Reward::where('key', $key)->first();
            if (! $reward || $business->rewards()->where('reward_id', $reward->id)->exists()) {
                continue;
            }
            $business->rewards()->create(['reward_id' => $reward->id, 'status' => 'unlocked', 'reason' => $rule['reason'], 'unlocked_at' => now()]);
            $this->notifications->push($business, 'reward', 'Selamat! Anda membuka '.$reward->name, $rule['reason'], 'Klaim reward', route('rewards.index'));
            $unlocked[] = $reward;
        }

        return $unlocked;
    }

    /** Claim a reward; tier trials upgrade the tier for 30 days. */
    public function claim(Business $business, int $businessRewardId): void
    {
        $br = $business->rewards()->with('reward')->findOrFail($businessRewardId);
        if ($br->status === 'claimed') {
            return;
        }
        $br->update(['status' => 'claimed', 'claimed_at' => now()]);

        $upgrade = ['silver_trial' => 'silver', 'gold_trial' => 'gold'][$br->reward->key] ?? null;
        if ($upgrade) {
            $business->update(['tier' => $upgrade, 'tier_expires_at' => now()->addDays(30), 'tier_note' => 'Reward: '.$br->reward->name]);
            $this->notifications->push($business, 'reward', 'Anda membuka '.ucfirst($upgrade).' selama 1 bulan', 'Fitur '.ucfirst($upgrade).' aktif hingga '.now()->addDays(30)->translatedFormat('d M Y').'.', 'Lihat fitur', route('rewards.tiers'));
        }
    }
}
