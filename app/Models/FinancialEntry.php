<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialEntry extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function categoryLabel(): string
    {
        $map = $this->type === 'income' ? config('tumbuh.income_categories') : config('tumbuh.expense_categories');

        return $map[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }
}
