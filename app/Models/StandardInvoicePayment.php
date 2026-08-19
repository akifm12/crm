<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandardInvoicePayment extends Model
{
    protected $fillable = [
        'standard_invoice_id', 'tenant_id', 'payment_date',
        'amount', 'method', 'reference', 'journal_entry_id', 'created_by',
    ];

    protected $casts = ['payment_date' => 'date'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StandardInvoice::class, 'standard_invoice_id');
    }
}
