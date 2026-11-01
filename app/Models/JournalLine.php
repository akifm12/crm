<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $fillable = [
        'tenant_id','journal_entry_id','account_id','line_number','description',
        'debit_amount','credit_amount','currency_code','exchange_rate','is_reconciled',
    ];

    protected $casts = ['is_reconciled' => 'boolean'];

    public function entry()   { return $this->belongsTo(JournalEntry::class, 'journal_entry_id'); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
