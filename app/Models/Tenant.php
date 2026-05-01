<?php
// app/Models/Tenant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',         // e.g. "Acme Real Estate"
        'slug',         // e.g. "acme"  → bluearrow.ae/acme
        'logo_url',
        'primary_color',// hex for branding
        'contact_email',
        'is_active',
        'settings',     // JSON: custom fields, required docs, etc.
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class);
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
