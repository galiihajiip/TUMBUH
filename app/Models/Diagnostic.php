<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagnostic extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'baseline_confirmed_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(DiagnosticResponse::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
