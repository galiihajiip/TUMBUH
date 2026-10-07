<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosticResponse extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'normalized_score' => 'float',
        ];
    }

    public function diagnostic(): BelongsTo
    {
        return $this->belongsTo(Diagnostic::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(DiagnosticQuestion::class, 'diagnostic_question_id');
    }
}
