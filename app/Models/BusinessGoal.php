<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessGoal extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'baseline_value' => 'float',
            'target_value' => 'float',
            'target_date' => 'date',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function label(): string
    {
        return config("tumbuh.goals.{$this->goal_key}.label", $this->goal_key);
    }
}
