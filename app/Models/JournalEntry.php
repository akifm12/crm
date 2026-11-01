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

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function period()  { return $this->belongsTo(AccountingPeriod::class); }
    public function lines()   { return $this->hasMany(JournalLine::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_id'); }
}
