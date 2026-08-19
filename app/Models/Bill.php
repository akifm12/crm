<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    const CATEGORIES = [
        'salaries'         => 'Salaries & Wages',
        'rent'             => 'Rent',
        'utilities'        => 'Utilities',
        'internet'         => 'Internet & Telecom',
        'marketing'        => 'Marketing & Advertising',
        'professional_fees'=> 'Professional Fees',
        'insurance'        => 'Insurance',
        'travel'           => 'Travel & Transport',
        'office_supplies'  => 'Office Supplies',
        'other'            => 'Other',
    ];

    const VAT_TREATMENTS = ['standard', 'zero_rated', 'reverse_charge', 'exempt'];

    protected $fillable = [
        'tenant_id', 'bill_number', 'supplier_name', 'supplier_vat_number',
        'reference', 'bill_date', 'due_date', 'status',
        'subtotal', 'vat_total', 'total', 'amount_paid',
        'journal_entry_id', 'notes', 'created_by', 'posted_at', 'voided_at',
    ];

    protected $casts = [
        'bill_date'  => 'date',
        'due_date'   => 'date',
        'posted_at'  => 'datetime',
        'voided_at'  => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BillLine::class)->orderBy('line_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class)->orderBy('payment_date');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isFullyPaid(): bool
    {
        return $this->status === 'posted' && (float) $this->amount_paid >= (float) $this->total;
    }

    public function outstandingBalance(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'posted' && $this->due_date && $this->due_date->isPast() && ! $this->isFullyPaid();
    }
}
