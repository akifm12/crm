<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'tenant_id','code','name','name_arabic','trn',
        'is_vat_registered','is_foreign_vendor','email','phone','contact_person',
        'address_line1','city','emirate','country','payment_terms_days','currency_code',
        'ap_account_id','bank_name','bank_account_no','iban','swift_code',
        'is_active','is_deleted','notes',
    ];

    protected $casts = [
        'is_vat_registered' => 'boolean',
        'is_foreign_vendor' => 'boolean',
        'is_active'         => 'boolean',
        'is_deleted'        => 'boolean',
    ];

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function apAccount() { return $this->belongsTo(ChartOfAccount::class, 'ap_account_id'); }
    public function bills()     { return $this->hasMany(Bill::class); }
}
