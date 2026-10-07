<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'launched_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function roas(): float
    {
        return $this->spend > 0 ? round($this->attributed_revenue / $this->spend, 1) : 0.0;
    }

    public function ctr(): float
    {
        return $this->reach > 0 ? round($this->clicks / $this->reach * 100, 1) : 0.0;
    }

    public function conversionRate(): float
    {
        return $this->leads > 0 ? round($this->conversions / $this->leads * 100, 1) : 0.0;
    }
}
