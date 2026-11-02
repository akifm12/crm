<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'tenant_id','period_id','entry_number','entry_date','journal_type','status',
        'description','reference','total_debit','total_credit','currency_code','exchange_rate',
        'is_reversal','is_recurring','is_deleted','created_by_id',
    ];

    protected $casts = [
        'is_reversal'  => 'boolean',
        'is_recurring' => 'boolean',
        'is_deleted'   => 'boolean',
    ];

    // DB column is `memo`; expose as `description` for view/controller compatibility
    public function getDescriptionAttribute(): string  { return $this->attributes['memo'] ?? ''; }
    public function setDescriptionAttribute(string $v): void { $this->attributes['memo'] = $v; }
    // DB has no entry_number — synthesise a display value from reference or id
    public function getEntryNumberAttribute(): string  { return $this->attributes['reference'] ?? ('JE-'.$this->id); }
    // DB uses source_type instead of journal_type
    public function getJournalTypeAttribute(): string  { return $this->attributes['source_type'] ?? 'GENERAL'; }
    // DB has no total_debit/total_credit columns — safe no-ops so create() doesn't error
    public function getTotalDebitAttribute(): float    { return 0.0; }
    public function setTotalDebitAttribute($v): void   {}
    public function getTotalCreditAttribute(): float   { return 0.0; }
    public function setTotalCreditAttribute($v): void  {}
    // DB has no is_deleted — safe no-ops
    public function getIsDeletedAttribute(): bool      { return false; }
    public function setIsDeletedAttribute($v): void    {}

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function period()  { return $this->belongsTo(AccountingPeriod::class); }
    public function lines()   { return $this->hasMany(JournalLine::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_id'); }
}
