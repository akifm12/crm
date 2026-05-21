<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceRequirement extends Model
{
    protected $fillable = [
        'title', 'description', 'regulator_id', 'license_activity_id',
        'frequency', 'category', 'submission_channel', 'penalty_note', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function regulator(): BelongsTo
    {
        return $this->belongsTo(Regulator::class);
    }

    public function licenseActivity(): BelongsTo
    {
        return $this->belongsTo(LicenseActivity::class);
    }

    public function deadlines(): HasMany
    {
        return $this->hasMany(ComplianceDeadline::class, 'requirement_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
