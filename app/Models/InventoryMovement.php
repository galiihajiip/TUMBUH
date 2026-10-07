<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'moved_on' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function financialEntry(): BelongsTo
    {
        return $this->belongsTo(FinancialEntry::class);
    }
}
