<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthScoreSnapshot extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'dimensions' => 'array',
            'changes' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function dimensionLabel(string $key): string
    {
        return config("tumbuh.dimensions.{$key}.label", $key);
    }

    public function scoreFor(string $dimension): int
    {
        return (int) ($this->dimensions[$dimension] ?? 0);
    }
}
