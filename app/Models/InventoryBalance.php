<?php

// app/Models/InventoryBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'inventory_item_id', 'quantity_grams', 'pieces',
        'weighted_avg_cost', 'total_value', 'updated_at',
    ];

    protected $casts = [
        'quantity_grams' => 'decimal:3',
        'pieces' => 'integer',
        'weighted_avg_cost' => 'decimal:4',
        'total_value' => 'decimal:2',
        'updated_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
