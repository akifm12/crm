<?php
// app/Models/ClientDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClientDocument extends Model
{
    protected $fillable = [
        'bullion_client_id', 'tenant_id', 'document_type', 'document_label',
        'file_path', 'file_name', 'mime_type', 'file_size',
        'expiry_date', 'is_required', 'notes', 'uploaded_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_required' => 'boolean',
    ];

    public function client(): BelongsTo   { return $this->belongsTo(BullionClient::class, 'bullion_client_id'); }
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

    public function downloadUrl(): string
    {
        return route('tenant.docs.download', [
            app('tenant')->slug,
            $this->id,
        ]);
    }

    // ── Document type definitions ─────────────────────────────────────────

    public static function corporateDocTypes(): array
    {
        return [
            ['type' => 'trade_license',       'label' => 'Trade licence',                    'required' => true,  'has_expiry' => true],
            ['type' => 'moa',                 'label' => 'Memorandum of Association (MoA)',   'required' => true,  'has_expiry' => false],
            ['type' => 'certificate_incorp',  'label' => 'Certificate of incorporation',      'required' => true,  'has_expiry' => false],
            ['type' => 'signatory_passport',  'label' => 'Authorised signatory passport',     'required' => true,  'has_expiry' => true],
            ['type' => 'signatory_eid',       'label' => 'Authorised signatory Emirates ID',  'required' => false, 'has_expiry' => true],
            ['type' => 'shareholder_passport','label' => 'Shareholder passport(s)',            'required' => true,  'has_expiry' => true],
            ['type' => 'ubo_passport',        'label' => 'UBO passport(s)',                   'required' => false, 'has_expiry' => true],
            ['type' => 'source_of_funds',     'label' => 'Source of funds evidence',          'required' => false, 'has_expiry' => false],
            ['type' => 'bank_statement',      'label' => 'Bank statement (3 months)',          'required' => false, 'has_expiry' => false],
            ['type' => 'other',               'label' => 'Other / additional document',        'required' => false, 'has_expiry' => false],
        ];
    }

    public static function individualDocTypes(): array
    {
        return [
            ['type' => 'passport',        'label' => 'Passport',                        'required' => true,  'has_expiry' => true],
            ['type' => 'eid',             'label' => 'Emirates ID',                     'required' => false, 'has_expiry' => true],
            ['type' => 'proof_of_address','label' => 'Proof of address',                'required' => false, 'has_expiry' => false],
            ['type' => 'source_of_funds', 'label' => 'Source of funds evidence',        'required' => false, 'has_expiry' => false],
            ['type' => 'bank_statement',  'label' => 'Bank statement (3 months)',        'required' => false, 'has_expiry' => false],
            ['type' => 'other',           'label' => 'Other / additional document',      'required' => false, 'has_expiry' => false],
        ];
    }
}
