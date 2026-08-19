<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardInvoice extends Model
{
    const VAT_TREATMENTS = ['standard', 'zero_rated', 'reverse_charge', 'exempt'];

    protected $fillable = [
        'tenant_id', 'module_type', 'invoice_number', 'client_name', 'client_vat_number',
        'reference', 'invoice_date', 'due_date', 'status',
        'subtotal', 'vat_total', 'total', 'amount_paid',
        'notes', 'journal_entry_id', 'created_by', 'posted_at', 'voided_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'posted_at'    => 'datetime',
        'voided_at'    => 'datetime',
    ];

    public function tenant(): BelongsTo   { return $this->belongsTo(Tenant::class); }
    public function lines(): HasMany      { return $this->hasMany(StandardInvoiceLine::class)->orderBy('line_order'); }
    public function payments(): HasMany   { return $this->hasMany(StandardInvoicePayment::class)->orderBy('payment_date'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }

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
