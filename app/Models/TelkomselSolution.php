<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelkomselSolution extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'helps' => 'array',
            'metrics' => 'array',
            'addresses' => 'array',
            'tier_access' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function pricingText(): string
    {
        return [
            'included' => 'Included in your plan',
            'activation_required' => 'Additional activation required',
            'usage_cost' => 'Additional usage cost',
            'eligibility_required' => 'Eligibility required',
        ][$this->pricing_label] ?? $this->pricing_label;
    }
}
