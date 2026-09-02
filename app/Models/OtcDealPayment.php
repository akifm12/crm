<?php
// app/Models/OtcDealPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtcDealPayment extends Model
{
    protected $fillable = [
        'otc_deal_id', 'tenant_id', 'payment_date', 'amount', 'direction',
        'method', 'reference', 'otc_journal_entry_id', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function deal(): BelongsTo      { return $this->belongsTo(OtcDeal::class, 'otc_deal_id'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(OtcJournalEntry::class, 'otc_journal_entry_id'); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
}
