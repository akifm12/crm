<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'tenant_id','parent_id','code','name','name_arabic','description',
        'account_type','account_sub_type','normal_balance',
        'is_control','is_bank','is_cash','is_receivable','is_payable','is_vat_account',
        'currency_code','is_active','notes','opening_balance','opening_balance_date',
    ];

    protected $casts = [
        'is_control'    => 'boolean',
        'is_bank'       => 'boolean',
        'is_cash'       => 'boolean',
        'is_receivable' => 'boolean',
        'is_payable'    => 'boolean',
        'is_vat_account'=> 'boolean',
        'is_active'     => 'boolean',
    ];

    public function tenant()   { return $this->belongsTo(Tenant::class); }
    public function parent()   { return $this->belongsTo(ChartOfAccount::class, 'parent_id'); }
    public function children() { return $this->hasMany(ChartOfAccount::class, 'parent_id'); }
}
