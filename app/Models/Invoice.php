<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id', 'client_id', 'invoice_number', 'invoice_date', 'due_date',
        'status', 'customer_name', 'customer_trn', 'customer_address',
        'subtotal', 'discount_amount', 'vat_amount', 'total_amount',
        'amount_paid', 'amount_due', 'currency_code', 'exchange_rate',
        'po_number', 'notes', 'journal_entry_id', 'is_deleted', 'created_by',
    ];

    protected $casts = [
        'invoice_date'    => 'date',
        'due_date'        => 'date',
        'subtotal'        => 'float',
        'discount_amount' => 'float',
        'amount_paid'     => 'float',
        'amount_due'      => 'float',
        'exchange_rate'   => 'float',
        'is_deleted'      => 'boolean',
    ];

    public function tenant()       { return $this->belongsTo(Tenant::class); }
    public function client()       { return $this->belongsTo(BullionClient::class, 'bullion_client_id'); }
    public function lines()        { return $this->hasMany(InvoiceLine::class)->orderBy('line_order'); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }

    // Bridge: existing table uses bullion_client_id, vat_total, total
    public function getClientIdAttribute()       { return $this->attributes['bullion_client_id'] ?? null; }
    public function setClientIdAttribute($v)     { $this->attributes['bullion_client_id'] = $v; }
    public function getVatAmountAttribute()      { return (float)($this->attributes['vat_total'] ?? 0); }
    public function setVatAmountAttribute($v)    { $this->attributes['vat_total'] = $v; }
    public function getTotalAmountAttribute()    { return (float)($this->attributes['total'] ?? 0); }
    public function setTotalAmountAttribute($v)  { $this->attributes['total'] = $v; }

    public function getStatusAttribute(): string { return strtoupper($this->attributes['status'] ?? 'DRAFT'); }
    public function setStatusAttribute(string $v): void { $this->attributes['status'] = strtolower($v); }

    public function statusBadge(): string
    {
        return match(strtolower($this->attributes['status'] ?? '')) {
            'draft'    => 'bg-gray-100 text-gray-600',
            'sent'     => 'bg-blue-50 text-blue-700',
            'approved' => 'bg-green-50 text-green-700',
            'paid'     => 'bg-emerald-50 text-emerald-700',
            'partial'  => 'bg-amber-50 text-amber-700',
            'void'     => 'bg-red-50 text-red-500',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    public static function nextNumber(int $tenantId, string $date): string
    {
        $year = date('Y', strtotime($date));
        $count = static::where('tenant_id', $tenantId)
            ->whereYear('invoice_date', $year)
            ->count() + 1;
        return 'INV-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
