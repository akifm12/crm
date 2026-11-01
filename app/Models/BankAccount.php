<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'tenant_id', 'account_name', 'bank_name', 'account_number',
        'currency', 'chart_of_account_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant()       { return $this->belongsTo(Tenant::class); }
    public function chartAccount() { return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id'); }
    public function transactions()  { return $this->hasMany(BankTransaction::class); }

    public function currentBalance(): float
    {
        return (float) $this->transactions()
            ->selectRaw('SUM(CASE WHEN transaction_type = "credit" THEN amount ELSE -amount END) as bal')
            ->value('bal') ?? 0;
    }
}
