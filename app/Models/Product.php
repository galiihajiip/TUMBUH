<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = ['id'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest('moved_on')->latest('id');
    }

    public function margin(): int
    {
        return (int) $this->selling_price - (int) $this->purchase_price;
    }

    public function marginPercent(): float
    {
        return $this->selling_price > 0 ? round($this->margin() / $this->selling_price * 100, 1) : 0.0;
    }

    public function stockStatus(): string
    {
        if ($this->stock <= 0) {
            return 'out';
        }

        return $this->stock <= $this->min_stock ? 'low' : 'ok';
    }

    public function stockValue(): int
    {
        return max(0, $this->stock) * (int) $this->purchase_price;
    }
}
