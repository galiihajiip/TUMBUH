<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Business extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'sales_channels' => 'array',
            'digital_tools' => 'array',
            'telkomsel_products' => 'array',
            'has_physical_store' => 'boolean',
            'is_peer' => 'boolean',
            'onboarded_at' => 'datetime',
            'goals_set_at' => 'datetime',
            'tier_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(BusinessGoal::class);
    }

    public function diagnostics(): HasMany
    {
        return $this->hasMany(Diagnostic::class);
    }

    public function latestDiagnostic(): HasOne
    {
        return $this->hasOne(Diagnostic::class)->latestOfMany();
    }

    public function scoreSnapshots(): HasMany
    {
        return $this->hasMany(GrowthScoreSnapshot::class)->orderBy('calculated_at')->orderBy('id');
    }

    public function latestScore(): HasOne
    {
        return $this->hasOne(GrowthScoreSnapshot::class)->ofMany(['calculated_at' => 'max', 'id' => 'max']);
    }

    public function kpiSnapshots(): HasMany
    {
        return $this->hasMany(KpiSnapshot::class)->orderBy('period');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function currentRecommendation(): HasOne
    {
        // Constraints go inside ofMany() so MAX(id) is taken over matching rows only.
        return $this->hasOne(Recommendation::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('kind', 'assistant')->where('is_current', true));
    }

    public function currentReview(): HasOne
    {
        return $this->hasOne(Recommendation::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('kind', 'review')->where('is_current', true));
    }

    public function actionPlans(): HasMany
    {
        return $this->hasMany(ActionPlan::class);
    }

    public function activePlan(): HasOne
    {
        return $this->hasOne(ActionPlan::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('status', 'active'));
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    /** Missions of the active 30-day plan (falls back to the latest plan). */
    public function planMissions()
    {
        $plan = $this->activePlan ?? $this->actionPlans()->latest('id')->first();

        return $this->missions()
            ->whereIn('action_plan_item_id', $plan ? $plan->items()->pluck('id') : [])
            ->orderBy('week');
    }

    public function activations(): HasMany
    {
        return $this->hasMany(SolutionActivation::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function financialEntries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ExpenseReceipt::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(GrowthNotification::class)->latest();
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(BusinessReward::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(ExpansionAssessment::class);
    }

    public function dceApplication(): HasOne
    {
        return $this->hasOne(DceApplication::class);
    }

    public function tierLabel(): string
    {
        return config("tumbuh.tiers.{$this->tier}.label", ucfirst($this->tier));
    }

    public function tierRank(): int
    {
        return (int) config("tumbuh.tiers.{$this->tier}.rank", 1);
    }

    public function usesProduct(string $key): bool
    {
        return in_array($key, $this->telkomsel_products ?? [], true);
    }

    public function usesTool(string $key): bool
    {
        return in_array($key, $this->digital_tools ?? [], true);
    }

    public function hasChannel(string $key): bool
    {
        return in_array($key, $this->sales_channels ?? [], true);
    }

    public function locationLabel(): string
    {
        return collect([$this->district, $this->city])->filter()->implode(', ');
    }

    public function revenueRangeLabel(): string
    {
        return config("tumbuh.revenue_ranges.{$this->revenue_range}.label", '—');
    }

    public function onboardingComplete(): bool
    {
        return $this->onboarded_at !== null;
    }
}
