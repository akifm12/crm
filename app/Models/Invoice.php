<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const TYPES = ['sale', 'purchase', 'exchange'];
    public const PRICING_TYPES = ['fixed', 'unfixed'];

    protected $fillable = [
        'tenant_id', 'bullion_client_id', 'invoice_number', 'invoice_type', 'status',
        'invoice_date', 'currency_code', 'exchange_rate', 'subtotal', 'vat_total', 'total',
        'amount_paid', 'journal_entry_id', 'client_transaction_id', 'notes', 'created_by',
        'posted_at', 'voided_at', 'pricing_type', 'gold_rate_per_oz', 'gold_rate_per_gram',
        'usd_aed_rate', 'metal_rates', 'party_reference', 'premium_amount', 'discount_amount', 'fixed_at', 'fixed_by',
    ];

    protected $casts = [
        'invoice_date'       => 'date',
        'exchange_rate'      => 'decimal:6',
        'subtotal'           => 'decimal:2',
        'vat_total'          => 'decimal:2',
        'total'              => 'decimal:2',
        'amount_paid'        => 'decimal:2',
        'posted_at'          => 'datetime',
        'voided_at'          => 'datetime',
        'gold_rate_per_oz'   => 'decimal:4',
        'gold_rate_per_gram' => 'decimal:6',
        'usd_aed_rate'       => 'decimal:4',
        'metal_rates'        => 'array',
        'premium_amount'     => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'fixed_at'           => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(BullionClient::class, 'bullion_client_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function clientTransaction(): BelongsTo
    {
        return $this->belongsTo(ClientTransaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fixedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fixed_by');
    }

    public function isFullyPaid(): bool
    {
        return (float) $this->amount_paid >= (float) $this->total;
    }

    public function outstandingBalance(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }
}
