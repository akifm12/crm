<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regulator extends Model
{
    protected $fillable = [
        'name', 'acronym', 'sector', 'description',
        'logo_url', 'website', 'jurisdiction', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function licenseActivities(): HasMany
    {
        return $this->hasMany(LicenseActivity::class, 'suggested_regulator_id');
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
