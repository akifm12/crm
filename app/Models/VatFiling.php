<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatFiling extends Model
{
    protected $fillable = [
        'tenant_id', 'period_from', 'period_to', 'due_date',
        'standard_rated_sales', 'vat_on_sales', 'standard_rated_purchases', 'vat_on_purchases',
        'net_vat_payable', 'box_values', 'status', 'submitted_at', 'paid_at', 'reference', 'notes',
    ];

    protected $casts = [
        'period_from'               => 'date',
        'period_to'                 => 'date',
        'due_date'                  => 'date',
        'submitted_at'              => 'datetime',
        'paid_at'                   => 'datetime',
        'box_values'                => 'array',
        'standard_rated_sales'      => 'decimal:4',
        'vat_on_sales'              => 'decimal:4',
        'standard_rated_purchases'  => 'decimal:4',
        'vat_on_purchases'          => 'decimal:4',
        'net_vat_payable'           => 'decimal:4',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }

    public function isSubmitted(): bool { return $this->status !== 'draft'; }
    public function isPaid(): bool      { return $this->status === 'paid'; }
}
