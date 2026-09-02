<?php
// app/Models/OtcDealLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtcDealLine extends Model
{
    protected $fillable = [
        'otc_deal_id', 'inventory_item_id', 'metal_type', 'description',
        'purity', 'gross_weight_grams', 'quantity_grams', 'pcs',
        'unit_price', 'line_subtotal', 'line_order',
    ];

    protected $casts = [
        'purity'             => 'decimal:3',
        'gross_weight_grams' => 'decimal:4',
        'quantity_grams'     => 'decimal:4',
        'unit_price'         => 'decimal:6',
        'line_subtotal'      => 'decimal:2',
    ];

    public function deal(): BelongsTo          { return $this->belongsTo(OtcDeal::class, 'otc_deal_id'); }
    public function inventoryItem(): BelongsTo { return $this->belongsTo(InventoryItem::class); }
}
