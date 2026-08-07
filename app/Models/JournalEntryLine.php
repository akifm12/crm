<?php
// app/Models/JournalEntryLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id', 'chart_of_account_id', 'debit', 'credit',
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
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
