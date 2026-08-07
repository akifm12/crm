<?php
// app/Models/TenantInvoiceSequence.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInvoiceSequence extends Model
{
    protected $fillable = [
        'tenant_id', 'invoice_type', 'prefix', 'next_number', 'year_reset', 'current_year',
    ];

    protected $casts = [
        'year_reset' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
