<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenseActivity extends Model
{
    protected $fillable = [
        'name', 'description', 'sector',
        'suggested_regulator_id', 'additional_regulator_ids', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_regulator_ids' => 'array',
    ];

    public function suggestedRegulator(): BelongsTo
    {
        return $this->belongsTo(Regulator::class, 'suggested_regulator_id');
    }

    public function complianceRequirements(): HasMany
    {
        return $this->hasMany(ComplianceRequirement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
