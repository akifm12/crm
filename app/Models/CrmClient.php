<?php
// app/Models/CrmClient.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CrmClient extends Model
{
    protected $fillable = [
        'stage', 'status', 'company_name', 'license_number', 'license_issue',
        'license_expiry', 'license_authority', 'legal_status', 'country_inc',
        'regulator', 'ejari', 'trn', 'address', 'contact_person', 'telephone',
        'email', 'website', 'services', 'relationship_manager', 'client_since',
        'notes', 'tenant_id', 'portal_type', 'created_by', 'assigned_to',
    ];

    protected $casts = [
        'license_issue'  => 'date',
        'license_expiry' => 'date',
        'client_since'   => 'date',
        'services'       => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function tenant(): BelongsTo       { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo      { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee(): BelongsTo     { return $this->belongsTo(User::class, 'assigned_to'); }
    public function shareholders(): HasMany   { return $this->hasMany(CrmShareholder::class); }
    public function contacts(): HasMany       { return $this->hasMany(CrmContact::class); }
    public function documents(): HasMany      { return $this->hasMany(CrmDocument::class); }
    public function notes(): HasMany          { return $this->hasMany(CrmNote::class)->latest(); }
    public function tasks(): HasMany          { return $this->hasMany(CrmTask::class); }
    public function slas(): HasMany           { return $this->hasMany(CrmSla::class); }
    public function quotations(): HasMany     { return $this->hasMany(CrmQuotation::class); }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function stageBadgeColor(): string
    {
        return match($this->stage) {
            'lead'          => 'bg-gray-100 text-gray-600',
            'qualified'     => 'bg-blue-100 text-blue-700',
            'proposal_sent' => 'bg-purple-100 text-purple-700',
            'negotiation'   => 'bg-amber-100 text-amber-700',
            'onboarding'    => 'bg-orange-100 text-orange-700',
            'active'        => 'bg-green-100 text-green-700',
            'inactive'      => 'bg-red-100 text-red-700',
            default         => 'bg-gray-100 text-gray-500',
        };
    }

    public function stageLabel(): string
    {
        return match($this->stage) {
            'lead'          => 'Lead',
            'qualified'     => 'Qualified',
            'proposal_sent' => 'Proposal Sent',
            'negotiation'   => 'Negotiation',
            'onboarding'    => 'Onboarding',
            'active'        => 'Active',
            'inactive'      => 'Inactive',
            default         => ucfirst($this->stage),
        };
    }

    public function isLicenseExpired(): bool
    {
        return $this->license_expiry && $this->license_expiry->isPast();
    }

    public function isLicenseExpiringSoon(): bool
    {
        return $this->license_expiry
            && $this->license_expiry->isFuture()
            && $this->license_expiry->diffInDays(now()) <= 30;
    }

    public function pendingTasksCount(): int
    {
        return $this->tasks()->whereIn('status', ['pending', 'in_progress'])->count();
    }

    public function activeSla(): ?CrmSla
    {
        return $this->slas()->where('status', 'active')->latest()->first();
    }

    // ── Auto-generate tenant slug from company name ────────────────────────

    public static function generateSlug(string $companyName): string
    {
        $base = Str::slug(
            implode('-', array_slice(explode(' ', $companyName), 0, 3))
        );
        $slug = $base;
        $count = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count++;
        }
        return $slug;
    }

    // ── Data migration helper: import from old companies table ─────────────

    public static function importFromLegacy(array $row): self
    {
        return self::create([
            'company_name'     => $row['company_name'],
            'license_number'   => $row['license_number'],
            'license_issue'    => $row['license_issue'],
            'license_expiry'   => $row['license_expiry'],
            'license_authority'=> $row['license_authority'],
            'legal_status'     => $row['legal_status'],
            'country_inc'      => $row['country_inc'],
            'regulator'        => $row['regulator'],
            'ejari'            => $row['ejari'],
            'trn'              => $row['trn'],
            'address'          => $row['address'],
            'contact_person'   => $row['contact_person'],
            'telephone'        => $row['telephone'],
            'email'            => $row['email'],
            'website'          => $row['website'],
            'stage'            => $row['company_status'] === 'Active' ? 'active' : 'inactive',
            'status'           => $row['company_status'] === 'Active' ? 'active' : 'inactive',
        ]);
    }
}
