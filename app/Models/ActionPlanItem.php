<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ActionPlanItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'actions' => 'array',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ActionPlan::class, 'action_plan_id');
    }

    public function mission(): HasOne
    {
        return $this->hasOne(Mission::class);
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(TelkomselSolution::class, 'solution_key', 'key');
    }
}
