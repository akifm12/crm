<?php
// app/Models/KycSubmission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycSubmission extends Model
{
    protected $fillable = [
        'tenant_id',
        'client_user_id',
        'full_name',
        'email',
        'phone',
        'nationality',
        'id_type',         // passport | emirates_id | trade_license
        'id_number',
        'documents',       // JSON: uploaded file paths
        'status',          // pending | under_review | approved | rejected
        'reviewer_notes',
        'reviewed_by',
        'reviewed_at',
        'metadata',        // JSON: any extra fields defined by tenant settings
    ];

    protected $casts = [
        'documents'   => 'array',
        'metadata'    => 'array',
        'reviewed_at' => 'datetime',
    ];

    const STATUS_PENDING      = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED     = 'approved';
    const STATUS_REJECTED     = 'rejected';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'client_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
