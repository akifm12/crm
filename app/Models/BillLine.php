<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillLine extends Model
{
    protected $fillable = [
        'bill_id', 'description', 'category',
        'amount', 'vat_treatment', 'vat_rate', 'vat_amount', 'line_total', 'line_order',
    ];

    protected $casts = [
        'amount'     => 'float',
        'vat_rate'   => 'float',
        'vat_amount' => 'float',
        'line_total' => 'float',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
