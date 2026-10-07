<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpansionAssessment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'result' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
