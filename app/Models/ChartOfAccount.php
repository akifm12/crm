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

    // The DB column is `type`; expose it as `account_type` for view compatibility
    public function getAccountTypeAttribute(): string { return $this->attributes['type'] ?? ''; }
    public function setAccountTypeAttribute(string $v): void { $this->attributes['type'] = $v; }
    // DB has `subtype` not `account_sub_type`
    public function getAccountSubTypeAttribute(): ?string { return $this->attributes['subtype'] ?? null; }
    public function setAccountSubTypeAttribute($v): void { $this->attributes['subtype'] = $v; }
    // Boolean flags that don't exist in DB — no-ops (DB only has is_system/is_active)
    public function getIsPayableAttribute(): bool { return false; }
    public function setIsPayableAttribute($v): void {}
    public function getIsBankAttribute(): bool { return false; }
    public function setIsBankAttribute($v): void {}
    public function getIsCashAttribute(): bool { return false; }
    public function setIsCashAttribute($v): void {}
    public function getIsReceivableAttribute(): bool { return false; }
    public function setIsReceivableAttribute($v): void {}
    public function getIsVatAccountAttribute(): bool { return false; }
    public function setIsVatAccountAttribute($v): void {}
    public function getIsControlAttribute(): bool { return false; }
    public function setIsControlAttribute($v): void {}
    // Other missing columns — no-ops
    public function setCurrencyCodeAttribute($v): void {}
    public function setNotesAttribute($v): void {}
    public function setOpeningBalanceAttribute($v): void {}
    public function setOpeningBalanceDateAttribute($v): void {}
    public function setNameArabicAttribute($v): void {}
}
