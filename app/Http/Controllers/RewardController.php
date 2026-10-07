<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use App\Services\RewardService;

class RewardController extends Controller
{
    public function index(LeaderboardService $leaderboard, RewardService $rewards)
    {
        $business = $this->business();
        $rewards->evaluate($business);

        return view('rewards.index', [
            'business' => $business,
            'board' => $leaderboard->board(),
            'position' => $leaderboard->position($business),
            'weights' => $leaderboard->weights(),
            'unlocked' => $business->rewards()->with('reward')->latest('unlocked_at')->get(),
            'rules' => $rewards->rules($business),
            'catalog' => \App\Models\Reward::all()->keyBy('key'),
            'achievements' => $business->achievements()->latest('earned_at')->get(),
        ]);
    }

    public function claim(int $businessReward, RewardService $rewards)
    {
        $rewards->claim($this->business(), $businessReward);

        return back()->with('success', 'Reward diklaim.');
    }

    public function tiers()
    {
        return view('rewards.tiers', ['business' => $this->business()]);
    }
}
