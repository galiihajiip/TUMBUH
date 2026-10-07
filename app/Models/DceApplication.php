<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DceApplication extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'profile' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public const STAGES = [
        'eligibility' => 'Eligibility assessment',
        'application' => 'Application',
        'profile_submitted' => 'Profile submission',
        'selection' => 'Selection',
        'mentoring' => 'Mentoring / Accelerator',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
