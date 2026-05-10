<?php
// app/Models/ClientTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientTransaction extends Model
{
    protected $fillable = [
        'bullion_client_id', 'tenant_id', 'visit_date',
        'invoice_number', 'invoice_amount', 'transaction_type',
        'notes', 'created_by',
    ];

    protected $casts = [
        'visit_date'       => 'date',
        'invoice_amount'   => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(BullionClient::class, 'bullion_client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
