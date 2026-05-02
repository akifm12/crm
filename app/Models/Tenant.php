<?php
// app/Models/Tenant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo_url', 'primary_color',
        'contact_email', 'phone', 'address',
        'trade_license_no', 'dnfbp_reg_no',
        'mlro_name', 'mlro_email', 'mlro_phone',
        'is_active', 'settings',
    ];

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
