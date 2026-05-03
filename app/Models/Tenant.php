<?php
// app/Models/Tenant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'business_type', 'logo_url', 'primary_color',
        'contact_email', 'phone', 'address',
        'trade_license_no', 'dnfbp_reg_no',
        'mlro_name', 'mlro_email', 'mlro_phone',
        'is_active', 'settings',
    ];

    public function sectorConfig(): array
    {
        return \App\Support\SectorConfig::get($this->business_type ?? 'gold');
    }

    public function sectorLabel(): string
    {
        return \App\Support\SectorConfig::sectors()[$this->business_type ?? 'gold'] ?? 'DNFBP';
    }

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TenantDocument::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    /**
     * Helper: get the public-facing portal URL for this tenant.
     */
    public function portalUrl(): string
    {
        return 'https://bluearrow.ae/' . $this->slug;
    }
}
