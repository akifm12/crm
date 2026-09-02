<?php
// app/Models/OtcDeal.php — a generic, non-VAT B-Book deal. Not a tax invoice.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtcDeal extends Model
{
    public const TYPES = ['buy', 'sell', 'exchange'];

    protected $fillable = [
        'tenant_id', 'bullion_client_id', 'deal_number', 'deal_type', 'counterparty_name',
        'deal_date', 'status', 'currency_code', 'exchange_rate', 'metal_rates',
        'subtotal', 'total', 'amount_paid', 'notes',
        'otc_journal_entry_id', 'created_by', 'posted_at', 'voided_at',
    ];

    protected $casts = [
        'deal_date'    => 'date',
        'metal_rates'  => 'array',
        'posted_at'    => 'datetime',
        'voided_at'    => 'datetime',
    ];

    public function tenant(): BelongsTo    { return $this->belongsTo(Tenant::class); }
    public function client(): BelongsTo    { return $this->belongsTo(BullionClient::class, 'bullion_client_id'); }
    public function lines(): HasMany       { return $this->hasMany(OtcDealLine::class)->orderBy('line_order'); }
    public function payments(): HasMany    { return $this->hasMany(OtcDealPayment::class)->orderBy('payment_date'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(OtcJournalEntry::class, 'otc_journal_entry_id'); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }

    public function isFullyPaid(): bool
    {
        return $this->status === 'posted' && (float) $this->amount_paid >= (float) $this->total;
    }

    public function outstandingBalance(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }
}
