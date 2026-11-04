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

    // DB columns vs model names — map through accessors/mutators
    // DB: name, vat_number, contact_name, address — no trn/code/is_deleted/ap_account_id etc.

    public function getTrnAttribute(): ?string { return $this->attributes['vat_number'] ?? null; }
    public function setTrnAttribute($v): void { $this->attributes['vat_number'] = $v; }
    public function getContactPersonAttribute(): ?string { return $this->attributes['contact_name'] ?? null; }
    public function setContactPersonAttribute($v): void { $this->attributes['contact_name'] = $v; }
    public function getAddressLine1Attribute(): ?string { return $this->attributes['address'] ?? null; }
    public function setAddressLine1Attribute($v): void { $this->attributes['address'] = $v; }

    // columns that don't exist in DB — no-ops
    public function getIsDeletedAttribute(): bool { return false; }
    public function setIsDeletedAttribute($v): void {}
    public function setCodeAttribute($v): void {}
    public function setNameArabicAttribute($v): void {}
    public function getIsVatRegisteredAttribute(): bool { return false; }
    public function setIsVatRegisteredAttribute($v): void {}
    public function getIsForeignVendorAttribute(): bool { return false; }
    public function setIsForeignVendorAttribute($v): void {}
    public function setCityAttribute($v): void {}
    public function setEmirateAttribute($v): void {}
    public function setCountryAttribute($v): void {}
    public function getPaymentTermsDaysAttribute(): int { return 30; }
    public function setPaymentTermsDaysAttribute($v): void {}
    public function setCurrencyCodeAttribute($v): void {}
    public function getApAccountIdAttribute(): ?int { return null; }
    public function setApAccountIdAttribute($v): void {}
    public function setBankNameAttribute($v): void {}
    public function setBankAccountNoAttribute($v): void {}
    public function setIbanAttribute($v): void {}
    public function setSwiftCodeAttribute($v): void {}

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function apAccount() { return null; }
    public function bills()     { return $this->hasMany(Bill::class); }
}
