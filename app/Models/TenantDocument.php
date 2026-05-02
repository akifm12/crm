<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDocument extends Model
{
    protected $fillable = [
        'tenant_id', 'document_type', 'document_label',
        'file_path', 'file_name', 'mime_type', 'file_size',
        'expiry_date', 'notes', 'uploaded_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function tenant(): BelongsTo   { return $this->belongsTo(Tenant::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date
            && $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function fileSizeFormatted(): string
    {
        if (!$this->file_size) return '—';
        $kb = $this->file_size / 1024;
        return $kb > 1024 ? round($kb / 1024, 1) . ' MB' : round($kb, 0) . ' KB';
    }

    public static function companyDocTypes(): array
    {
        return [
            ['type' => 'dnfbp_registration',  'label' => 'DNFBP registration certificate',       'has_expiry' => true],
            ['type' => 'aml_policy',           'label' => 'AML/CFT policy & procedures',          'has_expiry' => false],
            ['type' => 'mlro_appointment',     'label' => 'MLRO appointment letter',              'has_expiry' => false],
            ['type' => 'risk_assessment',      'label' => 'Business-wide risk assessment',         'has_expiry' => false],
            ['type' => 'training_records',     'label' => 'AML training records',                 'has_expiry' => false],
            ['type' => 'trade_license',        'label' => 'Company trade licence',                'has_expiry' => true],
            ['type' => 'moa',                  'label' => 'Memorandum of Association',             'has_expiry' => false],
            ['type' => 'goaml_registration',   'label' => 'goAML system registration',            'has_expiry' => false],
            ['type' => 'cbuae_correspondence', 'label' => 'CBUAE / regulator correspondence',     'has_expiry' => false],
            ['type' => 'audit_report',         'label' => 'Internal/external audit report',       'has_expiry' => false],
            ['type' => 'other',                'label' => 'Other company document',               'has_expiry' => false],
        ];
    }
}
