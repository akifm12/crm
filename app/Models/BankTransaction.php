<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id', 'tenant_id', 'transaction_date', 'description',
        'amount', 'transaction_type', 'reference', 'journal_entry_id',
    ];

    protected $casts = ['transaction_date' => 'date', 'amount' => 'decimal:4'];

    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
}
