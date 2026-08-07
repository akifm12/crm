<?php
// app/Models/InvoiceLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    public const LINE_TYPES = ['metal_out', 'metal_in', 'cash_topup', 'other'];
    public const VAT_TREATMENTS = ['standard', 'zero_rated', 'reverse_charge', 'exempt'];

    protected $fillable = [
        'invoice_id', 'line_type', 'inventory_item_id', 'description', 'purity',
        'quantity_grams', 'gross_weight_grams', 'pcs', 'unit_price', 'line_subtotal',
        'metal_vat_treatment', 'metal_vat_rate', 'metal_vat_amount',
        'making_charge_rate', 'making_charge_amount',
        'making_vat_treatment', 'making_vat_rate', 'making_vat_amount',
        'line_total', 'line_order',
    ];

    protected $casts = [
        'purity'                => 'decimal:3',
        'quantity_grams'        => 'decimal:3',
        'gross_weight_grams'    => 'decimal:3',
        'pcs'                   => 'integer',
        'unit_price'            => 'decimal:4',
        'line_subtotal'         => 'decimal:2',
        'metal_vat_rate'        => 'decimal:2',
        'metal_vat_amount'      => 'decimal:2',
        'making_charge_rate'    => 'decimal:4',
        'making_charge_amount'  => 'decimal:2',
        'making_vat_rate'       => 'decimal:2',
        'making_vat_amount'     => 'decimal:2',
        'line_total'            => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
