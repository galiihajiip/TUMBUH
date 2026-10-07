<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticQuestion extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'scoring' => 'array',
            'active' => 'boolean',
            'weight' => 'float',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('position');
    }
}
