<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CrmEmployeeTraining extends Model
{
    protected $fillable = [
        'crm_client_id', 'employee_name', 'employee_role', 'training_type',
        'training_date', 'expiry_date', 'trainer', 'notes',
        'certificate_path', 'material_path', 'status',
        'certificate_template', 'signatory_name', 'signatory_title', 'public_token',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->public_token ??= Str::random(32);
        });
    }

    protected $casts = [
        'training_date' => 'date',
        'expiry_date'   => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(CrmClient::class, 'crm_client_id');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date && !$this->isExpired() && $this->expiry_date->lte(now()->addDays(60));
    }
}
