<?php
// app/Models/OtcDealLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtcDealLine extends Model
{
    public const LINE_TYPES = ['metal_in', 'metal_out', 'cash_topup', 'other'];

    protected $fillable = [
        'otc_deal_id', 'line_type', 'inventory_item_id', 'metal_type', 'description',
        'purity', 'gross_weight_grams', 'quantity_grams', 'pcs',
        'unit_price', 'making_charge_rate', 'making_charge_amount',
        'line_subtotal', 'line_total', 'line_order',
    ];

    protected $casts = [
        'purity'                => 'decimal:3',
        'gross_weight_grams'    => 'decimal:4',
        'quantity_grams'        => 'decimal:4',
        'unit_price'            => 'decimal:6',
        'making_charge_rate'    => 'decimal:4',
        'making_charge_amount'  => 'decimal:2',
        'line_subtotal'         => 'decimal:2',
        'line_total'            => 'decimal:2',
    ];

    public function deal(): BelongsTo          { return $this->belongsTo(OtcDeal::class, 'otc_deal_id'); }
    public function inventoryItem(): BelongsTo { return $this->belongsTo(InventoryItem::class); }
}
