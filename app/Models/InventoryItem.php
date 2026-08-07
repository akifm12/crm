<?php
// app/Models/InventoryItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryItem extends Model
{
    public const METAL_TYPES = ['gold', 'silver', 'platinum', 'palladium'];

    protected $fillable = [
        'tenant_id', 'sku', 'name', 'metal_type', 'purity', 'nominal_weight_grams', 'form',
        'chart_of_account_id', 'is_active',
    ];

    protected $casts = [
        'purity'                => 'decimal:3',
        'nominal_weight_grams'  => 'decimal:4',
        'is_active'             => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryStockMovement::class);
    }

    public function balance(): HasOne
    {
        return $this->hasOne(InventoryBalance::class);
    }
}
