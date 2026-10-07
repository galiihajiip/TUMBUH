<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Services\MissionService;
use App\Services\TierService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** 30-Day Action Plan + Weekly Growth Missions with check-ins. */
class MissionController extends Controller
{
    public function plan(TierService $tiers)
    {
        $business = $this->business();
        $plan = $business->activePlan ?? $business->actionPlans()->latest('id')->first();

        return view('missions.plan', [
            'business' => $business,
            'plan' => $plan?->load('items.mission', 'recommendation'),
            'personalized' => $tiers->allows($business, 'action_plan.personalized'),
        ]);
    }

    public function index(MissionService $missions)
    {
        $business = $this->business();

        return view('missions.index', [
            'business' => $business,
            'current' => $missions->current($business),
            'missions' => $business->missions()->with('submissions')->orderByDesc('id')->get()->sortBy('week')->groupBy(fn ($m) => $m->planItem?->action_plan_id),
            'plan' => $business->activePlan,
            'done' => $business->missions()->whereIn('status', ['completed', 'partial'])->count(),
            'total' => $business->missions()->count(),
            'points' => $business->missions()->whereIn('status', ['completed', 'partial'])->sum('points'),
        ]);
    }

    public function show(Mission $mission)
    {
        $this->authorizeOwn($mission);

        return view('missions.show', [
            'business' => $this->business(),
            'mission' => $mission->load('submissions', 'planItem.plan'),
            'latestKpi' => app(\App\Services\KpiService::class)->latest($this->business()),
        ]);
    }

    public function checkIn(Request $request, Mission $mission, MissionService $missions)
    {
        $this->authorizeOwn($mission);
        abort_if($mission->status === 'upcoming', 422, 'Misi ini belum dimulai.');

        $data = $request->validate([
            'completion' => ['required', Rule::in(['yes', 'partial', 'no'])],
            'kpi_value' => ['nullable', 'numeric', 'min:0'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
            'problem' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $missions->submit($mission, $data, $request->file('evidence'));

        $message = match ($data['completion']) {
            'yes' => 'Check-in tersimpan. Misi selesai — misi berikutnya sudah aktif.',
            'partial' => 'Check-in tersimpan sebagai sebagian selesai. Misi berikutnya dibuka.',
            default => 'Check-in tersimpan. Misi tetap aktif — AI mencatat kendala Anda.',
        };

        return redirect()->route('missions.index')->with('success', $message);
    }
}
