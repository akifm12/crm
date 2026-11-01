<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillLine extends Model
{
    protected $fillable = [
        'tenant_id','bill_id','account_id','line_number','description',
        'quantity','unit_price','line_amount','vat_rate','vat_amount','total_amount','is_reverse_charge',
    ];

    protected $casts = ['is_reverse_charge' => 'boolean'];

    public function bill()    { return $this->belongsTo(Bill::class); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
