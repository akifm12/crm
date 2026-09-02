<?php
// app/Models/OtcJournalEntryLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtcJournalEntryLine extends Model
{
    protected $fillable = [
        'otc_journal_entry_id', 'otc_chart_of_account_id', 'debit', 'credit',
        'base_debit', 'base_credit', 'description', 'line_order',
    ];

    protected $casts = [
        'debit'       => 'decimal:2',
        'credit'      => 'decimal:2',
        'base_debit'  => 'decimal:2',
        'base_credit' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(OtcJournalEntry::class, 'otc_journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(OtcChartOfAccount::class, 'otc_chart_of_account_id');
    }
}
