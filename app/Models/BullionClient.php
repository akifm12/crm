<?php
// app/Models/BullionClient.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class BullionClient extends Model
{
    protected $fillable = [
        'tenant_id', 'client_type', 'company_name', 'trade_license_no',
        'trade_license_issue', 'trade_license_expiry', 'trn_number', 'ejari_number',
        'legal_form', 'country_of_incorporation', 'business_activity', 'nature_of_business',
        'registered_address', 'operating_address', 'phone', 'email', 'website',
        'full_name', 'name_arabic', 'nationality', 'dob', 'passport_number', 'passport_expiry',
        'eid_number', 'eid_expiry', 'occupation', 'employer_name', 'pep_status', 'pep_details',
        'source_of_funds', 'source_of_funds_other', 'source_of_wealth', 'source_of_wealth_other',
        'purpose_of_relationship', 'expected_monthly_volume', 'expected_monthly_frequency',
        'countries_involved', 'cdd_type', 'risk_rating', 'next_review_date', 'risk_notes',
        'status', 'screening_status', 'screening_date', 'screening_reference', 'screening_result',
        'decl_pep', 'decl_supply_chain', 'decl_cahra', 'decl_source_of_funds',
        'decl_sanctions', 'decl_ubo', 'decl_master_signed', 'master_declaration_path',
        'created_by', 'risk_assessment_data', 'risk_assessed_at', 'risk_assessed_by',
    ];

    protected $casts = [
        'trade_license_issue'   => 'date',
        'trade_license_expiry'  => 'date',
        'dob'                   => 'date',
        'passport_expiry'       => 'date',
        'eid_expiry'            => 'date',
        'next_review_date'      => 'date',
        'screening_date'        => 'datetime',
        'source_of_funds'       => 'array',
        'source_of_wealth'      => 'array',
        'countries_involved'    => 'array',
        'screening_result'      => 'array',
        'pep_status'            => 'boolean',
        'decl_pep'              => 'boolean',
        'decl_supply_chain'     => 'boolean',
        'decl_cahra'            => 'boolean',
        'decl_source_of_funds'  => 'boolean',
        'decl_sanctions'        => 'boolean',
        'decl_ubo'              => 'boolean',
        'decl_master_signed'    => 'boolean',
        'risk_assessment_data'  => 'array',
        'risk_assessed_at'      => 'datetime',
    ];

    public function tenant(): BelongsTo    { return $this->belongsTo(Tenant::class); }
    public function signatories(): HasMany { return $this->hasMany(ClientSignatory::class); }
    public function shareholders(): HasMany{ return $this->hasMany(ClientShareholder::class); }
    public function ubos(): HasMany        { return $this->hasMany(ClientUbo::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }

    // ── Display helpers ────────────────────────────────────────────────────

    public function displayName(): string
    {
        return $this->client_type === 'individual'
            ? ($this->full_name ?? 'Unnamed Individual')
            : ($this->company_name ?? 'Unnamed Company');
    }

    public function riskBadgeColor(): string
    {
        return match($this->risk_rating) {
            'high'   => 'bg-red-100 text-red-700',
            'medium' => 'bg-amber-100 text-amber-700',
            'low'    => 'bg-green-100 text-green-700',
            default  => 'bg-gray-100 text-gray-500',
        };
    }

    public function statusBadgeColor(): string
    {
        return match($this->status) {
            'active'    => 'bg-green-100 text-green-700',
            'pending'   => 'bg-amber-100 text-amber-700',
            'suspended' => 'bg-red-100 text-red-700',
            'inactive'  => 'bg-gray-100 text-gray-500',
            default     => 'bg-gray-100 text-gray-500',
        };
    }

    public function isLicenseExpiringSoon(): bool
    {
        if (!$this->trade_license_expiry) return false;
        return $this->trade_license_expiry->diffInDays(now()) <= 30
            && $this->trade_license_expiry->isFuture();
    }

    public function isLicenseExpired(): bool
    {
        if (!$this->trade_license_expiry) return false;
        return $this->trade_license_expiry->isPast();
    }

    public function isReviewDue(): bool
    {
        if (!$this->next_review_date) return false;
        return $this->next_review_date->isPast()
            || $this->next_review_date->diffInDays(now()) <= 30;
    }

    public function allDeclarationsSigned(): bool
    {
        return $this->decl_pep && $this->decl_supply_chain && $this->decl_cahra
            && $this->decl_source_of_funds && $this->decl_sanctions && $this->decl_ubo;
    }
}
