<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillLine extends Model
{
    protected $fillable = [
        'tenant_id','bill_id','account_id','line_number','description',
        'quantity','unit_price','line_amount','vat_rate','vat_amount','total_amount','is_reverse_charge',
    ];

    // DB columns vs model names — map through accessors/mutators
    // DB: amount, line_total, line_order, category — no account_id/quantity/unit_price/tenant_id

    public function getLineAmountAttribute(): float { return (float)($this->attributes['amount'] ?? 0); }
    public function setLineAmountAttribute($v): void { $this->attributes['amount'] = $v; }
    public function getTotalAmountAttribute(): float { return (float)($this->attributes['line_total'] ?? $this->attributes['amount'] ?? 0); }
    public function setTotalAmountAttribute($v): void { $this->attributes['line_total'] = $v; }
    public function getLineNumberAttribute(): int { return (int)($this->attributes['line_order'] ?? 0); }
    public function setLineNumberAttribute($v): void { $this->attributes['line_order'] = $v; }
    public function getUnitPriceAttribute(): float { return (float)($this->attributes['amount'] ?? 0); }
    public function setUnitPriceAttribute($v): void {}
    public function getQuantityAttribute(): float { return 1.0; }
    public function setQuantityAttribute($v): void {}
    public function getAccountIdAttribute(): ?int { return null; }
    public function setAccountIdAttribute($v): void {}
    public function setTenantIdAttribute($v): void {}
    public function setIsReverseChargeAttribute($v): void {}

    public function bill() { return $this->belongsTo(Bill::class); }
    public function account() { return null; }
}
