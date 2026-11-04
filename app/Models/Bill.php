<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'tenant_id','supplier_id','period_id','journal_entry_id','bill_number','vendor_ref',
        'bill_type','bill_date','due_date','status','currency_code','exchange_rate',
        'vendor_name','vendor_trn','subtotal','discount_amount','total_vat_amount',
        'total_amount','amount_paid','amount_due','is_deleted','notes','attachment_path',
    ];

    // DB columns vs model names — map everything through accessors/mutators

    // status: DB stores lowercase; views expect uppercase
    public function getStatusAttribute(): string { return strtoupper($this->attributes['status'] ?? 'DRAFT'); }
    public function setStatusAttribute(string $v): void { $this->attributes['status'] = strtolower($v); }

    // vat_total / total → total_vat_amount / total_amount
    public function getTotalVatAmountAttribute(): float { return (float)($this->attributes['vat_total'] ?? 0); }
    public function setTotalVatAmountAttribute(float $v): void { $this->attributes['vat_total'] = $v; }
    public function getTotalAmountAttribute(): float { return (float)($this->attributes['total'] ?? 0); }
    public function setTotalAmountAttribute(float $v): void { $this->attributes['total'] = $v; }

    // supplier_name → vendor_name
    public function getVendorNameAttribute(): string { return $this->attributes['supplier_name'] ?? ''; }
    public function setVendorNameAttribute(string $v): void { $this->attributes['supplier_name'] = $v; }

    // supplier_vat_number → vendor_trn
    public function getVendorTrnAttribute(): ?string { return $this->attributes['supplier_vat_number'] ?? null; }
    public function setVendorTrnAttribute($v): void { $this->attributes['supplier_vat_number'] = $v; }

    // reference → vendor_ref
    public function getVendorRefAttribute(): ?string { return $this->attributes['reference'] ?? null; }
    public function setVendorRefAttribute($v): void { $this->attributes['reference'] = $v; }

    // amount_due: computed (not a DB column)
    public function getAmountDueAttribute(): float { return max(0.0, $this->getTotalAmountAttribute() - (float)($this->attributes['amount_paid'] ?? 0)); }
    public function setAmountDueAttribute($v): void {}

    // columns that don't exist in DB — safe no-ops
    public function getIsDeletedAttribute(): bool { return false; }
    public function setIsDeletedAttribute($v): void {}
    public function getPeriodIdAttribute(): ?int { return null; }
    public function setPeriodIdAttribute($v): void {}
    public function getBillTypeAttribute(): string { return 'TAX_INVOICE'; }
    public function setBillTypeAttribute($v): void {}
    public function getDiscountAmountAttribute(): float { return 0.0; }
    public function setDiscountAmountAttribute($v): void {}
    public function getCurrencyCodeAttribute(): string { return $this->attributes['currency_code'] ?? 'AED'; }
    public function setCurrencyCodeAttribute($v): void {}
    public function getExchangeRateAttribute(): float { return 1.0; }
    public function setExchangeRateAttribute($v): void {}

    public function tenant()       { return $this->belongsTo(Tenant::class); }
    public function supplier()     { return $this->belongsTo(Supplier::class); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
    public function lines()        { return $this->hasMany(BillLine::class); }
}
