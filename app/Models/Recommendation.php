<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'solution_keys' => 'array',
            'review' => 'array',
            'is_current' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function solutions()
    {
        return TelkomselSolution::whereIn('key', $this->solution_keys ?? [])->orderBy('position')->get();
    }
}
