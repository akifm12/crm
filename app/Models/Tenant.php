<?php
// app/Models/Tenant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'crm_client_id', 'name', 'slug', 'business_type', 'logo_url', 'primary_color',
        'contact_email', 'phone', 'address',
        'trade_license_no', 'dnfbp_reg_no', 'vat_trn',
        'mlro_name', 'mlro_email', 'mlro_phone',
        'is_active', 'settings',
    ];

    /**
     * Top-level URL segments already claimed by literal routes (public
     * marketing site, auth, admin) or by legacy static folders served
     * directly by nginx — a tenant slug matching one of these would be
     * permanently unreachable at bluearrow.ae/{slug}, since the literal
     * route/folder always wins over the {slug} tenant catch-all.
     */
    public static function reservedSlugs(): array
    {
        return [
            // public marketing site (routes/web.php)
            'services', 'about', 'privacy', 'contact', 'compliance-calendar',
            'resources', 'news', 'technology', 'account', 'newsletter',
            // auth (routes/auth.php)
            'login', 'logout', 'register', 'forgot-password', 'reset-password',
            'confirm-password', 'verify-email', 'email', 'password',
            // admin portal (routes/web.php)
            'dashboard', 'crm', 'quotations', 'settings', 'marketing',
            'screening', 'whatsapp', 'accounting', 'kyc',
            // legacy static folders served directly by nginx, not Laravel
            'motiwala', 'clients',
        ];
    }

    public function sectorConfig(): array
    {
        return \App\Support\SectorConfig::get($this->business_type ?? 'gold');
    }

    public function sectorLabel(): string
    {
        return \App\Support\SectorConfig::sectors()[$this->business_type ?? 'gold'] ?? 'DNFBP';
    }

    public function hasModule(string $key): bool
    {
        return (bool) ($this->settings['enabled_modules'][$key] ?? false);
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
        return $this->hasMany(BullionClient::class);
    }

    /**
     * Helper: get the public-facing portal URL for this tenant.
     */
    public function portalUrl(): string
    {
        return 'https://bluearrow.ae/' . $this->slug;
    }
}