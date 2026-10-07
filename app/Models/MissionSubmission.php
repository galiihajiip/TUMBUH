<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionSubmission extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kpi_value' => 'float',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
