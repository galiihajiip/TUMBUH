<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'completed_at' => 'datetime',
            'kpi_target' => 'float',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(MissionSubmission::class)->latest();
    }

    public function planItem(): BelongsTo
    {
        return $this->belongsTo(ActionPlanItem::class, 'action_plan_item_id');
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(TelkomselSolution::class, 'solution_key', 'key');
    }

    public function isDone(): bool
    {
        return in_array($this->status, ['completed', 'partial'], true);
    }
}
