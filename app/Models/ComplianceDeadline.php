<?php
// app/Models/ComplianceDeadline.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceDeadline extends Model
{
    protected $table = 'public_compliance_deadlines';

    protected $fillable = [
        'title', 'description', 'sector', 'category', 'authority',
        'recurrence', 'next_due_date', 'source_url', 'sort_order',
    ];

    protected $casts = [
        'next_due_date' => 'date',
        'sort_order'    => 'integer',
    ];

    public function scopeForSector($query, ?string $sector)
    {
        return $query->when($sector, fn ($q) => $q->where(function ($q) use ($sector) {
            $q->where('sector', $sector)->orWhereNull('sector');
        }));
    }

    public function scopeOrdered($query)
    {
        return $query->orderByRaw('next_due_date IS NULL, next_due_date asc')->orderBy('sort_order');
    }

    public function userDeadlines(): HasMany
    {
        return $this->hasMany(UserDeadline::class);
    }

    public function categoryBadgeColor(): string
    {
        return match ($this->category) {
            'goaml'     => 'bg-red-100 text-red-700',
            'licensing' => 'bg-amber-100 text-amber-700',
            'screening' => 'bg-blue-100 text-blue-700',
            'training'  => 'bg-purple-100 text-purple-700',
            'reporting' => 'bg-emerald-100 text-emerald-700',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
