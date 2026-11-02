<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'tenant_id','journal_entry_id','account_id','line_number','description',
        'debit_amount','credit_amount','currency_code','exchange_rate','is_reconciled',
    ];

    protected $casts = ['is_reconciled' => 'boolean'];

    // DB columns: chart_of_account_id, debit, credit, line_order — map model-conventional names
    public function getDebitAmountAttribute(): float   { return (float)($this->attributes['debit'] ?? 0); }
    public function setDebitAmountAttribute($v): void  { $this->attributes['debit'] = $v; }
    public function getCreditAmountAttribute(): float  { return (float)($this->attributes['credit'] ?? 0); }
    public function setCreditAmountAttribute($v): void { $this->attributes['credit'] = $v; }
    public function getAccountIdAttribute(): int|null  { return $this->attributes['chart_of_account_id'] ?? null; }
    public function setAccountIdAttribute($v): void    { $this->attributes['chart_of_account_id'] = $v; }
    public function getLineNumberAttribute(): int      { return (int)($this->attributes['line_order'] ?? 0); }
    public function setLineNumberAttribute($v): void   { $this->attributes['line_order'] = $v; }
    // columns that don't exist in DB — safe no-ops
    public function setTenantIdAttribute($v): void     {}
    public function setCurrencyCodeAttribute($v): void {}
    public function setExchangeRateAttribute($v): void {}
    public function setIsReconciledAttribute($v): void {}

    public function entry()   { return $this->belongsTo(JournalEntry::class, 'journal_entry_id'); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id'); }
}
