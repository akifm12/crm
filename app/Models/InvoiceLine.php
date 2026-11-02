<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id', 'inventory_item_id', 'chart_of_account_id', 'line_order',
        'description', 'quantity', 'unit', 'unit_price', 'line_amount',
        'vat_rate', 'vat_amount', 'total_amount', 'gold_weight_grams',
    ];

    protected $casts = [
        'quantity'           => 'float',
        'unit_price'         => 'float',
        'line_amount'        => 'float',
        'vat_rate'           => 'float',
        'vat_amount'         => 'float',
        'total_amount'       => 'float',
        'gold_weight_grams'  => 'float',
    ];

    public function invoice()       { return $this->belongsTo(Invoice::class); }
    public function item()          { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function account()       { return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id'); }
}
