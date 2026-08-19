<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandardInvoiceLine extends Model
{
    protected $fillable = [
        'standard_invoice_id', 'description', 'quantity', 'unit_price',
        'amount', 'vat_treatment', 'vat_rate', 'vat_amount', 'line_total', 'line_order',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'amount'     => 'float',
        'vat_rate'   => 'float',
        'vat_amount' => 'float',
        'line_total' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StandardInvoice::class, 'standard_invoice_id');
    }
}
