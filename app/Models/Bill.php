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

    protected $casts = ['is_deleted' => 'boolean'];

    // DB columns are `vat_total` and `total`; expose the model-conventional names as aliases
    public function getTotalVatAmountAttribute(): float { return (float)($this->attributes['vat_total'] ?? 0); }
    public function setTotalVatAmountAttribute(float $v): void { $this->attributes['vat_total'] = $v; }
    public function getTotalAmountAttribute(): float { return (float)($this->attributes['total'] ?? 0); }
    public function setTotalAmountAttribute(float $v): void { $this->attributes['total'] = $v; }

    public function tenant()       { return $this->belongsTo(Tenant::class); }
    public function supplier()     { return $this->belongsTo(Supplier::class); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
    public function lines()        { return $this->hasMany(BillLine::class); }
}
