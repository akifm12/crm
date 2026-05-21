<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceDeadline extends Model
{
    protected $fillable = ['requirement_id', 'due_date', 'title', 'notes', 'year'];

    protected $casts = ['due_date' => 'date'];

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ComplianceRequirement::class, 'requirement_id');
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>=', now()->toDateString());
    }
}
