<?php

// app/Models/InventoryStockMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockMovement extends Model
{
    protected $fillable = [
        'tenant_id', 'inventory_item_id', 'movement_type', 'quantity_grams', 'pieces',
        'unit_cost', 'total_cost', 'source_type', 'source_id',
        'journal_entry_id', 'moved_at', 'notes',
    ];

    protected $casts = [
        'quantity_grams' => 'decimal:3',
        'pieces' => 'integer',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'moved_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
