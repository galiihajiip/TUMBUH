<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiSnapshot extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'is_baseline' => 'boolean',
            'conversion_rate' => 'float',
            'repeat_rate' => 'float',
            'gross_margin' => 'float',
            'digital_txn_share' => 'float',
            'contributions' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
